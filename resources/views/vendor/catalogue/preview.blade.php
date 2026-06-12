@extends('layouts.vendor')

@section('title', 'Review Extraction')
@section('breadcrumb', 'Catalogue / Review')

@push('styles')
<style>
    .series-block {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        margin-bottom: 24px;
        overflow: hidden;
    }
    .series-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 1px solid var(--border);
        background: rgba(168,85,247,0.04);
    }
    .series-title { font-weight: 700; font-size: 16px; color: var(--text-primary); }
    .series-meta  { font-size: 12.5px; color: var(--text-muted); margin-top: 3px; }
    .series-badge { background: rgba(168,85,247,0.15); color: var(--purple-bright); padding: 3px 10px; border-radius:20px; font-size:12px; }
    .variants-table { width: 100%; border-collapse: collapse; }
    .variants-table th {
        padding: 10px 14px; font-size: 11px; text-transform: uppercase;
        letter-spacing: 0.8px; color: var(--text-muted);
        border-bottom: 1px solid var(--border); text-align: left;
    }
    .variants-table td {
        padding: 10px 14px; font-size: 13px; color: var(--text-primary);
        border-bottom: 1px solid rgba(255,255,255,0.04);
        vertical-align: top;
    }
    .variants-table tr:last-child td { border-bottom: none; }
    .editable-cell {
        background: transparent; border: 1px solid transparent; border-radius: 4px;
        color: var(--text-primary); font-size: 13px; padding: 3px 6px; width: 100%;
        transition: border-color 0.2s;
    }
    .editable-cell:focus {
        outline: none; border-color: var(--purple-bright);
        background: rgba(168,85,247,0.05);
    }
    .specs-badge {
        display: inline-block; background: rgba(255,255,255,0.05);
        border: 1px solid var(--border); border-radius: 4px;
        padding: 2px 8px; font-size: 11px; color: var(--text-secondary);
        margin: 2px;
    }
    .approve-bar {
        position: sticky; bottom: 0;
        background: rgba(10,0,21,0.92); backdrop-filter: blur(20px);
        border-top: 1px solid var(--border);
        padding: 16px 24px;
        display: flex; align-items: center; justify-content: space-between;
        z-index: 100;
    }
    .variant-count { font-size: 14px; color: var(--text-secondary); }
    .variant-count strong { color: var(--purple-neon); }
    .btn-del { background:none; border:none; color:var(--error); cursor:pointer; font-size:16px; padding:2px 6px; }
    .btn-del:hover { opacity: 0.7; }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="page-tag">Review</div>
    <h1 class="page-title">Review Extracted Products</h1>
    <p class="page-subtitle">
        AI has structured your catalogue. Review the data below, edit any field, then click
        <strong style="color:var(--purple-neon)">Approve &amp; Publish</strong> to make these products
        available for Qong Studio matching.
    </p>
</div>

@php
    $structured = $job->ai_structured ?? ['vendor_series' => []];
    $series = $structured['vendor_series'] ?? [];
    $totalVariants = collect($series)->sum(fn($s) => count($s['variants'] ?? []));
@endphp

@if(count($series) === 0)
<div class="card" style="text-align:center; padding:48px;">
    <div style="font-size:36px; margin-bottom:16px;">🤖</div>
    <h3 style="color:var(--text-primary); margin-bottom:8px;">AI returned no structured data</h3>
    <p style="color:var(--text-muted); margin-bottom:24px;">
        This can happen when the file has unusual formatting. Please add your products manually or re-upload a cleaner file.
    </p>
    <a href="{{ route('vendor.catalogue.upload') }}" class="btn btn-outline">Upload Again</a>
    &nbsp;
    <form method="POST" action="{{ route('vendor.catalogue.reject', $job) }}" style="display:inline;">
        @csrf
        <button type="submit" class="btn btn-outline" style="color:var(--error);border-color:var(--error);">Discard Job</button>
    </form>
</div>
@else

