@extends('layouts.vendor')

@section('title', 'Datasheets')
@section('breadcrumb', 'Datasheets')

@push('styles')
<style>
    .split-layout {
        display: grid;
        grid-template-columns: 350px 1fr;
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
    .action-links {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    .action-link {
        font-size: 12.5px;
        color: var(--purple-neon);
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s;
        background: none;
        border: none;
        padding: 0;
    }
    .action-link:hover {
        color: var(--text-primary);
    }
    .action-link.danger {
        color: #f87171;
    }
    .action-link.danger:hover {
        color: #fca5a5;
    }

    /* Modal Styling */
    .custom-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(10,0,21,0.85);
        backdrop-filter: blur(8px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 1000;
    }
    .custom-modal {
        background: #0f0020;
        border: 1px solid var(--border-bright);
        border-radius: var(--radius);
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 50px rgba(168,85,247,0.3);
        overflow: hidden;
        animation: modalFadeIn 0.3s ease-out;
    }
    @keyframes modalFadeIn {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .modal-header {
        padding: 20px 24px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .modal-title {
        font-family: var(--font-display);
        font-size: 20px;
        letter-spacing: 2px;
        color: var(--text-primary);
    }
    .modal-close {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 20px;
        cursor: pointer;
        transition: color 0.2s;
    }
    .modal-close:hover {
        color: #fff;
    }
    .modal-body {
        padding: 24px;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">Module</div>
    <h1 class="page-title">Technical Datasheets</h1>
    <p class="page-subtitle">Upload and link specification sheets, manuals, or testing reports to your products.</p>
</div>

<div class="split-layout">
    <!-- Left: Upload Datasheet Form -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">📄</div>
            <div>
                <div class="card-title">Upload Datasheet</div>
                <div class="card-desc">Add document details.</div>
            </div>
        </div>
        <div class="card-body">
            @if($products->isEmpty())
                <div style="text-align: center; padding: 12px 0;">
                    <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 16px;">You must register at least one product before uploading a datasheet.</p>
                    <a href="{{ route('vendor.products.create') }}" class="btn btn-primary btn-sm" style="display: inline-block;">Register Product</a>
                </div>
            @else
                <form action="{{ route('vendor.datasheets.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="field" style="margin-bottom: 20px;">
                        <label class="field-label">Select Product <span class="req">*</span></label>
                        <div class="input-wrap no-icon">
                            <select name="product_id" required>
                                <option value="">-- Choose Product --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('product_id')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field" style="margin-bottom: 20px;">
                        <label class="field-label">Datasheet Name <span class="req">*</span></label>
                        <div class="input-wrap no-icon">
                            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Pump curve & dimensions">
                        </div>
                        @error('name')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field" style="margin-bottom: 20px;">
                        <label class="field-label">Upload PDF <span class="req">*</span></label>
                        <div class="file-drop">
                            <div class="file-drop-icon">📄</div>
                            <div class="file-drop-text">Drag PDF here, or <span>browse</span></div>
                            <div class="file-drop-sub">PDF only (Max 10MB)</div>
                            <div class="file-name-display"></div>
                            <input type="file" name="pdf" accept="application/pdf" required>
                        </div>
                        @error('pdf')
                            <span class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center;">Upload Datasheet</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Right: Datasheets List -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">📂</div>
            <div>
                <div class="card-title">Uploaded Datasheets</div>
                <div class="card-desc">Overview of your technical product literature.</div>
            </div>
        </div>
        <div class="card-body" style="padding: 0;">
            @if($datasheets->isEmpty())
                <div style="text-align: center; padding: 48px 24px;">
                    <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.3;">📄</div>
                    <h4 style="font-family: var(--font-display); font-size: 18px; letter-spacing: 1px; color: var(--text-primary);">No Datasheets Uploaded</h4>
                    <p style="color: var(--text-muted); font-size: 13px; margin-top: 4px;">Use the upload form to add specifications for your registered products.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Datasheet Name</th>
                                <th>Product Name</th>
                                <th>Upload Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($datasheets as $ds)
                                <tr>
                                    <td>
                                        <div style="font-weight: 500; color: var(--text-primary);">{{ $ds->name }}</div>
                                        <div style="font-size: 11px; color: var(--text-muted);">PDF Specification</div>
                                    </td>
                                    <td>{{ $ds->product->name ?? 'N/A' }}</td>
                                    <td>{{ $ds->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="action-links">
                                            <a href="{{ asset('storage/' . $ds->pdf_path) }}" target="_blank" class="action-link">View</a>
                                            <a href="{{ asset('storage/' . $ds->pdf_path) }}" download class="action-link">Download</a>
                                            <button type="button" class="action-link" onclick="openReplaceModal({{ $ds->id }}, '{{ addslashes($ds->name) }}', {{ $ds->product_id }})">Replace</button>
                                            <form action="{{ route('vendor.datasheets.destroy', $ds->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Are you sure you want to delete this datasheet?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-link danger">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div style="padding: 20px;">
                    {{ $datasheets->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Replace Modal -->
<div id="replaceModalBackdrop" class="custom-modal-backdrop">
    <div class="custom-modal">
        <div class="modal-header">
            <h3 class="modal-title">Replace Datasheet</h3>
            <button class="modal-close" onclick="closeReplaceModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="replaceForm" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="field" style="margin-bottom: 20px;">
                    <label class="field-label">Select Product <span class="req">*</span></label>
                    <div class="input-wrap no-icon">
                        <select name="product_id" id="replace_product_id" required>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="field" style="margin-bottom: 20px;">
                    <label class="field-label">Datasheet Name <span class="req">*</span></label>
                    <div class="input-wrap no-icon">
                        <input type="text" name="name" id="replace_name" required placeholder="e.g. Pump curve & dimensions">
                    </div>
                </div>

                <div class="field" style="margin-bottom: 20px;">
                    <label class="field-label">Upload New PDF <span class="req">*</span></label>
                    <div class="file-drop">
                        <div class="file-drop-icon">📄</div>
                        <div class="file-drop-text">Drag PDF here, or <span>browse</span></div>
                        <div class="file-drop-sub">PDF only (Max 10MB) - Will replace existing PDF file</div>
                        <div class="file-name-display"></div>
                        <input type="file" name="pdf" accept="application/pdf" required>
                    </div>
                </div>

                <div class="btn-row" style="padding: 16px 0 0; border-top: none;">
                    <button type="button" class="btn btn-outline" onclick="closeReplaceModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const replaceBackdrop = document.getElementById('replaceModalBackdrop');
    const replaceForm = document.getElementById('replaceForm');
    const replaceName = document.getElementById('replace_name');
    const replaceProduct = document.getElementById('replace_product_id');

    function openReplaceModal(id, name, productId) {
        replaceForm.action = `/vendor/datasheets/${id}/replace`;
        replaceName.value = name;
        replaceProduct.value = productId;
        replaceBackdrop.style.display = 'flex';
    }

    function closeReplaceModal() {
        replaceBackdrop.style.display = 'none';
    }

    // Close on click outside modal content
    replaceBackdrop.addEventListener('click', (e) => {
        if (e.target === replaceBackdrop) {
            closeReplaceModal();
        }
    });
</script>
@endpush
@endsection
