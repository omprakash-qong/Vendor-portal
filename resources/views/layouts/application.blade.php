<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>QONG – @yield('title', 'Vendor Application')</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/qong-mark.png') }}">
    <link rel="stylesheet" href="{{ asset('css/qong-tokens.css') }}">

    <style>
        :root {
            --bg-base:        #0a0a12;
            --bg-surface:     #0f0f1a;
            --bg-card:        rgba(15,15,30,0.85);
            --border:         rgba(168,85,247,0.18);
            --border-hover:   rgba(168,85,247,0.55);
            --input-bg:       rgba(168,85,247,0.06);
            --purple-neon:    #c084fc;
            --purple-bright:  #a855f7;
            --purple-dim:     rgba(168,85,247,0.35);
            --text-primary:   #f1f0ff;
            --text-secondary: #a0a0c0;
            --text-muted:     #5a5a80;
            --error:          #f87171;
            --success:        #4ade80;
            --radius-sm:      10px;
            --radius-md:      16px;
            --shadow-glow:    0 0 24px rgba(168,85,247,0.12);
            --transition:     all 0.25s cubic-bezier(.4,0,.2,1);
            --grid-color:     rgba(168,85,247,0.06);
        }
        [data-theme="light"] {
            --bg-base:        #f0eeff;
            --bg-surface:     #ffffff;
            --bg-card:        rgba(255,255,255,0.96);
            --border:         rgba(109,40,217,0.28);
            --border-hover:   rgba(109,40,217,0.65);
            --input-bg:       rgba(109,40,217,0.07);
            --purple-neon:    #5b21b6;
            --purple-bright:  #6d28d9;
            --purple-dim:     rgba(109,40,217,0.3);
            --text-primary:   #12082a;
            --text-secondary: #2e1a5e;
            --text-muted:     #6b5fa0;
            --error:          #b91c1c;
            --success:        #15803d;
            --shadow-glow:    0 4px 32px rgba(109,40,217,0.12);
            --grid-color:     rgba(109,40,217,0.07);
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: var(--font-sans);
            background: var(--bg-base);
            color: var(--text-primary);
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

        /* ── Application Header ─────────────────────────────────── */
        .app-header {
            position: fixed; top: 0; left: 0; right: 0; height: 60px;
            z-index: 200;
            background: rgba(10,10,18,0.88);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 32px;
        }
        [data-theme="light"] .app-header { background: rgba(240,238,255,0.92); }
        .app-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .app-logo-icon { font-size: 20px; }
        .app-logo-title { font-size: 17px; font-weight: 900; letter-spacing: 3px; color: var(--text-primary); }
        .app-logo-sub { font-size: 9px; color: var(--text-muted); letter-spacing: 2px; }

        /* ── Content ─────────────────────────────────────────────── */
        .app-content {
            position: relative; z-index: 1;
            padding-top: 60px;
        }
    </style>

    @stack('styles')
</head>
<body>
<script>
(function(){
    var t = localStorage.getItem('vendorTheme');
    if(t === 'light') document.documentElement.setAttribute('data-theme','light');
})();
</script>

<header class="app-header">
    <a href="{{ url('/') }}" class="app-logo">
        <img src="{{ asset('images/qong-mark.png') }}" alt="QONG" class="app-logo-icon" style="width:28px;height:28px;object-fit:contain;">
        <div>
            <div class="app-logo-title">QONG</div>
            <div class="app-logo-sub">Beyond Plant</div>
        </div>
    </a>
    <div class="app-header-right">
        <label class="theme-toggle" title="Toggle light/dark mode">
            <input type="checkbox" id="themeToggle">
            <span class="theme-slider">
                <span class="theme-icon">🌙</span>
            </span>
        </label>
    </div>
</header>

<div class="app-content">
    @yield('content')
</div>

@stack('scripts')
<style>
.theme-toggle { display:flex; align-items:center; cursor:pointer; }
.theme-toggle input { display:none; }
.theme-slider {
    width:48px; height:26px;
    background: rgba(168,85,247,0.25);
    border: 1px solid var(--border);
    border-radius:13px;
    position:relative;
    transition: background 0.3s;
    display:flex; align-items:center; padding:0 4px;
}
.theme-toggle input:checked + .theme-slider { background: rgba(109,40,217,0.35); }
.theme-icon {
    font-size:14px;
    width:20px; height:20px;
    border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    background: rgba(168,85,247,0.3);
    transition: transform 0.3s;
    user-select:none;
}
.theme-toggle input:checked + .theme-slider .theme-icon { transform:translateX(20px); }
</style>
<script>
(function(){
    var stored = localStorage.getItem('vendorTheme') || 'dark';
    var toggle = document.getElementById('themeToggle');
    if(stored === 'light'){
        document.documentElement.setAttribute('data-theme','light');
        if(toggle) toggle.checked = true;
        if(toggle) toggle.nextElementSibling.querySelector('.theme-icon').textContent = '☀️';
    }
    if(toggle){
        toggle.addEventListener('change', function(){
            if(this.checked){
                document.documentElement.setAttribute('data-theme','light');
                this.nextElementSibling.querySelector('.theme-icon').textContent = '☀️';
                localStorage.setItem('vendorTheme','light');
            } else {
                document.documentElement.removeAttribute('data-theme');
                this.nextElementSibling.querySelector('.theme-icon').textContent = '🌙';
                localStorage.setItem('vendorTheme','dark');
            }
        });
    }
})();
</script>
</body>
</html>
