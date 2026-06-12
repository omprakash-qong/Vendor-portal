@extends('layouts.vendor')

@section('title', 'My Products')
@section('breadcrumb', 'Catalogue / My Products')

@push('styles')
<style>
    .status-dot-pub {
        display: inline-block; width:7px; height:7px; border-radius:50%;
        background: var(--success); margin-right:5px;
    }
    .status-dot-del {
        display: inline-block; width:7px; height:7px; border-radius:50%;
        background: var(--error); margin-right:5px;
    }
    .tag-chip {
        display: inline-block; font-size:11px; padding:2px 8px;
        border-radius:20px; background:rgba(168,85,247,0.1);
        color: var(--purple-neon); margin:2px;
    }
    .score-bar { display:inline-block; width:60px; height:5px; background:rgba(255,255,255,0.08); border-radius:3px; }
    .score-fill { height:100%; border-radius:3px; background: var(--purple-bright); }
    .tbl-row:hover td { background: rgba(168,85,247,0.03); }
    .empty-state { text-align:center; padding:60px; color:var(--text-muted); }
    .processing-banner {
        display:flex; align-items:center; gap:14px;
        padding:14px 20px; margin-bottom:24px;
        background: rgba(168,85,247,0.08);
        border: 1px solid rgba(168,85,247,0.30);
        border-radius: var(--radius-sm);
        font-size:13.5px; color:var(--purple-neon);
    }
    .spin { display:inline-block; animation: spin 1s linear infinite; font-size:18px; }
    @keyframes spin { to { transform:rotate(360deg); } }
</style>
@endpush

@section('content')
<div class="page-header" style="display:flex; align-items:flex-start; justify-content:space-between;">
    <div>
        <div class="page-tag">Catalogue</div>
        <h1 class="page-title">My Products</h1>
        <p class="page-subtitle">Products visible to Qong Studio for matching.</p>
    </div>
    <a href="{{ route('vendor.catalogue.upload') }}" class="btn btn-primary" style="margin-top:8px;">+ Upload More</a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="margin-bottom:24px;">{{ session('success') }}</div>
@endif

@if($processingJobIds->isNotEmpty())
<div class="processing-banner" id="processingBanner">
    <span class="spin">⟳</span>
    <span id="processingText">Extracting products from your catalogue — this page will update automatically.</span>
</div>
@endif

@php
    $published  = $variants->filter(fn($v) => $v->is_published && !$v->deleted_at);
    $unpublished = $variants->filter(fn($v) => $v->deleted_at);
@endphp

@if($variants->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div style="font-size:40px; margin-bottom:16px;">📦</div>
            <p>No products published yet. Upload a catalogue to get started.</p>
            <a href="{{ route('vendor.catalogue.upload') }}" class="btn btn-primary" style="margin-top:20px;">Upload Catalogue</a>
        </div>
    </div>
@else
<div class="card">
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid var(--border);">
                    <th style="padding:12px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted);">Variant</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted);">Series</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted);">Category</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted);">Key Specs</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted);">Tags</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted);">Status</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted);"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($variants as $v)
                <tr class="tbl-row" style="border-bottom:1px solid rgba(255,255,255,0.04);">
                    <td style="padding:12px 16px;">
                        <div style="font-weight:600; font-size:13.5px; color:var(--text-primary);">{{ $v->variant_name }}</div>
                        <div style="font-size:12px; color:var(--text-muted);">{{ $v->equipment_type }}</div>
                    </td>
                    <td style="padding:12px 16px; font-size:13px; color:var(--text-secondary);">
                        {{ $v->series->name ?? '—' }}
                        @if($v->series->brand)
                            <div style="font-size:11.5px; color:var(--text-muted);">{{ $v->series->brand }}</div>
                        @endif
                    </td>
                    <td style="padding:12px 16px; font-size:13px; color:var(--text-secondary);">{{ $v->category->name ?? '—' }}</td>
                    <td style="padding:12px 16px; font-size:12.5px; color:var(--text-secondary);">
                        @if($v->power_kw)    <div>⚡ {{ $v->power_kw }} kW</div>@endif
                        @if($v->size_inch)   <div>⌀ {{ $v->size_inch }}"</div>@endif
                        @if($v->size_mm)     <div>⌀ {{ $v->size_mm }} mm</div>@endif
                        @if($v->pressure_bar)<div>💨 {{ $v->pressure_bar }} bar</div>@endif
                        @if($v->flow_m3h)    <div>🌊 {{ $v->flow_m3h }} m³/h</div>@endif
                        @if($v->poles)       <div>🔁 {{ $v->poles }} Pole</div>@endif
                    </td>
                    <td style="padding:12px 16px;">
                        @foreach(array_slice((array)($v->capability_tags ?? []), 0, 3) as $tag)
                            <span class="tag-chip">{{ $tag }}</span>
                        @endforeach
                    </td>
                    <td style="padding:12px 16px; font-size:12.5px;">
                        @if($v->deleted_at)
                            <span style="color:var(--error);"><span class="status-dot-del"></span>Removed</span>
                        @elseif($v->is_published)
                            <span style="color:var(--success);"><span class="status-dot-pub"></span>Live</span>
                        @else
                            <span style="color:var(--text-muted);">Draft</span>
                        @endif
                    </td>
                    <td style="padding:12px 16px;">
                        @if(!$v->deleted_at)
                        <form method="POST" action="{{ route('vendor.catalogue.variants.destroy', $v) }}"
                              onsubmit="return confirm('Remove this variant from matching?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none;border:none;cursor:pointer;color:var(--error);font-size:12.5px;">Remove</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($variants->hasPages())
        <div style="padding:16px;">{{ $variants->links() }}</div>
    @endif
</div>
@endif

@php $runningIds = $processingJobIds ?? collect(); @endphp
@if($runningIds->isNotEmpty())
<script>
const jobIds = @json($runningIds);
const poll = setInterval(async () => {
    let allDone = true;
    for (const id of jobIds) {
        const res  = await fetch(`/vendor/catalogue/jobs/${id}/status`);
        const data = await res.json();
        if (['pending','processing'].includes(data.status)) { allDone = false; }
    }
    if (allDone) {
        clearInterval(poll);
        window.location.reload();
    }
}, 3000);
</script>
@endif
@endsection
