@extends('layouts.vendor')

@section('title', 'RFQs & Quotations')
@section('breadcrumb', 'RFQs')

@push('styles')
<style>
    .tab-nav {
        display: flex;
        gap: 16px;
        border-bottom: 1px solid var(--border);
        margin-bottom: 28px;
    }
    .tab-link {
        font-family: var(--font-display);
        font-size: 18px;
        letter-spacing: 1.5px;
        color: var(--text-secondary);
        background: none;
        border: none;
        padding: 10px 4px 8px;
        cursor: pointer;
        transition: all 0.2s;
        border-bottom: 2px solid transparent;
        text-decoration: none;
    }
    .tab-link:hover {
        color: var(--text-primary);
    }
    .tab-link.active {
        color: var(--purple-neon);
        border-color: var(--purple-bright);
        text-shadow: 0 0 10px var(--purple-glow);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
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
    .rfq-num {
        font-family: var(--font-display);
        font-size: 16px;
        letter-spacing: 1px;
        color: var(--text-primary);
    }
    .price-text {
        font-weight: 600;
        color: var(--purple-neon);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">Module</div>
    <h1 class="page-title">RFQs & Quotations</h1>
    <p class="page-subtitle">Review incoming Request for Quotations (RFQs) and track your submitted bids.</p>
</div>

<div class="tab-nav">
    <button class="tab-link active" onclick="switchTab('pending-rfqs', this)">Pending RFQs</button>
    <button class="tab-link" onclick="switchTab('submitted-quotes', this)">Submitted Quotations</button>
</div>

<!-- Pending RFQs Content -->
<div id="pending-rfqs" class="tab-content active">
    <div class="card">
        <div class="card-header">
            <div class="card-icon">⏳</div>
            <div>
                <div class="card-title">Assigned RFQs</div>
                <div class="card-desc">RFQs requiring your technical and commercial proposal.</div>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            @php
                // Filter RFQs that do not have a quotation submitted yet
                $pendingRfqs = $rfqs->filter(function($rfq) {
                    return !$rfq->quotation;
                });
            @endphp

            @if($pendingRfqs->isEmpty())
                <div style="text-align: center; padding: 48px 24px;">
                    <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;">🎉</div>
                    <h4 style="font-family: var(--font-display); font-size: 18px; letter-spacing: 1px; color: var(--text-primary);">All Caught Up</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">There are no pending RFQs awaiting your quotation.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>RFQ Number</th>
                                <th>Product</th>
                                <th>Date Received</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingRfqs as $rfq)
                                <tr>
                                    <td class="rfq-num">{{ $rfq->rfq_number }}</td>
                                    <td>{{ $rfq->product_name }}</td>
                                    <td>{{ $rfq->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('vendor.rfqs.show', $rfq->id) }}" class="btn btn-primary btn-sm" style="padding: 6px 16px; font-size: 12.5px; height: auto;">Quote</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding: 20px;">
                    {{ $rfqs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Submitted Quotations Content -->
<div id="submitted-quotes" class="tab-content">
    <div class="card">
        <div class="card-header">
            <div class="card-icon">✅</div>
            <div>
                <div class="card-title">Sent Quotations</div>
                <div class="card-desc">Bids you have submitted for procurement analysis.</div>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($quotations->isEmpty())
                <div style="text-align: center; padding: 48px 24px;">
                    <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;">💰</div>
                    <h4 style="font-family: var(--font-display); font-size: 18px; letter-spacing: 1px; color: var(--text-primary);">No Quotations Submitted</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Quotations you submit for RFQs will appear in this list.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>RFQ Number</th>
                                <th>Product Name</th>
                                <th>Quoted Price</th>
                                <th>Lead Time</th>
                                <th>Submitted Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($quotations as $quote)
                                <tr>
                                    <td class="rfq-num">{{ $quote->rfq->rfq_number ?? 'N/A' }}</td>
                                    <td>{{ $quote->rfq->product_name ?? 'N/A' }}</td>
                                    <td class="price-text">${{ number_format($quote->price, 2) }}</td>
                                    <td>{{ $quote->lead_time }}</td>
                                    <td>{{ $quote->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <a href="{{ route('vendor.rfqs.show', $quote->rfq_id) }}" class="btn btn-outline btn-sm" style="padding: 6px 16px; font-size: 12.5px; height: auto;">Details</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding: 20px;">
                    {{ $quotations->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    function switchTab(tabId, el) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-link').forEach(link => {
            link.classList.remove('active');
        });

        document.getElementById(tabId).classList.add('active');
        el.classList.add('active');
    }
</script>
@endpush
@endsection
