@extends(request()->routeIs('vendor.apply', 'vendor.apply.submit') ? 'layouts.application' : 'layouts.vendor')

@section('title', 'Vendor Onboarding — Qong Systems')
@section('breadcrumb', 'Vendor Onboarding')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════
   THEME TOKENS — Dark (default) & Light
═══════════════════════════════════════════════════ */
:root {
    --bg-base:         #0a0a12;
    --bg-surface:      #0f0f1a;
    --bg-card:         rgba(15,15,30,0.85);
    --border:          rgba(168,85,247,0.18);
    --border-hover:    rgba(168,85,247,0.55);
    --input-bg:        rgba(168,85,247,0.06);
    --purple-neon:     #c084fc;
    --purple-bright:   #a855f7;
    --purple-dim:      rgba(168,85,247,0.35);
    --text-primary:    #f1f0ff;
    --text-secondary:  #a0a0c0;
    --text-muted:      #5a5a80;
    --error:           #f87171;
    --success:         #4ade80;
    --radius-sm:       10px;
    --radius-md:       16px;
    --shadow-glow:     0 0 24px rgba(168,85,247,0.12);
    --transition:      all 0.25s cubic-bezier(.4,0,.2,1);
    --grid-color:      rgba(168,85,247,0.06);
}

[data-theme="light"] {
    --bg-base:         #f0eeff;
    --bg-surface:      #ffffff;
    --bg-card:         rgba(255,255,255,0.96);
    --border:          rgba(109,40,217,0.28);
    --border-hover:    rgba(109,40,217,0.65);
    --input-bg:        rgba(109,40,217,0.07);
    --purple-neon:     #5b21b6;
    --purple-bright:   #6d28d9;
    --purple-dim:      rgba(109,40,217,0.3);
    --text-primary:    #12082a;
    --text-secondary:  #2e1a5e;
    --text-muted:      #6b5fa0;
    --error:           #b91c1c;
    --success:         #15803d;
    --shadow-glow:     0 4px 32px rgba(109,40,217,0.12);
    --grid-color:      rgba(109,40,217,0.07);

    /* Sidebar overrides for light mode — high contrast */
    --sidebar-bg:          #1a0a3d;
    --sidebar-text:        #e8e0ff;
    --sidebar-text-muted:  #a89cc8;
    --sidebar-active-bg:   rgba(168,85,247,0.25);
    --sidebar-active-text: #d8b4fe;
    --sidebar-icon:        #c4b5fd;
    --sidebar-hover-bg:    rgba(168,85,247,0.14);
}

/* Dark mode sidebar vars */
:root {
    --sidebar-bg:          #07070f;
    --sidebar-text:        #d4c8f0;
    --sidebar-text-muted:  #6a5fa0;
    --sidebar-active-bg:   rgba(168,85,247,0.2);
    --sidebar-active-text: #c084fc;
    --sidebar-icon:        #9f7aea;
    --sidebar-hover-bg:    rgba(168,85,247,0.1);
}

/* ═══════════════════════════════════════════════════
   SIDEBAR — high-contrast in both modes
═══════════════════════════════════════════════════ */
.sidebar,
.vendor-sidebar,
[class*="sidebar"] {
    background: var(--sidebar-bg) !important;
}

.sidebar a,
.sidebar .nav-link,
.sidebar .menu-item,
.vendor-sidebar a,
[class*="sidebar"] a {
    color: var(--sidebar-text) !important;
}

.sidebar .nav-link.active,
.sidebar .menu-item.active,
.vendor-sidebar .active,
[class*="sidebar"] .active {
    background: var(--sidebar-active-bg) !important;
    color: var(--sidebar-active-text) !important;
    border-left: 3px solid var(--purple-bright) !important;
}

.sidebar .nav-link:hover,
.sidebar .menu-item:hover,
[class*="sidebar"] a:hover {
    background: var(--sidebar-hover-bg) !important;
    color: var(--sidebar-active-text) !important;
}

.sidebar .nav-icon,
.sidebar i,
.sidebar svg,
[class*="sidebar"] i,
[class*="sidebar"] svg {
    color: var(--sidebar-icon) !important;
    opacity: 1 !important;
}

/* ═══════════════════════════════════════════════════
   ANIMATED GRID BACKGROUND
═══════════════════════════════════════════════════ */
body {
    background: var(--bg-base);
    color: var(--text-primary);
    font-family: var(--font-sans);
    min-height: 100vh;
    position: relative;
}
body::before {
    content: '';
    position: fixed;
    inset: 0;
    background-image:
        linear-gradient(var(--grid-color) 1px, transparent 1px),
        linear-gradient(90deg, var(--grid-color) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
    z-index: 0;
    animation: gridDrift 20s linear infinite;
}
@keyframes gridDrift {
    from { background-position: 0 0; }
    to   { background-position: 48px 48px; }
}

/* ═══════════════════════════════════════════════════
   TOP-RIGHT CONTROLS — Theme toggle + Avatar only
   (bell / notifications removed)
═══════════════════════════════════════════════════ */
.top-controls {
    position: fixed;
    top: 20px;
    right: 24px;
    z-index: 1000;
    display: flex;
    align-items: center;
    gap: 12px;
}

/* Hide any bell / notification icon injected by layout */
.notification-bell,
.bell-icon,
[class*="notif"],
[class*="bell"] {
    display: none !important;
}

.theme-toggle {
    width: 52px;
    height: 28px;
    background: var(--input-bg);
    border: 1.5px solid var(--border);
    border-radius: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    padding: 3px;
    transition: var(--transition);
    backdrop-filter: blur(12px);
}
.theme-toggle:hover { border-color: var(--purple-bright); }
.theme-toggle-knob {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--purple-bright), var(--purple-neon));
    transition: var(--transition);
    display: flex; align-items: center; justify-content: center;
    font-size: 11px;
    box-shadow: 0 0 8px rgba(168,85,247,0.5);
}
[data-theme="light"] .theme-toggle-knob { transform: translateX(24px); }

.user-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--purple-bright), var(--purple-neon));
    border: 2px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 15px;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 0 10px rgba(168,85,247,0.2);
    overflow: hidden;
}
.user-avatar:hover {
    border-color: var(--purple-bright);
    box-shadow: 0 0 18px rgba(168,85,247,0.4);
}

/* ═══════════════════════════════════════════════════
   PAGE LAYOUT
═══════════════════════════════════════════════════ */
.onboarding-wrap {
    position: relative;
    z-index: 1;
    max-width: 1060px;
    margin: 0 auto;
    padding: 40px 24px 80px;
}

/* ── Page Header ── */
.page-header { margin-bottom: 36px; }
.page-tag {
    display: inline-block;
    font-size: 11px; font-weight: 600; letter-spacing: 3px;
    text-transform: uppercase; color: var(--purple-neon);
    margin-bottom: 10px; opacity: .8;
}
.page-title {
    font-family: var(--font-display);
    font-size: clamp(32px, 5vw, 52px); letter-spacing: 3px;
    color: var(--text-primary); margin: 0 0 8px;
    background: linear-gradient(135deg, var(--text-primary) 40%, var(--purple-neon));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
}
.page-subtitle { color: var(--text-secondary); font-size: 14px; }
.req { color: var(--purple-bright); }

/* ── Cards ── */
.card {
    background: var(--bg-card); border: 1px solid var(--border);
    border-radius: var(--radius-md); margin-bottom: 24px;
    backdrop-filter: blur(20px); box-shadow: var(--shadow-glow);
    overflow: hidden; transition: var(--transition);
}
.card:hover { border-color: var(--border-hover); }
.card-header {
    display: flex; align-items: flex-start; gap: 14px;
    padding: 22px 28px 18px; border-bottom: 1px solid var(--border);
}
.card-icon {
    width: 44px; height: 44px; border-radius: var(--radius-sm);
    background: rgba(168,85,247,0.12); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; flex-shrink: 0;
}
.card-title {
    font-family: var(--font-display); letter-spacing: 2px;
    font-size: 18px; color: var(--text-primary);
}
.card-desc { font-size: 12px; color: var(--text-muted); margin-top: 2px; }
.card-body { padding: 24px 28px; }

/* ── Section Dividers ── */
.section-divider { display: flex; align-items: center; gap: 14px; margin: 6px 0 18px; }
.section-divider-line { flex: 1; height: 1px; background: var(--border); }
.section-divider-text {
    font-family: var(--font-display); letter-spacing: 2.5px;
    font-size: 12px; color: var(--text-muted); white-space: nowrap;
}

/* ── Form Grid ── */
.form-grid   { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
.form-grid-3 { grid-template-columns: repeat(3, 1fr); }
.col-span-2  { grid-column: span 2; }
.col-span-3  { grid-column: span 3; }
@media (max-width: 700px) {
    .form-grid, .form-grid-3 { grid-template-columns: 1fr; }
    .col-span-2, .col-span-3 { grid-column: span 1; }
}

/* ── Fields ── */
.field { display: flex; flex-direction: column; }
.field-label {
    font-size: 12px; font-weight: 600; color: var(--text-secondary);
    letter-spacing: .8px; text-transform: uppercase; margin-bottom: 8px;
}
.input-wrap { position: relative; display: flex; align-items: center; }
.input-icon  { position: absolute; left: 14px; font-size: 15px; pointer-events: none; z-index: 1; }
.input-wrap input, .input-wrap select {
    width: 100%; padding: 11px 14px 11px 42px;
    background: var(--input-bg); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); color: var(--text-primary);
    font-size: 13.5px; outline: none; transition: var(--transition);
}
.field.no-icon input,
.field.no-icon select,
.field.no-icon textarea {
    padding: 11px 14px; width: 100%;
    background: var(--input-bg); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); color: var(--text-primary);
    font-size: 13.5px; outline: none; transition: var(--transition);
}
input:focus, select:focus, textarea:focus {
    border-color: var(--purple-bright) !important;
    box-shadow: 0 0 0 3px rgba(168,85,247,0.12);
}
input::placeholder, textarea::placeholder { color: var(--text-muted); }
select option { background: var(--bg-surface); color: var(--text-primary); }
.field-hint  { font-size: 11px; color: var(--text-muted); margin-top: 5px; }
.field-error { font-size: 11px; color: var(--error); margin-top: 5px; }
.divider     { height: 1px; background: var(--border); margin: 20px 0; }

