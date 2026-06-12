@extends('layouts.vendor')

@section('title', 'Quotations')
@section('breadcrumb', 'Quotations')

@push('styles')
<style>
.q-table { width: 100%; border-collapse: collapse; }
.q-table th, .q-table td {
    padding: 14px 16px; text-align: left;
    border-bottom: 1px solid var(--border); font-size: 13.5px; vertical-align: middle;
}
.q-table th { color: var(--text-muted); font-weight: 600; letter-spacing: 1px; text-transform: uppercase; font-size: 10.5px; }
.q-table tr:hover td { background: var(--bg-card-hover); }
.q-cust { font-weight: 600; color: var(--text-primary); }
.q-subj { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.tbl-action {
    padding: 6px 12px; border-radius: 6px; border: 1px solid var(--border);
    background: var(--input-bg); color: var(--text-secondary); font-size: 12.5px; font-weight: 600;
    cursor: pointer; text-decoration: none; transition: all 0.2s; display: inline-block;
}
.tbl-action:hover { border-color: var(--purple-bright); color: var(--purple-neon); }
.tbl-action-del { border-color: rgba(239,68,68,0.30); color: #dc2626; background: rgba(239,68,68,0.05); }
.tbl-action-del:hover { background: rgba(239,68,68,0.12); }
.empty-state { text-align: center; padding: 60px 24px; color: var(--text-muted); }
.empty-state-icon { font-size: 48px; margin-bottom: 16px; }
.empty-state-title { font-family: var(--font-display); font-size: 24px; letter-spacing: 1px; color: var(--text-secondary); }
.status-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.status-draft { background: rgba(107,111,138,0.12); border:1px solid rgba(107,111,138,0.3); color:#6B6F8A; }
.status-sent  { background: rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); color:#059669; }
</style>
@endpush

@section('content')
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
    <div>
        <div class="page-tag">📄 Sales</div>
        <h1 class="page-title">Quotations</h1>
        <p class="page-subtitle">Create a quotation and attach the PDF you want to send to your customer.</p>
    </div>
    <a href="{{ route('vendor.quotations.create') }}" class="btn btn-primary" style="padding:11px 20px;margin-top:8px;">New Quotation</a>
</div>


@if($quotations->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">📄</div>
            <div class="empty-state-title">No Quotations Yet</div>
            <div style="font-size:13px;margin-top:8px;">
                Create your first quotation and upload its PDF.
                <a href="{{ route('vendor.quotations.create') }}" style="color:var(--purple-neon);">New Quotation</a>
            </div>
        </div>
    </div>
@else
    <div class="card" style="overflow:hidden;">
        <table class="q-table">
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Document</th>
                    <th>Created</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quotations as $q)
                <tr>
                    <td>
                        <div class="q-cust">{{ $q->customer_name }}</div>
                        @if($q->subject)<div class="q-subj">{{ $q->subject }}</div>@endif
                    </td>
                    <td style="color:var(--text-secondary);">📎 {{ $q->original_filename ?? 'quotation.pdf' }}</td>
                    <td style="color:var(--text-muted);font-size:12px;">{{ $q->created_at->format('d M Y') }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:8px;justify-content:flex-end;align-items:center;">
                            <a href="{{ route('vendor.quotations.download', $q->id) }}" class="tbl-action">⬇ Download</a>
                            <form action="{{ route('vendor.quotations.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Delete this quotation?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="tbl-action tbl-action-del">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $quotations->links() }}</div>
@endif
@endsection
