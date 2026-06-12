<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Your Qong Systems Vendor Application — Update</title>
<style>
  body { margin: 0; padding: 0; background: #0a0a12; font-family: 'Segoe UI', Arial, sans-serif; color: #f1f0ff; }
  .wrapper { max-width: 560px; margin: 40px auto; background: rgba(15,15,30,0.98); border: 1px solid rgba(168,85,247,0.2); border-radius: 16px; overflow: hidden; }
  .header { background: linear-gradient(135deg, #1a0a3d, #0f0020); padding: 36px 40px; text-align: center; border-bottom: 1px solid rgba(168,85,247,0.18); }
  .logo { font-size: 28px; font-weight: 900; letter-spacing: 4px; color: #c084fc; }
  .logo-sub { font-size: 11px; color: #6a5fa0; letter-spacing: 2px; margin-top: 4px; }
  .body { padding: 36px 40px; }
  h2 { font-size: 20px; font-weight: 700; color: #f1f0ff; margin-bottom: 8px; }
  p { font-size: 14px; color: #a0a0c0; line-height: 1.7; margin-bottom: 16px; }
  .feedback-box { background: rgba(239,68,68,0.06); border: 1px solid rgba(239,68,68,0.25); border-radius: 10px; padding: 20px 24px; margin: 24px 0; }
  .feedback-label { font-size: 11px; color: #f87171; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; font-weight: 700; }
  .feedback-text { font-size: 14px; color: #fca5a5; line-height: 1.6; }
  .btn { display: inline-block; background: linear-gradient(135deg, #7c3aed, #c084fc); color: #fff; text-decoration: none; padding: 14px 32px; border-radius: 10px; font-size: 14px; font-weight: 700; margin: 8px 0; }
  .note { font-size: 12px; color: #5a5a80; margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(168,85,247,0.1); }
  .footer { background: rgba(10,10,18,0.5); padding: 20px 40px; text-align: center; font-size: 12px; color: #5a5a80; }
</style>
</head>
<body>
<div class="wrapper">
  <div class="header">
    <div class="logo"><img src="{{ url('images/qong-logo.png') }}" alt="QONG" style="width:36px;height:36px;object-fit:contain;vertical-align:middle;margin-right:10px;">QONG</div>
    <div class="logo-sub">Beyond Plant</div>
  </div>
  <div class="body">
    <h2>Application Update, {{ $vendor->primary_name }}</h2>
    <p>Thank you for your interest in becoming a Qong Systems vendor. After careful review, your application for <strong style="color:#f1f0ff;">{{ $vendor->legal_company_name }}</strong> has not been approved at this time.</p>

    @if($vendor->admin_notes)
    <div class="feedback-box">
      <div class="feedback-label">Reviewer Feedback</div>
      <div class="feedback-text">{{ $vendor->admin_notes }}</div>
    </div>
    @endif

    <p>You are welcome to address the feedback above and resubmit a new application.</p>

    <p style="text-align:center;">
      <a href="{{ url('/apply') }}" class="btn">Resubmit Application →</a>
    </p>

    <p class="note">
      If you believe this decision was made in error or have questions, please contact us at
      <a href="mailto:procurement@qong.com" style="color:#c084fc;">procurement@qong.com</a>.
    </p>
  </div>
  <div class="footer">
    © {{ date('Y') }} Qong Systems. All rights reserved.
  </div>
</div>
</body>
</html>