/* ── Email Rejection Banner ── */
.email-reject-banner {
    display: none;
    background: rgba(248,113,113,0.10);
    border: 1px solid rgba(248,113,113,0.35);
    border-radius: var(--radius-sm);
    padding: 8px 14px;
    font-size: 12px; color: var(--error);
    margin-top: 6px;
    align-items: center; gap: 6px;
}
.email-reject-banner.visible { display: flex; }

/* ── Category Pills / Radio Buttons ── */
.cat-input { display: none; }
.cat-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); font-size: 13px; color: var(--text-secondary);
    cursor: pointer; transition: var(--transition); user-select: none;
}
.cat-input:checked + .cat-pill {
    border-color: var(--purple-bright); background: rgba(168,85,247,0.12);
    color: var(--purple-neon); box-shadow: 0 0 12px rgba(168,85,247,0.20);
}
.cat-pill:hover { border-color: var(--border-hover); }

/* ── Tag Checkboxes ── */
.tag-group { display: flex; flex-wrap: wrap; gap: 8px; }
.tag-checkbox { display: none; }
.tag-label {
    display: inline-block; padding: 6px 14px;
    border: 1px solid var(--border); border-radius: 30px;
    font-size: 12px; color: var(--text-secondary);
    cursor: pointer; transition: var(--transition); user-select: none;
}
.tag-checkbox:checked + .tag-label {
    border-color: var(--purple-bright); background: rgba(168,85,247,0.12); color: var(--purple-neon);
}
.tag-label:hover { border-color: var(--border-hover); }

/* ── File Drop Zone ── */
.file-drop {
    position: relative; border: 1.5px dashed var(--border);
    border-radius: var(--radius-sm); padding: 24px 16px;
    text-align: center; background: var(--input-bg);
    cursor: pointer; transition: var(--transition); overflow: hidden;
}
.file-drop:hover { border-color: var(--purple-bright); background: rgba(168,85,247,0.08); }
.file-drop input[type="file"] {
    position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
}
.file-drop-icon { font-size: 28px; margin-bottom: 8px; }
.file-drop-text { font-size: 13px; color: var(--text-secondary); }
.file-drop-text strong { color: var(--purple-neon); }
.file-drop-sub  { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
.file-name-display { margin-top: 8px; font-size: 12px; color: var(--purple-neon); display: none; word-break: break-all; }

/* ── POC Cards ── */
.poc-card {
    background: rgba(168,85,247,0.04); border: 1px solid rgba(168,85,247,0.14);
    border-radius: var(--radius-sm); padding: 20px; margin-bottom: 16px;
}
.poc-title {
    font-family: var(--font-display); letter-spacing: 2px; font-size: 15px;
    color: var(--purple-neon); margin-bottom: 16px;
    display: flex; align-items: center; gap: 8px;
}

/* ── Inline Toggle ── */
.inline-toggle {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; border: 1px solid var(--border); border-radius: 20px;
    font-size: 12px; color: var(--text-secondary);
    cursor: pointer; transition: var(--transition); user-select: none;
    background: var(--input-bg); margin-bottom: 18px;
}
.inline-toggle input { accent-color: var(--purple-bright); }
.inline-toggle:hover { border-color: var(--purple-bright); }

/* ── Sub-domain Grid ── */
.subdomain-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
.subdomain-item {
    background: var(--input-bg); border: 1px solid var(--border);
    border-radius: var(--radius-sm); padding: 14px 16px;
}

/* ── Conditional Sections ── */
.conditional-section { display: none; animation: fadeInDown 0.35s ease; }
.conditional-section.active { display: block; }
@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-12px); }
    to   { opacity: 1; transform: translateY(0); }
}

.reseller-section-card {
    background: rgba(34,197,94,0.04); border: 1px solid rgba(34,197,94,0.18);
    border-radius: var(--radius-sm); padding: 20px; margin-top: 20px; position: relative;
}
.reseller-section-card::before {
    content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
    background: linear-gradient(180deg, #4ade80, transparent);
    border-radius: 3px 0 0 3px;
}
.section-badge {
    display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
    letter-spacing: 1.5px; text-transform: uppercase; margin-bottom: 18px;
}
.section-badge.reseller { background: rgba(34,197,94,0.1); color: #4ade80; border: 1px solid rgba(34,197,94,0.2); }

/* ── Check Row ── */
.check-row {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: 13px; color: var(--text-secondary);
    cursor: pointer; margin-bottom: 14px;
}
.check-row input[type="checkbox"] { accent-color: var(--purple-bright); margin-top: 2px; flex-shrink: 0; }

/* ── Alert ── */
.alert { display: flex; gap: 12px; padding: 14px 18px; border-radius: var(--radius-sm); margin-bottom: 20px; font-size: 13px; }
.alert-error { background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.3); color: var(--error); }

/* ── Buttons ── */
.btn {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 26px; border-radius: var(--radius-sm);
    font-size: 13px; font-weight: 700; letter-spacing: .8px;
    cursor: pointer; transition: var(--transition); border: none; text-decoration: none;
}
.btn-primary {
    background: linear-gradient(135deg, #7c3aed, var(--purple-neon));
    color: #fff; box-shadow: 0 0 20px rgba(168,85,247,0.3);
}
.btn-primary:hover { box-shadow: 0 0 32px rgba(168,85,247,0.5); transform: translateY(-1px); }
.btn-outline {
    background: var(--input-bg); border: 1.5px solid var(--border); color: var(--text-secondary);
}
.btn-outline:hover { border-color: var(--purple-bright); color: var(--purple-neon); }
.btn-row {
    display: flex; justify-content: flex-end; gap: 12px;
    padding: 20px 28px; border-top: 1px solid var(--border);
}

/* ── Quality Cards ── */
.quality-card {
    background: var(--input-bg); border: 1px solid var(--border);
    border-radius: var(--radius-sm); padding: 20px; transition: var(--transition);
}
.quality-card:hover { border-color: var(--border-hover); }

/* ── Industry Standards — Grouped Checkbox Cards ── */
.std-groups-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px; margin-bottom: 20px;
}
.std-group-card {
    background: var(--input-bg); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); overflow: hidden; transition: var(--transition);
}
.std-group-card:hover {
    border-color: var(--grp-color, var(--purple-bright));
    box-shadow: 0 0 16px rgba(168,85,247,0.15);
}
.std-group-header {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 14px; border-bottom: 1px solid var(--border);
    background: rgba(168,85,247,0.07);
}
.std-group-icon  { font-size: 16px; flex-shrink: 0; }
.std-group-label {
    font-size: 11px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase;
    color: var(--grp-color, var(--purple-neon));
}
.std-check-list  { padding: 12px 14px; display: flex; flex-direction: column; gap: 10px; }
.std-check-row   { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
.std-checkbox    { display: none; }
.std-check-box {
    width: 18px; height: 18px; border-radius: 5px; border: 1.5px solid var(--border);
    background: var(--input-bg); flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    transition: var(--transition); position: relative;
}
.std-checkbox:checked + .std-check-box {
    border-color: var(--grp-color, var(--purple-bright));
    background: rgba(168,85,247,0.18); box-shadow: 0 0 8px rgba(168,85,247,0.3);
}
.std-checkbox:checked + .std-check-box::after {
    content: "✓"; font-size: 11px; font-weight: 800;
    color: var(--grp-color, var(--purple-neon)); line-height: 1;
}
.std-check-row:hover .std-check-box { border-color: var(--border-hover); }
.std-check-text  { font-size: 13px; color: var(--text-secondary); transition: color 0.2s; }
.std-check-row:hover .std-check-text { color: var(--text-primary); }
.std-other-wrap {
    background: rgba(168,85,247,0.04); border: 1.5px dashed var(--border);
    border-radius: var(--radius-sm); padding: 16px 18px; margin-top: 4px; transition: var(--transition);
}
.std-other-wrap:focus-within { border-color: var(--purple-bright); background: rgba(168,85,247,0.07); }
.std-other-label {
    font-size: 12px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase;
    color: var(--text-secondary); margin-bottom: 10px; display: flex; align-items: center; gap: 6px;
}
.std-other-wrap textarea {
    width: 100%; background: transparent; border: none; outline: none;
    color: var(--text-primary); font-size: 13.5px; line-height: 1.7; font-family: inherit; padding: 0;
}
.std-other-wrap textarea::placeholder { color: var(--text-muted); }

/* Scrollbar */
::-webkit-scrollbar { width: 6px; height: 6px; }
::-webkit-scrollbar-track { background: transparent; }
::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }
::-webkit-scrollbar-thumb:hover { background: var(--purple-dim); }

/* Neon glow on focus */
input:focus, select:focus { animation: neonPulse 0.3s ease; }
@keyframes neonPulse {
    from { box-shadow: 0 0 0 0 rgba(168,85,247,0); }
    to   { box-shadow: 0 0 0 3px rgba(168,85,247,0.12); }
}
</style>
@endpush

@section('content')

{{-- ── Top Controls: Theme Toggle + User Avatar only (no bell) ── --}}
<div class="top-controls">
    <button class="theme-toggle" onclick="toggleTheme()" title="Toggle light / dark mode" aria-label="Toggle theme">
        <div class="theme-toggle-knob" id="themeKnob">☽</div>
    </button>
    <div class="user-avatar" title="Profile">
        @if(auth()->user()?->avatar)
            <img src="{{ auth()->user()->avatar }}" alt="Avatar" style="width:100%;height:100%;object-fit:cover;">
        @else
            👤
        @endif
    </div>
</div>

<div class="onboarding-wrap">

{{-- ── Page Header ─────────────────────────────────────────────── --}}
<div class="page-header">
    <div class="page-tag">✦ Qong Systems — Vendor Portal</div>
    <h1 class="page-title">Vendor Onboarding</h1>
    <p class="page-subtitle">Complete all sections to register as an approved Qong Systems vendor. Fields marked <span class="req">(*)</span> are mandatory.</p>
