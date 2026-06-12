@extends('layouts.vendor')

@section('title', 'Upload Catalogue')
@section('breadcrumb', 'Catalogue / Upload')

@push('styles')
<style>
    .upload-zone {
        border: 2px dashed var(--border-bright);
        border-radius: var(--radius);
        padding: 60px 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: var(--bg-card);
        position: relative;
    }
    .upload-zone:hover, .upload-zone.drag-over {
        border-color: var(--purple-bright);
        background: rgba(168,85,247,0.06);
    }
    .upload-icon { font-size: 48px; margin-bottom: 16px; }
    .upload-title { font-size: 20px; font-weight: 600; color: var(--text-primary); margin-bottom: 8px; }
    .upload-sub   { color: var(--text-secondary); font-size: 13.5px; margin-bottom: 24px; }
    .upload-zone input[type="file"] {
        position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }
    .format-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-top: 32px;
    }
    .format-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-sm);
        padding: 20px;
        text-align: center;
    }
    .format-icon { font-size: 28px; margin-bottom: 10px; }
    .format-name { font-weight: 600; color: var(--text-primary); margin-bottom: 4px; }
    .format-desc { font-size: 12px; color: var(--text-secondary); }
    .not-supported {
        margin-top: 24px;
        padding: 14px 20px;
        background: rgba(248,113,113,0.07);
        border: 1px solid rgba(248,113,113,0.25);
        border-radius: var(--radius-sm);
        font-size: 13px;
        color: var(--error);
    }
    .not-supported strong { display: block; margin-bottom: 4px; }
    .selected-file-info {
        display: none;
        margin-top: 16px;
        padding: 12px 18px;
        background: rgba(168,85,247,0.08);
        border: 1px solid var(--border-bright);
        border-radius: var(--radius-sm);
        font-size: 13.5px;
        color: var(--text-primary);
    }
    .btn-upload {
        margin-top: 20px;
        display: none;
    }
    .btn-upload.visible { display: inline-block; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">Action</div>
    <h1 class="page-title">Upload Product Catalogue</h1>
    <p class="page-subtitle">Upload your product catalogue. Products will be extracted and added to your database automatically.</p>
</div>

@if ($errors->any())
<div class="alert alert-error" style="margin-bottom:24px;">
    {{ $errors->first() }}
</div>
@endif

<div class="card">
    <div class="card-body" style="padding: 32px;">
        <form action="{{ route('vendor.catalogue.store') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
            @csrf

            <div class="upload-zone" id="dropZone">
                <input type="file" name="file" id="fileInput" accept=".xlsx,.xls,.csv,.pdf" required>
                <div class="upload-icon">📂</div>
                <div class="upload-title">Drag & Drop or Click to Upload</div>
                <div class="upload-sub">Excel (.xlsx), CSV, or Digital PDF only</div>
                <button type="button" class="btn btn-outline" onclick="event.stopPropagation();">Browse Files</button>
            </div>

            <div class="selected-file-info" id="fileInfo">
                <strong id="fileName"></strong>
                <span id="fileSize" style="color:var(--text-secondary);margin-left:8px;"></span>
            </div>

            <button type="submit" class="btn btn-primary btn-upload" id="submitBtn">
                Upload & Start Extraction
            </button>
        </form>
    </div>
</div>

<div class="format-cards">
    <div class="format-card">
        <div class="format-icon">📊</div>
        <div class="format-name">Excel (.xlsx)</div>
        <div class="format-desc">Structured tables with headers. Multiple sheets supported.</div>
    </div>
    <div class="format-card">
        <div class="format-icon">📋</div>
        <div class="format-name">CSV</div>
        <div class="format-desc">Comma-separated values. Simple and reliable.</div>
    </div>
    <div class="format-card">
        <div class="format-icon">📄</div>
        <div class="format-name">Digital PDF</div>
        <div class="format-desc">Text must be selectable — not a scanned image.</div>
    </div>
</div>

<div class="not-supported">
    <strong>Not Supported</strong>
    Scanned PDFs, image-based PDFs, JPG, PNG — these cannot be processed accurately. Please export your catalogue as Excel or a digital PDF.
</div>

<script>
const dropZone  = document.getElementById('dropZone');
const fileInput = document.getElementById('fileInput');
const fileInfo  = document.getElementById('fileInfo');
const fileName  = document.getElementById('fileName');
const fileSize  = document.getElementById('fileSize');
const submitBtn = document.getElementById('submitBtn');

function updateUI(file) {
    if (!file) return;
    fileName.textContent = file.name;
    const mb = (file.size / 1048576).toFixed(1);
    fileSize.textContent = mb + ' MB';
    fileInfo.style.display = 'block';
    submitBtn.classList.add('visible');
}

fileInput.addEventListener('change', () => updateUI(fileInput.files[0]));

dropZone.addEventListener('dragover', e => { e.preventDefault(); dropZone.classList.add('drag-over'); });
dropZone.addEventListener('dragleave', () => dropZone.classList.remove('drag-over'));
dropZone.addEventListener('drop', e => {
    e.preventDefault();
    dropZone.classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) {
        const dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
        updateUI(file);
    }
});
</script>
@endsection
