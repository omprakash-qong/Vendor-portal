<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessCatalogueUploadJob;
use App\Models\ExtractionJob;
use App\Models\ProductVariant;
use App\Models\VendorDocument;
use App\Services\Catalogue\FileValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CatalogueController extends Controller
{
    public function __construct(private readonly FileValidationService $validator)
    {
    }

    // GET /vendor/catalogue — upload form
    public function upload()
    {
        return view('vendor.catalogue.upload');
    }

    // POST /vendor/catalogue/upload — handle file upload
    public function store(Request $request)
    {
        $request->validate(['file' => 'required|file|max:20480']);

        $vendor     = auth()->user()->vendorProfile;
        $uploadedFile = $request->file('file');

        // Validate file type + PDF digital check
        $check = $this->validator->validate($uploadedFile);

        if (!$check['valid']) {
            return back()->withErrors(['file' => $check['message']]);
        }

        // Store the file (never modified, never auto-deleted)
        $path = $uploadedFile->store('vendor-catalogues/' . $vendor->id, 'local');

        // Create document record
        $doc = VendorDocument::create([
            'vendor_profile_id' => $vendor->id,
            'file_name'         => $uploadedFile->getClientOriginalName(),
            'file_path'         => $path,
            'mime_type'         => $uploadedFile->getMimeType(),
            'file_size_bytes'   => $uploadedFile->getSize(),
            'file_type'         => $check['type'],
        ]);

        // Create extraction job record
        $job = ExtractionJob::create([
            'vendor_profile_id'  => $vendor->id,
            'vendor_document_id' => $doc->id,
            'status'             => 'pending',
        ]);

        // Dispatch async processing
        ProcessCatalogueUploadJob::dispatch($job->id);

        return redirect()
            ->route('vendor.catalogue.variants')
            ->with('success', 'File uploaded. Products are being extracted and will appear here shortly.');
    }

    // GET /vendor/catalogue/jobs — list all extraction jobs
    public function jobs()
    {
        $vendor = auth()->user()->vendorProfile;

        $jobs = ExtractionJob::where('vendor_profile_id', $vendor->id)
            ->with('document')
            ->latest()
            ->paginate(15);

        return view('vendor.catalogue.jobs', compact('jobs'));
    }

    // GET /vendor/catalogue/jobs/{job} — preview extracted data
    public function preview(ExtractionJob $job)
    {
        $this->authorizeJob($job);

        if (!in_array($job->status, ['preview_ready', 'approved'])) {
            return redirect()->route('vendor.catalogue.jobs')
                ->with('info', 'This job is not ready for preview yet.');
        }

        return view('vendor.catalogue.preview', compact('job'));
    }

    // POST /vendor/catalogue/jobs/{job}/approve — approve & publish
    public function approve(Request $request, ExtractionJob $job)
    {
        $this->authorizeJob($job);

        if ($job->status !== 'preview_ready') {
            return back()->withErrors(['status' => 'Job is not in preview state.']);
        }

        // Merge any edits the vendor made in the preview form
        if ($request->has('ai_structured')) {
            $job->update(['ai_structured' => $request->input('ai_structured')]);
        }

        $count = ProcessCatalogueUploadJob::publish($job);

        return redirect()
            ->route('vendor.catalogue.variants')
            ->with('success', "$count product variants published successfully.");
    }

    // POST /vendor/catalogue/jobs/{job}/reject — vendor discards the extraction
    public function reject(ExtractionJob $job)
    {
        $this->authorizeJob($job);
        $job->update(['status' => 'rejected']);

        return redirect()->route('vendor.catalogue.jobs')
            ->with('info', 'Extraction job discarded.');
    }

    // DELETE /vendor/catalogue/variants/{variant} — unpublish a single variant
    public function destroyVariant(ProductVariant $variant)
    {
        $vendor = auth()->user()->vendorProfile;

        if ($variant->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        $variant->delete();

        return back()->with('success', 'Variant removed from catalogue.');
    }

    // GET /vendor/catalogue/variants — all published variants
    public function variants()
    {
        $vendor = auth()->user()->vendorProfile;

        $variants = ProductVariant::where('vendor_profile_id', $vendor->id)
            ->with(['series', 'category'])
            ->withTrashed()
            ->latest()
            ->paginate(20);

        // Pass any currently-processing job IDs for the polling banner
        $processingJobIds = ExtractionJob::where('vendor_profile_id', $vendor->id)
            ->whereIn('status', ['pending', 'processing'])
            ->pluck('id')
            ->values();

        return view('vendor.catalogue.variants', compact('variants', 'processingJobIds'));
    }

    // GET /vendor/catalogue/jobs/{job}/status — JSON polling endpoint
    public function status(ExtractionJob $job)
    {
        $this->authorizeJob($job);

        return response()->json([
            'status'       => $job->status,
            'status_label' => $job->statusLabel(),
            'color'        => $job->statusColor(),
            'variant_count' => $job->status === 'preview_ready'
                ? collect($job->ai_structured['vendor_series'] ?? [])
                    ->sum(fn($s) => count($s['variants'] ?? []))
                : null,
        ]);
    }

    private function authorizeJob(ExtractionJob $job): void
    {
        $vendor = auth()->user()->vendorProfile;
        if ($job->vendor_profile_id !== $vendor->id) abort(403);
    }
}