</div>

{{-- ── Validation Errors ───────────────────────────────────────── --}}
@if ($errors->any())
<div class="alert alert-error">
    <span>✖</span>
    <div>
        <strong>Please fix the following errors:</strong>
        <ul style="margin-top:8px;padding-left:18px;font-size:12px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════
     MAIN FORM
════════════════════════════════════════════════════════════════ --}}
<form action="{{ request()->routeIs('vendor.apply') ? route('vendor.apply.submit') : route('vendor.onboarding.submit') }}" method="POST" enctype="multipart/form-data" id="onboarding-form">
@csrf

{{-- ────────────────────────────────────────────────────────────
     SECTION 1 – COMPANY INFORMATION
──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-icon">🏢</div>
        <div>
            <div class="card-title">Company Information</div>
            <div class="card-desc">Legal identity, registered address &amp; online presence</div>
        </div>
    </div>
    <div class="card-body">

        <div class="form-grid form-grid-2">
            <div class="field">
                <label class="field-label">Legal Company Name (*)</label>
                <div class="input-wrap">
                    <span class="input-icon">🏛</span>
                    <input type="text" name="legal_company_name" value="{{ old('legal_company_name') }}"
                           placeholder="As per MCA / registration certificate" required>
                </div>
                @error('legal_company_name') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label class="field-label">Trade Name / DBA</label>
                <div class="input-wrap">
                    <span class="input-icon">🏷</span>
                    <input type="text" name="trade_name" value="{{ old('trade_name') }}"
                           placeholder="Doing Business As — name (if different)">
                </div>
            </div>
        </div>

        <div class="divider"></div>
        <div class="section-divider">
            <div class="section-divider-line"></div>
            <div class="section-divider-text">Company Type</div>
            <div class="section-divider-line"></div>
        </div>

        <div class="field" style="margin-bottom:22px;">
            <label class="field-label">Company Type (*)</label>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:4px;">
                @foreach(['Pvt Ltd','LLP','Public Ltd','Partnership','Proprietorship','Inc','LLC','Other'] as $type)
                <div>
                    <input type="radio" name="company_type" id="ct_{{ Str::slug($type) }}"
                           value="{{ $type }}" class="cat-input"
                           {{ old('company_type') === $type ? 'checked' : '' }}>
                    <label for="ct_{{ Str::slug($type) }}" class="cat-pill">{{ $type }}</label>
                </div>
                @endforeach
            </div>
            @error('company_type') <span class="field-error" style="margin-top:4px;display:block;">{{ $message }}</span> @enderror
        </div>

        <div class="divider"></div>
        <div class="section-divider">
            <div class="section-divider-line"></div>
            <div class="section-divider-text">Registered Address</div>
            <div class="section-divider-line"></div>
        </div>

        <div class="form-grid" style="margin-bottom:16px;">
            <div class="field no-icon">
                <label class="field-label">Address Line 1 (*)</label>
                <input type="text" name="reg_address_line1" value="{{ old('reg_address_line1') }}"
                       placeholder="Street, building, unit number" required>
                @error('reg_address_line1') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field no-icon">
                <label class="field-label">Address Line 2</label>
                <input type="text" name="reg_address_line2" value="{{ old('reg_address_line2') }}"
                       placeholder="Area, landmark">
            </div>
        </div>
        <div class="form-grid form-grid-3" style="margin-bottom:8px;">
            <div class="field no-icon">
                <label class="field-label">Country (*)</label>
                <select name="reg_country" id="reg_country" required>
                    <option value="">Select country</option>
                    @foreach(['India','USA','United Kingdom','UAE','Saudi Arabia','Qatar','Bahrain','Kuwait','Oman','Singapore','Malaysia','Indonesia','Australia','Germany','France','Netherlands','Japan','South Korea','Canada','Other'] as $c)
                    <option value="{{ $c }}" {{ old('reg_country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
                @error('reg_country') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field no-icon">
                <label class="field-label">State / Province (*)</label>
                <input type="text" name="reg_state" id="reg_state" value="{{ old('reg_state') }}" placeholder="State / province" list="reg_state_list" required autocomplete="off">
                <datalist id="reg_state_list"></datalist>
                @error('reg_state') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field no-icon">
                <label class="field-label">City (*)</label>
                <input type="text" name="reg_city" id="reg_city" value="{{ old('reg_city') }}" placeholder="City" list="reg_city_list" required autocomplete="off">
                <datalist id="reg_city_list"></datalist>
                @error('reg_city') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field no-icon">
                <label class="field-label">PIN / ZIP Code (*)</label>
                <input type="text" name="reg_pincode" value="{{ old('reg_pincode') }}" placeholder="Postal code" required>
                @error('reg_pincode') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="divider"></div>
        <div class="section-divider">
            <div class="section-divider-line"></div>
            <div class="section-divider-text">Operating Address</div>
            <div class="section-divider-line"></div>
        </div>

        <label class="inline-toggle">
            <input type="checkbox" id="same_as_registered" name="same_as_registered" value="1"
                   {{ old('same_as_registered') ? 'checked' : '' }}>
            Same as Registered Address
        </label>

        <div id="op-addr-wrap">
            <div class="form-grid" style="margin-bottom:16px;">
                <div class="field no-icon operating-addr-field">
                    <label class="field-label">Address Line 1</label>
                    <input type="text" name="op_address_line1" value="{{ old('op_address_line1') }}"
                           placeholder="Street, building, unit number">
                </div>
                <div class="field no-icon operating-addr-field">
                    <label class="field-label">Address Line 2</label>
                    <input type="text" name="op_address_line2" value="{{ old('op_address_line2') }}"
                           placeholder="Area, landmark">
                </div>
            </div>
            <div class="form-grid form-grid-3">
                <div class="field no-icon operating-addr-field">
                    <label class="field-label">Country</label>
                    <select name="op_country" id="op_country">
                        <option value="">Select country</option>
                        @foreach(['India','USA','United Kingdom','UAE','Saudi Arabia','Qatar','Bahrain','Kuwait','Oman','Singapore','Malaysia','Indonesia','Australia','Germany','France','Netherlands','Japan','South Korea','Canada','Other'] as $c)
                        <option value="{{ $c }}" {{ old('op_country') === $c ? 'selected' : '' }}>{{ $c }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="field no-icon operating-addr-field">
                    <label class="field-label">State / Province</label>
                    <input type="text" name="op_state" id="op_state" value="{{ old('op_state') }}" placeholder="State / province" list="op_state_list" autocomplete="off">
                    <datalist id="op_state_list"></datalist>
                </div>
                <div class="field no-icon operating-addr-field">
                    <label class="field-label">City</label>
                    <input type="text" name="op_city" id="op_city" value="{{ old('op_city') }}" placeholder="City" list="op_city_list" autocomplete="off">
                    <datalist id="op_city_list"></datalist>
                </div>
                <div class="field no-icon operating-addr-field">
                    <label class="field-label">PIN / ZIP Code</label>
                    <input type="text" name="op_pincode" value="{{ old('op_pincode') }}" placeholder="Postal code">
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="form-grid form-grid-2">
            <div class="field">
                <label class="field-label">Company Website (*)</label>
                <div class="input-wrap">
                    <span class="input-icon">🌐</span>
                    <input type="url" name="company_website" value="{{ old('company_website') }}"
                           placeholder="https://yourcompany.com" required>
                </div>
                @error('company_website') <span class="field-error">{{ $message }}</span> @enderror
            </div>
            <div class="field">
                <label class="field-label">Company Logo</label>
                <div class="file-drop">
                    <input type="file" name="company_logo" accept="image/png,image/jpeg,image/svg+xml">
                    <div class="file-drop-icon">🖼</div>
                    <div class="file-drop-text">Drag &amp; drop or <strong>browse</strong></div>
                    <div class="file-drop-sub">PNG, JPG, SVG · Max 2 MB</div>
                    <div class="file-name-display"></div>
                </div>
            </div>
        </div>

    </div>
</div>


{{-- ────────────────────────────────────────────────────────────
     SECTION 2 – POINTS OF CONTACT
──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-icon">👤</div>
        <div>
            <div class="card-title">Points of Contact</div>
            <div class="card-desc">Primary &amp; secondary contacts — official company email required (Gmail, Yahoo, Hotmail &amp; Outlook are not accepted)</div>
        </div>
    </div>
    <div class="card-body">

        <div class="poc-card">
            <div class="poc-title">◈ Primary Contact <span style="font-size:12px;color:var(--text-muted);font-family:var(--font-sans);font-weight:400;letter-spacing:0;margin-left:6px;">(required)</span></div>
            <div class="form-grid form-grid-3">
                <div class="field">
                    <label class="field-label">Full Name (*)</label>
                    <div class="input-wrap"><span class="input-icon">👤</span>
                        <input type="text" name="primary_name" value="{{ old('primary_name') }}" placeholder="Contact full name" required>
                    </div>
                    @error('primary_name') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label class="field-label">Designation (*)</label>
                    <div class="input-wrap"><span class="input-icon">🏷</span>
                        <input type="text" name="primary_designation" value="{{ old('primary_designation') }}" placeholder="e.g. Sales Manager" required>
                    </div>
                    @error('primary_designation') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field">
                    <label class="field-label">Phone Number (*)</label>
                    <div class="input-wrap"><span class="input-icon">📞</span>
                        <input type="tel" name="primary_phone" id="primary_phone" value="{{ old('primary_phone') }}" placeholder="+[Country Code] XXXXXXXXXX" required>
                    </div>
                    @error('primary_phone') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field col-span-2">
                    <label class="field-label">Official Company Email (*)</label>
                    <div class="input-wrap"><span class="input-icon">✉</span>
                        <input type="email" name="primary_email" id="primary_email"
                               value="{{ old('primary_email') }}"
                               placeholder="name@yourcompany.com"
                               class="company-email-input" required>
                    </div>
                    <div class="email-reject-banner" id="primary_email_err">
                        ✖ Personal email providers (Gmail, Yahoo, Hotmail, Outlook) are not accepted. Please use your official company email.
                    </div>
                    <span class="field-hint">Must be a company domain — personal email addresses are not accepted</span>
                    @error('primary_email') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div id="sec-poc-wrapper"></div>

        <button type="button" class="btn btn-outline" style="font-size:13px;padding:9px 20px;margin-top:8px;" onclick="addSecPoc()">
            + Add Another Contact
        </button>

    </div>
</div>


{{-- ────────────────────────────────────────────────────────────
     SECTION 3 – TAX & COMPLIANCE
──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-icon">📋</div>
        <div>
            <div class="card-title">Tax &amp; Compliance</div>
            <div class="card-desc">Enter your applicable tax registration and compliance details.</div>
        </div>
    </div>
    <div class="card-body">

        <div class="form-grid form-grid-3">
            <div class="field">
                <label class="field-label">GST / VAT / Tax Number (*)</label>
                <div class="input-wrap">
                    <span class="input-icon">🌍</span>
                    <input type="text" name="gstin" value="{{ old('gstin') }}"
                           placeholder="GST / VAT / TIN / EIN / TRN" maxlength="20"
                           style="text-transform:uppercase;" required>
                </div>
                <span class="field-hint">Enter your applicable tax registration number</span>
                @error('gstin') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            <div class="field">
                <label class="field-label">Incorporation Number (*)</label>
                <div class="input-wrap">
                    <span class="input-icon">🔢</span>
                    <input type="text" name="incorporation_number" value="{{ old('incorporation_number') }}" placeholder="e.g. U17110MH2000PLC123456">
                </div>
                @error('incorporation_number') <span class="field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="divider"></div>

        <div class="field" style="margin-bottom:0;">
            <label class="field-label">Tax / GST / VAT Certificate</label>
            <div class="file-drop" style="max-width:520px;">
                <input type="file" name="tax_certificate" accept=".pdf,.jpg,.jpeg,.png">
                <div class="file-drop-icon">📄</div>
                <div class="file-drop-text">Upload Tax / GST / VAT Certificate</div>
                <div class="file-drop-sub">PDF, JPG, JPEG, PNG · Max 5 MB</div>
                <div class="file-name-display"></div>
            </div>
        </div>

    </div>
</div>


{{-- ────────────────────────────────────────────────────────────
     SECTION 4 – VENDOR CATEGORY & CAPABILITIES
──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-icon">⚙️</div>
        <div>
            <div class="card-title">Vendor Category &amp; Capabilities</div>
            <div class="card-desc">Select your vendor type — additional fields appear based on your selection</div>
        </div>
    </div>
    <div class="card-body">

        <div class="field" style="margin-bottom:24px;">
            <label class="field-label">Vendor Category</label>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;" id="vendor-cat-group">
                @foreach([
                    'OEM',
                    'Authorized Distributor',
                    'General Reseller',
                    'EPC Company',
                    'EPC Subcontractor',
                    'Consultancy',
                    'Engineering Services',
                    'Other'
                ] as $cat)
                <div>
                    <input type="checkbox" name="vendor_category[]" id="vc_{{ Str::slug($cat) }}"
                           value="{{ $cat }}" class="cat-input vendor-cat-check"
                           {{ in_array($cat, old('vendor_category', [])) ? 'checked' : '' }}>
                    <label for="vc_{{ Str::slug($cat) }}" class="cat-pill">{{ $cat }}</label>
                </div>
                @endforeach
            </div>
            <div id="vc-other-wrap" style="display:none;margin-top:12px;">
                <div class="field no-icon">
                    <label class="field-label">Please Specify (*)</label>
                    <input type="text" name="vendor_category_other" value="{{ old('vendor_category_other') }}"
                           placeholder="Describe your vendor category">
                </div>
            </div>
            @error('vendor_category') <span class="field-error" style="margin-top:6px;display:block;">{{ $message }}</span> @enderror
        </div>


        {{-- ── CONDITIONAL: General Reseller ── --}}
        <div class="conditional-section" id="reseller-section">
            <div class="reseller-section-card">
                <div class="section-badge reseller">🛒 Distributor / Reseller Details</div>
                <div class="form-grid form-grid-2">
                    <div class="field no-icon">
                        <label class="field-label">Authorized Brands</label>
                        <input type="text" name="authorized_brands" value="{{ old('authorized_brands') }}"
                               placeholder="Brands you are authorized to distribute">
                        <span class="field-hint">Comma-separated brand names</span>
                    </div>
                    <div class="field no-icon">
                        <label class="field-label">Distribution Region</label>
                        <input type="text" name="distribution_region" value="{{ old('distribution_region') }}"
                               placeholder="Regions you distribute to">
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label class="field-label">Inventory Capability</label>
                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <div>
                                <input type="radio" name="inventory_capability" id="inv_yes" value="yes" class="cat-input"
                                       {{ old('inventory_capability') === 'yes' ? 'checked' : '' }}>
                                <label for="inv_yes" class="cat-pill">✔ Yes</label>
                            </div>
                            <div>
                                <input type="radio" name="inventory_capability" id="inv_no" value="no" class="cat-input"
                                       {{ old('inventory_capability') === 'no' ? 'checked' : '' }}>
                                <label for="inv_no" class="cat-pill">✖ No</label>
                            </div>
                        </div>
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label class="field-label">Warehouse Availability</label>
                        <div style="display:flex;gap:12px;margin-top:8px;">
                            <div>
                                <input type="radio" name="warehouse_availability" id="wh_yes" value="yes" class="cat-input"
                                       {{ old('warehouse_availability') === 'yes' ? 'checked' : '' }}>
                                <label for="wh_yes" class="cat-pill">✔ Yes</label>
                            </div>
                            <div>
                                <input type="radio" name="warehouse_availability" id="wh_no" value="no" class="cat-input"
                                       {{ old('warehouse_availability') === 'no' ? 'checked' : '' }}>
                                <label for="wh_no" class="cat-pill">✖ No</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="field">
                    <label class="field-label">Dealer / Authorization Certificate</label>
                    <div class="file-drop" style="max-width:520px;">
                        <input type="file" name="dealer_certificate" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="file-drop-icon">📜</div>
                        <div class="file-drop-text">Upload Dealer / Authorization Certificate</div>
                        <div class="file-drop-sub">PDF, JPG, PNG · Max 5 MB</div>
                        <div class="file-name-display"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="field" style="margin-bottom:24px;">
            <label class="field-label">Industry Focus (*)</label>
            <span class="field-hint" style="margin-bottom:10px;display:block;">Select all industries your products / services cater to</span>
            <div class="tag-group">
                @foreach(['Compressors','Pumps','Turbines','Instruments','Motors','Gears','Valves','Transmitters','Control Systems','Process Equipment','Piping','Electrical','Civil','Safety','HVAC','Automation','Other'] as $ind)
                <div>
                    <input type="checkbox" name="industry_focus[]" id="if_{{ Str::slug($ind) }}"
                           value="{{ $ind }}" class="tag-checkbox industry-focus-check"
                           {{ in_array($ind, old('industry_focus', [])) ? 'checked' : '' }}>
                    <label for="if_{{ Str::slug($ind) }}" class="tag-label">{{ $ind }}</label>
                </div>
                @endforeach
            </div>
            <div id="industry-focus-other-wrap" style="display:none;margin-top:12px;">
                <div class="field no-icon">
                    <label class="field-label">Please Specify <span class="req">(*)</span></label>
                    <input type="text" name="industry_focus_other" value="{{ old('industry_focus_other') }}"
                           placeholder="Describe your industry focus">
                </div>
            </div>
            @error('industry_focus') <span class="field-error">{{ $message }}</span> @enderror
        </div>

        <div class="divider"></div>

        <div>
            <label class="field-label" style="margin-bottom:14px;display:block;">Product Sub-domain Classification</label>
            <div class="subdomain-grid">
                @php
                $subdomains = [
                    'Pumps'       => ['Centrifugal','Vertical','BB','Reciprocating','Other'],
                    'Compressors' => ['Centrifugal','Reciprocating','Screw','Diaphragm','Other'],
                    'Instruments' => ['Transmitters','Analyzers','Flow','Pressure','Temperature','Level','Other'],
                    'Valves'      => ['Gate','Globe','Ball','Butterfly','Check','Control','Safety Relief','Other'],
                    'Turbines'    => ['Steam','Gas','Expander','Hydraulic','Other'],
                    'Motors'      => ['AC Motor','DC Motor','VFD','Servo','Gear Motor','Other'],
                ];
                @endphp
                @foreach($subdomains as $domain => $subs)
                <div class="subdomain-item">
                    <div class="field-label">{{ $domain }}</div>
                    <div class="tag-group" style="margin-top:8px;">
                        @foreach($subs as $sub)
                        @php $key = Str::slug(strtolower($domain) . '_' . $sub); @endphp
                        <div>
                            <input type="checkbox" name="subdomain_{{ strtolower($domain) }}[]"
                                   id="sd_{{ $key }}" value="{{ $sub }}" class="tag-checkbox"
                                   {{ in_array($sub, old('subdomain_' . strtolower($domain), [])) ? 'checked' : '' }}>
                            <label for="sd_{{ $key }}" class="tag-label" style="font-size:11px;padding:4px 10px;">{{ $sub }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>


{{-- ────────────────────────────────────────────────────────────
     SECTION 5 – QUALITY MANAGEMENT
──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-icon">🏅</div>
        <div>
            <div class="card-title">Quality Management &amp; Standards</div>
            <div class="card-desc">If you enter a certificate number, uploading the certificate is required</div>
        </div>
    </div>
    <div class="card-body">

        <div class="quality-card" style="margin-bottom:20px;">
            <div class="section-badge epc" style="margin-bottom:14px;">🔖 Quality Certificate</div>
            <div class="form-grid form-grid-2" style="margin-bottom:20px;">
                <div class="field no-icon">
                    <label class="field-label">Certificate Number</label>
                    <input type="text" name="quality_certificate_number" id="quality_cert_number"
                           value="{{ old('quality_certificate_number') }}"
                           placeholder="e.g. ISO 9001:2015 · QMS-2024-XXXX">
                    <span class="field-hint">Entering a certificate number requires uploading the certificate below</span>
                    @error('quality_certificate_number') <span class="field-error">{{ $message }}</span> @enderror
                </div>
                <div class="field" id="quality_cert_file_field">
                    <label class="field-label" id="quality_cert_file_label">Upload Quality Certificate <span id="quality_cert_req_star" class="req" style="display:none;">(*)</span></label>
                    <div class="file-drop">
                        <input type="file" name="quality_certificate_file" id="quality_certificate_file"
                               accept=".pdf,.jpg,.jpeg,.png">
                        <div class="file-drop-icon">🏅</div>
                        <div class="file-drop-text">Upload Certificate</div>
                        <div class="file-drop-sub">PDF, JPG, JPEG, PNG · Max 5 MB</div>
                        <div class="file-name-display"></div>
                    </div>
                    <span class="field-hint" id="quality_cert_file_hint" style="display:none;color:var(--error);">Certificate upload is required when a certificate number is entered.</span>
                    @error('quality_certificate_file') <span class="field-error">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <div class="field">
            <label class="field-label">Industry Standards</label>
            <span class="field-hint" style="margin-bottom:16px;display:block;">
                Check all standards your products / services comply with. Describe any additional ones in the field below.
            </span>

            @php
            $stdGroups = [
                ['icon' => '⚙️',  'color' => '#a855f7', 'label' => 'Mechanical & Pressure',
                 'items' => ['ASME','API','EN 13480','PED','NACE']],
                ['icon' => '⚡',  'color' => '#3b82f6', 'label' => 'Electrical & Hazardous Area',
                 'items' => ['ATEX','IECEx','CE Marking','UL Listed','FM Approved']],
                ['icon' => '🛡',  'color' => '#f59e0b', 'label' => 'Safety & Functional Safety',
                 'items' => ['SIL','SIL 2','SIL 3','OISD','DGMS']],
                ['icon' => '🌐',  'color' => '#10b981', 'label' => 'International / Other',
                 'items' => ['ISO 9001','ISO 14001','ISO 45001','OHSAS 18001','IATF 16949']],
            ];
            @endphp

            <div class="std-groups-grid">
                @foreach($stdGroups as $group)
                <div class="std-group-card" style="--grp-color: {{ $group['color'] }};">
                    <div class="std-group-header">
                        <span class="std-group-icon">{{ $group['icon'] }}</span>
                        <span class="std-group-label">{{ $group['label'] }}</span>
                    </div>
                    <div class="std-check-list">
                        @foreach($group['items'] as $std)
                        @php $stdId = Str::slug($std . '-' . $group['label']); @endphp
                        <label class="std-check-row" for="is_{{ $stdId }}">
                            <input type="checkbox"
                                   name="industry_standards[]"
                                   id="is_{{ $stdId }}"
                                   value="{{ $std }}"
                                   class="std-checkbox"
                                   {{ in_array($std, old('industry_standards', [])) ? 'checked' : '' }}>
                            <span class="std-check-box"></span>
                            <span class="std-check-text">{{ $std }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            <div class="std-other-wrap">
                <div class="std-other-label">
                    <span style="color:var(--purple-neon);">✎</span>
                    Other Standards / Compliance Not Listed Above
                </div>
                <textarea
                    name="other_standards"
                    rows="3"
                    placeholder="Any client-specific or country-specific standards you comply with"
                    style="resize:vertical;">{{ old('other_standards') }}</textarea>
                <span class="field-hint" style="margin-top:6px;display:block;">
                    Include the standard name, issuing body, and scope of compliance.
                </span>
            </div>
        </div>

    </div>
</div>


{{-- ────────────────────────────────────────────────────────────
     SECTION 6 – SUPPORTING DOCUMENTS
──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-icon">📁</div>
        <div>
            <div class="card-title">Supporting Documents</div>
            <div class="card-desc">Upload required certificates &amp; compliance documents for Qong Systems review</div>
        </div>
    </div>
    <div class="card-body">
        <div class="form-grid form-grid-2">

            {{-- 1. Company Profile — MANDATORY --}}
            <div class="field">
                <label class="field-label">Company Profile / Brochure (*)</label>
                <div class="file-drop">
                    <input type="file" name="company_brochure" accept=".pdf,.ppt,.pptx">
                    <div class="file-drop-icon">📊</div>
                    <div class="file-drop-text">Company Profile / Capability Statement</div>
                    <div class="file-drop-sub">PDF, PPT · Max 10 MB</div>
                    <div class="file-name-display"></div>
                </div>
                @error('company_brochure') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- 2. Incorporation Certificate — MANDATORY --}}
            <div class="field">
                <label class="field-label">Incorporation Certificate (*)</label>
                <div class="file-drop">
                    <input type="file" name="incorporation_cert" id="incorporation_cert" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="file-drop-icon">📄</div>
                    <div class="file-drop-text">Certificate of Incorporation</div>
                    <div class="file-drop-sub">PDF, JPG, PNG · Max 5 MB</div>
                    <div class="file-name-display"></div>
                </div>
                @error('incorporation_cert') <span class="field-error">{{ $message }}</span> @enderror
            </div>

            {{-- 3. Tax / GST / VAT Certificate --}}
            <div class="field">
                <label class="field-label">Tax / GST / VAT Certificate</label>
                <div class="file-drop">
                    <input type="file" name="tax_gst_vat_certificate" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="file-drop-icon">🌍</div>
                    <div class="file-drop-text">Upload Tax / GST / VAT Certificate</div>
                    <div class="file-drop-sub">PDF, JPG, PNG · Max 5 MB</div>
                    <div class="file-name-display"></div>
                </div>
            </div>

            {{-- 5. ISO / Quality Certificate --}}
            <div class="field">
                <label class="field-label">ISO / Quality Certificate</label>
                <div class="file-drop">
                    <input type="file" name="iso_quality_certificate" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="file-drop-icon">🏅</div>
                    <div class="file-drop-text">Upload ISO / QMS Certificate</div>
                    <div class="file-drop-sub">PDF, JPG, PNG · Max 5 MB</div>
                    <div class="file-name-display"></div>
                </div>
            </div>

            {{-- 6. Authorization Letter --}}
            <div class="field">
                <label class="field-label">Authorization Letter</label>
                <div class="file-drop">
                    <input type="file" name="authorization_letter" accept=".pdf,.jpg,.jpeg,.png">
                    <div class="file-drop-icon">📜</div>
                    <div class="file-drop-text">Upload Authorization Letter</div>
                    <div class="file-drop-sub">For distributors &amp; resellers · PDF, JPG, PNG · Max 5 MB</div>
                    <div class="file-name-display"></div>
                </div>
            </div>

        </div>
    </div>
</div>


{{-- ────────────────────────────────────────────────────────────
     SECTION 7 – TERMS & CONDITIONS
──────────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header">
        <div class="card-icon">📜</div>
        <div>
            <div class="card-title">Terms &amp; Conditions</div>
            <div class="card-desc">Review and accept Qong Systems' vendor terms before submitting</div>
        </div>
    </div>
    <div class="card-body">
        <label class="check-row">
            <input type="checkbox" name="terms_accepted" value="1" required
                   {{ old('terms_accepted') ? 'checked' : '' }}>
            I have read and agree to Qong Systems' <a href="#" style="color:var(--purple-neon);">Terms &amp; Conditions</a> and <a href="#" style="color:var(--purple-neon);">Vendor Code of Conduct</a> (*)
        </label>
        @error('terms_accepted') <span class="field-error" style="display:block;margin-top:-8px;margin-bottom:12px;">{{ $message }}</span> @enderror

        <label class="check-row">
            <input type="checkbox" name="data_accurate" value="1" required
                   {{ old('data_accurate') ? 'checked' : '' }}>
            I certify that all information and documents submitted are accurate and authentic (*)
        </label>
        @error('data_accurate') <span class="field-error" style="display:block;margin-top:-8px;">{{ $message }}</span> @enderror
    </div>

    <div class="btn-row">
        <button type="submit" class="btn btn-primary">Submit to Qong Systems &nbsp;→</button>
    </div>
</div>

</form>
</div>{{-- /onboarding-wrap --}}
@endsection

@push('scripts')
<script>
/* ═══════════════════════════════════════════════
   THEME TOGGLE
═══════════════════════════════════════════════ */
function toggleTheme() {
    const html = document.documentElement;
    const knob = document.getElementById('themeKnob');
    const isDark = !html.hasAttribute('data-theme') || html.getAttribute('data-theme') === 'dark';
    if (isDark) {
        html.setAttribute('data-theme', 'light');
        knob.textContent = '☀';
        localStorage.setItem('qong-theme', 'light');
    } else {
        html.setAttribute('data-theme', 'dark');
        knob.textContent = '☽';
        localStorage.setItem('qong-theme', 'dark');
    }
}
(function () {
    const saved = localStorage.getItem('qong-theme');
    const knob  = document.getElementById('themeKnob');
    if (saved === 'light') {
        document.documentElement.setAttribute('data-theme', 'light');
        if (knob) knob.textContent = '☀';
    } else {
        document.documentElement.setAttribute('data-theme', 'dark');
    }
})();

/* ═══════════════════════════════════════════════
   SAME-AS-REGISTERED TOGGLE
═══════════════════════════════════════════════ */
document.getElementById('same_as_registered').addEventListener('change', function () {
    document.getElementById('op-addr-wrap').style.display = this.checked ? 'none' : 'block';
});
(function () {
    const el = document.getElementById('same_as_registered');
    if (el && el.checked) document.getElementById('op-addr-wrap').style.display = 'none';
})();

/* ═══════════════════════════════════════════════
   BLOCKED EMAIL DOMAINS
═══════════════════════════════════════════════ */
const BLOCKED_DOMAINS = [
    'gmail.com','googlemail.com',
    'yahoo.com','yahoo.co.in','yahoo.co.uk','yahoo.co.jp','ymail.com','rocketmail.com',
    'hotmail.com','hotmail.co.uk','hotmail.in','hotmail.fr','hotmail.de',
    'outlook.com','outlook.in','outlook.co.uk','outlook.fr','outlook.de','outlook.jp',
    'live.com','live.in','live.co.uk','live.fr','msn.com','windowslive.com',
    'rediffmail.com','icloud.com','me.com','mac.com',
    'aol.com','aim.com',
    'protonmail.com','proton.me',
    'zoho.com','zohomail.com',
    'yandex.com','yandex.ru','mail.ru','inbox.ru','list.ru','bk.ru',
    'gmx.com','gmx.net','gmx.de',
    'web.de','freenet.de','t-online.de',
    'tutanota.com','tutamail.com',
    'fastmail.com','fastmail.fm',
    'hushmail.com','mailfence.com','runbox.com',
    'cox.net','comcast.net','verizon.net','att.net','sbcglobal.net',
    'btinternet.com','sky.com','virginmedia.com',
    'email.com','usa.com','myself.com','consultant.com','cheerful.com',
    'qq.com','163.com','126.com','sina.com','sohu.com',
    'naver.com','daum.net','hanmail.net',
    'sify.com','indiatimes.com','in.com'
];

function isBlockedEmail(email) {
    if (!email || !email.includes('@')) return false;
    const parts = email.split('@');
    if (parts.length < 2) return false;
    const domain = parts[parts.length - 1].toLowerCase().trim();
    return BLOCKED_DOMAINS.includes(domain);
}

function validateEmailField(input, bannerEl) {
    const val = input.value.trim();
    if (val.includes('@')) {
        if (isBlockedEmail(val)) {
            bannerEl.classList.add('visible');
            input.style.borderColor = 'var(--error)';
            input.style.boxShadow   = '0 0 0 3px rgba(248,113,113,0.15)';
            return false;
        } else {
            bannerEl.classList.remove('visible');
            input.style.borderColor = '';
            input.style.boxShadow   = '';
        }
    } else {
        bannerEl.classList.remove('visible');
        input.style.borderColor = '';
        input.style.boxShadow   = '';
    }
    return true;
}

const primaryEmailInput  = document.getElementById('primary_email');
const primaryEmailBanner = document.getElementById('primary_email_err');
if (primaryEmailInput) {
    ['input','blur'].forEach(ev => primaryEmailInput.addEventListener(ev, () => validateEmailField(primaryEmailInput, primaryEmailBanner)));
    primaryEmailInput.addEventListener('paste', () => setTimeout(() => validateEmailField(primaryEmailInput, primaryEmailBanner), 10));
}

/* ═══════════════════════════════════════════════
   REAL-TIME INLINE VALIDATION
═══════════════════════════════════════════════ */
const PHONE_RE = /^\+?[0-9]{1,4}[\s\-\.]?(\(?\d{1,4}\)?[\s\-\.]?){1,4}\d{3,10}$/;
const GSTIN_RE = /^[A-Z0-9\/\-]{5,20}$/i;
const URL_RE   = /^https?:\/\/.+\..+/i;

function showInlineError(input, msg) {
    const errId = 'js_live_err_' + input.name.replace(/[\[\].]/g, '_');
    let errEl = document.getElementById(errId);
    input.style.borderColor = 'var(--error)';
    input.style.boxShadow   = '0 0 0 3px rgba(248,113,113,0.12)';
    if (!errEl) {
        errEl = document.createElement('span');
        errEl.className = 'field-error';
        errEl.id = errId;
        const parent = input.closest('.field') || input.parentElement;
        parent.appendChild(errEl);
    }
    errEl.textContent = msg;
}

function clearInlineError(input) {
    const errId = 'js_live_err_' + input.name.replace(/[\[\].]/g, '_');
    const errEl = document.getElementById(errId);
    input.style.borderColor = '';
    input.style.boxShadow   = '';
    if (errEl) errEl.remove();
}

function validatePhone(input) {
    const val = input.value.trim();
    if (!val) { clearInlineError(input); return true; }
    if (!PHONE_RE.test(val)) {
        showInlineError(input, 'Enter a valid phone number (e.g. +91 98765 43210 or +1 212 555 0100).');
        return false;
    }
    clearInlineError(input);
    return true;
}

function validateRequired(input, label) {
    if (!input.value.trim()) {
        showInlineError(input, label + ' is required.');
        return false;
    }
    clearInlineError(input);
    return true;
}

function validateGSTIN(input) {
    const val = input.value.trim();
    if (!val) { showInlineError(input, 'VAT / GST / Tax ID is required.'); return false; }
    if (val.length < 5) { showInlineError(input, 'Must be at least 5 characters.'); return false; }
    if (val.length > 20) { showInlineError(input, 'Must be 20 characters or less.'); return false; }
    if (!GSTIN_RE.test(val)) { showInlineError(input, 'Only letters, digits, /, - allowed.'); return false; }
    clearInlineError(input);
    return true;
}

function validateWebsite(input) {
    const val = input.value.trim();
    if (!val) { clearInlineError(input); return true; }
    if (!URL_RE.test(val)) { showInlineError(input, 'Enter a valid URL starting with http:// or https://'); return false; }
    clearInlineError(input);
    return true;
}

// Wire up blur (on leave) + input (while typing, after first blur)
function wireBlurValidation(input, fn) {
    let touched = false;
    input.addEventListener('blur', () => { touched = true; fn(input); });
    input.addEventListener('input', () => { if (touched) fn(input); });
}

// Required text fields
[
    ['legal_company_name', 'Company Name'],
    ['company_type', null],
    ['reg_address_line1', 'Registered Address'],
    ['reg_city', 'City'],
    ['reg_state', 'State'],
    ['reg_pincode', 'Pincode'],
    ['reg_country', 'Country'],
    ['primary_name', 'Contact Name'],
    ['primary_designation', 'Designation'],
].forEach(([name, label]) => {
    const el = document.querySelector(`[name="${name}"]`);
    if (el && label) wireBlurValidation(el, inp => validateRequired(inp, label));
});

/* ── Phone field: block invalid characters as you type ── */
const PHONE_ALLOWED_KEYS = new Set([
    'Backspace','Delete','Tab','Escape','Enter','ArrowLeft','ArrowRight','ArrowUp','ArrowDown',
    'Home','End','Control','Meta','Shift','+',' ','-','.','(',')'
]);

function enforcePhoneInput(input) {
    // Block non-phone keys on keydown
    input.addEventListener('keydown', function(e) {
        if (PHONE_ALLOWED_KEYS.has(e.key)) return;
        if (e.ctrlKey || e.metaKey) return; // allow copy/paste shortcuts
        if (/^\d$/.test(e.key)) return;     // allow digits
        e.preventDefault();
    });

    // Strip invalid chars on paste
    input.addEventListener('paste', function(e) {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        const cleaned = pasted.replace(/[^\d\+\s\-\.\(\)]/g, '');
        document.execCommand('insertText', false, cleaned);
        setTimeout(() => validatePhone(input), 10);
    });

    // Strip any invalid chars that sneak through (e.g. autofill)
    input.addEventListener('input', function() {
        const pos = input.selectionStart;
        const cleaned = input.value.replace(/[^\d\+\s\-\.\(\)]/g, '');
        if (cleaned !== input.value) {
            input.value = cleaned;
            input.setSelectionRange(pos - 1, pos - 1);
        }
    });
}

const primaryPhoneEl = document.getElementById('primary_phone');
if (primaryPhoneEl) {
    enforcePhoneInput(primaryPhoneEl);
    wireBlurValidation(primaryPhoneEl, validatePhone);
}

// GST field
const gstinEl = document.querySelector('[name="gstin"]');
if (gstinEl) wireBlurValidation(gstinEl, validateGSTIN);

// Website
const websiteEl = document.querySelector('[name="company_website"]');
if (websiteEl) wireBlurValidation(websiteEl, validateWebsite);

// Email fields (already handled by validateEmailField above, just attach blur too)
document.querySelectorAll('.company-email-input').forEach(input => {
    const banner = document.getElementById(input.id + '_err');
    if (banner) {
        let touched = false;
        input.addEventListener('blur', () => { touched = true; validateEmailField(input, banner); });
        input.addEventListener('input', () => { if (touched) validateEmailField(input, banner); });
    }
});

document.getElementById('onboarding-form').addEventListener('submit', function (e) {
    let blocked = false;

    // Re-run all validations on submit to catch untouched fields
    const primaryPhone = document.getElementById('primary_phone');
    if (primaryPhone && !validatePhone(primaryPhone)) blocked = true;

    document.querySelectorAll('input[name^="secondary"][name$="[phone]"]').forEach(function(inp) {
        if (inp.value.trim() && !validatePhone(inp)) blocked = true;
    });

    // Validate company email fields (personal email domains not allowed)
    document.querySelectorAll('.company-email-input').forEach(input => {
        const bannerId = input.id + '_err';
        const banner   = document.getElementById(bannerId);
        if (banner && !validateEmailField(input, banner)) blocked = true;
    });

    // Validate required file uploads (browser can't show tooltip on hidden file inputs)
    const requiredFiles = [
        { id: 'incorporation_cert', label: 'Certificate of Incorporation' },
    ];
    requiredFiles.forEach(function(f) {
        const input = document.getElementById(f.id);
        if (!input) return;
        const errorEl = input.closest('.field') && input.closest('.field').querySelector('.field-error');
        if (!input.files || !input.files.length) {
            input.closest('.file-drop').style.borderColor = 'var(--error)';
            if (!document.getElementById('js_err_' + f.id)) {
                const msg = document.createElement('span');
                msg.className = 'field-error';
                msg.id = 'js_err_' + f.id;
                msg.textContent = f.label + ' is required.';
                input.closest('.field').appendChild(msg);
            }
            input.closest('.file-drop').scrollIntoView({ behavior: 'smooth', block: 'center' });
            blocked = true;
        } else {
            input.closest('.file-drop').style.borderColor = '';
            const existing = document.getElementById('js_err_' + f.id);
            if (existing) existing.remove();
        }
    });

    // Validate quality cert file only when cert number is provided
    const certNum  = document.getElementById('quality_cert_number');
    const certFile = document.getElementById('quality_certificate_file');
    const certHint = document.getElementById('quality_cert_file_hint');
    if (certNum && certNum.value.trim() !== '' && certFile && !certFile.files.length) {
        if (certHint) certHint.style.display = 'block';
        certFile.closest('.file-drop').style.borderColor = 'var(--error)';
        if (!blocked) certFile.closest('.file-drop').scrollIntoView({ behavior: 'smooth', block: 'center' });
        blocked = true;
    } else {
        // Clear any stale quality cert validation state when cert number is empty
        if (certHint) certHint.style.display = 'none';
        if (certFile) certFile.closest('.file-drop').style.borderColor = '';
    }

    // Block if any file exceeds its size limit (shown in red in the display, with actual content)
    document.querySelectorAll('.file-name-display').forEach(d => {
        if (d.style.color === 'rgb(248, 113, 113)' && d.style.display !== 'none' && d.textContent.trim() !== '') {
            if (!blocked) d.closest('.file-drop')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            blocked = true;
        }
    });

    if (blocked) e.preventDefault();
});

/* ═══════════════════════════════════════════════
   VENDOR CATEGORY DYNAMIC SECTIONS
═══════════════════════════════════════════════ */
const RESELLER_CATS = ['General Reseller', 'Authorized Distributor'];

function updateConditionalSections() {
    const checked = Array.from(document.querySelectorAll('.vendor-cat-check:checked')).map(c => c.value);
    const anyChecked = checked.length > 0;
    const isReseller = checked.some(v => RESELLER_CATS.includes(v));

    // Show the details section for any category selection
    document.getElementById('reseller-section').classList.toggle('active', anyChecked);

    // Update the section badge label based on selection
    const badge = document.querySelector('#reseller-section .section-badge');
    if (badge) {
        if (isReseller) {
            badge.textContent = '🛒 Distributor / Reseller Details';
        } else {
            badge.textContent = '📋 Vendor Details';
        }
    }

    document.getElementById('vc-other-wrap').style.display = checked.includes('Other') ? 'block' : 'none';
}
document.querySelectorAll('.vendor-cat-check').forEach(cb => cb.addEventListener('change', updateConditionalSections));
updateConditionalSections();

/* ═══════════════════════════════════════════════
   INDUSTRY FOCUS "OTHER" SHOW/HIDE
═══════════════════════════════════════════════ */
function updateIndustryFocusOther() {
    const checked = Array.from(document.querySelectorAll('.industry-focus-check:checked')).map(c => c.value);
    document.getElementById('industry-focus-other-wrap').style.display = checked.includes('Other') ? 'block' : 'none';
}
document.querySelectorAll('.industry-focus-check').forEach(cb => cb.addEventListener('change', updateIndustryFocusOther));
updateIndustryFocusOther();

/* ═══════════════════════════════════════════════
   QUALITY CERT NUMBER → FILE REQUIRED INDICATOR
═══════════════════════════════════════════════ */
const qualityCertNumInput  = document.getElementById('quality_cert_number');
const qualityCertReqStar   = document.getElementById('quality_cert_req_star');
const qualityCertHint      = document.getElementById('quality_cert_file_hint');
const qualityCertFileInput = document.getElementById('quality_certificate_file');

if (qualityCertNumInput) {
    qualityCertNumInput.addEventListener('input', function () {
        const hasNum = this.value.trim() !== '';
        qualityCertReqStar.style.display = hasNum ? 'inline' : 'none';
        if (!hasNum) {
            qualityCertHint.style.display = 'none';
            qualityCertFileInput.closest('.file-drop').style.borderColor = '';
        }
    });
}
if (qualityCertFileInput) {
    qualityCertFileInput.addEventListener('change', function () {
        if (this.files.length) {
            qualityCertHint.style.display = 'none';
            this.closest('.file-drop').style.borderColor = '';
        }
    });
}

/* ═══════════════════════════════════════════════
   FILE DROP DISPLAY
═══════════════════════════════════════════════ */
function parseSizeLimitMB(zone) {
    const sub = zone.querySelector('.file-drop-sub');
    if (!sub) return null;
    const match = sub.textContent.match(/Max\s+([\d]+)\s*MB/i);
    return match ? parseInt(match[1]) : null;
}

function formatBytes(bytes) {
    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
    return (bytes / 1024).toFixed(0) + ' KB';
}

function initFileDrop(zone) {
    if (zone._dropInit) return;
    zone._dropInit = true;
    const input   = zone.querySelector('input[type="file"]');
    const display = zone.querySelector('.file-name-display');
    if (!input || !display) return;

    const limitMB = parseSizeLimitMB(zone);

    function checkFiles(files) {
        display.style.display = 'block';
        let names = [], warnings = [];

        Array.from(files).forEach(f => {
            names.push(f.name);
            if (limitMB && f.size > limitMB * 1048576) {
                warnings.push(`⚠ "${f.name}" is ${formatBytes(f.size)} — exceeds the ${limitMB} MB limit.`);
            }
        });

        if (warnings.length) {
            display.style.color = '#f87171';
            display.innerHTML = warnings.map(w => `<span style="display:block;">${w}</span>`).join('') +
                `<span style="display:block;margin-top:4px;color:#a0a0c0;">Please select a smaller file.</span>`;
            // Clear the input so the oversized file isn't submitted
            input.value = '';
        } else {
            display.style.color = 'var(--purple-neon)';
            display.textContent = '📎 ' + names.join(', ');
        }
    }

    input.addEventListener('change', () => {
        if (input.files.length) checkFiles(input.files);
        else { display.style.display = 'none'; display.textContent = ''; }
    });

    zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.style.borderColor = 'var(--purple-bright)';
        zone.style.background  = 'rgba(168,85,247,0.1)';
    });
    zone.addEventListener('dragleave', () => {
        zone.style.borderColor = '';
        zone.style.background  = '';
    });
    zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.style.borderColor = '';
        zone.style.background  = '';
        if (e.dataTransfer.files.length && input) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
    });
}
document.querySelectorAll('.file-drop').forEach(initFileDrop);

/* ═══════════════════════════════════════════════
   SECONDARY POC BUILDER
═══════════════════════════════════════════════ */
let secPocCount = 0;

function addSecPoc() {
    if (secPocCount >= 4) { alert('Maximum 4 secondary contacts allowed.'); return; }
    const idx = secPocCount;
    const num = idx + 1;
    const html = `
    <div class="poc-card" id="sec-poc-${num}">
        <div class="poc-title" style="justify-content:space-between;">
            <span>◉ Secondary Contact ${num}</span>
            <button type="button" onclick="removeSecPoc(this)"
                    style="background:none;border:none;color:var(--error);cursor:pointer;font-size:18px;line-height:1;">✕</button>
        </div>
        <div class="form-grid form-grid-3">
            <div class="field">
                <label class="field-label">Full Name</label>
                <div class="input-wrap"><span class="input-icon">👤</span>
                    <input type="text" name="secondary[${idx}][name]" placeholder="Contact full name">
                </div>
            </div>
            <div class="field">
                <label class="field-label">Designation</label>
                <div class="input-wrap"><span class="input-icon">🏷</span>
                    <input type="text" name="secondary[${idx}][designation]" placeholder="e.g. Sales, BD, Technical">
                </div>
            </div>
            <div class="field">
                <label class="field-label">Phone Number</label>
                <div class="input-wrap"><span class="input-icon">📞</span>
                    <input type="tel" name="secondary[${idx}][phone]" placeholder="+91 98765 43210">
                </div>
            </div>
            <div class="field col-span-2">
                <label class="field-label">Official Company Email</label>
                <div class="input-wrap"><span class="input-icon">✉</span>
                    <input type="email" name="secondary[${idx}][email]"
                           id="sec_email_${idx}"
                           placeholder="name@yourcompany.com"
                           class="company-email-input"
                           oninput="validateSecEmail(this, 'sec_email_err_${idx}')"
                           onpaste="setTimeout(()=>validateSecEmail(this,'sec_email_err_${idx}'),10)"
                           onblur="validateSecEmail(this, 'sec_email_err_${idx}')">
                </div>
                <div class="email-reject-banner" id="sec_email_err_${idx}">
                    ✖ Personal email providers (Gmail, Yahoo, Hotmail, Outlook) are not accepted. Please use your official company email.
                </div>
            </div>
        </div>
    </div>`;
    document.getElementById('sec-poc-wrapper').insertAdjacentHTML('beforeend', html);
    // Wire real-time validation on newly added phone field
    const newPhone = document.querySelector(`input[name="secondary[${secPocCount}][phone]"]`);
    if (newPhone) {
        enforcePhoneInput(newPhone);
        wireBlurValidation(newPhone, validatePhone);
    }
    secPocCount++;
}

function validateSecEmail(input, bannerId) {
    const banner = document.getElementById(bannerId);
    if (banner) validateEmailField(input, banner);
}

function removeSecPoc(btn) {
    btn.closest('.poc-card').remove();
    secPocCount = Math.max(0, secPocCount - 1);
}

/* ═══════════════════════════════════════════════
   COUNTRY → STATE → CITY CASCADE
═══════════════════════════════════════════════ */
const GEO = {
    'India': {
        states: ['Andhra Pradesh','Assam','Bihar','Chhattisgarh','Delhi','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal'],
        cities: {
            'Maharashtra':['Mumbai','Pune','Nagpur','Nashik','Aurangabad','Solapur','Kolhapur','Thane','Navi Mumbai'],
            'Delhi':['New Delhi','Noida','Gurgaon','Faridabad','Ghaziabad'],
            'Karnataka':['Bengaluru','Mysuru','Hubli','Mangaluru','Belagavi'],
            'Tamil Nadu':['Chennai','Coimbatore','Madurai','Salem','Tirupur','Trichy'],
            'Gujarat':['Ahmedabad','Surat','Vadodara','Rajkot','Bhavnagar','Gandhinagar'],
            'Telangana':['Hyderabad','Warangal','Karimnagar'],
            'West Bengal':['Kolkata','Howrah','Durgapur','Siliguri'],
            'Rajasthan':['Jaipur','Jodhpur','Udaipur','Kota','Ajmer'],
            'Uttar Pradesh':['Lucknow','Kanpur','Agra','Varanasi','Meerut','Noida','Allahabad'],
            'Punjab':['Chandigarh','Ludhiana','Amritsar','Jalandhar'],
            'Andhra Pradesh':['Visakhapatnam','Vijayawada','Guntur','Tirupati'],
            'Haryana':['Gurugram','Faridabad','Ambala','Panipat'],
            'Kerala':['Thiruvananthapuram','Kochi','Kozhikode','Thrissur'],
            'Madhya Pradesh':['Bhopal','Indore','Jabalpur','Gwalior'],
            'Odisha':['Bhubaneswar','Cuttack','Rourkela'],
        }
    },
    'USA': {
        states: ['Alabama','Alaska','Arizona','Arkansas','California','Colorado','Connecticut','Delaware','Florida','Georgia','Hawaii','Idaho','Illinois','Indiana','Iowa','Kansas','Kentucky','Louisiana','Maine','Maryland','Massachusetts','Michigan','Minnesota','Mississippi','Missouri','Montana','Nebraska','Nevada','New Hampshire','New Jersey','New Mexico','New York','North Carolina','North Dakota','Ohio','Oklahoma','Oregon','Pennsylvania','Rhode Island','South Carolina','South Dakota','Tennessee','Texas','Utah','Vermont','Virginia','Washington','West Virginia','Wisconsin','Wyoming'],
        cities: {
            'California':['Los Angeles','San Francisco','San Diego','San Jose','Sacramento','Fresno'],
            'Texas':['Houston','Dallas','Austin','San Antonio','Fort Worth','El Paso'],
            'New York':['New York City','Buffalo','Rochester','Yonkers','Syracuse'],
            'Florida':['Miami','Orlando','Tampa','Jacksonville','Fort Lauderdale'],
            'Illinois':['Chicago','Aurora','Naperville','Joliet','Rockford'],
            'Pennsylvania':['Philadelphia','Pittsburgh','Allentown','Erie'],
            'Ohio':['Columbus','Cleveland','Cincinnati','Toledo'],
            'Georgia':['Atlanta','Augusta','Columbus','Savannah'],
            'Michigan':['Detroit','Grand Rapids','Warren','Sterling Heights'],
            'Washington':['Seattle','Spokane','Tacoma','Vancouver'],
        }
    },
    'United Kingdom': {
        states: ['England','Scotland','Wales','Northern Ireland'],
        cities: {
            'England':['London','Manchester','Birmingham','Leeds','Liverpool','Bristol','Sheffield','Newcastle','Nottingham','Leicester'],
            'Scotland':['Edinburgh','Glasgow','Aberdeen','Dundee','Inverness'],
            'Wales':['Cardiff','Swansea','Newport','Wrexham'],
            'Northern Ireland':['Belfast','Derry','Lisburn','Newry'],
        }
    },
    'UAE': {
        states: ['Abu Dhabi','Dubai','Sharjah','Ajman','Umm Al Quwain','Ras Al Khaimah','Fujairah'],
        cities: {
            'Dubai':['Dubai City','Deira','Bur Dubai','Jumeirah','Al Quoz','Jebel Ali'],
            'Abu Dhabi':['Abu Dhabi City','Al Ain','Khalifa City','Musaffah'],
            'Sharjah':['Sharjah City','Khor Fakkan','Kalba'],
        }
    },
    'Saudi Arabia': {
        states: ['Riyadh','Makkah','Madinah','Eastern Province','Asir','Tabuk','Hail','Qassim','Jizan','Najran','Al Bahah','Al Jawf','Northern Borders'],
        cities: {
            'Riyadh':['Riyadh','Al Kharj','Dawadmi'],
            'Makkah':['Jeddah','Makkah','Taif'],
            'Eastern Province':['Dammam','Al Khobar','Dhahran','Jubail','Hofuf'],
            'Madinah':['Madinah','Yanbu'],
        }
    },
    'Qatar': {
        states: ['Doha','Al Rayyan','Al Wakrah','Al Khor','Umm Salal','Al Shamal','Al Shahaniya','Madinat ash Shamal'],
        cities: {
            'Doha':['Doha','West Bay','Lusail','Al Sadd','The Pearl'],
            'Al Rayyan':['Al Rayyan','Education City','Al Waab'],
        }
    },
    'Singapore': {
        states: ['Central Region','East Region','North Region','North-East Region','West Region'],
        cities: {
            'Central Region':['Singapore City','Orchard','Marina Bay','Toa Payoh','Bishan'],
            'East Region':['Tampines','Pasir Ris','Bedok','Changi'],
            'West Region':['Jurong','Clementi','Buona Vista','Tuas'],
        }
    },
    'Australia': {
        states: ['New South Wales','Victoria','Queensland','South Australia','Western Australia','Tasmania','Australian Capital Territory','Northern Territory'],
        cities: {
            'New South Wales':['Sydney','Newcastle','Wollongong','Parramatta'],
            'Victoria':['Melbourne','Geelong','Ballarat','Bendigo'],
            'Queensland':['Brisbane','Gold Coast','Townsville','Cairns'],
            'Western Australia':['Perth','Fremantle','Mandurah','Bunbury'],
        }
    },
    'Germany': {
        states: ['Bavaria','Berlin','Brandenburg','Bremen','Hamburg','Hesse','Lower Saxony','Mecklenburg-Vorpommern','North Rhine-Westphalia','Rhineland-Palatinate','Saarland','Saxony','Saxony-Anhalt','Schleswig-Holstein','Thuringia','Baden-Württemberg'],
        cities: {
            'Bavaria':['Munich','Nuremberg','Augsburg','Regensburg'],
            'Berlin':['Berlin'],
            'North Rhine-Westphalia':['Cologne','Düsseldorf','Dortmund','Essen','Duisburg'],
            'Hamburg':['Hamburg'],
            'Baden-Württemberg':['Stuttgart','Karlsruhe','Freiburg','Heidelberg'],
        }
    },
    'Canada': {
        states: ['Ontario','Quebec','British Columbia','Alberta','Manitoba','Saskatchewan','Nova Scotia','New Brunswick','Newfoundland and Labrador','Prince Edward Island','Northwest Territories','Nunavut','Yukon'],
        cities: {
            'Ontario':['Toronto','Ottawa','Mississauga','Brampton','Hamilton'],
            'Quebec':['Montreal','Quebec City','Laval','Gatineau'],
            'British Columbia':['Vancouver','Victoria','Surrey','Burnaby'],
            'Alberta':['Calgary','Edmonton','Red Deer','Lethbridge'],
        }
    },
    'Japan': {
        states: ['Tokyo','Osaka','Kanagawa','Aichi','Saitama','Chiba','Hyogo','Hokkaido','Fukuoka','Shizuoka','Ibaraki','Hiroshima','Kyoto','Niigata','Nagano'],
        cities: {
            'Tokyo':['Tokyo','Shinjuku','Shibuya','Akihabara','Harajuku'],
            'Osaka':['Osaka','Sakai','Higashiosaka'],
            'Kanagawa':['Yokohama','Kawasaki','Sagamihara'],
            'Aichi':['Nagoya','Toyota','Okazaki'],
        }
    },
    'Malaysia': {
        states: ['Kuala Lumpur','Selangor','Johor','Penang','Sabah','Sarawak','Perak','Pahang','Negeri Sembilan','Melaka','Kedah','Kelantan','Terengganu','Perlis','Putrajaya','Labuan'],
        cities: {
            'Kuala Lumpur':['Kuala Lumpur','Chow Kit','Bangsar','Petaling Jaya'],
            'Selangor':['Shah Alam','Subang Jaya','Klang','Petaling Jaya'],
            'Johor':['Johor Bahru','Skudai','Batu Pahat'],
            'Penang':['George Town','Butterworth'],
        }
    },
    'Indonesia': {
        states: ['Jakarta','West Java','East Java','Central Java','Bali','North Sumatra','South Sulawesi','Riau','South Kalimantan','Banten'],
        cities: {
            'Jakarta':['Jakarta','South Jakarta','North Jakarta','East Jakarta'],
            'West Java':['Bandung','Bekasi','Depok','Bogor'],
            'East Java':['Surabaya','Malang','Sidoarjo'],
            'Bali':['Denpasar','Kuta','Ubud','Nusa Dua'],
        }
    },
};

function populateDatalist(datalistId, options) {
    const dl = document.getElementById(datalistId);
    if (!dl) return;
    dl.innerHTML = options.map(o => `<option value="${o}">`).join('');
}

function setupCascade(countrySelId, stateInputId, stateListId, cityInputId, cityListId) {
    const countrySel  = document.getElementById(countrySelId);
    const stateInput  = document.getElementById(stateInputId);
    const cityInput   = document.getElementById(cityInputId);
    if (!countrySel || !stateInput || !cityInput) return;

    countrySel.addEventListener('change', function () {
        const geo = GEO[this.value];
        stateInput.value = '';
        cityInput.value  = '';
        populateDatalist(stateListId, geo ? geo.states : []);
        populateDatalist(cityListId, []);
    });

    stateInput.addEventListener('input', function () {
        const country = countrySel.value;
        const state   = this.value.trim();
        const geo     = GEO[country];
        if (geo && geo.cities && geo.cities[state]) {
            populateDatalist(cityListId, geo.cities[state]);
        } else {
            populateDatalist(cityListId, []);
        }
    });

    // Init on page load (for old() repopulation)
    if (countrySel.value) {
        const geo = GEO[countrySel.value];
        if (geo) populateDatalist(stateListId, geo.states);
        if (geo && stateInput.value && geo.cities && geo.cities[stateInput.value]) {
            populateDatalist(cityListId, geo.cities[stateInput.value]);
        }
    }
}

setupCascade('reg_country','reg_state','reg_state_list','reg_city','reg_city_list');
setupCascade('op_country','op_state','op_state_list','op_city','op_city_list');

/* ── Prevent Enter key from submitting / scrolling to top ── */
document.getElementById('onboarding-form').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        const tag = e.target.tagName;
        // Allow Enter in textareas (for line breaks)
        if (tag === 'TEXTAREA') return;
        // Allow Enter on the submit button
        if (tag === 'BUTTON' && e.target.type === 'submit') return;
        e.preventDefault();
    }
});
</script>
@endpush
