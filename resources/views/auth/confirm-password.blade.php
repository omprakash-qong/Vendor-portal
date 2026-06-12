<x-guest-layout>
<style>    @import url('/css/qong-tokens.css');
    *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
    html,body{height:100%;background:#05010f;font-family:var(--font-sans);overflow:hidden}

    /* ── Background: grid centered on page ── */
    .qong-bg{position:fixed;inset:0;z-index:0;
        background:
            radial-gradient(ellipse 80% 60% at 50% 50%,rgba(160,0,240,.18) 0%,transparent 65%),
            radial-gradient(ellipse 55% 45% at 80% 20%,rgba(180,0,255,.14) 0%,transparent 55%),
            radial-gradient(ellipse 50% 40% at 20% 80%,rgba(110,0,200,.12) 0%,transparent 55%),
            #05010f;}
    .qong-bg::before{content:'';position:absolute;inset:0;
        background-image:linear-gradient(rgba(200,0,255,.07) 1px,transparent 1px),
            linear-gradient(90deg,rgba(200,0,255,.07) 1px,transparent 1px);
        background-size:56px 56px;
        background-position:center center;
        animation:gridDrift 24s linear infinite;}
    .qong-bg::after{content:'';position:absolute;width:700px;height:700px;border-radius:50%;
        background:radial-gradient(circle,rgba(200,0,255,.16) 0%,transparent 70%);
        top:-200px;right:-150px;animation:orbPulse 10s ease-in-out infinite;}
    @keyframes gridDrift{0%{background-position:center center}100%{background-position:calc(50% + 56px) calc(50% + 56px)}}
    @keyframes orbPulse{0%,100%{transform:scale(1);opacity:.65}50%{transform:scale(1.14);opacity:1}}

    /* ── Page wrapper ── */
    .page-wrapper{position:relative;z-index:1;height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem}

    /* ── Card ── */
    .qong-card{width:100%;max-width:480px;background:rgba(14,2,26,.88);
        border:1.5px solid rgba(200,0,255,.52);border-radius:20px;padding:2.2rem 2.6rem;
        backdrop-filter:blur(28px);
        box-shadow:0 0 0 1px rgba(200,0,255,.06),0 0 70px rgba(200,0,255,.20),
            0 30px 70px rgba(0,0,0,.8),inset 0 1px 0 rgba(255,255,255,.06);
        animation:cardIn .7s cubic-bezier(.22,1,.36,1) both}
    @keyframes cardIn{from{opacity:0;transform:translateY(24px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}

    /* ── Logo ── */
    .logo-block{display:flex;align-items:center;gap:14px;margin-bottom:1.2rem;animation:fadeUp .5s .1s both}
    .logo-img{width:52px;height:52px;object-fit:contain;filter:drop-shadow(0 0 14px rgba(180,0,255,.65));flex-shrink:0}
    .logo-text-col{display:flex;flex-direction:column;line-height:1}
    .logo-name{
        font-family:var(--font-display);
        font-weight:400;
        font-size:2rem;
        letter-spacing:.15em;
        background:linear-gradient(90deg,#6abfff 0%,#b55ee8 45%,#f000ff 100%);
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        filter:drop-shadow(0 0 10px rgba(180,0,255,.45));}
    .logo-tag{
        font-family:var(--font-sans);
        font-size:.6rem;font-weight:500;letter-spacing:.32em;
        text-transform:uppercase;color:rgba(255,255,255,.28);margin-top:4px;}

    /* ── Divider ── */
    .divider{height:1px;background:linear-gradient(90deg,transparent,rgba(200,0,255,.55) 40%,rgba(200,0,255,.55) 60%,transparent);margin-bottom:1.2rem;animation:fadeUp .5s .18s both}

    /* ── Headings — Barlow Condensed like the website ── */
    .qong-heading{
        font-family:var(--font-display);
        font-weight:400;font-size:1.7rem;
        color:#fff;letter-spacing:.12em;
        margin-bottom:.3rem;
        animation:fadeUp .5s .24s both;}
    .qong-sub{
        font-family:var(--font-sans);
        font-size:.82rem;color:rgba(255,255,255,.38);
        margin-bottom:1.3rem;font-weight:300;line-height:1.6;
        animation:fadeUp .5s .29s both;}

    /* ── Alerts ── */
    .alert-success{background:rgba(0,200,100,.08);border:1px solid rgba(0,200,100,.3);border-radius:8px;padding:.65rem .9rem;color:#4ade80;font-size:.82rem;margin-bottom:1.1rem;animation:fadeUp .4s both;font-family:var(--font-sans)}
    .alert-error{background:rgba(255,60,60,.1);border:1px solid rgba(255,80,80,.35);border-radius:8px;padding:.65rem .9rem;color:#ff7070;font-size:.82rem;margin-bottom:1.1rem;animation:fadeUp .4s both;font-family:var(--font-sans)}

    /* ── Form group ── */
    .form-group{margin-bottom:1rem}
    .form-group label{display:block;font-family:var(--font-sans);font-size:.68rem;font-weight:600;letter-spacing:.18em;text-transform:uppercase;color:rgba(255,255,255,.4);margin-bottom:.42rem}
    .input-wrap{position:relative}
    .input-icon{position:absolute;left:13px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:rgba(200,0,255,.65);pointer-events:none}
    .form-control{width:100%;background:rgba(255,255,255,.04);border:1px solid rgba(200,0,255,.25);border-radius:9px;padding:.78rem 1rem .78rem 2.55rem;font-size:.9rem;font-family:var(--font-sans);color:#fff;outline:none;transition:border-color .22s,background .22s,box-shadow .22s}
    .form-control::placeholder{color:rgba(255,255,255,.18)}
    .form-control:focus{border-color:rgba(224,0,255,.78);background:rgba(255,255,255,.065);box-shadow:0 0 0 3px rgba(224,0,255,.13),0 0 18px rgba(224,0,255,.09)}
    .form-control.is-invalid{border-color:rgba(255,80,80,.6)}
    .form-control:-webkit-autofill,.form-control:-webkit-autofill:focus{-webkit-box-shadow:0 0 0 1000px #0d0020 inset;-webkit-text-fill-color:#fff;transition:background-color 9999s}
    .error-message{display:block;font-size:.76rem;color:#ff7070;margin-top:.35rem;font-family:var(--font-sans)}

    /* ── Back link ── */
    .back-link{display:inline-flex;align-items:center;gap:6px;font-family:var(--font-sans);font-size:.78rem;color:rgba(200,0,255,.7);text-decoration:none;margin-bottom:1.2rem;transition:color .2s,gap .2s;animation:fadeUp .5s .15s both}
    .back-link:hover{color:#e000ff;gap:10px}
    .back-link svg{width:14px;height:14px;flex-shrink:0}

    /* ── Buttons ── */
    .btn-primary{width:100%;padding:.88rem 1rem;
        background:linear-gradient(135deg,#d400f0 0%,#8800bb 100%);
        border:none;border-radius:9px;
        font-family:var(--font-display);
        font-size:1.05rem;font-weight:400;letter-spacing:.18em;text-transform:uppercase;
        color:#fff;cursor:pointer;position:relative;overflow:hidden;
        transition:transform .18s,box-shadow .18s;
        box-shadow:0 4px 28px rgba(200,0,255,.4),0 0 0 1px rgba(200,0,255,.22)}
    .btn-primary::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.16) 0%,transparent 55%);opacity:0;transition:opacity .2s}
    .btn-primary:hover{transform:translateY(-2px);box-shadow:0 10px 40px rgba(200,0,255,.6),0 0 0 1px rgba(200,0,255,.4)}
    .btn-primary:hover::before{opacity:1}
    .btn-primary:active{transform:scale(.985)}
    .btn-primary.loading{pointer-events:none}
    .btn-primary.loading::after{content:'';position:absolute;inset:0;background:linear-gradient(90deg,transparent 20%,rgba(255,255,255,.18) 50%,transparent 80%);background-size:200% 100%;animation:shimmer 1.1s linear infinite}

    .btn-logout{background:none;border:none;font-family:var(--font-sans);font-size:.82rem;color:rgba(255,255,255,.35);cursor:pointer;text-decoration:underline;text-underline-offset:3px;transition:color .2s;padding:0}
    .btn-logout:hover{color:rgba(255,255,255,.7)}

    /* ── Links ── */
    .text-link{font-family:var(--font-sans);font-size:.8rem;color:rgba(200,0,255,.7);text-decoration:none;transition:color .2s,text-shadow .2s}
    .text-link:hover{color:#e000ff;text-shadow:0 0 12px rgba(224,0,255,.5)}

    /* ── Footer ── */
    .qong-footer{margin-top:1.2rem;text-align:center;font-family:var(--font-sans);font-size:.7rem;color:rgba(255,255,255,.15);letter-spacing:.06em;animation:fadeUp .5s .54s both}
    .qong-footer span{color:rgba(200,0,255,.4)}

    /* ── Options row ── */
    .form-options{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.4rem;animation:fadeUp .5s .44s both}
    .checkbox-label{display:flex;align-items:center;gap:8px;font-family:var(--font-sans);font-size:.82rem;color:rgba(255,255,255,.35);cursor:pointer;user-select:none}
    .checkbox-label input[type="checkbox"]{appearance:none;-webkit-appearance:none;width:16px;height:16px;border:1.5px solid rgba(200,0,255,.4);border-radius:4px;background:rgba(255,255,255,.04);cursor:pointer;position:relative;transition:border-color .2s,background .2s;flex-shrink:0}
    .checkbox-label input[type="checkbox"]:checked{background:rgba(200,0,255,.75);border-color:#e000ff}
    .checkbox-label input[type="checkbox"]:checked::after{content:'';position:absolute;left:4px;top:1px;width:5px;height:9px;border:2px solid #fff;border-top:none;border-left:none;transform:rotate(45deg)}

    @keyframes shimmer{from{background-position:-200% 0}to{background-position:200% 0}}
    @keyframes fadeUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

    @media(max-height:700px){.qong-card{padding:1.4rem 2rem}.logo-img{width:42px;height:42px}.logo-name{font-size:1.65rem}.qong-sub{margin-bottom:.9rem}.form-group{margin-bottom:.75rem}}
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
        <h2 class="qong-heading">Confirm Password</h2>
        <p class="qong-sub">Secure area — please re-enter your password to continue.</p>
        <form method="POST" action="{{ route('password.confirm') }}" id="confirmForm">
            @csrf
            <div class="form-group" style="animation:fadeUp .5s .34s both">
                <label>Password</label>
                <div class="input-wrap"><svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
                </div>
                @error('password')<span class="error-message">{{ $message }}</span>@enderror
            </div>
            <button type="submit" class="btn-primary" id="confirmBtn" style="animation:fadeUp .5s .4s both">Confirm &amp; Continue</button>
        </form>
        <div class="qong-footer">QONG Systems &mdash; <span>Next-Engineered for Oil &amp; Gas</span></div>
        <script>document.getElementById('confirmForm').addEventListener('submit',function(){const b=document.getElementById('confirmBtn');b.classList.add('loading');b.textContent='Confirming...';});<\/script>
    </div>
</div>
</x-guest-layout>