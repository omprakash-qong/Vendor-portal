@extends('layouts.vendor')

@section('title', 'Support')
@section('breadcrumb', 'Support')

@push('styles')
<style>
    .split-layout {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 32px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .split-layout {
            grid-template-columns: 1fr;
        }
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    table.custom-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13.5px;
    }
    table.custom-table th {
        background: rgba(168,85,247,0.06);
        border-bottom: 1px solid var(--border);
        padding: 14px 18px;
        text-align: left;
        font-family: var(--font-display);
        letter-spacing: 1.5px;
        font-size: 15px;
        color: var(--text-primary);
    }
    table.custom-table td {
        padding: 14px 18px;
        border-bottom: 1px solid var(--border);
        color: var(--text-secondary);
        vertical-align: middle;
    }
    table.custom-table tr:hover td {
        background: rgba(255,255,255,0.02);
    }
    .ticket-subject {
        font-weight: 500;
        color: var(--text-primary);
    }
    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        padding: 3px 8px;
        border-radius: 4px;
        border: 1px solid transparent;
    }
    .badge-open {
        background: rgba(168,85,247,0.1);
        border-color: rgba(168,85,247,0.3);
        color: var(--purple-neon);
    }
    .badge-in_progress {
        background: rgba(251,191,36,0.1);
        border-color: rgba(251,191,36,0.3);
        color: #fcd34d;
    }
    .badge-resolved {
        background: rgba(52,211,153,0.1);
        border-color: rgba(52,211,153,0.3);
        color: #6ee7b7;
    }
    .badge-closed {
        background: rgba(255,255,255,0.05);
        border-color: rgba(255,255,255,0.15);
        color: var(--text-muted);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">Module</div>
    <h1 class="page-title">Support Desk</h1>
    <p class="page-subtitle">Submit support requests or technical queries regarding the vendor portal or RFQs.</p>
</div>

<div class="split-layout">
    <!-- Left: Raise Support Ticket Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">🛠</div>
            <div>
                <div class="card-title">Raise Support Ticket</div>
                <div class="card-desc">Open a new assistance request.</div>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('vendor.support.store') }}" method="POST">
                @csrf

                <div class="field" style="margin-bottom: 20px;">
                    <label class="field-label">Subject <span class="req">*</span></label>
                    <div class="input-wrap no-icon">
                        <input type="text" name="subject" value="{{ old('subject') }}" required placeholder="e.g. Issue uploading PDF datasheet">
                    </div>
                    @error('subject')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="field" style="margin-bottom: 24px;">
                    <label class="field-label">Description / Details <span class="req">*</span></label>
                    <div class="input-wrap no-icon">
                        <textarea name="description" required placeholder="Provide a detailed description of your query or technical issue..." style="min-height: 120px;">{{ old('description') }}</textarea>
                    </div>
                    @error('description')
                        <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Submit Ticket</button>
            </form>
        </div>
    </div>

    <!-- Right: Ticket History List -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">📜</div>
            <div>
                <div class="card-title">Support History</div>
                <div class="card-desc">Overview of your current and past support requests.</div>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($tickets->isEmpty())
                <div style="text-align: center; padding: 48px 24px;">
                    <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;">💬</div>
                    <h4 style="font-family: var(--font-display); font-size: 18px; letter-spacing: 1px; color: var(--text-primary);">No Tickets Raised</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">If you face any issues, use the form to raise a support request.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Ticket Details</th>
                                <th>Status</th>
                                <th>Date Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                <tr>
                                    <td>
                                        <div class="ticket-subject">{{ $ticket->subject }}</div>
                                        <div style="font-size: 12px; color: var(--text-secondary); margin-top: 4px; line-height: 1.4; white-space: pre-line;">{{ $ticket->description }}</div>
                                    </td>
                                    <td>
                                        <span class="badge-status badge-{{ $ticket->status }}">
                                            {{ str_replace('_', ' ', $ticket->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding: 20px;">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
