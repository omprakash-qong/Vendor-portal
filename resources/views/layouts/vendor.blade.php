<!DOCTYPE html>
<html lang="en">
<head>
    <script>try{if(localStorage.getItem('qong-theme')==='dark')document.documentElement.setAttribute('data-theme','dark');}catch(e){}</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QONG – @yield('title', 'Vendor Panel')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/qong-mark.png') }}">

    <!-- Preload brand fonts so they're ready before first paint — stops the
         logo/headings flashing from the system fallback to Barlow/Outfit
         (font-display:swap) every time you navigate (e.g. clicking the logo). -->
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/qong/outfit.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/qong/barlow-condensed-700.woff2') }}" crossorigin>
    <link rel="preload" as="font" type="font/woff2" href="{{ asset('fonts/qong/barlow-condensed-600.woff2') }}" crossorigin>

    <!-- QONG Design System — tokens + self-hosted brand fonts -->
    <link rel="stylesheet" href="{{ asset('css/qong-tokens.css') }}">

    <style>
        /* ─── Layout-only variables (brand tokens come from qong-tokens.css) ─── */
        :root {
            --sidebar-w:      260px;
            --navbar-h:       64px;
        }

        /* ─── Dark mode (toggled via data-theme on <html>) ─── */
        [data-theme="dark"] {
            --bg-deep:        #0f1117;
            --bg-mid:         #161922;
            --bg-card:        #161922;
            --bg-card-hover:  #1d2130;
            --border:         #2a2f40;
            --input-bg:       #1b1f2b;
            --text-primary:   #f1f3f9;
            --text-secondary: #c2c7d6;
            --text-muted:     #8b90a3;
        }
        [data-theme="dark"] .sidebar { background: rgba(20,23,32,0.92); }
        [data-theme="dark"] .navbar  { background: rgba(20,23,32,0.88); }
        [data-theme="dark"] select option { background: #1b1f2b; color: #f1f3f9; }

        /* ─── Reset & Base ──────────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-sans);
            background: var(--bg-deep);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ─── Animated Grid Background ─────────────────────────────── */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(199,63,190,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(199,63,190,0.04) 1px, transparent 1px);
            background-size: 48px 48px;
            animation: gridShift 20s linear infinite;
            z-index: 0;
            pointer-events: none;
        }
        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 20% 10%,  rgba(199,63,190,0.06) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 80% 80%,  rgba(255,77,168,0.05) 0%, transparent 70%);
            z-index: 0;
            pointer-events: none;
            animation: ambientPulse 8s ease-in-out infinite alternate;
        }
        @keyframes gridShift {
            0%   { background-position: 0 0; }
            100% { background-position: 48px 48px; }
        }
        @keyframes ambientPulse {
            from { opacity: 0.7; }
            to   { opacity: 1; }
        }

        /* ─── Layout Shell ──────────────────────────────────────────── */
        .shell { display: flex; position: relative; z-index: 1; }

        /* ─── Sidebar ───────────────────────────────────────────────── */
        .sidebar {
            width: var(--sidebar-w);
            background: rgba(255,255,255,0.88);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0; left: 0; bottom: 0;
            z-index: 100;
            transition: transform 0.3s ease;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--border);
        }
        .logo-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
            border-radius: 10px;
            display: grid; place-items: center;
            box-shadow: 0 0 16px var(--purple-glow);
            font-size: 18px;
        }
        .logo-text { line-height: 1; }
        .logo-title {
            font-family: var(--font-display);
            font-size: 22px;
            letter-spacing: 3px;
            color: var(--purple-neon);
            text-shadow: 0 0 12px var(--purple-glow);
        }
        .logo-sub {
            font-size: 9px;
            letter-spacing: 2px;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: 2px;
        }

        .nav-group {
            padding: 4px 12px;
        }
        .nav-label {
            font-size: 9px;
            letter-spacing: 2.5px;
            color: var(--text-muted);
            text-transform: uppercase;
            padding: 0 4px;
            margin: 16px 0 4px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            letter-spacing: 0.3px;
            transition: all 0.2s ease;
            position: relative;
            margin-bottom: 2px;
        }
        .nav-item:hover {
            background: var(--bg-card-hover);
            color: var(--text-primary);
        }
        .nav-item.active {
            background: linear-gradient(90deg, rgba(199,63,190,0.12), rgba(255,77,168,0.04));
            color: var(--purple-neon);
            border-left: 2px solid var(--purple-bright);
            font-weight: 600;
        }
        .nav-item.active::after {
            content: '';
            position: absolute;
            right: 0; top: 25%; bottom: 25%;
            width: 3px;
            background: var(--purple-bright);
            border-radius: 3px 0 0 3px;
            box-shadow: 0 0 8px var(--purple-glow);
        }
        .nav-icon { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }
        .nav-badge {
            margin-left: auto;
            background: var(--purple-mid);
            color: #fff;
            font-size: 10px;
            font-weight: 700;
            padding: 2px 7px;
            border-radius: 20px;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid var(--border);
        }
        .sidebar-footer form { margin: 0; }
        /* Logout button: identical box-model to the nav items so it lines up
           pixel-for-pixel with Dashboard / My Products / Quotations above. */
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 11px 14px;
            border: none;
            background: none;
            border-radius: var(--radius-sm);
            color: #ef4444;
            font-family: var(--font-sans);
            font-size: 13.5px;
            font-weight: 500;
            letter-spacing: 0.3px;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .logout-btn:hover { background: rgba(239,68,68,0.10); color: #f87171; }
        .logout-btn .nav-icon { width: 18px; text-align: center; font-size: 15px; flex-shrink: 0; }

        /* ─── Main Area ─────────────────────────────────────────────── */
        .main {
            margin-left: var(--sidebar-w);
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ─── Top Navbar ────────────────────────────────────────────── */
        .navbar {
            height: var(--navbar-h);
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            position: sticky;
            top: 0;
            z-index: 90;
        }
        .navbar-left {
            display: flex; align-items: center; gap: 16px;
        }
        .back-btn {
            display: inline-flex; align-items: center; gap: 7px;
            height: 36px; padding: 0 14px;
            border: 1px solid var(--border);
            background: var(--input-bg);
            border-radius: 8px;
            color: var(--text-secondary);
            font-family: var(--font-sans);
            font-size: 13px; font-weight: 600; letter-spacing: 0.3px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .back-btn:hover { border-color: var(--purple-bright); color: var(--purple-neon); }
        .back-btn .back-arrow { font-size: 15px; line-height: 1; }
        .navbar-breadcrumb {
            font-size: 13px;
            color: var(--text-muted);
            letter-spacing: 0.4px;
        }
        .navbar-breadcrumb span { color: var(--purple-neon); }
        /* "QONG" crumb acts as a Home button back to the dashboard */
        .crumb-home {
            display: inline-flex; align-items: center;
            padding: 5px 12px; border-radius: 8px;
            border: 1px solid var(--border); background: var(--input-bg);
            color: var(--text-secondary); text-decoration: none;
            font-weight: 700; letter-spacing: 0.5px;
            transition: all 0.2s;
        }
        .crumb-home:hover { border-color: var(--purple-bright); color: var(--purple-neon); }
        .navbar-right {
            display: flex; align-items: center; gap: 14px;
        }
        /* clickable logo */
        a.sidebar-logo { text-decoration: none; cursor: pointer; }
        a.sidebar-logo:hover .logo-title { text-shadow: 0 0 16px var(--purple-glow); }

        /* ── Dark-mode toggle switch ── */
        .theme-toggle {
            position: relative; width: 56px; height: 28px;
            border: 1px solid var(--border); border-radius: 20px;
            background: var(--input-bg); cursor: pointer; padding: 0;
            display: flex; align-items: center; justify-content: space-between;
            transition: background 0.25s, border-color 0.25s;
        }
        .theme-toggle .theme-ico { font-size: 12px; width: 22px; text-align: center; color: var(--text-muted); line-height: 1; }
        .theme-knob {
            position: absolute; top: 2px; left: 2px;
            width: 22px; height: 22px; border-radius: 50%;
            background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
            box-shadow: 0 0 10px var(--purple-glow);
            transition: transform 0.25s ease;
        }
        [data-theme="dark"] .theme-knob { transform: translateX(28px); }
        .theme-toggle:hover { border-color: var(--purple-bright); }

        /* ── Popover menus (notifications / profile) ── */
        .nav-pop { position: relative; }
        .pop-menu {
            position: absolute; top: calc(100% + 10px); right: 0;
            min-width: 230px; background: var(--bg-card);
            border: 1px solid var(--border); border-radius: 12px;
            box-shadow: 0 12px 32px rgba(0,0,0,0.14);
            padding: 6px; z-index: 200; display: none;
        }
        .pop-menu.open { display: block; animation: popIn 0.14s ease; }
        @keyframes popIn { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }
        .pop-head { padding: 10px 12px 8px; border-bottom: 1px solid var(--border); margin-bottom: 4px; }
        .pop-name { font-weight: 700; color: var(--text-primary); font-size: 13.5px; }
        .pop-mail { font-size: 11.5px; color: var(--text-muted); margin-top: 2px; }
        .pop-empty { padding: 14px 12px; color: var(--text-muted); font-size: 12.5px; }
        .pop-item {
            display: block; padding: 9px 12px; border-radius: 8px;
            color: var(--text-secondary); text-decoration: none; font-size: 13px; font-weight: 500;
        }
        .pop-item:hover { background: var(--bg-card-hover); color: var(--text-primary); }
        .pop-item-danger { color: #dc2626; }
        .pop-item-danger:hover { background: rgba(239,68,68,0.08); color: #dc2626; }

        .navbar-right {
            display: flex; align-items: center; gap: 14px;
        }
        .notif-btn {
            width: 36px; height: 36px;
            border: 1px solid var(--border);
            background: var(--input-bg);
            border-radius: 8px;
            display: grid; place-items: center;
            cursor: pointer;
            color: var(--text-secondary);
            font-size: 16px;
            transition: all 0.2s;
            position: relative;
        }
        .notif-btn:hover { border-color: var(--purple-bright); color: var(--purple-neon); }
        .notif-dot {
            position: absolute;
            top: 6px; right: 6px;
            width: 7px; height: 7px;
            background: var(--purple-bright);
            border-radius: 50%;
            box-shadow: 0 0 6px var(--purple-glow);
        }
        .avatar {
            width: 36px; height: 36px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
            display: grid; place-items: center;
            font-family: var(--font-display);
            font-size: 16px;
            letter-spacing: 1px;
            cursor: pointer;
            box-shadow: 0 0 12px var(--purple-glow);
        }

        /* ─── Page Content ──────────────────────────────────────────── */
        .content {
            flex: 1;
            padding: 32px;
            max-width: 1200px;
            width: 100%;
        }

        /* ─── Page Header ───────────────────────────────────────────── */
        .page-header {
            margin-bottom: 32px;
        }
        .page-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 10px;
        }
        .page-title {
            font-family: var(--font-display);
            font-size: 40px;
            letter-spacing: 1px;
            color: var(--text-primary);
            line-height: 1.1;
        }
        .page-subtitle {
            font-size: 13.5px;
            color: var(--text-secondary);
            margin-top: 6px;
            letter-spacing: 0.3px;
        }

        /* ─── Progress Bar ──────────────────────────────────────────── */
        .progress-bar {
            display: flex;
            gap: 0;
            margin-bottom: 36px;
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 24px;
            backdrop-filter: blur(10px);
            overflow-x: auto;
        }
        .progress-step {
            flex: 1;
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 130px;
        }
        .step-bubble {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: var(--input-bg);
            border: 1.5px solid var(--border);
            display: grid; place-items: center;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-muted);
            flex-shrink: 0;
            transition: all 0.3s;
        }
        .progress-step.done .step-bubble {
            background: var(--purple-mid);
            border-color: var(--purple-bright);
            color: #fff;
            box-shadow: 0 0 12px var(--purple-glow);
        }
        .progress-step.active .step-bubble {
            background: transparent;
            border-color: var(--purple-bright);
            color: var(--purple-neon);
            box-shadow: 0 0 14px var(--purple-glow);
            animation: pulseBubble 2s ease-in-out infinite;
        }
        @keyframes pulseBubble {
            0%,100% { box-shadow: 0 0 10px var(--purple-glow); }
            50%      { box-shadow: 0 0 22px rgba(168,85,247,0.6); }
        }
        .step-info { line-height: 1.3; }
        .step-num  { font-size: 10px; color: var(--text-muted); letter-spacing: 1px; text-transform: uppercase; }
        .step-name { font-size: 12px; font-weight: 600; color: var(--text-secondary); }
        .progress-step.active .step-name { color: var(--purple-neon); }
        .step-connector {
            flex: 0 0 24px;
            height: 1.5px;
            background: var(--border);
            margin: 0 4px;
        }
        .progress-step.done + .progress-step .step-connector,
        .progress-step.done .step-connector { background: var(--purple-mid); }

        /* ─── Card ──────────────────────────────────────────────────── */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: border-color 0.3s;
            margin-bottom: 24px;
        }
        .card:hover { border-color: rgba(168,85,247,0.35); }
        .card-header {
            padding: 20px 28px 16px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .card-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(124,58,237,0.3), rgba(168,85,247,0.15));
            border: 1px solid rgba(168,85,247,0.30);
            display: grid; place-items: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .card-title {
            font-family: var(--font-display);
            font-size: 20px;
            letter-spacing: 2.5px;
            color: var(--text-primary);
        }
        .card-desc { font-size: 12px; color: var(--text-muted); margin-top: 1px; }
        .card-body { padding: 28px; }

        /* ─── Form Grid ─────────────────────────────────────────────── */
        .form-grid { display: grid; gap: 22px; }
        .form-grid-2 { grid-template-columns: repeat(2, 1fr); }
        .form-grid-3 { grid-template-columns: repeat(3, 1fr); }
        @media (max-width: 800px) {
            .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
        }
        .col-span-2 { grid-column: span 2; }
        .col-span-3 { grid-column: span 3; }
        @media (max-width: 800px) {
            .col-span-2, .col-span-3 { grid-column: span 1; }
        }

        /* ─── Form Fields ───────────────────────────────────────────── */
        .field { display: flex; flex-direction: column; gap: 7px; }
        .field-label {
            font-size: 10.5px;
            font-weight: 600;
            letter-spacing: 1.8px;
            text-transform: uppercase;
            color: var(--text-secondary);
        }
        .field-label span.req { color: var(--purple-bright); margin-left: 2px; }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 15px;
            pointer-events: none;
        }
        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="url"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            background: var(--input-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-family: var(--font-sans);
            font-size: 13.5px;
            padding: 11px 14px 11px 40px;
            outline: none;
            transition: border-color 0.25s, box-shadow 0.25s, background 0.25s;
            appearance: none;
        }
        .no-icon input[type="text"],
        .no-icon select,
        .no-icon textarea,
        .no-icon input[type="url"],
        .no-icon input[type="number"] {
            padding-left: 14px;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--purple-bright);
            box-shadow: 0 0 0 3px rgba(168,85,247,0.14), 0 0 16px rgba(168,85,247,0.12);
            background: rgba(168,85,247,0.06);
        }
        input::placeholder, textarea::placeholder { color: var(--text-muted); }
        select option { background: #ffffff; color: var(--text-primary); }
        textarea { resize: vertical; min-height: 90px; line-height: 1.6; }
        .field-hint { font-size: 11px; color: var(--text-muted); }
        .field-error { font-size: 11px; color: var(--error); }

        /* ─── Checkbox / Radio Rows ─────────────────────────────────── */
        .check-row {
            display: flex; align-items: center; gap: 10px;
            cursor: pointer;
            font-size: 13.5px;
            color: var(--text-secondary);
        }
        .check-row input[type="checkbox"],
        .check-row input[type="radio"] {
            width: 17px; height: 17px;
            accent-color: var(--purple-bright);
            cursor: pointer;
            padding: 0;
        }

        /* ─── File Upload ───────────────────────────────────────────── */
        .file-drop {
            border: 2px dashed var(--border);
            border-radius: var(--radius-sm);
            padding: 28px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: var(--input-bg);
            position: relative;
        }
        .file-drop:hover, .file-drop.drag-over {
            border-color: var(--purple-bright);
            background: rgba(168,85,247,0.07);
            box-shadow: inset 0 0 20px rgba(168,85,247,0.06);
        }
        .file-drop input[type="file"] {
            position: absolute; inset: 0;
            opacity: 0; cursor: pointer;
            padding: 0; border: none; background: none;
        }
        .file-drop-icon { font-size: 28px; margin-bottom: 10px; }
        .file-drop-text { font-size: 13px; color: var(--text-secondary); }
        .file-drop-sub  { font-size: 11px; color: var(--text-muted); margin-top: 4px; }
        .file-name-display {
            font-size: 12px;
            color: var(--purple-neon);
            margin-top: 8px;
            display: none;
        }

        /* ─── Tag Multi-select ──────────────────────────────────────── */
        .tag-group { display: flex; flex-wrap: wrap; gap: 8px; }
        .tag-checkbox { display: none; }
        .tag-label {
            padding: 6px 14px;
            border-radius: 20px;
            border: 1px solid var(--border);
            font-size: 12px;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            user-select: none;
        }
        .tag-checkbox:checked + .tag-label {
            background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
            border-color: var(--purple-bright);
            color: #fff;
            box-shadow: 0 0 10px var(--purple-glow);
        }
        .tag-label:hover { border-color: var(--purple-bright); color: var(--purple-neon); }

        /* ─── Divider ───────────────────────────────────────────────── */
        .divider {
            height: 1px;
            background: var(--border);
            margin: 24px 0;
        }

        /* ─── Buttons ───────────────────────────────────────────────── */
        .btn-row {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 24px 28px;
            border-top: 1px solid var(--border);
        }
        .btn {
            font-family: var(--font-display);
            letter-spacing: 2px;
            font-size: 15px;
            padding: 12px 32px;
            border-radius: var(--radius-sm);
            border: none;
            cursor: pointer;
            transition: all 0.25s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-outline {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-secondary);
        }
        .btn-outline:hover { border-color: var(--purple-bright); color: var(--purple-neon); }
        .btn-primary {
            background: linear-gradient(135deg, var(--purple-mid), var(--purple-bright));
            color: #fff;
            box-shadow: 0 0 20px rgba(168,85,247,0.35);
        }
        .btn-primary:hover {
            box-shadow: 0 0 32px rgba(168,85,247,0.55);
            transform: translateY(-1px);
        }
        .btn-primary:active { transform: translateY(0); }

        /* ─── Alert/Flash ───────────────────────────────────────────── */
        .alert {
            display: flex; align-items: flex-start; gap: 8px;
            padding: 6px 0;
            margin-bottom: 20px;
            font-size: 13.5px;
            line-height: 1.5;
            background: none;
            border: none;
            font-weight: 500;
        }
        .alert-error { color: #dc2626; }
        .alert-success { color: #059669; }

        /* ─── Status Badge ──────────────────────────────────────────── */
        .status-badge {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 11px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
            padding: 4px 12px; border-radius: 20px;
        }
        .status-pending { background: rgba(251,191,36,0.12); border: 1px solid rgba(251,191,36,0.30); color: #fcd34d; }
        .status-approved { background: rgba(52,211,153,0.12); border: 1px solid rgba(52,211,153,0.30); color: #6ee7b7; }
        .status-rejected { background: rgba(248,113,113,0.12); border: 1px solid rgba(248,113,113,0.30); color: #fca5a5; }
        .status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

        /* ─── Responsive Sidebar Toggle ─────────────────────────────── */
        .menu-toggle {
            display: none;
            background: none; border: 1px solid var(--border);
            border-radius: 8px; padding: 8px 10px;
            color: var(--text-primary); cursor: pointer; font-size: 18px;
        }
        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main { margin-left: 0; }
            .menu-toggle { display: block; }
            .content { padding: 20px 16px; }
        }

        /* ─── Scrollbar ─────────────────────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--purple-mid); }
    </style>

    @stack('styles')
</head>
<body>
<div class="shell">

    <!-- ── SIDEBAR ─────────────────────────────────────────── -->
    <aside class="sidebar" id="sidebar">
        <a href="{{ route('vendor.dashboard') }}" class="sidebar-logo" title="Go to Dashboard">
            <div class="logo-icon"><img src="{{ asset('images/qong-mark.png') }}" alt="QONG" style="width:32px;height:32px;object-fit:contain;"></div>
            <div class="logo-text">
                <div class="logo-title">QONG</div>
                <div class="logo-sub">Beyond Plant</div>
            </div>
        </a>

        <div class="nav-scroll">
            <div class="nav-label">Main</div>
            <a href="{{ route('vendor.dashboard') }}" class="nav-item {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}">
                <span class="nav-icon">⬡</span> Dashboard
            </a>

            <div class="nav-label">Product Catalogue</div>
            <a href="{{ route('vendor.products.index') }}" class="nav-item {{ request()->routeIs('vendor.products.*') || request()->routeIs('vendor.import.*') ? 'active' : '' }}">
                <span class="nav-icon">📦</span> My Products
            </a>

            <div class="nav-label">Sales</div>
            <a href="{{ route('vendor.quotations.index') }}" class="nav-item {{ request()->routeIs('vendor.quotations.*') ? 'active' : '' }}">
                <span class="nav-icon">📄</span> Quotations
            </a>
        </div>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <span class="nav-icon">⏻</span> Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- ── MAIN ─────────────────────────────────────────────── -->
    <div class="main">

        <!-- Top Navbar -->
        <nav class="navbar">
            <div class="navbar-left">
                <button class="menu-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')">☰</button>
                @unless(request()->routeIs('vendor.dashboard'))
                    <button type="button" class="back-btn" onclick="vendorGoBack()">
                        <span class="back-arrow">←</span> Back
                    </button>
                @endunless
                <div class="navbar-breadcrumb">
                    <a href="{{ route('vendor.dashboard') }}" class="crumb-home">QONG</a> &nbsp;/&nbsp; <span>@yield('breadcrumb', 'Dashboard')</span>
                </div>
            </div>
            <div class="navbar-right">
                <!-- Dark mode toggle -->
                <button type="button" class="theme-toggle" id="themeToggle" title="Toggle dark mode" aria-label="Toggle dark mode">
                    <span class="theme-ico theme-ico-sun">☀</span>
                    <span class="theme-knob"></span>
                    <span class="theme-ico theme-ico-moon">☾</span>
                </button>

                <!-- Notifications -->
                <div class="nav-pop" id="notifWrap">
                    <button type="button" class="notif-btn" id="notifBtn" aria-label="Notifications">
                        🔔<span class="notif-dot"></span>
                    </button>
                    <div class="pop-menu" id="notifMenu">
                        <div class="pop-head">Notifications</div>
                        <div class="pop-empty">You're all caught up — no new notifications.</div>
                    </div>
                </div>

                <!-- Profile -->
                <div class="nav-pop" id="profileWrap">
                    <button type="button" class="avatar" id="profileBtn" aria-label="Account menu">
                        {{ strtoupper(substr(auth()->user()->name ?? 'V', 0, 1)) }}
                    </button>
                    <div class="pop-menu pop-menu-right" id="profileMenu">
                        <div class="pop-head">
                            <div class="pop-name">{{ auth()->user()->name ?? 'Vendor' }}</div>
                            <div class="pop-mail">{{ auth()->user()->email ?? '' }}</div>
                        </div>
                        <a href="{{ route('vendor.dashboard') }}" class="pop-item">⬡ Dashboard</a>
                        <a href="{{ route('vendor.products.index') }}" class="pop-item">📦 My Products</a>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="pop-item pop-item-danger" style="width:100%;border:none;background:none;text-align:left;cursor:pointer;">⇥ Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="content">
            @if(session('success'))
                <div class="alert alert-success">✔ {{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">✖ {{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div><!-- /main -->

</div><!-- /shell -->

<script>
    // ── Dark mode: persist choice across pages ──
    (function () {
        var root = document.documentElement;
        try {
            if (localStorage.getItem('qong-theme') === 'dark') root.setAttribute('data-theme', 'dark');
        } catch (e) {}
        var toggle = document.getElementById('themeToggle');
        if (toggle) {
            toggle.addEventListener('click', function () {
                var dark = root.getAttribute('data-theme') === 'dark';
                if (dark) { root.removeAttribute('data-theme'); try { localStorage.setItem('qong-theme', 'light'); } catch (e) {} }
                else { root.setAttribute('data-theme', 'dark'); try { localStorage.setItem('qong-theme', 'dark'); } catch (e) {} }
            });
        }
    })();

    // ── Navbar popovers: notifications + profile ──
    (function () {
        function wire(btnId, menuId) {
            var btn = document.getElementById(btnId), menu = document.getElementById(menuId);
            if (!btn || !menu) return;
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                document.querySelectorAll('.pop-menu.open').forEach(function (m) { if (m !== menu) m.classList.remove('open'); });
                menu.classList.toggle('open');
            });
            menu.addEventListener('click', function (e) { e.stopPropagation(); });
        }
        wire('notifBtn', 'notifMenu');
        wire('profileBtn', 'profileMenu');
        document.addEventListener('click', function () {
            document.querySelectorAll('.pop-menu.open').forEach(function (m) { m.classList.remove('open'); });
        });
    })();

    // Top-bar Back button: step back through in-app history,
    // falling back to the dashboard when there is nowhere to go back to.
    function vendorGoBack() {
        const home = @json(route('vendor.dashboard'));
        if (window.history.length > 1 && document.referrer && document.referrer !== window.location.href) {
            window.history.back();
        } else {
            window.location.href = home;
        }
    }

    // File drop handlers
    document.querySelectorAll('.file-drop').forEach(zone => {
        const input = zone.querySelector('input[type="file"]');
        const display = zone.querySelector('.file-name-display');

        if (input) {
            input.addEventListener('change', () => {
                if (input.files.length && display) {
                    display.style.display = 'block';
                    display.textContent = '📎 ' + Array.from(input.files).map(f => f.name).join(', ');
                }
            });
        }
        zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
        zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
        zone.addEventListener('drop', e => {
            e.preventDefault(); zone.classList.remove('drag-over');
            if (input && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                if (display) {
                    display.style.display = 'block';
                    display.textContent = '📎 ' + Array.from(e.dataTransfer.files).map(f => f.name).join(', ');
                }
            }
        });
    });

    // Same-as checkbox for Operating Address
    const sameAsCheck = document.getElementById('same_as_registered');
    if (sameAsCheck) {
        sameAsCheck.addEventListener('change', function () {
            const fields = document.querySelectorAll('.operating-addr-field');
            fields.forEach(f => {
                f.style.opacity = this.checked ? '0.4' : '1';
                f.querySelectorAll('input, select, textarea').forEach(el => el.disabled = this.checked);
            });
        });
    }
</script>

@stack('scripts')
</body>
</html>
