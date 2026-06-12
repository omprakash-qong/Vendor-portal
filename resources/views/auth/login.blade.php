<x-guest-layout>
<style>    @import url('/css/qong-tokens.css');
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
    html,body{height:100%;background:var(--ink-50);font-family:var(--font-sans);overflow:hidden}

    /* Soft brand background */
    .qong-bg{position:fixed;inset:0;z-index:0;
        background:
            radial-gradient(ellipse 70% 55% at 50% 40%,rgba(199,63,190,.07) 0%,transparent 60%),
            radial-gradient(ellipse 50% 45% at 85% 15%,rgba(255,77,168,.06) 0%,transparent 55%),
            var(--ink-50);}
    .qong-bg::before{content:'';position:absolute;inset:0;
        background-image:linear-gradient(rgba(199,63,190,.04) 1px,transparent 1px),
            linear-gradient(90deg,rgba(199,63,190,.04) 1px,transparent 1px);
        background-size:56px 56px;background-position:center center;
        animation:gridDrift 24s linear infinite;}
    @keyframes gridDrift{0%{background-position:center center}100%{background-position:calc(50% + 56px) calc(50% + 56px)}}

    .page-wrapper{position:relative;z-index:1;height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}

    .qong-card{width:100%;max-width:460px;background:#fff;
        border:1px solid var(--ink-200);border-radius:20px;padding:2.4rem 2.6rem;
        box-shadow:0 12px 32px rgba(20,22,42,.10),0 4px 8px rgba(20,22,42,.05);
        animation:cardIn .6s cubic-bezier(.22,1,.36,1) both}
    @keyframes cardIn{from{opacity:0;transform:translateY(20px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}

    .logo-block{display:flex;align-items:center;gap:14px;margin-bottom:1.3rem;animation:fadeUp .5s .1s both}
    .logo-img{width:48px;height:48px;object-fit:contain;flex-shrink:0}
    .logo-text-col{display:flex;flex-direction:column;line-height:1}
    .logo-name{font-family:var(--font-display);font-weight:700;font-size:2rem;letter-spacing:.04em;
        background:var(--qong-gradient-pink);
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .logo-tag{font-family:var(--font-sans);font-size:.6rem;font-weight:600;letter-spacing:.28em;
        text-transform:uppercase;color:var(--ink-400);margin-top:5px;}

    .divider{height:1px;background:var(--ink-200);margin-bottom:1.3rem;animation:fadeUp .5s .18s both}

    .qong-heading{font-family:var(--font-display);font-weight:700;font-size:1.9rem;
        color:var(--ink-900);letter-spacing:.01em;margin-bottom:.3rem;animation:fadeUp .5s .24s both;}
    .qong-sub{font-family:var(--font-sans);font-size:.85rem;color:var(--ink-500);
        margin-bottom:1.4rem;font-weight:400;line-height:1.6;animation:fadeUp .5s .29s both;}

    .alert-success{color:#059669;font-size:.85rem;margin-bottom:1.1rem;font-family:var(--font-sans);font-weight:500}
    .alert-error{color:#dc2626;font-size:.85rem;margin-bottom:1.1rem;font-family:var(--font-sans);font-weight:500}

    .form-group{margin-bottom:1.05rem}
    .form-group label{display:block;font-family:var(--font-sans);font-size:.7rem;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-600);margin-bottom:.45rem}
    .input-wrap{position:relative}
    .input-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--qong-purple);pointer-events:none}
    .form-control{width:100%;background:#fff;border:1px solid var(--ink-200);border-radius:9px;padding:.78rem 1rem .78rem 2.55rem;font-size:.9rem;font-family:var(--font-sans);color:var(--ink-900);outline:none;transition:border-color .2s,box-shadow .2s}
    .form-control::placeholder{color:var(--ink-400)}
    .form-control:focus{border-color:var(--qong-purple);box-shadow:0 0 0 4px rgba(199,63,190,.14)}
    .form-control.is-invalid{border-color:rgba(239,68,68,.6)}
    .error-message{display:block;font-size:.76rem;color:#dc2626;margin-top:.35rem;font-family:var(--font-sans)}

    .btn-primary{width:100%;padding:.9rem 1rem;background:var(--qong-gradient-pink);border:none;border-radius:9px;
        font-family:var(--font-display);font-size:1.1rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;
        color:#fff;cursor:pointer;position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s;
        box-shadow:0 8px 24px rgba(199,63,190,.30)}
    .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(199,63,190,.42)}
    .btn-primary:active{transform:scale(.99)}
    .btn-primary.loading{pointer-events:none;opacity:.85}

    .form-options{display:flex;justify-content:flex-end;margin-bottom:1.4rem;animation:fadeUp .5s .44s both}
    .text-link{font-family:var(--font-sans);font-size:.82rem;color:var(--qong-purple);text-decoration:none;transition:color .2s}
    .text-link:hover{color:var(--qong-magenta)}

    .qong-footer{margin-top:1.4rem;text-align:center;font-family:var(--font-sans);font-size:.72rem;color:var(--ink-400);letter-spacing:.04em;animation:fadeUp .5s .54s both}
    .qong-footer span{color:var(--qong-purple)}

    @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
    @media(max-height:700px){.qong-card{padding:1.6rem 2rem}}
    @media(max-width:520px){.qong-card{padding:1.6rem 1.4rem}}
</style>
<div class="qong-bg"></div>
<div class="page-wrapper">
    <div class="qong-card">

        <div class="logo-block">
            <img src="{{ asset('images/qong-logo.png') }}" alt="QONG" class="logo-img">
            <div class="logo-text-col">
                <span class="logo-name">QONG</span>
                <span class="logo-tag">Beyond Plant</span>
            </div>
        </div>
        <div class="divider"></div>
        <h2 class="qong-heading">Sign In</h2>
        <p class="qong-sub">Access engineering deliverables &amp; P&amp;ID data</p>
        @if (session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif
        <form action="{{ route('login') }}" method="POST" id="loginForm">
            @csrf
            <div class="form-group" style="animation:fadeUp .5s .32s both">
                <label>Email Address</label>
                <div class="input-wrap"><svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="3"/><path d="m2 7 10 7 10-7"/></svg>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="vendor@company.com" autocomplete="email" autofocus>
                </div>
                @error('email')<span class="error-message">{{ $message }}</span>@enderror
            </div>
            <div class="form-group" style="animation:fadeUp .5s .38s both">
                <label>Password</label>
                <div class="input-wrap"><svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" autocomplete="current-password">
                </div>
                @error('password')<span class="error-message">{{ $message }}</span>@enderror
            </div>
            <div class="form-options">
    <a href="{{ route('password.request') }}" class="text-link">
        Forgot password?
    </a>
</div>
            <button type="submit" class="btn-primary" id="loginBtn">Sign In to Dashboard</button>
        </form>
        <div class="qong-footer">QONG Systems &mdash; <span>Next-Engineered for Oil &amp; Gas</span></div>
        <script>document.getElementById('loginForm').addEventListener('submit',function(){const b=document.getElementById('loginBtn');b.classList.add('loading');b.textContent='Signing In...';});<\/script>
    </div>
</div>
</x-guest-layout>