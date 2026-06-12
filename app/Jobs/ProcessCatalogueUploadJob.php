<?php

namespace App\Jobs;

use App\Models\ExtractionJob;
use App\Models\ProductSeries;
use App\Models\ProductVariant;
use App\Models\ProductCategory;
use App\Services\Catalogue\ExcelExtractionService;
use App\Services\Catalogue\CsvExtractionService;
use App\Services\Catalogue\PdfExtractionService;
use App\Services\Catalogue\RuleBasedStructuringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessCatalogueUploadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180; // 3 min max

    public function __construct(private readonly int $extractionJobId)
    {
    }

    public function handle(
        ExcelExtractionService       $excel,
        CsvExtractionService         $csv,
        PdfExtractionService         $pdf,
        RuleBasedStructuringService  $structurer
    ): void {
        $job = ExtractionJob::with(['document', 'vendorProfile'])->find($this->extractionJobId);

        if (!$job) return;

        $job->update([
            'status'                => 'processing',
            'processing_started_at' => now(),
        ]);

        try {
            $filePath = Storage::disk('local')->path($job->document->file_path);
            $fileType = $job->document->file_type;
            $vendor   = $job->vendorProfile;

            // ─── Step 1: Extract raw content ──────────────────────────
            $rawExtracted = match($fileType) {
                'excel' => $excel->extract($filePath),
                'csv'   => $csv->extract($filePath),
                'pdf'   => $pdf->extract($filePath),
            };

            $job->update([
                'raw_extracted' => $rawExtracted,
                'status'        => 'processing',
            ]);

            // ─── Step 2: Rule-based structuring (no API, no key) ─────
            $vendorName   = $vendor->trade_name ?? $vendor->legal_company_name ?? '';
            $aiStructured = $structurer->structure($rawExtracted, $vendorName);

            $job->update([
                'ai_structured'           => $aiStructured,
                'processing_completed_at' => now(),
            ]);

            // Auto-publish directly — no manual approval step
            self::publish($job);

        } catch (\Throwable $e) {
            Log::error('Catalogue extraction failed', [
                'job_id' => $this->extractionJobId,
                'error'  => $e->getMessage(),
            ]);

            $job->update([
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    public function failed(\Throwable $e): void
    {
        ExtractionJob::where('id', $this->extractionJobId)->update([
            'status'        => 'failed',
            'error_message' => 'Job failed after retries: ' . $e->getMessage(),
        ]);
    }

    /**
     * Called from CatalogueController when vendor clicks "Approve & Publish".
     * Takes the reviewed ai_structured data and creates DB records.
     */
    public static function publish(ExtractionJob $job): int
    {
        $data    = $job->ai_structured ?? ['vendor_series' => []];
        $created = 0;

        foreach ($data['vendor_series'] ?? [] as $seriesData) {
            $category = ProductCategory::where('name', $seriesData['category'] ?? '')
                                       ->orWhere('slug', \Str::slug($seriesData['category'] ?? ''))
                                       ->first();

            if (!$category) continue;

            $series = ProductSeries::create([
                'vendor_profile_id' => $job->vendor_profile_id,
                'category_id'       => $category->id,
                'extraction_job_id' => $job->id,
                'name'              => $seriesData['series_name'],
                'brand'             => $seriesData['brand'] ?? null,
            ]);

            foreach ($seriesData['variants'] ?? [] as $v) {
                ProductVariant::create([
                    'vendor_profile_id' => $job->vendor_profile_id,
                    'series_id'         => $series->id,
                    'category_id'       => $category->id,
                    'extraction_job_id' => $job->id,
                    'variant_name'      => $v['variant_name'],
                    'equipment_type'    => $v['equipment_type'],
                    'power_kw'          => $v['power_kw']    ?? null,
                    'size_inch'         => $v['size_inch']   ?? null,
                    'size_mm'           => $v['size_mm']     ?? null,
                    'pressure_bar'      => $v['pressure_bar'] ?? null,
                    'flow_m3h'          => $v['flow_m3h']    ?? null,
                    'voltage_v'         => $v['voltage_v']   ?? null,
                    'poles'             => $v['poles']       ?? null,
                    'industry_tags'     => $v['industry_tags']   ?? [],
                    'capability_tags'   => $v['capability_tags']  ?? [],
                    'certifications'    => $v['certifications']   ?? [],
                    'specifications'    => $v['specifications']   ?? [],
                    'is_active'         => true,
                    'is_published'      => true,
                    'published_at'      => now(),
                ]);
                $created++;
            }
        }

        $job->update(['status' => 'approved', 'approved_at' => now()]);

        return $created;
    }
}
