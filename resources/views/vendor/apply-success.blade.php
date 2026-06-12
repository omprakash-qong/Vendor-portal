<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted — QONG</title>
    <link rel="stylesheet" href="{{ asset('css/qong-tokens.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-sans);
            background: #0a0a12;
            color: #f1f0ff;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(168,85,247,0.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(168,85,247,0.06) 1px, transparent 1px);
            background-size: 48px 48px;
            pointer-events: none; z-index: 0;
            animation: gridDrift 20s linear infinite;
        }
        @keyframes gridDrift { from { background-position: 0 0; } to { background-position: 48px 48px; } }
        .card {
            position: relative; z-index: 1;
            background: rgba(15,15,30,0.92);
            border: 1px solid rgba(168,85,247,0.2);
            border-radius: 20px;
            padding: 52px 48px;
            max-width: 520px;
            width: 90%;
            text-align: center;
            box-shadow: 0 0 40px rgba(168,85,247,0.1);
        }
        .icon { font-size: 56px; margin-bottom: 20px; animation: float 3s ease-in-out infinite; }
        @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }
        h1 { font-size: 28px; font-weight: 800; color: #f1f0ff; margin-bottom: 8px; }
        .thank-you { font-size: 18px; font-weight: 600; color: #4ade80; margin-bottom: 12px; }
        p  { font-size: 14px; color: #a0a0c0; line-height: 1.7; margin-bottom: 20px; }
        .highlight { color: #4ade80; font-weight: 600; }
        .steps {
            background: rgba(168,85,247,0.06);
            border: 1px solid rgba(168,85,247,0.15);
            border-radius: 12px;
            padding: 20px 24px;
            text-align: left;
            margin: 24px 0;
        }
        .step { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
        .step:last-child { margin-bottom: 0; }
        .step-num { width: 24px; height: 24px; border-radius: 50%; background: rgba(168,85,247,0.2); border: 1px solid rgba(168,85,247,0.4); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #c084fc; flex-shrink: 0; }
        .step-text { font-size: 13px; color: #9d8ec4; line-height: 1.5; }
        .step-text strong { color: #d4c8f0; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #7c3aed, #c084fc);
            color: #fff; text-decoration: none;
            padding: 13px 32px; border-radius: 10px;
            font-size: 13px; font-weight: 700; letter-spacing: .5px;
            transition: box-shadow 0.2s;
        }
        .btn:hover { box-shadow: 0 0 28px rgba(168,85,247,0.45); }
        .logo { font-size: 13px; color: #3a3a5a; margin-top: 28px; }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">✅</div>
    <h1>Application Submitted!</h1>
    <div class="thank-you">Thank you for applying to Qong Systems.</div>
    <p>Your vendor application has been received and is now under review by our procurement team.</p>

    <div class="steps">
        <div class="step">
            <div class="step-num">1</div>
            <div class="step-text"><strong>Application Received</strong> — We've got your details and supporting documents.</div>
        </div>
        <div class="step">
            <div class="step-num">2</div>
            <div class="step-text"><strong>Review in Progress</strong> — Our procurement team will review your application within <strong>3–5 business days</strong>.</div>
        </div>
        <div class="step">
            <div class="step-num">3</div>
            <div class="step-text"><strong>Credentials via Email</strong> — If approved, you'll receive your portal login credentials at the email you provided.</div>
        </div>
        <div class="step">
            <div class="step-num">4</div>
            <div class="step-text"><strong>Access the Portal</strong> — Log in to track your approval status and manage your vendor profile.</div>
        </div>
    </div>

    <div class="logo"><img src="{{ asset('images/qong-logo.png') }}" alt="QONG" style="width:24px;height:24px;object-fit:contain;vertical-align:middle;margin-right:8px;">QONG — Beyond Plant</div>
</div>
</body>
</html>