<form method="POST" action="{{ route('vendor.catalogue.approve', $job) }}" id="approveForm">
    @csrf

    @foreach($series as $si => $s)
    <div class="series-block">
        <div class="series-header">
            <div>
                <div class="series-title">{{ $s['series_name'] ?? 'Unknown Series' }}</div>
                <div class="series-meta">
                    Brand: {{ $s['brand'] ?? '—' }} &nbsp;|&nbsp; Category: {{ $s['category'] ?? '—' }}
                </div>
            </div>
            <span class="series-badge">{{ count($s['variants'] ?? []) }} variants</span>
        </div>

        <div style="overflow-x:auto;">
            <table class="variants-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Variant Name</th>
                        <th>Type</th>
                        <th>Power (kW)</th>
                        <th>Size (inch)</th>
                        <th>Size (mm)</th>
                        <th>Pressure (bar)</th>
                        <th>Flow (m³/h)</th>
                        <th>Poles</th>
                        <th>Specifications</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="tbody-{{ $si }}">
                    @foreach($s['variants'] ?? [] as $vi => $v)
                    <tr id="variant-row-{{ $si }}-{{ $vi }}">
                        <td style="color:var(--text-muted);">{{ $vi + 1 }}</td>
                        <td>
                            <input type="text"
                                   name="variants[{{ $si }}][{{ $vi }}][variant_name]"
                                   value="{{ $v['variant_name'] ?? '' }}"
                                   class="editable-cell" style="min-width:140px;">
                            <input type="hidden" name="variants[{{ $si }}][{{ $vi }}][equipment_type]" value="{{ $v['equipment_type'] ?? '' }}">
                            <input type="hidden" name="variants[{{ $si }}][{{ $vi }}][series_idx]" value="{{ $si }}">
                        </td>
                        <td style="color:var(--text-secondary);">{{ $v['equipment_type'] ?? '—' }}</td>
                        <td>
                            <input type="number" step="0.01"
                                   name="variants[{{ $si }}][{{ $vi }}][power_kw]"
                                   value="{{ $v['power_kw'] ?? '' }}"
                                   class="editable-cell" style="width:70px;">
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                   name="variants[{{ $si }}][{{ $vi }}][size_inch]"
                                   value="{{ $v['size_inch'] ?? '' }}"
                                   class="editable-cell" style="width:70px;">
                        </td>
                        <td>
                            <input type="number" step="0.1"
                                   name="variants[{{ $si }}][{{ $vi }}][size_mm]"
                                   value="{{ $v['size_mm'] ?? '' }}"
                                   class="editable-cell" style="width:70px;">
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                   name="variants[{{ $si }}][{{ $vi }}][pressure_bar]"
                                   value="{{ $v['pressure_bar'] ?? '' }}"
                                   class="editable-cell" style="width:70px;">
                        </td>
                        <td>
                            <input type="number" step="0.01"
                                   name="variants[{{ $si }}][{{ $vi }}][flow_m3h]"
                                   value="{{ $v['flow_m3h'] ?? '' }}"
                                   class="editable-cell" style="width:70px;">
                        </td>
                        <td>
                            <input type="number" step="1"
                                   name="variants[{{ $si }}][{{ $vi }}][poles]"
                                   value="{{ $v['poles'] ?? '' }}"
                                   class="editable-cell" style="width:50px;">
                        </td>
                        <td style="max-width:220px;">
                            @foreach($v['specifications'] ?? [] as $k => $val)
                                @if($val)
                                <span class="specs-badge">{{ $k }}: {{ $val }}</span>
                                @endif
                            @endforeach
                            @foreach($v['capability_tags'] ?? [] as $tag)
                                <span class="specs-badge" style="color:var(--purple-neon);">{{ $tag }}</span>
                            @endforeach
                        </td>
                        <td>
                            <button type="button" class="btn-del" title="Remove variant"
                                    onclick="removeVariant('variant-row-{{ $si }}-{{ $vi }}')">✕</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endforeach

    <div class="approve-bar">
        <div class="variant-count">
            <strong>{{ $totalVariants }}</strong> variants detected across
            <strong>{{ count($series) }}</strong> series
        </div>
        <div style="display:flex; gap:12px;">
            <form method="POST" action="{{ route('vendor.catalogue.reject', $job) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-outline" style="color:var(--error);border-color:rgba(248,113,113,0.4);">
                    Discard
                </button>
            </form>
            <button type="submit" form="approveForm" class="btn btn-primary">
                Approve &amp; Publish Products
            </button>
        </div>
    </div>
</form>
@endif

<script>
function removeVariant(rowId) {
    const row = document.getElementById(rowId);
    if (row) row.remove();
}
</script>
@endsection
