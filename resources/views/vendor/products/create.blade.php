@extends('layouts.vendor')

@section('title', 'Add Product')
@section('breadcrumb', 'My Products / Add')

@push('styles')
<style>
    .spec-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
    @media (max-width: 760px) { .spec-grid { grid-template-columns: 1fr; } }
    .section-label {
        font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
        color: var(--text-muted); margin: 6px 0 14px;
    }
    .spec-empty { color: var(--text-muted); font-size: 13px; padding: 6px 0; }
    .extra-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .extra-row input { flex: 1; }
    .extra-remove {
        border: 1px solid rgba(239,68,68,0.3); color: #dc2626; background: rgba(239,68,68,0.05);
        border-radius: var(--radius-sm); padding: 0 14px; cursor: pointer; font-weight: 600;
    }
    .add-extra-btn {
        display: inline-flex; align-items: center; gap: 6px; background: transparent;
        border: 1px dashed var(--border); border-radius: var(--radius-sm); color: var(--text-muted);
        font-size: 13px; padding: 9px 16px; cursor: pointer; transition: all 0.2s;
    }
    .add-extra-btn:hover { border-color: var(--purple-bright); color: var(--purple-neon); }
    .divider-soft { height: 1px; background: var(--border); margin: 26px 0; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">📦 Catalogue</div>
    <h1 class="page-title">Add Product</h1>
    <p class="page-subtitle">Fill in the details. Pick a category to see the fields for that product type.</p>
</div>

@if($errors->any())
    <div class="alert alert-error">✖ {{ $errors->first() }}</div>
@endif

<div class="card" style="max-width:900px;">
    <div class="card-body">
        <form action="{{ route('vendor.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Common fields --}}
            <div class="form-grid form-grid-2" style="margin-bottom:22px;">
                <div class="field">
                    <label class="field-label">Product Name <span class="req">*</span></label>
                    <div class="input-wrap no-icon">
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Enter product name">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Brand</label>
                    <div class="input-wrap no-icon">
                        <input type="text" name="brand" value="{{ old('brand') }}" placeholder="Enter brand">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Product <span class="req">*</span></label>
                    <div class="input-wrap no-icon">
                        <select name="category" id="categorySelect" required>
                            <option value="">-- Select Product --</option>
                            @foreach(array_keys($categoryFields) as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="field" style="margin-bottom:22px;">
                <label class="field-label">Description</label>
                <div class="input-wrap no-icon">
                    <textarea name="description" placeholder="Full description, ratings, materials, features…">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="field" style="margin-bottom:8px;">
                <label class="field-label">Product Image</label>
                <div class="file-drop">
                    <div class="file-drop-icon">🖼</div>
                    <div class="file-drop-text">Drag &amp; drop an image, or <span>browse</span></div>
                    <div class="file-drop-sub">JPG, PNG, WEBP · Max 4MB</div>
                    <div class="file-name-display"></div>
                    <input type="file" name="image" accept="image/*">
                </div>
            </div>

            <div class="divider-soft"></div>

            {{-- Category-specific fields (rendered by JS on category change) --}}
            <div class="section-label" id="specHeading">Specifications</div>
            <div id="specFields" class="spec-grid"></div>
            <div id="specEmpty" class="spec-empty">Select a category above to see its specification fields.</div>

            <div class="divider-soft"></div>

            {{-- Additional / unknown specs --}}
            <div class="section-label">Other Details (optional)</div>
            <div id="extraRows"></div>
            <button type="button" class="add-extra-btn" id="addExtraBtn">+ Add another detail</button>

            <div class="btn-row" style="padding-left:0;padding-right:0;margin-top:8px;">
                <a href="{{ route('vendor.products.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">✔ Save Product</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const FIELDS   = @json($categoryFields);
    const OLD_SPEC = @json(old('specs', (object)[]));

    const sel      = document.getElementById('categorySelect');
    const container = document.getElementById('specFields');
    const emptyMsg = document.getElementById('specEmpty');

    function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;'); }

    function renderFields() {
        const cat = sel.value;
        container.innerHTML = '';
        const fields = FIELDS[cat];
        if (!cat || !fields) { emptyMsg.style.display = 'block'; return; }
        emptyMsg.style.display = 'none';
        for (const key in fields) {
            const val = (OLD_SPEC && OLD_SPEC[key]) ? OLD_SPEC[key] : '';
            const wrap = document.createElement('div');
            wrap.className = 'field';
            wrap.innerHTML =
                '<label class="field-label">' + esc(fields[key]) + '</label>' +
                '<div class="input-wrap no-icon">' +
                '<input type="text" name="specs[' + esc(key) + ']" value="' + esc(val) + '" placeholder="' + esc(fields[key]) + '">' +
                '</div>';
            container.appendChild(wrap);
        }
    }
    sel.addEventListener('change', renderFields);
    renderFields(); // restore on validation error

    // Additional details repeater
    const extraRows = document.getElementById('extraRows');
    document.getElementById('addExtraBtn').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'extra-row';
        row.innerHTML =
            '<div class="input-wrap no-icon" style="flex:1"><input type="text" name="extra_name[]" placeholder="Detail name"></div>' +
            '<div class="input-wrap no-icon" style="flex:1"><input type="text" name="extra_value[]" placeholder="Value"></div>' +
            '<button type="button" class="extra-remove" onclick="this.closest(\'.extra-row\').remove()">✕</button>';
        extraRows.appendChild(row);
    });
})();
</script>
@endpush
