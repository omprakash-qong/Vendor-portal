<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Jobs\CrawlWebsiteJob;
use App\Models\ImportJob;
use App\Models\Product;
use App\Services\Catalogue\CsvExtractionService;
use App\Services\Catalogue\ExcelExtractionService;
use App\Services\Catalogue\FileValidationService;
use App\Services\Catalogue\PdfExtractionService;
use App\Services\Catalogue\RuleBasedStructuringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function index()
    {
        $vendor     = auth()->user()->vendorProfile;
        $recentJobs = ImportJob::where('vendor_profile_id', $vendor->id)
            ->latest()
            ->take(10)
            ->get();

        return view('vendor.import.index', compact('recentJobs'));
    }

    // ── POST /vendor/import/website ────────────────────────────────────
    public function website(Request $request)
    {
        $request->validate(['url' => 'required|url|max:500']);

        $vendor = auth()->user()->vendorProfile;

        $job = ImportJob::create([
            'vendor_profile_id' => $vendor->id,
            'source_type'       => 'website',
            'website_url'       => $request->url,
            'status'            => 'queued',
        ]);

        CrawlWebsiteJob::dispatch($job->id);

        return redirect()->route('vendor.import.status', $job)
            ->with('success', 'Import started. Your products will appear in My Products shortly.');
    }

    // ── POST /vendor/import/file (PDF / Excel / CSV) ────────────────────
    public function file(Request $request)
    {
        $request->validate(['file' => 'required|file|max:20480']);

        $file      = $request->file('file');
        $validator = app(FileValidationService::class);
        $validation = $validator->validate($file);

        if (!$validation['valid']) {
            return back()->withErrors(['file' => $validation['message']]);
        }

        $vendor   = auth()->user()->vendorProfile;
        $fileType = $validation['type'];
        $tempPath = $file->store('catalogue/temp', 'local');
        $absPath  = Storage::disk('local')->path($tempPath);

        $job = ImportJob::create([
            'vendor_profile_id' => $vendor->id,
            'source_type'       => $fileType,
            'file_name'         => $file->getClientOriginalName(),
            'file_path'         => $tempPath,
            'status'            => 'running',
            'started_at'        => now(),
        ]);

        try {
            $rawExtracted = match ($fileType) {
                'pdf'   => app(PdfExtractionService::class)->extract($absPath),
                'excel' => app(ExcelExtractionService::class)->extract($absPath),
                'csv'   => app(CsvExtractionService::class)->extract($absPath),
                default => throw new \RuntimeException('Unsupported file type.'),
            };

            $vendorName = $vendor->trade_name ?? $vendor->legal_company_name ?? '';
            $structured = app(RuleBasedStructuringService::class)->structure($rawExtracted, $vendorName);

            $count = $this->saveToStaging($job, $structured);

            $job->update([
                'status'         => 'completed',
                'products_found' => $count,
                'completed_at'   => now(),
            ]);

            return redirect()->route('vendor.products.index')
                ->with('success', "{$count} product(s) imported and saved as inactive.");

        } catch (\Throwable $e) {
            $job->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'completed_at' => now()]);
            return back()->withErrors(['file' => 'Extraction failed: ' . $e->getMessage()]);
        } finally {
            Storage::disk('local')->delete($tempPath);
        }
    }

    // ── GET /vendor/import/jobs/{job} ───────────────────────────────────
    public function jobStatus(ImportJob $job)
    {
        $this->authorizeJob($job);
        $logs = $job->logs()->latest('created_at')->take(50)->get();
        return view('vendor.import.job-status', compact('job', 'logs'));
    }

    // ── GET /vendor/import/jobs/{job}/poll (JSON polling) ──────────────
    public function poll(ImportJob $job)
    {
        $this->authorizeJob($job);
        return response()->json([
            'status'         => $job->status,
            'pages_crawled'  => $job->pages_crawled,
            'products_found' => $job->products_found,
            'failed_pages'   => $job->failed_pages,
            'completed'      => $job->isCompleted(),
        ]);
    }

    private function saveToStaging(ImportJob $job, array $structured): int
    {
        $count = 0;
        foreach ($structured['vendor_series'] ?? [] as $series) {
            $category = $series['category'] ?? 'Other';

            foreach ($series['variants'] ?? [] as $v) {
                $specs = $v['specifications'] ?? [];
                foreach (['power_kw', 'poles', 'size_mm', 'size_inch', 'pressure_bar', 'flow_m3h', 'voltage_v', 'equipment_type'] as $k) {
                    if (isset($v[$k])) $specs[$k] = $v[$k];
                }

                $descParts = [];
                if (!empty($v['power_kw']))     $descParts[] = $v['power_kw'] . 'kW';
                if (!empty($v['poles']))        $descParts[] = $v['poles'] . ' Pole';
                if (!empty($v['size_mm']))      $descParts[] = 'DN' . $v['size_mm'];
                if (!empty($v['pressure_bar'])) $descParts[] = $v['pressure_bar'] . ' bar';
                if (!empty($v['flow_m3h']))     $descParts[] = $v['flow_m3h'] . ' m³/h';

                Product::create([
                    'vendor_profile_id' => $job->vendor_profile_id,
                    'name'              => $v['variant_name'] ?? 'Unknown',
                    'model_number'      => $v['model_number'] ?? null,
                    'brand'             => $series['brand']   ?? null,
                    'category'          => $category,
                    'short_description' => implode(' · ', $descParts) ?: null,
                    'specifications'    => empty($specs) ? null : $specs,
                    'import_source'     => $job->source_type ?? 'file',
                    'status'            => 'inactive',
                ]);
                $count++;
            }
        }
        return $count;
    }

    private function authorizeJob(ImportJob $job): void
    {
        $vendor = auth()->user()->vendorProfile;
        if ($job->vendor_profile_id !== $vendor->id) abort(403);
    }
}
