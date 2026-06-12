@extends('layouts.vendor')

@section('title', 'New Quotation')
@section('breadcrumb', 'Quotations / New')

@section('content')
<div class="page-header">
    <div class="page-tag">📄 Sales</div>
    <h1 class="page-title">New Quotation</h1>
    <p class="page-subtitle">Fill in the customer, then attach the quotation PDF you want to send.</p>
</div>

@if($errors->any())
    <div class="alert alert-error">✖ {{ $errors->first() }}</div>
@endif

<div class="card" style="max-width:720px;">
    <div class="card-body">
        <form action="{{ route('vendor.quotations.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="field" style="margin-bottom:22px;">
                <label class="field-label">Customer Name <span class="req">*</span></label>
                <div class="input-wrap no-icon">
                    <input type="text" name="customer_name" value="{{ old('customer_name') }}" required placeholder="Customer name">
                </div>
                @error('customer_name')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="field" style="margin-bottom:22px;">
                <label class="field-label">Subject / Reference</label>
                <div class="input-wrap no-icon">
                    <input type="text" name="subject" value="{{ old('subject') }}" placeholder="Subject or reference for this quotation">
                </div>
            </div>

            <div class="field" style="margin-bottom:22px;">
                <label class="field-label">Notes</label>
                <div class="input-wrap no-icon">
                    <textarea name="remarks" placeholder="Anything you want noted with this quotation (optional)">{{ old('remarks') }}</textarea>
                </div>
            </div>

            <div class="field" style="margin-bottom:26px;">
                <label class="field-label">Quotation PDF <span class="req">*</span></label>
                <div class="file-drop">
                    <div class="file-drop-icon">📄</div>
                    <div class="file-drop-text">Drag &amp; drop your quotation PDF, or <span>browse</span></div>
                    <div class="file-drop-sub">PDF only · Max 20MB</div>
                    <div class="file-name-display"></div>
                    <input type="file" name="attachment" accept="application/pdf,.pdf" required>
                </div>
                @error('attachment')<span class="field-error">{{ $message }}</span>@enderror
            </div>

            <div class="btn-row" style="padding-left:0;padding-right:0;">
                <a href="{{ route('vendor.quotations.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">Save Quotation</button>
            </div>
        </form>
    </div>
</div>
@endsection
