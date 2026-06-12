<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Jobs\CrawlWebsiteJob;
use App\Models\ImportJob;
use Illuminate\Http\Request;

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

    private function authorizeJob(ImportJob $job): void
    {
        $vendor = auth()->user()->vendorProfile;
        if ($job->vendor_profile_id !== $vendor->id) abort(403);
    }
}
