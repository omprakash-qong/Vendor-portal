@extends('layouts.vendor')

@section('title', 'Extraction Jobs')
@section('breadcrumb', 'Catalogue / Jobs')

@push('styles')
<style>
    .status-badge {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 3px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 600;
    }
    .status-badge.success  { background: rgba(52,211,153,0.12); color: var(--success); }
    .status-badge.warning  { background: rgba(251,191,36,0.12);  color: #fbbf24; }
    .status-badge.error    { background: rgba(248,113,113,0.12); color: var(--error); }
    .status-badge.muted    { background: rgba(255,255,255,0.05); color: var(--text-muted); }
    .status-dot { width:7px; height:7px; border-radius:50%; background:currentColor; }
    .spinning { animation: spin 1s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .table-action { font-size: 12.5px; color: var(--purple-bright); text-decoration:none; margin-right:12px; }
    .table-action:hover { text-decoration: underline; }
    .empty-state { text-align:center; padding:60px 20px; color:var(--text-muted); }
    .empty-state .icon { font-size: 40px; margin-bottom:16px; }
</style>
@endpush

@section('content')
<div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;">
    <div>
        <div class="page-tag">Catalogue</div>
        <h1 class="page-title">Extraction Jobs</h1>
        <p class="page-subtitle">Track the status of your uploaded catalogues.</p>
    </div>
    <a href="{{ route('vendor.catalogue.upload') }}" class="btn btn-primary" style="margin-top:8px;">+ Upload File</a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:24px;">{{ session('success') }}</div>
@endif

@if($jobs->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="icon">📂</div>
            <p>No uploads yet. Upload your first product catalogue to get started.</p>
            <a href="{{ route('vendor.catalogue.upload') }}" class="btn btn-primary" style="margin-top:20px;">Upload Catalogue</a>
        </div>
    </div>
@else
<div class="card">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th style="padding:14px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted);">File</th>
                    <th style="padding:14px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted);">Type</th>
                    <th style="padding:14px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted);">Size</th>
                    <th style="padding:14px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted);">Status</th>
                    <th style="padding:14px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted);">Uploaded</th>
                    <th style="padding:14px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted);">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobs as $job)
                <tr style="border-bottom:1px solid rgba(255,255,255,0.04);" id="job-row-{{ $job->id }}">
                    <td style="padding:14px 16px; color:var(--text-primary); font-size:13.5px;">
                        {{ $job->document->file_name ?? '—' }}
                    </td>
                    <td style="padding:14px 16px; font-size:13px; color:var(--text-secondary); text-transform:uppercase; letter-spacing:0.5px;">
                        {{ $job->document->file_type ?? '—' }}
                    </td>
                    <td style="padding:14px 16px; font-size:13px; color:var(--text-secondary);">
                        {{ $job->document?->fileSizeHuman() ?? '—' }}
                    </td>
                    <td style="padding:14px 16px;">
                        <span class="status-badge {{ $job->statusColor() }}" id="status-{{ $job->id }}">
                            @if(in_array($job->status, ['pending','processing','ai_structuring']))
                                <span class="status-dot spinning">⟳</span>
                            @else
                                <span class="status-dot"></span>
                            @endif
                            {{ $job->statusLabel() }}
                        </span>
                    </td>
                    <td style="padding:14px 16px; font-size:12.5px; color:var(--text-muted);">
                        {{ $job->created_at->diffForHumans() }}
                    </td>
                    <td style="padding:14px 16px;">
                        @if($job->status === 'preview_ready')
                            <a href="{{ route('vendor.catalogue.preview', $job) }}" class="table-action">Review &amp; Publish</a>
                        @elseif($job->status === 'approved')
                            <a href="{{ route('vendor.catalogue.variants') }}" class="table-action">View Products</a>
                        @elseif($job->status === 'failed')
                            <span style="font-size:12px;color:var(--error);" title="{{ $job->error_message }}">See error</span>
                        @endif
                        @if(in_array($job->status, ['preview_ready', 'failed']))
                            <form method="POST" action="{{ route('vendor.catalogue.reject', $job) }}" style="display:inline;" onsubmit="return confirm('Discard this extraction job?')">
                                @csrf
                                <button type="submit" class="table-action" style="background:none;border:none;cursor:pointer;color:var(--error);padding:0;">Discard</button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($jobs->hasPages())
        <div style="padding:16px;">{{ $jobs->links() }}</div>
    @endif
</div>
@endif

@php
    $runningIds = $jobs->filter(fn($j) => in_array($j->status, ['pending','processing']))->pluck('id')->values();
@endphp
<script>
const runningJobs = @json($runningIds);

if (runningJobs.length > 0) {
    const poll = setInterval(async () => {
        let allDone = true;
        for (const id of runningJobs) {
            const res  = await fetch(`/vendor/catalogue/jobs/${id}/status`);
            const data = await res.json();
            const badge = document.getElementById(`status-${id}`);
            if (badge) {
                badge.className = `status-badge ${data.color}`;
                badge.innerHTML = `<span class="status-dot"></span> ${data.status_label}`;
            }
            if (['pending','processing'].includes(data.status)) {
                allDone = false;
            }
            if (data.status === 'preview_ready') {
                // Show review link
                const row = document.getElementById(`job-row-${id}`);
                const actTd = row?.querySelector('td:last-child');
                if (actTd) actTd.innerHTML = `<a href="/vendor/catalogue/jobs/${id}" class="table-action">Review &amp; Publish</a>`;
            }
        }
        if (allDone) clearInterval(poll);
    }, 4000);
}
</script>
@endsection
