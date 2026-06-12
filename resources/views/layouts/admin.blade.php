<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin – @yield('title', 'Vendor Management') | QONG</title>
    <link rel="stylesheet" href="{{ asset('css/qong-tokens.css') }}">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-sans);
            background: #0a0012;
            color: #e8e0ff;
            min-height: 100vh;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(168,85,247,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(168,85,247,0.05) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none; z-index: 0;
        }
        .shell { display: flex; position: relative; z-index: 1; }

        /* Sidebar */
        .sidebar {
            width: 220px;
            background: rgba(10,0,21,0.9);
            border-right: 1px solid rgba(168,85,247,0.15);
            position: sticky; top: 0; height: 100vh; overflow-y: auto;
            display: flex; flex-direction: column; padding: 24px 0;
            flex-shrink: 0;
        }
        .sidebar-brand { padding: 0 20px 24px; border-bottom: 1px solid rgba(168,85,247,0.1); margin-bottom: 16px; }
        .sidebar-brand-title { font-size: 17px; font-weight: 900; letter-spacing: 3px; color: #c084fc; }
        .sidebar-brand-sub { font-size: 12px; color: #5a5a80; letter-spacing: 1.5px; margin-top: 2px; }
        .sidebar-label { font-size: 12px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: #5a5a80; padding: 0 20px; margin-bottom: 6px; margin-top: 16px; }
        .sidebar-item {
            display: flex; align-items: center; gap: 10px;
            padding: 12px 20px; font-size: 16px; color: #9d8ec4;
            text-decoration: none; transition: all 0.2s;
        }
        .sidebar-item:hover, .sidebar-item.active { color: #c084fc; background: rgba(168,85,247,0.1); }
        .sidebar-item.active { border-left: 3px solid #a855f7; }
        .sidebar-footer { margin-top: auto; padding: 16px 20px; border-top: 1px solid rgba(168,85,247,0.1); }

        /* Main */
        .main { flex: 1; min-width: 0; padding: 32px; min-height: 100vh; }
        .page-title { font-size: 30px; font-weight: 800; color: #f1f0ff; margin-bottom: 4px; }
        .page-sub { font-size: 15px; color: #6a5fa0; margin-bottom: 24px; }

        /* Cards */
        .card { background: rgba(15,15,30,0.9); border: 1px solid rgba(168,85,247,0.15); border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
        .card-body { padding: 20px; }

        /* Table */
        table { width: 100%; border-collapse: collapse; }
        th { font-size: 13px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: #5a5a80; padding: 14px 16px; text-align: left; border-bottom: 1px solid rgba(168,85,247,0.12); }
        td { font-size: 15px; color: #c8bfed; padding: 16px 16px; border-bottom: 1px solid rgba(168,85,247,0.07); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(168,85,247,0.04); }

        /* Badges */
        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 700; letter-spacing: .8px; text-transform: uppercase; }
        .badge-pending  { background: rgba(251,191,36,0.1); color: #fbbf24; border: 1px solid rgba(251,191,36,0.2); }
        .badge-approved { background: rgba(74,222,128,0.1); color: #4ade80; border: 1px solid rgba(74,222,128,0.2); }
        .badge-rejected { background: rgba(248,113,113,0.1); color: #f87171; border: 1px solid rgba(248,113,113,0.2); }
        .badge-draft    { background: rgba(148,163,184,0.1); color: #94a3b8; border: 1px solid rgba(148,163,184,0.2); }

        /* Buttons */
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border-radius: 8px; font-size: 15px; font-weight: 600; cursor: pointer; border: none; text-decoration: none; transition: all 0.2s; }
        .btn-primary { background: linear-gradient(135deg, #7c3aed, #c084fc); color: #fff; }
        .btn-primary:hover { box-shadow: 0 0 20px rgba(168,85,247,0.4); }
        .btn-danger  { background: rgba(248,113,113,0.15); color: #f87171; border: 1px solid rgba(248,113,113,0.3); }
        .btn-danger:hover { background: rgba(248,113,113,0.25); }
        .btn-ghost   { background: transparent; color: #9d8ec4; border: 1px solid rgba(168,85,247,0.2); }
        .btn-ghost:hover { border-color: #a855f7; color: #c084fc; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }

        /* Alerts */
        .alert { padding: 14px 18px; border-radius: 8px; font-size: 15px; margin-bottom: 20px; }
        .alert-success { background: rgba(74,222,128,0.08); border: 1px solid rgba(74,222,128,0.2); color: #4ade80; }
        .alert-error   { background: rgba(248,113,113,0.08); border: 1px solid rgba(248,113,113,0.2); color: #f87171; }

        /* Filter pills */
        .filter-bar { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
        .filter-pill { padding: 7px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; text-decoration: none; border: 1px solid rgba(168,85,247,0.2); color: #9d8ec4; transition: all 0.2s; }
        .filter-pill:hover, .filter-pill.active { border-color: #a855f7; color: #c084fc; background: rgba(168,85,247,0.1); }

        /* Detail rows */
        .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .detail-item label { font-size: 14px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #5a5a80; display: block; margin-bottom: 6px; }
        .detail-item span  { font-size: 18px; color: #c8bfed; }

        /* ── Light mode overrides ── */
        body.light-mode { background:#f4f3ff; color:#1a1230; }
        body.light-mode::before { display:none; }
        body.light-mode .sidebar { background:#fff; border-right-color:#e5e0f5; }
        body.light-mode .sidebar-brand-title { color:#7c3aed; }
        body.light-mode .sidebar-brand-sub { color:#9d8ec4; }
        body.light-mode .sidebar-label { color:#b0a8cc; }
        body.light-mode .sidebar-item { color:#5a4a78; }
        body.light-mode .sidebar-item:hover,
        body.light-mode .sidebar-item.active { color:#7c3aed; background:rgba(124,58,237,0.08); }
        body.light-mode .sidebar-item.active { border-left-color:#7c3aed; }
        body.light-mode .main { background:#f4f3ff; }
        body.light-mode .card { background:#fff; border-color:#e5e0f5; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
        body.light-mode .page-title { color:#1a1230; }
        body.light-mode .page-sub { color:#6a5fa0; }
        body.light-mode th { color:#9d8ec4; border-bottom-color:#e5e0f5; }
        body.light-mode td { color:#3d2e6b; border-bottom-color:#f0edfb; }
        body.light-mode tr:hover td { background:rgba(124,58,237,0.04); }
        body.light-mode .detail-item label { color:#9d8ec4; }
        body.light-mode .detail-item span { color:#2d1f5e; }
        body.light-mode .filter-pill { color:#6a5fa0; border-color:rgba(124,58,237,0.2); }
        body.light-mode .filter-pill:hover,
        body.light-mode .filter-pill.active { color:#7c3aed; background:rgba(124,58,237,0.08); border-color:#7c3aed; }
        body.light-mode .btn-ghost { color:#6a5fa0; border-color:rgba(124,58,237,0.25); }
        body.light-mode .btn-ghost:hover { color:#7c3aed; border-color:#7c3aed; }
        body.light-mode #theme-label { color:#6a5fa0; }
    </style>
</head>
<body>
<div class="shell">

    <aside class="sidebar">
        <div class="sidebar-brand">
            <div class="sidebar-brand-title"><img src="{{ asset('images/qong-logo.png') }}" alt="QONG" style="width:28px;height:28px;object-fit:contain;vertical-align:middle;margin-right:8px;">QONG</div>
            <div class="sidebar-brand-sub">{{ auth()->user()->role === 'super_admin' ? 'Super Admin Panel' : 'Admin Panel' }}</div>
        </div>

        <div class="sidebar-label">Vendors</div>
        <a href="{{ route('admin.vendors.index') }}" class="sidebar-item {{ request()->routeIs('admin.vendors.*') ? 'active' : '' }}">
            ◈ Vendor Applications
        </a>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="sidebar-item" style="width:100%;background:none;border:none;cursor:pointer;color:#f87171;">
                    ⇥ Logout
                </button>
            </form>
        </div>
    </aside>

    <main class="main">
        {{-- Top bar with theme toggle --}}
        <div style="display:flex;justify-content:flex-end;align-items:center;margin-bottom:24px;gap:10px;">
            <span id="theme-label" style="font-size:12px;color:#5a5a80;letter-spacing:1px;text-transform:uppercase;">Dark</span>
            <label style="display:inline-flex;align-items:center;cursor:pointer;position:relative;width:44px;height:24px;">
                <input type="checkbox" id="theme-toggle" style="opacity:0;width:0;height:0;position:absolute;">
                <span id="theme-track" style="position:absolute;inset:0;background:rgba(168,85,247,0.2);border:1px solid rgba(168,85,247,0.3);border-radius:12px;transition:background .3s;"></span>
                <span id="theme-thumb" style="position:absolute;left:3px;top:3px;width:16px;height:16px;background:#a855f7;border-radius:50%;transition:transform .3s;"></span>
            </label>
        </div>
        @if(session('success'))
            <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-error">✖ {{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

</div>
<style>
body.light-mode { background: #f4f3ff !important; color: #1a1a2e !important; }
body.light-mode::before { display:none; }
body.light-mode .sidebar { background: #ffffff !important; border-right-color: rgba(168,85,247,0.2) !important; }
body.light-mode .sidebar-brand-title { color: #7c3aed !important; }
body.light-mode .sidebar-brand-sub,
body.light-mode .sidebar-label { color: #9d8ec4 !important; }
body.light-mode .sidebar-item { color: #4a3f6b !important; }
body.light-mode .sidebar-item:hover,
body.light-mode .sidebar-item.active { background: rgba(168,85,247,0.08) !important; color: #7c3aed !important; }
body.light-mode .card { background: #ffffff !important; border-color: rgba(168,85,247,0.2) !important; box-shadow: 0 2px 12px rgba(168,85,247,0.08); }
body.light-mode .page-title { color: #1a1a2e !important; }
body.light-mode .page-sub { color: #6a5fa0 !important; }
body.light-mode th { color: #6a5fa0 !important; }
body.light-mode td { color: #3a3060 !important; }
body.light-mode .detail-item label { color: #9d8ec4 !important; }
body.light-mode .detail-item span { color: #2a2050 !important; }
body.light-mode .btn-ghost { color: #7c3aed !important; border-color: rgba(168,85,247,0.3) !important; }
body.light-mode .filter-pill { color: #6a5fa0 !important; }
body.light-mode .filter-pill.active,
body.light-mode .filter-pill:hover { color: #7c3aed !important; background: rgba(168,85,247,0.1) !important; }
body.light-mode #theme-label { color: #9d8ec4 !important; }
body.light-mode #theme-track { background: rgba(168,85,247,0.15) !important; }
</style>
<script>
(function() {
    const toggle = document.getElementById('theme-toggle');
    const label  = document.getElementById('theme-label');
    const thumb  = document.getElementById('theme-thumb');
    const track  = document.getElementById('theme-track');

    // Restore saved preference
    if (localStorage.getItem('adminTheme') === 'light') {
        document.body.classList.add('light-mode');
        toggle.checked = true;
        label.textContent = 'Light';
        thumb.style.transform = 'translateX(20px)';
        track.style.background = 'rgba(168,85,247,0.5)';
    }

    toggle.addEventListener('change', function () {
        if (this.checked) {
            document.body.classList.add('light-mode');
            label.textContent = 'Light';
            thumb.style.transform = 'translateX(20px)';
            track.style.background = 'rgba(168,85,247,0.5)';
            localStorage.setItem('adminTheme', 'light');
        } else {
            document.body.classList.remove('light-mode');
            label.textContent = 'Dark';
            thumb.style.transform = 'translateX(0)';
            track.style.background = 'rgba(168,85,247,0.2)';
            localStorage.setItem('adminTheme', 'dark');
        }
    });
})();
</script>
</body>
</html>
