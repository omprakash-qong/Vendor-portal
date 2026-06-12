@extends('layouts.vendor')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@push('styles')
<style>
    .welcome-name { color: var(--purple-neon); }
    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-top: 36px;
    }
    .action-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 32px;
        backdrop-filter: blur(12px);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .action-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; height: 3px;
        background: linear-gradient(90deg, var(--purple-mid), var(--purple-bright));
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .action-card:hover {
        border-color: var(--border-bright);
        transform: translateY(-4px);
        background: var(--bg-card-hover);
        box-shadow: 0 12px 30px rgba(168,85,247,0.15);
    }
    .action-card:hover::before {
        opacity: 1;
    }
    .action-icon {
        font-size: 36px;
        margin-bottom: 20px;
    }
    .action-title {
        font-family: var(--font-display);
        font-size: 24px;
        letter-spacing: 2.5px;
        color: var(--text-primary);
        margin-bottom: 12px;
    }
    .action-desc {
        font-size: 13.5px;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 24px;
        flex-grow: 1;
    }
    .action-btn {
        margin-top: auto;
        display: inline-block;
        text-align: center;
        text-decoration: none;
    }
</style>
@endpush

@push('styles')
<style>
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 36px;
    }
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 26px 28px;
    }
    .stat-card .num {
        font-family: var(--font-display);
        font-size: 44px;
        letter-spacing: 2px;
        line-height: 1;
        color: var(--text-primary);
    }
    .stat-card.warn .num { color: #fde68a; }
    .stat-card.good .num { color: #6ee7b7; }
    .stat-card .lbl {
        font-size: 12px;
        color: var(--text-muted);
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-top: 8px;
    }
</style>
@endpush

@section('content')
<div style="margin-bottom: 32px;">
    <p style="font-size:13px;color:var(--text-muted);letter-spacing:1px;text-transform:uppercase;margin-bottom:6px;">Products</p>
    <h1 style="font-family:var(--font-display);font-size:38px;letter-spacing:4px;color:var(--text-primary);line-height:1;">
        Welcome back, <span class="welcome-name">{{ auth()->user()->name ?? 'Vendor' }}</span>
    </h1>
</div>

<div class="stat-grid">
    <a href="{{ route('vendor.products.index') }}" class="stat-card" style="text-decoration:none;">
        <div class="num">{{ $stats['total'] }}</div>
        <div class="lbl">Total Products</div>
    </a>
    <a href="{{ route('vendor.products.index', ['status' => 'inactive']) }}" class="stat-card warn" style="text-decoration:none;">
        <div class="num">{{ $stats['needs_review'] }}</div>
        <div class="lbl">Needs Review</div>
    </a>
    <a href="{{ route('vendor.products.index', ['status' => 'active']) }}" class="stat-card good" style="text-decoration:none;">
        <div class="num">{{ $stats['active'] }}</div>
        <div class="lbl">Active Products</div>
    </a>
</div>

<div class="action-grid">
    <!-- Card 1: Import Products -->
    <div class="action-card">
        <div>
            <div class="action-icon">📥</div>
            <div class="action-title">Import Products</div>
            <div class="action-desc">Add products in bulk by importing them automatically from your website.</div>
        </div>
        <a href="{{ route('vendor.import.index') }}" class="btn btn-primary action-btn">Import Products</a>
    </div>

    <!-- Card 2: Add Product -->
    <div class="action-card">
        <div>
            <div class="action-icon">📦</div>
            <div class="action-title">Add Product</div>
            <div class="action-desc">Register a single component, equipment item, or machine in your catalogue.</div>
        </div>
        <a href="{{ route('vendor.products.create') }}" class="btn btn-primary action-btn">Add Product</a>
    </div>
</div>
@endsection