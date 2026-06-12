@extends('layouts.vendor')

@section('title', 'My Products')
@section('breadcrumb', 'My Products')

@push('styles')
<style>
.top-bar {
    display: flex; gap: 12px; flex-wrap: wrap;
    align-items: center; justify-content: space-between;
    margin-bottom: 20px;
}
.top-bar-left { display: flex; gap: 10px; flex: 1; flex-wrap: wrap; align-items: center; }
.filter-row {
    display: flex; gap: 10px; flex-wrap: wrap;
    margin-bottom: 20px; align-items: center;
}
.filter-row select, .filter-row input[type="text"] {
    padding: 8px 12px;
    background: var(--input-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius-sm);
    color: var(--text-primary);
    font-size: 13px;
    min-width: 150px;
}
.filter-row select:focus, .filter-row input:focus { border-color: var(--purple-bright); outline: none; }
.products-table { width: 100%; border-collapse: collapse; }
.products-table th, .products-table td {
    padding: 13px 16px; text-align: left;
    border-bottom: 1px solid var(--border); font-size: 13px;
    vertical-align: middle;
}
.products-table th {
    color: var(--text-muted); font-weight: 600;
    letter-spacing: 1px; text-transform: uppercase; font-size: 10.5px;
}
.importing-note { color: var(--purple-neon); font-weight: 600; margin-left: 8px; display: inline-flex; align-items: center; gap: 6px; }
.importing-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--purple-bright); display: inline-block; animation: impPulse 1s ease-in-out infinite; }
@keyframes impPulse { 0%,100% { opacity: 1; } 50% { opacity: 0.25; } }
.products-table tr:hover td { background: var(--bg-card-hover); }
.products-table tr { cursor: pointer; }
.col-cat { color: var(--text-secondary); font-size: 13px; }
.col-status { font-size: 12.5px; font-weight: 600; }
.status-needs { color: #B7791F; }   /* subtle — needs review */
.status-live  { color: #059669; }   /* subtle — active */
.tbl-action {
    color: var(--purple-bright); font-size: 13px; font-weight: 600;
    text-decoration: none; transition: color 0.2s;
}
.tbl-action:hover { color: var(--qong-magenta); text-decoration: underline; }
.product-name-cell { font-weight: 600; color: var(--text-primary); }
.product-model-cell { font-size: 11px; color: var(--text-muted); margin-top: 2px; }
.empty-state { text-align: center; padding: 60px 24px; color: var(--text-muted); }
.empty-state-icon { font-size: 48px; margin-bottom: 16px; }
.empty-state-title { font-family: var(--font-display); font-size: 24px; letter-spacing: 1px; color: var(--text-secondary); }
.tab-bar { display: flex; gap: 4px; margin-bottom: 16px; border-bottom: 1px solid var(--border); padding-bottom: 0; }
.tab-item {
    padding: 9px 16px; font-size: 13px; font-weight: 600; color: var(--text-muted);
    text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -1px;
    display: flex; align-items: center; gap: 6px; transition: color 0.15s;
}
.tab-item:hover { color: var(--text-primary); }
.tab-active { color: var(--purple-neon) !important; border-bottom-color: var(--purple-neon); }
.tab-count { color: var(--text-muted); font-size: 12px; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;">
    <div>
        <div class="page-tag">📦 Catalogue</div>
        <h1 class="page-title">My Products</h1>
        <p class="page-subtitle">
            {{ $products->total() }} product(s) in your catalogue.
            @if(!empty($importing))
                <span class="importing-note">
                    <span class="importing-dot"></span> Importing more… this page refreshes automatically.
                </span>
            @endif
        </p>
    </div>
    <div style="display:flex;gap:10px;margin-top:8px;">
        <a href="{{ route('vendor.import.index') }}" class="btn btn-outline" style="padding:10px 18px;font-size:14px;">Import</a>
        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary" style="padding:10px 18px;font-size:14px;">Add Manually</a>
    </div>
</div>

{{-- Status tabs + search --}}
@php
    $tab = request('status', 'all');
    $allCount      = \App\Models\Product::where('vendor_profile_id', auth()->user()->vendorProfile->id)->count();
    $inactiveCount = \App\Models\Product::where('vendor_profile_id', auth()->user()->vendorProfile->id)->where('status','inactive')->count();
    $activeCount   = \App\Models\Product::where('vendor_profile_id', auth()->user()->vendorProfile->id)->whereIn('status',['active','published'])->count();
@endphp
<div class="tab-bar">
    <a href="{{ route('vendor.products.index', array_merge(request()->except('status','page'), ['status'=>'all'])) }}"
       class="tab-item {{ $tab === 'all' ? 'tab-active' : '' }}">
        All <span class="tab-count">{{ $allCount }}</span>
    </a>
    <a href="{{ route('vendor.products.index', array_merge(request()->except('status','page'), ['status'=>'inactive'])) }}"
       class="tab-item {{ $tab === 'inactive' ? 'tab-active' : '' }}">
        Needs Review <span class="tab-count">{{ $inactiveCount }}</span>
    </a>
    <a href="{{ route('vendor.products.index', array_merge(request()->except('status','page'), ['status'=>'active'])) }}"
       class="tab-item {{ $tab === 'active' ? 'tab-active' : '' }}">
        Active <span class="tab-count">{{ $activeCount }}</span>
    </a>
</div>

<form method="GET" action="{{ route('vendor.products.index') }}" id="filter-form" style="margin-bottom:16px;">
    <input type="hidden" name="status" value="{{ $tab }}">
    <div style="display:flex;gap:10px;align-items:center;">
        <input type="text" name="search" placeholder="Search products..." value="{{ request('search') }}"
               style="flex:1;max-width:400px;padding:9px 14px;background:var(--input-bg);border:1px solid var(--border);border-radius:var(--radius-sm);color:var(--text-primary);font-size:13px;">
        <button type="submit" class="btn btn-outline" style="padding:9px 18px;font-size:13px;">Search</button>
        @if(request('search'))
            <a href="{{ route('vendor.products.index', ['status' => $tab]) }}" style="color:var(--text-muted);font-size:12px;">Clear</a>
        @endif
    </div>
</form>

@if($products->isEmpty())
    <div class="card">
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <div class="empty-state-title">No Products Found</div>
            <div style="font-size:13px;margin-top:8px;">
                @if(request('search'))
                    No products match your search. <a href="{{ route('vendor.products.index') }}" style="color:var(--purple-neon);">Clear search</a>
                @else
                    You haven't added any products yet.
                    <a href="{{ route('vendor.import.index') }}" style="color:var(--purple-neon);">Import products</a> or
                    <a href="{{ route('vendor.products.create') }}" style="color:var(--purple-neon);">add manually</a>.
                @endif
            </div>
        </div>
    </div>
@else
    <div class="card" style="overflow:hidden;">
        <table class="products-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $product)
                @php $url = route('vendor.products.edit', $product->id); @endphp
                <tr onclick="window.location='{{ $url }}'">
                    <td>
                        <div class="product-name-cell">{{ $product->name }}</div>
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ $url }}" class="tbl-action">View →</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">
        {{ $products->links() }}
    </div>
@endif

@if(!empty($importing))
@push('scripts')
<script>
    // While an import is in progress, refresh the list every 6s so newly
    // imported products appear automatically. Stops once importing ends.
    setTimeout(function () { window.location.reload(); }, 6000);
</script>
@endpush
@endif
@endsection
