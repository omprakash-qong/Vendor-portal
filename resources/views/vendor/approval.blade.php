
@extends('layouts.vendor')
@section('title','Approval Status')
@section('breadcrumb','Approval Status')
 
@push('styles')
<style>
.timeline { display:flex; flex-direction:column; gap:0; }
.timeline-step { display:flex; gap:20px; position:relative; padding-bottom:28px; }
.timeline-step:last-child { padding-bottom:0; }
.timeline-left { display:flex; flex-direction:column; align-items:center; }
.timeline-bubble {
    width:38px; height:38px; border-radius:50%;
    border:2px solid var(--border);
    display:grid; place-items:center;
    font-size:16px; flex-shrink:0;
    background:var(--input-bg);
    z-index:1; position:relative;
}
.timeline-bubble.done  { border-color:var(--purple-bright); background:rgba(168,85,247,0.15); box-shadow:0 0 12px var(--purple-glow); }
.timeline-bubble.current{ border-color:#fbbf24; background:rgba(251,191,36,0.10); box-shadow:0 0 12px rgba(251,191,36,0.3); }
.timeline-connector { width:2px; flex:1; background:var(--border); margin:4px 0; }
.timeline-connector.done { background:var(--purple-mid); }
.timeline-content { padding-top:6px; }
.timeline-title { font-family:var(--font-display); font-size:18px; letter-spacing:2px; color:var(--text-primary); }
.timeline-desc  { font-size:12.5px; color:var(--text-muted); margin-top:3px; }
.timeline-time  { font-size:11px; color:var(--text-muted); margin-top:4px; }
</style>
@endpush
 
@section('content')
<div class="page-header">
    <div class="page-tag">◎ Vendor Status</div>
    <h1 class="page-title">Approval Status</h1>
    <p class="page-subtitle">Track your vendor onboarding review progress below.</p>
</div>
 
<div class="two-col" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
    <div class="card">
        <div class="card-header">
            <div class="card-icon">⏱</div>
            <div>
                <div class="card-title">Review Timeline</div>
                <div class="card-desc">Current stage of your application</div>
            </div>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-step">
                    <div class="timeline-left">
                        <div class="timeline-bubble done">✔</div>
                        <div class="timeline-connector done"></div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Account Registered</div>
                        <div class="timeline-desc">Your account has been created and verified.</div>
                        <div class="timeline-time">{{ auth()->user()->created_at?->format('d M Y') }}</div>
                    </div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-left">
                        <div class="timeline-bubble {{ ($vendor->submitted_at ?? false) ? 'done' : '' }}">
                            {{ ($vendor->submitted_at ?? false) ? '✔' : '2' }}
                        </div>
                        <div class="timeline-connector {{ ($vendor->submitted_at ?? false) ? 'done' : '' }}"></div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Form Submitted</div>
                        <div class="timeline-desc">All company details and documents uploaded.</div>
                        <div class="timeline-time">{{ isset($vendor->submitted_at) ? $vendor->submitted_at->format('d M Y') : 'Not yet submitted' }}</div>
                    </div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-left">
                        <div class="timeline-bubble {{ ($vendor->submission_status ?? '') === 'pending_review' ? 'current' : (in_array($vendor->submission_status ?? '', ['approved','rejected']) ? 'done' : '') }}">
                            {{ in_array($vendor->submission_status ?? '', ['approved','rejected']) ? '✔' : '3' }}
                        </div>
                        <div class="timeline-connector {{ in_array($vendor->submission_status ?? '', ['approved','rejected']) ? 'done' : '' }}"></div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Under Review</div>
                        <div class="timeline-desc">QONG procurement team reviewing your application.</div>
                        <div class="timeline-time">Estimated 3–5 business days</div>
                    </div>
                </div>
                <div class="timeline-step">
                    <div class="timeline-left">
                        <div class="timeline-bubble {{ ($vendor->submission_status ?? '') === 'approved' ? 'done' : '' }}">
                            {{ ($vendor->submission_status ?? '') === 'approved' ? '✔' : '4' }}
                        </div>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-title">Approved & Active</div>
                        <div class="timeline-desc">You'll receive an email confirmation upon approval.</div>
                        <div class="timeline-time">{{ isset($vendor->reviewed_at) && $vendor->submission_status === 'approved' ? $vendor->reviewed_at->format('d M Y') : 'Pending' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
 
    <div>
        <div class="card" style="margin-bottom:20px;">
            <div class="card-header">
                <div class="card-icon">📊</div>
                <div>
                    <div class="card-title">Current Status</div>
                    <div class="card-desc">Your application state</div>
                </div>
            </div>
            <div class="card-body" style="text-align:center;padding:40px;">
                @php $status = $vendor->submission_status ?? 'pending_review'; @endphp
                <div style="font-size:48px;margin-bottom:16px;">
                    @if($status==='approved') ✅ @elseif($status==='rejected') ❌ @else ⏳ @endif
                </div>
                <div class="status-badge status-{{ $status==='approved'?'approved':($status==='rejected'?'rejected':'pending') }}" style="font-size:14px;padding:8px 20px;">
                    <span class="status-dot"></span>
                    {{ $status === 'pending_review' ? 'Pending Review' : ucfirst($status) }}
                </div>
                @if($vendor->admin_notes ?? false)
                <div style="margin-top:20px;padding:14px;background:var(--input-bg);border:1px solid var(--border);border-radius:var(--radius-sm);text-align:left;font-size:12.5px;color:var(--text-secondary);">
                    <strong style="display:block;margin-bottom:6px;color:var(--text-primary);">Reviewer Notes:</strong>
                    {{ $vendor->admin_notes }}
                </div>
                @endif
            </div>
        </div>
 
        @if(($vendor->submission_status ?? 'draft') === 'rejected')
        <div class="card">
            <div class="card-body" style="padding:20px;">
                <p style="font-size:13px;color:var(--text-secondary);margin-bottom:0;">
                    To reapply, please visit <a href="{{ url('/apply') }}" style="color:var(--purple-neon);">{{ url('/apply') }}</a> and submit a new application addressing the reviewer feedback above.
                </p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection