@extends('layouts.vendor')

@section('title', 'Importing Products')
@section('breadcrumb', 'Import')

@push('styles')
<style>
.import-panel {
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 56px 32px;
    text-align: center;
}
.import-icon { font-size: 52px; margin-bottom: 20px; }
.import-title {
    font-family: var(--font-display);
    font-size: 30px;
    letter-spacing: 2px;
    color: var(--text-primary);
    margin-bottom: 10px;
}
.import-sub { font-size: 14px; color: var(--text-secondary); max-width: 420px; margin: 0 auto 28px; line-height: 1.6; }
.spinner {
    width: 44px; height: 44px; margin: 0 auto 24px;
    border: 4px solid rgba(168,85,247,0.2);
    border-top-color: var(--purple-bright);
    border-radius: 50%;
    animation: spin 0.9s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }
.count-big {
    font-family: var(--font-display);
    font-size: 64px; letter-spacing: 2px;
    color: var(--purple-neon); line-height: 1;
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">📥 Import</div>
    <h1 class="page-title">Importing Products</h1>
</div>

@if($job->status === 'completed' && $job->products_found > 0)
    {{-- Done --}}
    <div class="import-panel">
        <div class="import-icon">✅</div>
        <div class="count-big">{{ number_format($job->products_found) }}</div>
        <div class="import-title" style="margin-top:10px;">Products Imported</div>
        <div class="import-sub">Your products have been added to your catalogue and are ready to review.</div>
        <a href="{{ route('vendor.products.index', ['status' => 'inactive']) }}" class="btn btn-primary" style="padding:12px 28px;">Review Products →</a>
    </div>

@elseif($job->status === 'completed')
    {{-- Completed but nothing found --}}
    <div class="import-panel">
        <div class="import-icon">📭</div>
        <div class="import-title">No Products Found</div>
        <div class="import-sub">We couldn't find any products to import. Try a different page or import a PDF / Excel / CSV file instead.</div>
        <a href="{{ route('vendor.import.index') }}" class="btn btn-outline" style="padding:12px 28px;">← Back to Import</a>
    </div>

@elseif($job->status === 'failed')
    {{-- Failed (no technical details exposed) --}}
    <div class="import-panel">
        <div class="import-icon">⚠️</div>
        <div class="import-title">Import Couldn't Complete</div>
        <div class="import-sub">Something went wrong while importing. Please try again, or import a PDF / Excel / CSV file instead.</div>
        <a href="{{ route('vendor.import.index') }}" class="btn btn-outline" style="padding:12px 28px;">← Back to Import</a>
    </div>

@else
    {{-- In progress --}}
    <div class="import-panel">
        <div class="spinner"></div>
        <div class="import-title">Import In Progress</div>
        <div class="import-sub">We're processing your products in the background. This can take a few minutes — you can leave this page and check back later.</div>
        <a href="{{ route('vendor.products.index') }}" class="btn btn-outline" style="padding:12px 28px;">Go to My Products</a>
    </div>
@endif

@endsection

@push('scripts')
@if($job->isRunning())
<script>
const POLL_URL = '{{ route('vendor.import.poll', $job) }}';
let pollInterval = setInterval(async () => {
    try {
        const res  = await fetch(POLL_URL);
        const data = await res.json();
        if (data.completed) {
            clearInterval(pollInterval);
            window.location.reload();
        }
    } catch(e) { /* keep polling */ }
}, 3000);
</script>
@endif
@endpush
