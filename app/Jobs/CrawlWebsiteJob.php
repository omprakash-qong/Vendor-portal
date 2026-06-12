<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Models\ImportJobLog;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CrawlWebsiteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 3600;   // large catalogues can take ~30 min to crawl fully
    public int $tries   = 1;

    public function __construct(private int $importJobId) {}

    public function handle(): void
    {
        $job = ImportJob::find($this->importJobId);
        if (!$job) return;

        $job->update(['status' => 'running', 'started_at' => now()]);
        $this->addLog($job, 'info', null, 'Crawl started for: ' . $job->website_url);

        $script = base_path('scripts/crawl_website.py');
        $python = $this->findPython();

        // Pass a temp file path for the JSON output — Crawl4AI writes progress
        // logs directly to fd 1 (stdout) via rich, so we can't trust stdout.
        // The script writes clean JSON to this file; PHP reads it back.
        $outFile    = sys_get_temp_dir() . '/crawl_out_' . $job->id . '.json';
        $stderrFile = sys_get_temp_dir() . '/crawl_job_' . $job->id . '.log';

        // Hand the crawler the app's known per-category field labels so it can
        // read div-based spec layouts and distinguish product pages from
        // navigation. Generic — driven entirely by config, no site specifics.
        $fieldsFile = sys_get_temp_dir() . '/crawl_fields_' . $job->id . '.json';
        $labels = [];
        foreach ((array) config('category_fields', []) as $cat => $fields) {
            $labels[$cat] = [];
            foreach ($fields as $key => $label) {
                $labels[$cat][$label] = $key;
            }
        }
        file_put_contents($fieldsFile, json_encode($labels, JSON_UNESCAPED_UNICODE));

        // Resume support: hand the crawler the product URLs this vendor already
        // has, so it skips them and only scrapes new ones.
        $skipFile = sys_get_temp_dir() . '/crawl_skip_' . $job->id . '.txt';
        $known = Product::where('vendor_profile_id', $job->vendor_profile_id)
            ->whereNotNull('catalogue_url')
            ->pluck('catalogue_url')
            ->implode("\n");
        file_put_contents($skipFile, $known);

        $cmd = $python
            . ' ' . escapeshellarg($script)
            . ' ' . escapeshellarg($job->website_url)
            . ' ' . escapeshellarg($outFile)
            . ' ' . escapeshellarg($fieldsFile)
            . ' ' . escapeshellarg($skipFile)
            . ' 2>' . escapeshellarg($stderrFile);

        // Stream the crawler's output so products are saved the moment they
        // are scraped — the catalogue fills up live while the rest imports.
        $mapper = app(\App\Services\Catalogue\CategorySpecMapper::class);
        $count  = 0;
        $seenNames = [];

        $handle = popen($cmd, 'r');
        if ($handle === false) {
            $job->update(['status' => 'failed', 'error_message' => 'Could not start crawler.', 'completed_at' => now()]);
            @unlink($fieldsFile);
            return;
        }

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) break;
            if (!str_starts_with($line, "PRODUCT\t")) continue;   // ignore noise

            $raw = json_decode(substr($line, 8), true);
            if (!is_array($raw) || empty($raw['product_name'])) continue;

            // Skip duplicates within this run.
            $nameKey = mb_strtolower(trim((string) $raw['product_name']));
            if (isset($seenNames[$nameKey])) continue;
            $seenNames[$nameKey] = true;

            if ($this->saveProduct($raw, $job, $mapper)) {
                $count++;
                // DEBUG: record the exact page the first product was scraped
                // from, so we can confirm the crawler traversed down to a real
                // product/listing page (not the homepage) for the typed URL.
                if ($count === 1 && !empty($raw['source_url'])) {
                    $this->addLog($job, 'info', $raw['source_url'],
                        '[DEBUG] First product "' . ($raw['product_name'] ?? '?') . '" scraped from: ' . $raw['source_url']);
                }
                // Update the live count so the UI can show progress.
                $job->update(['status' => 'running', 'products_found' => $count]);
            }
        }
        pclose($handle);

        // The crawler also writes a final summary file (pages, errors, and the
        // full product list as a fallback when nothing was streamed).
        $pagesCrawled = 0;
        $errors = [];
        if (file_exists($outFile) && filesize($outFile)) {
            $data = json_decode(file_get_contents($outFile), true);
            if (is_array($data)) {
                if (isset($data['error'])) {
                    $job->update(['status' => 'failed', 'error_message' => $data['error'], 'completed_at' => now()]);
                    @unlink($outFile);
                    @unlink($fieldsFile);
                    return;
                }
                $pagesCrawled = $data['pages_crawled'] ?? 0;
                $errors       = $data['errors'] ?? [];
                foreach ($errors as $err) {
                    $this->addLog($job, 'error', $err['url'] ?? null, $err['error'] ?? 'Unknown error');
                }
                // Fallback: nothing was streamed (legacy path) — save from file.
                if ($count === 0) {
                    foreach (($data['products'] ?? []) as $raw) {
                        if (empty($raw['product_name'])) continue;
                        $nameKey = mb_strtolower(trim((string) $raw['product_name']));
                        if (isset($seenNames[$nameKey])) continue;
                        $seenNames[$nameKey] = true;
                        if ($this->saveProduct($raw, $job, $mapper)) $count++;
                    }
                }
            }
        }
        @unlink($outFile);
        @unlink($fieldsFile);
        @unlink($skipFile);

        if ($count === 0) {
            $stderr = @file_get_contents($stderrFile) ?: '';
            // Drop invalid byte sequences, then truncate by CHARACTER (not byte)
            // so a multibyte symbol is never sliced in half — a half-character
            // would make MySQL reject the write (1366) and freeze the job.
            $stderr = mb_convert_encoding($stderr, 'UTF-8', 'UTF-8');
            $job->update([
                'status'        => 'failed',
                'error_message' => 'No products found. ' . mb_substr($stderr, 0, 150),
                'completed_at'  => now(),
            ]);
            return;
        }

        $job->update([
            'status'         => 'completed',
            'products_found' => $count,
            'pages_crawled'  => $pagesCrawled,
            'failed_pages'   => count($errors),
            'completed_at'   => now(),
        ]);

        $this->addLog($job, 'info', null, "Crawl complete. {$count} products found from {$pagesCrawled} pages.");

        Log::info('CrawlWebsiteJob complete', ['job' => $job->id, 'products' => $count, 'pages' => $pagesCrawled]);
    }

    public function failed(\Throwable $e): void
    {
        $job = ImportJob::find($this->importJobId);
        // A finished job must never be flipped back to failed (e.g. by a
        // stale duplicate attempt after a queue re-dispatch).
        if (!$job || $job->status === 'completed') return;
        // Sanitize + cap so a malformed/long message can't itself fail the
        // write (1366) and leave the job stuck on "running".
        $msg = mb_substr(mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8'), 0, 500);
        $job->update(['status' => 'failed', 'error_message' => $msg, 'completed_at' => now()]);
    }

    private function addLog(ImportJob $job, string $level, ?string $url, string $message): void
    {
        ImportJobLog::create([
            'import_job_id' => $job->id,
            'level'         => $level,
            'url'           => $url,
            'message'       => $message,
            'created_at'    => now(),
        ]);
    }

    /**
     * Persist one scraped product as an inactive (needs-review) catalogue
     * entry. Strict: only the planned per-category fields are stored —
     * unmapped scraped labels (documents tables, standards lists, marketing
     * rows) are discarded. One bad row must never fail the import.
     */
    private function saveProduct(array $raw, ImportJob $job, $mapper): bool
    {
        try {
            // Resumable import: skip products already in this vendor's catalogue
            // so repeated runs accumulate the rest instead of duplicating.
            $name = mb_substr((string) $raw['product_name'], 0, 255);

            // Defense in depth behind the crawler's own filters: a "name"
            // that reads like a marketing sentence or a page title is not a
            // product — never save it.
            if ((mb_strlen($name) > 40 && str_ends_with(rtrim($name), '.')) || str_contains($name, ' | ')) {
                $this->addLog($job, 'info', $raw['source_url'] ?? null, 'Skipped (not a product name): ' . $name);
                return false;
            }
            $exists = Product::where('vendor_profile_id', $job->vendor_profile_id)
                ->where('name', $name)->exists();
            if ($exists) {
                return false;
            }

            // The category must resolve to one of the planned categories —
            // and is stored in its canonical form so the edit form always
            // shows the matching field set.
            $category = $mapper->resolveCategory($raw['category'] ?? null);

            $rawSpecs = (!empty($raw['specifications']) && is_array($raw['specifications'])) ? $raw['specifications'] : [];
            $specs    = $mapper->normalize($category, $rawSpecs, keepExtras: false);

            // A real product fills at least 2 of its category's planned fields.
            // Landing/marketing/category pages don't — never save them.
            $filled = count(array_filter($specs, fn ($v) => is_scalar($v) && trim((string) $v) !== ''));
            if (!$category || $filled < 2) {
                $this->addLog($job, 'info', $raw['source_url'] ?? null,
                    'Skipped (not a recognizable product page): ' . $name);
                return false;
            }

            // Alongside the display strings, store a machine-comparable index
            // ("110kV to 400kV" → min 110 / max 400 / unit kV) so matching and
            // search can compare numbers, never unit-suffixed strings.
            $numeric = app(\App\Services\Catalogue\SpecValueParser::class)->derive($specs);
            if ($numeric) $specs['_numeric'] = $numeric;

            $imagePath = $this->downloadImage($raw['image_url'] ?? null, $raw['source_url'] ?? $job->website_url);

            Product::create([
                'vendor_profile_id' => $job->vendor_profile_id,
                'name'              => $name,
                'model_number'      => $raw['model_number'] ? mb_substr((string) $raw['model_number'], 0, 100) : null,
                'brand'             => $raw['brand'] ? mb_substr((string) $raw['brand'], 0, 150) : null,
                'category'          => $category,
                'description'       => $raw['description'] ?? null,
                'image_path'        => $imagePath,
                'import_source'     => mb_substr((string) $job->website_url, 0, 500),  // internal only
                'catalogue_url'     => isset($raw['source_url']) ? mb_substr((string) $raw['source_url'], 0, 500) : null,  // internal: for resume dedup
                'specifications'    => $specs,
                'status'            => 'inactive',
            ]);
            return true;
        } catch (\Throwable $e) {
            $this->addLog($job, 'error', $raw['source_url'] ?? null, 'Skipped "' . ($raw['product_name'] ?? '?') . '": ' . $e->getMessage());
            Log::warning('CrawlWebsiteJob: product skipped', ['job' => $job->id, 'error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Download a product image and store it on the local public disk.
     * Resolves relative URLs against the page URL. Returns the stored path
     * (e.g. "products/images/abc.jpg") or null on any failure.
     */
    private function downloadImage(?string $url, ?string $pageUrl): ?string
    {
        if (!$url) return null;

        try {
            // Resolve protocol-relative / relative URLs against the page.
            if (str_starts_with($url, '//')) {
                $url = 'https:' . $url;
            } elseif (!preg_match('#^https?://#i', $url)) {
                if (!$pageUrl) return null;
                $p = parse_url($pageUrl);
                if (empty($p['scheme']) || empty($p['host'])) return null;
                $base = $p['scheme'] . '://' . $p['host'];
                $url  = str_starts_with($url, '/') ? $base . $url : rtrim(dirname($pageUrl), '/') . '/' . ltrim($url, './');
            }

            $resp = Http::timeout(20)->withHeaders(['User-Agent' => 'Mozilla/5.0'])->get($url);
            if (!$resp->successful()) return null;

            $ct  = strtolower($resp->header('Content-Type', ''));
            $ext = match (true) {
                str_contains($ct, 'png')  => 'png',
                str_contains($ct, 'webp') => 'webp',
                str_contains($ct, 'gif')  => 'gif',
                str_contains($ct, 'jpeg'), str_contains($ct, 'jpg') => 'jpg',
                default => null,
            };
            if (!$ext) {
                $urlExt = strtolower(pathinfo((string) parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
                $ext = in_array($urlExt, ['jpg', 'jpeg', 'png', 'webp', 'gif']) ? ($urlExt === 'jpeg' ? 'jpg' : $urlExt) : null;
            }
            if (!$ext) return null;

            $body = $resp->body();
            if (strlen($body) < 500 || strlen($body) > 6_000_000) return null; // skip tracking pixels / huge files

            $path = 'products/images/' . Str::random(24) . '.' . $ext;
            Storage::disk('public')->put($path, $body);
            return $path;
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function findPython(): string
    {
        foreach (['python3', 'python', '/usr/bin/python3'] as $candidate) {
            if (shell_exec('which ' . escapeshellarg($candidate) . ' 2>/dev/null')) {
                return escapeshellcmd($candidate);
            }
        }
        throw new \RuntimeException('Python 3 is not available.');
    }
}
