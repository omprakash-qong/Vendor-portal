@extends('layouts.vendor')

@section('title', 'RFQ Detail')
@section('breadcrumb', 'RFQs / Detail')

@push('styles')
<style>
    .details-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        align-items: start;
    }
    @media (max-width: 992px) {
        .details-grid {
            grid-template-columns: 1fr;
        }
    }
    .detail-item {
        margin-bottom: 20px;
    }
    .detail-label {
        font-size: 11px;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 4px;
    }
    .detail-val {
        font-size: 14.5px;
        color: var(--text-primary);
    }
    .rfq-title-large {
        font-family: var(--font-display);
        font-size: 26px;
        letter-spacing: 2px;
        color: var(--purple-neon);
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">Action</div>
    <h1 class="page-title">RFQ Details</h1>
    <p class="page-subtitle">Review Request for Quotation spec and submit or view your bid.</p>
</div>

<div style="margin-bottom: 24px;">
    <a href="{{ route('vendor.rfqs.index') }}" class="btn btn-outline" style="padding: 8px 20px; font-size: 13px;">⬅ Back to List</a>
</div>

<div class="details-grid">
    <!-- RFQ Technical Spec Card -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">⚡</div>
            <div>
                <div class="card-title">Specification Sheet</div>
                <div class="card-desc">Detailed requirements from procurement.</div>
            </div>
        </div>
        <div class="card-body">
            <div class="detail-item">
                <div class="detail-label">RFQ Number</div>
                <div class="detail-val rfq-title-large">{{ $rfq->rfq_number }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Product Required</div>
                <div class="detail-val" style="font-weight: 600;">{{ $rfq->product_name }}</div>
            </div>

            <div class="detail-item">
                <div class="detail-label">Date Received</div>
                <div class="detail-val">{{ $rfq->created_at->format('M d, Y H:i') }}</div>
            </div>

            <div class="divider"></div>

            <div class="detail-item">
                <div class="detail-label">Requirements Description</div>
                <div class="detail-val" style="line-height: 1.6; white-space: pre-line;">{{ $rfq->description ?? 'No detailed description provided.' }}</div>
            </div>
        </div>
    </div>

    <!-- Quotation Card -->
    @if($quotation)
        <!-- View Submitted Quotation -->
        <div class="card" style="border-color: rgba(52,211,153,0.3);">
            <div class="card-header" style="border-bottom-color: rgba(52,211,153,0.2);">
                <div class="card-icon" style="background: rgba(52,211,153,0.12); border-color: rgba(52,211,153,0.30); color: #6ee7b7;">✔</div>
                <div>
                    <div class="card-title" style="color: #6ee7b7;">Quotation Submitted</div>
                    <div class="card-desc">Details of your submitted bid proposal.</div>
                </div>
            </div>
            <div class="card-body">
                <div class="detail-item">
                    <div class="detail-label">Quoted Price</div>
                    <div class="detail-val" style="font-size: 24px; font-weight: 700; color: var(--purple-neon);">${{ number_format($quotation->price, 2) }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Guaranteed Lead Time</div>
                    <div class="detail-val">{{ $quotation->lead_time }}</div>
                </div>

                <div class="detail-item">
                    <div class="detail-label">Remarks</div>
                    <div class="detail-val" style="white-space: pre-line;">{{ $quotation->remarks ?? 'No remarks provided.' }}</div>
                </div>

                @if($quotation->attachment_path)
                    <div class="divider"></div>
                    <div class="detail-item">
                        <div class="detail-label">Commercial Attachment</div>
                        <div style="margin-top: 8px;">
                            <a href="{{ asset('storage/' . $quotation->attachment_path) }}" target="_blank" class="btn btn-outline" style="padding: 10px 24px; font-size: 13px; display: inline-flex;">
                                📄 View Attachment PDF
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <!-- Quotation Submission Form -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">💵</div>
                <div>
                    <div class="card-title">Submit Quotation</div>
                    <div class="card-desc">Provide your commercial bid for this RFQ.</div>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('vendor.rfqs.quote', $rfq->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="field" style="margin-bottom: 20px;">
                        <label class="field-label">Price ($) <span class="req">*</span></label>
                        <div class="input-wrap no-icon">
                            <input type="number" step="0.01" name="price" value="{{ old('price') }}" required placeholder="e.g. 4500.00">
                        </div>
                        @error('price')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field" style="margin-bottom: 20px;">
                        <label class="field-label">Lead Time <span class="req">*</span></label>
                        <div class="input-wrap no-icon">
                            <input type="text" name="lead_time" value="{{ old('lead_time') }}" required placeholder="e.g. 2-3 Weeks, 10 Days">
                        </div>
                        @error('lead_time')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field" style="margin-bottom: 20px;">
                        <label class="field-label">Remarks</label>
                        <div class="input-wrap no-icon">
                            <textarea name="remarks" placeholder="Optional notes regarding delivery, warranty, or packaging details...">{{ old('remarks') }}</textarea>
                        </div>
                        @error('remarks')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field" style="margin-bottom: 24px;">
                        <label class="field-label">Attachment (Optional PDF)</label>
                        <div class="file-drop">
                            <div class="file-drop-icon">📁</div>
                            <div class="file-drop-text">Drag PDF proposal here, or <span>browse</span></div>
                            <div class="file-drop-sub">PDF only (Max 10MB)</div>
                            <div class="file-name-display"></div>
                            <input type="file" name="attachment" accept="application/pdf">
                        </div>
                        @error('attachment')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Submit Quotation</button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
