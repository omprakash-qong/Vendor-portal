<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qong Systems — Vendor Portal</title>
    <link rel="stylesheet" href="{{ asset('css/qong-tokens.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-sans);
            background: #F6F7FB;
            color: #14162A;
            min-height: 100vh;
            display: flex; flex-direction: column;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(199,63,190,0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(199,63,190,0.05) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none; z-index: 0;
            animation: gridDrift 20s linear infinite;
        }
        body::after {
            content: '';
            position: fixed; inset: 0;
            background:
                radial-gradient(ellipse 60% 50% at 15% 20%, rgba(199,63,190,0.06) 0%, transparent 70%),
                radial-gradient(ellipse 50% 40% at 85% 75%, rgba(255,77,168,0.05) 0%, transparent 70%);
            pointer-events: none; z-index: 0;
        }
        @keyframes gridDrift { from { background-position: 0 0; } to { background-position: 48px 48px; } }

        /* Header */
        header {
            position: fixed; top: 0; left: 0; right: 0; height: 64px; z-index: 100;
            background: rgba(255,255,255,0.85); backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(199,63,190,0.15);
            display: flex; align-items: center; justify-content: space-between; padding: 0 40px;
        }
        .logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-icon { font-size: 22px; }
        .logo-title { font-family: var(--font-display); font-size: 22px; letter-spacing: 4px; color: #C73FBE; }
        .logo-sub { font-size: 10px; color: #5a5a80; letter-spacing: 2px; }

        /* Hero */
        .hero {
            position: relative; z-index: 1;
            flex: 1; display: flex; align-items: center; justify-content: center;
            text-align: center; padding: 120px 24px 80px;
        }
        .hero-tag {
            display: inline-block; background: rgba(168,85,247,0.1);
            border: 1px solid rgba(168,85,247,0.25); border-radius: 20px;
            font-size: 12px; font-weight: 600; color: #C73FBE; letter-spacing: 1px;
            padding: 5px 14px; margin-bottom: 24px;
        }
        h1 {
            font-size: clamp(36px, 6vw, 64px); font-weight: 900; line-height: 1.1;
            color: #14162A; margin-bottom: 20px;
        }
        h1 span { color: #C73FBE; }
        .hero-desc { font-size: 16px; color: #424766; max-width: 540px; margin: 0 auto 40px; line-height: 1.7; }
        .cta-row { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 64px; }
        .btn-apply {
            display: inline-flex; align-items: center; gap: 8px;
            background: var(--qong-gradient-pink); color: #fff;
            text-decoration: none; padding: 16px 36px; border-radius: 12px;
            font-size: 15px; font-weight: 700; letter-spacing: .5px;
            box-shadow: 0 0 32px rgba(168,85,247,0.3); transition: all 0.2s;
        }
        .btn-apply:hover { box-shadow: 0 0 48px rgba(168,85,247,0.5); transform: translateY(-2px); }

        /* Footer */
        footer { position: relative; z-index: 1; text-align: center; padding: 20px; font-size: 12px; color: #3a3a5a; border-top: 1px solid rgba(168,85,247,0.08); }
    </style>
</head>
<body>

<header>
    <a href="/" class="logo">
        <img src="{{ asset('images/qong-logo.png') }}" alt="QONG" class="logo-icon" style="width:28px;height:28px;object-fit:contain;">
        <div>
            <div class="logo-title">QONG</div>
            <div class="logo-sub">Beyond Plant</div>
        </div>
    </a>
    <div class="header-actions">
    </div>
</header>

<section class="hero">
    <div>
        <div class="hero-tag">✦ Qong Systems Vendor Portal</div>
        <h1>Vendor<br><span>Sign In</span></h1>
        <p class="hero-desc">Access your product catalogue. Sign in with the credentials provided by the Qong Systems procurement team.</p>
        <div class="cta-row">
            <a href="{{ route('login') }}" class="btn-apply">→ Sign In</a>
        </div>
    </div>
</section>

<footer>© {{ date('Y') }} Qong Systems. All rights reserved.</footer>

</body>
</html>
