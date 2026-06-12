@extends('layouts.vendor')

@section('title', $product->status === 'inactive' ? 'Review Product' : 'Edit Product')
@section('breadcrumb', $product->status === 'inactive' ? 'My Products / Review' : 'My Products / Edit')

@push('styles')
<style>
.review-banner {
    display: flex; align-items: center; gap: 10px;
    color: #92600c;
    padding: 6px 0; margin-bottom: 20px; font-size: 13.5px; line-height: 1.5;
}
.review-banner .ico { font-size: 20px; }
.section-label {
    font-size: 11px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--text-muted); margin: 6px 0 14px;
}
.spec-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
@media (max-width: 760px) { .spec-grid { grid-template-columns: 1fr; } }
.spec-empty { color: var(--text-muted); font-size: 13px; padding: 6px 0; }
.extra-row { display: flex; gap: 10px; margin-bottom: 10px; }
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
.current-img { max-width: 150px; border-radius: var(--radius-sm); border: 1px solid var(--border); }
</style>
@endpush

@section('content')
@php
    $specs   = $product->specifications ?? [];
    $extra   = (isset($specs['extra']) && is_array($specs['extra'])) ? $specs['extra'] : [];
    $catKeys = array_keys($categoryFields);
@endphp

<div class="page-header">
    <div class="page-tag">{{ $product->status === 'inactive' ? '📝 Review' : '✏️ Edit' }}</div>
    <h1 class="page-title">{{ $product->status === 'inactive' ? 'Review Product' : 'Edit Product' }}</h1>
    <p class="page-subtitle">Check the details, then click <strong>Save</strong> to add this product to your catalogue.</p>
</div>

@if($product->status === 'inactive')
<div class="review-banner">
    <span class="ico">⏳</span>
    <span>This product was brought in automatically and is waiting for your review. Edit anything that looks off, then <strong>Save</strong> — it will appear in your live catalogue.</span>
</div>
@endif

@if($errors->any())<div class="alert alert-error">✖ {{ $errors->first() }}</div>@endif

<div class="card" style="max-width:900px;">
    <div class="card-body">
        <form action="{{ route('vendor.products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-grid form-grid-2" style="margin-bottom:22px;">
                <div class="field">
                    <label class="field-label">Product Name <span class="req">*</span></label>
                    <div class="input-wrap no-icon">
                        <input type="text" name="name" value="{{ old('name', $product->name) }}" required>
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Brand</label>
                    <div class="input-wrap no-icon">
                        <input type="text" name="brand" value="{{ old('brand', $product->brand) }}">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Model Number</label>
                    <div class="input-wrap no-icon">
                        <input type="text" name="model_number" value="{{ old('model_number', $product->model_number) }}">
                    </div>
                </div>
                <div class="field">
                    <label class="field-label">Product <span class="req">*</span></label>
                    <div class="input-wrap no-icon">
                        <select name="category" id="categorySelect" required>
                            <option value="">-- Select Product --</option>
                            @foreach($catKeys as $cat)
                                <option value="{{ $cat }}" {{ old('category', $product->category) == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                            @if($product->category && !in_array($product->category, $catKeys))
                                <option value="{{ $product->category }}" selected>{{ $product->category }}</option>
                            @endif
                        </select>
                    </div>
                </div>
            </div>

            <div class="field" style="margin-bottom:22px;">
                <label class="field-label">Description</label>
                <div class="input-wrap no-icon">
                    <textarea name="description" placeholder="Full description, ratings, materials, features…">{{ old('description', $product->description) }}</textarea>
                </div>
            </div>

            @if($product->image_path)
                <div style="margin-bottom:18px;">
                    <div class="section-label">Current Image</div>
                    <img src="{{ asset('storage/' . $product->image_path) }}" alt="{{ $product->name }}" class="current-img">
                </div>
            @endif
            <div class="field" style="margin-bottom:8px;">
                <label class="field-label">{{ $product->image_path ? 'Replace Image' : 'Add Product Image' }}</label>
                <div class="file-drop">
                    <div class="file-drop-icon">🖼</div>
                    <div class="file-drop-text">Drag &amp; drop an image, or <span>browse</span></div>
                    <div class="file-drop-sub">JPG, PNG, WEBP · Max 4MB</div>
                    <div class="file-name-display"></div>
                    <input type="file" name="image" accept="image/*">
                </div>
            </div>

            <div class="divider-soft"></div>

            <div class="section-label">Specifications</div>
            <div id="specFields" class="spec-grid"></div>
            <div id="specEmpty" class="spec-empty">Select a category above to see its specification fields.</div>

            <div class="divider-soft"></div>

            <div class="section-label">Other Details (optional)</div>
            <div id="extraRows">
                @foreach($extra as $k => $v)
                <div class="extra-row">
                    <div class="input-wrap no-icon" style="flex:1"><input type="text" name="extra_name[]" value="{{ $k }}"></div>
                    <div class="input-wrap no-icon" style="flex:1"><input type="text" name="extra_value[]" value="{{ $v }}"></div>
                    <button type="button" class="extra-remove" onclick="this.closest('.extra-row').remove()">✕</button>
                </div>
                @endforeach
            </div>
            <button type="button" class="add-extra-btn" id="addExtraBtn">+ Add another detail</button>

            <div class="btn-row" style="padding-left:0;padding-right:0;margin-top:8px;">
                <a href="{{ route('vendor.products.index') }}" class="btn btn-outline">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    {{ $product->status === 'inactive' ? '✔ Save & Add to Catalogue' : 'Save Changes' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';
    const FIELDS  = @json($categoryFields);
    @php $specForJs = collect($specs)->except('extra'); @endphp
    const SAVED   = @json($specForJs->isEmpty() ? (object)[] : $specForJs);

    const sel       = document.getElementById('categorySelect');
    const container = document.getElementById('specFields');
    const emptyMsg  = document.getElementById('specEmpty');

    function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/"/g,'&quot;'); }

    function renderFields() {
        const cat = sel.value;
        container.innerHTML = '';
        const fields = FIELDS[cat];
        if (!cat || !fields) { emptyMsg.style.display = 'block'; return; }
        emptyMsg.style.display = 'none';
        for (const key in fields) {
            const val = (SAVED && SAVED[key] != null) ? SAVED[key] : '';
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
    renderFields();

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
