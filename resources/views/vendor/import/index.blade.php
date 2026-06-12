@extends('layouts.vendor')

@section('title', 'Import Products')
@section('breadcrumb', 'Import Products')

@push('styles')
<style>
.import-tabs {
    display: flex;
    gap: 4px;
    background: var(--bg-card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 6px;
    margin-bottom: 28px;
}
.tab-btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: var(--radius-sm);
    background: transparent;
    color: var(--text-secondary);
    font-family: var(--font-display);
    font-size: 15px;
    letter-spacing: 1.5px;
    cursor: pointer;
    transition: all 0.2s;
}
.tab-btn.active {
    background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
    color: #fff;
    box-shadow: 0 0 16px rgba(168,85,247,0.4);
}
.tab-btn:not(.active):hover {
    background: var(--bg-card-hover);
    color: var(--text-primary);
}
.tab-panel { display: none; }
.tab-panel.active { display: block; }

.jobs-table { width: 100%; border-collapse: collapse; }
.jobs-table th, .jobs-table td {
    padding: 12px 16px;
    text-align: left;
    border-bottom: 1px solid var(--border);
    font-size: 13px;
}
.jobs-table th { color: var(--text-muted); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; font-size: 10.5px; }
.jobs-table tr:hover td { background: var(--bg-card-hover); }
.source-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.8px;
}
.source-website  { background: rgba(59,130,246,0.15); border: 1px solid rgba(59,130,246,0.3); color: #93c5fd; }
.source-pdf      { background: rgba(239,68,68,0.15);  border: 1px solid rgba(239,68,68,0.3);  color: #fca5a5; }
.source-excel    { background: rgba(34,197,94,0.15);  border: 1px solid rgba(34,197,94,0.3);  color: #86efac; }
.source-csv      { background: rgba(251,191,36,0.15); border: 1px solid rgba(251,191,36,0.3); color: #fde68a; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">📥 Import</div>
    <h1 class="page-title">Import Products</h1>
    <p class="page-subtitle">Enter your website address and we'll bring your products in automatically.</p>
</div>

@if($errors->any())
    <div class="alert alert-error">✖ {{ $errors->first() }}</div>
@endif

{{-- Recent Import Jobs — shown above the form so the "View" section stays on top --}}
@if($recentJobs->count())
<div class="card" style="margin-bottom:32px;">
    <div class="card-header">
        <div class="card-icon">📋</div>
        <div>
            <div class="card-title">Recent Imports</div>
            <div class="card-desc">Last 10 import jobs</div>
        </div>
    </div>
    <div class="card-body" style="padding:0;">
        <table class="jobs-table">
            <thead>
                <tr>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Products</th>
                    <th>When</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentJobs as $job)
                <tr>
                    <td style="text-transform:capitalize;color:var(--text-secondary);">{{ $job->source_type }}</td>
                    <td style="color:var(--text-secondary);">
                        @if($job->status === 'completed') Done
                        @elseif($job->status === 'failed') Failed
                        @elseif(in_array($job->status, ['queued','running'])) Importing
                        @endif
                    </td>
                    <td style="font-weight:600;">{{ number_format($job->products_found) }}</td>
                    <td style="color:var(--text-muted);font-size:12px;">{{ $job->created_at->diffForHumans() }}</td>
                    <td>
                        @if($job->status === 'completed' && $job->products_found > 0)
                            <a href="{{ route('vendor.products.index', ['status' => 'inactive']) }}" style="color:var(--purple-bright);font-size:12px;font-weight:600;">View →</a>
                        @elseif(in_array($job->status, ['queued','running']) && $job->source_type === 'website')
                            <a href="{{ route('vendor.import.status', $job) }}" style="color:var(--purple-bright);font-size:12px;font-weight:600;">View →</a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- Website import (only source) --}}
<div class="card">
    <div class="card-header">
        <div class="card-icon">🌐</div>
        <div>
            <div class="card-title">Import from Website</div>
            <div class="card-desc">Enter your website address and we'll add your products to your catalogue automatically.</div>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('vendor.import.website') }}">
            @csrf
            <div class="field" style="margin-bottom:20px;">
                <label class="field-label">Website Address <span class="req">*</span></label>
                <div class="input-wrap">
                    <span class="input-icon">🔗</span>
                    <input type="url" name="url" placeholder="https://yourcompany.com/products" required value="{{ old('url') }}">
                </div>
                <p class="field-hint">Paste the page that lists your products. Importing runs in the background and products appear under “Needs Review”.</p>
            </div>
            <button type="submit" class="btn btn-primary">Import</button>
        </form>
    </div>
</div>

@endsection
