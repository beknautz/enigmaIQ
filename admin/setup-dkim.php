<?php
require_once __DIR__ . '/auth.php';
require_login();

$pubKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAu+4A3jH6xr+Ep9XuGFa5Sb0UTs/9KiEViT0w0vYEC9YVQbFKIwh8FqjM9Cc4bxNS+HoZ1xwFIwtkRVg/kvnm1qdbE8dBpS8LS55cm2VdHo82b6EbYyz14ke6jOpi6tElnh0gNZ0XzaImSbeHwewbMWRhDoiPbO/UNa1PL3gAJie256wye4ENEqjV7EZbUbzh1WcxkL8U9W8wnxjN76ECUxbD1pQIL2aciTnux8/yHXccp3jn60l57G7Y1GqE5pUBBQIJ0MYb2dYT3/K8MuRMsVMRwib0EGbubhE8OfDgTsSdauxHBlkWXbVn72UH2GAm9/xNVSwygumffIjoEtvh6QIDAQAB';
$dkimRecord  = 'v=DKIM1; k=rsa; p=' . $pubKey;
$spfRecord   = 'v=spf1 a mx include:ezhostingserver.com ~all';
$dmarcRecord = 'v=DMARC1; p=quarantine; rua=mailto:brent@enigmamarketing.com; fo=1';
$keyExists   = file_exists(__DIR__ . '/keys/dkim_private.pem');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>DKIM Setup — EnigmaIQ</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg:#07070d; --bg-2:#0d0d1a; --border:rgba(255,255,255,0.08);
      --border-bright:rgba(255,255,255,0.14); --surface:rgba(255,255,255,0.04);
      --text:#f0f0f8; --muted:#8888a8; --dim:#55556a;
      --purple:#7c3aed; --purple-light:#a78bfa; --green:#10b981;
      --grad:linear-gradient(135deg,#7c3aed,#2563eb); --mono:'JetBrains Mono',monospace;
    }
    body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text);
      min-height:100vh; display:flex; align-items:center; justify-content:center;
      padding:40px 24px; -webkit-font-smoothing:antialiased; }
    .card { width:100%; max-width:780px; background:var(--bg-2); border:1px solid var(--border);
      border-radius:20px; padding:48px; }
    .logo { display:flex; align-items:center; gap:10px; margin-bottom:36px; }
    .logo-mark { width:32px; height:32px; border-radius:8px; background:var(--grad);
      display:flex; align-items:center; justify-content:center; font-weight:900; font-size:15px; color:#fff; }
    .logo-text { font-weight:800; font-size:16px; }
    h1 { font-size:1.5rem; font-weight:800; letter-spacing:-0.03em; margin-bottom:10px; }
    .subtitle { font-size:14px; color:var(--muted); margin-bottom:36px; line-height:1.6; }
    .step { background:var(--bg); border:1px solid var(--border); border-radius:14px;
      padding:28px; margin-bottom:16px; }
    .step-num { font-size:11px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase;
      color:var(--purple-light); margin-bottom:10px; }
    .step h3 { font-size:1rem; font-weight:700; margin-bottom:10px; }
    .step p { font-size:14px; color:var(--muted); line-height:1.6; margin-bottom:14px; }
    .dns-block { background:#0a0a14; border:1px solid var(--border-bright); border-radius:10px; padding:24px; margin-top:14px; }
    .dns-section { margin-bottom:24px; padding-bottom:24px; border-bottom:1px solid var(--border); }
    .dns-section:last-child { margin-bottom:0; padding-bottom:0; border-bottom:none; }
    .dns-section-label { font-size:11px; font-weight:700; letter-spacing:0.1em; text-transform:uppercase;
      color:var(--dim); margin-bottom:14px; display:flex; align-items:center; gap:8px; }
    .tag { font-size:10px; font-weight:700; letter-spacing:0.06em; text-transform:uppercase;
      padding:2px 7px; border-radius:4px; }
    .tag-orange { background:rgba(245,158,11,0.15); color:#fcd34d; }
    .tag-green  { background:rgba(16,185,129,0.15); color:#34d399; }
    .dns-grid { display:grid; grid-template-columns:140px 60px 1fr; gap:12px 20px; align-items:start; }
    .dns-label { font-size:10px; font-weight:700; letter-spacing:0.08em; text-transform:uppercase;
      color:var(--dim); margin-bottom:4px; }
    .dns-val { font-family:var(--mono); font-size:12px; color:var(--text); word-break:break-all; }
    .dns-val.hl { color:var(--purple-light); }
    textarea { width:100%; padding:11px 14px; background:var(--surface); border:1px solid var(--border-bright);
      border-radius:8px; color:var(--text); font-family:var(--mono); font-size:12px;
      resize:none; outline:none; line-height:1.7; cursor:pointer; }
    textarea:focus { border-color:var(--purple); }
    .copy-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; margin-top:8px;
      background:var(--surface); border:1px solid var(--border-bright); border-radius:6px;
      color:var(--muted); font-size:12px; font-weight:600; font-family:inherit; cursor:pointer; transition:0.15s; }
    .copy-btn:hover { background:rgba(255,255,255,0.07); color:var(--text); }
    .copy-btn.copied { color:#34d399; border-color:rgba(16,185,129,0.3); }
    .alert { padding:14px 18px; border-radius:10px; font-size:14px; font-weight:600; margin-bottom:24px; display:flex; align-items:center; gap:10px; }
    .alert-success { background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); color:#34d399; }
    .alert-warn    { background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); color:#fcd34d; }
    .delete-warning { margin-top:24px; padding:14px 18px; border-radius:10px; font-size:13px;
      background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#fca5a5; }
    .btn-back { display:inline-flex; align-items:center; gap:8px; padding:11px 22px; margin-top:24px;
      background:var(--surface); border:1px solid var(--border-bright); border-radius:9px;
      color:var(--muted); font-size:14px; font-weight:600; font-family:inherit; text-decoration:none; transition:0.15s; }
    .btn-back:hover { color:var(--text); background:rgba(255,255,255,0.07); }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-mark">E</div>
    <span class="logo-text">EnigmaIQ — Email Authentication</span>
  </div>

  <h1>DKIM, SPF &amp; DMARC Setup</h1>
  <p class="subtitle">Add these 3 DNS records to enigmaiq.ai to authenticate outgoing email, improve deliverability, and prevent spoofing.</p>

  <?php if ($keyExists): ?>
    <div class="alert alert-success">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      Private key is on the server and ready to sign emails.
    </div>
  <?php else: ?>
    <div class="alert alert-warn">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
      Private key not found on server — FTP <code>admin/keys/dkim_private.pem</code> to activate signing.
    </div>
  <?php endif; ?>

  <div class="step">
    <div class="step-num">Step 1 — Add DNS Records</div>
    <h3>Log into your DNS manager and add these 3 TXT records</h3>
    <p>Use your domain registrar or Hostek's DNS manager. DNS changes take 24–48 hours to propagate.</p>

    <div class="dns-block">

      <!-- DKIM -->
      <div class="dns-section">
        <div class="dns-section-label">DKIM <span class="tag tag-orange">Required</span></div>
        <div class="dns-grid">
          <div>
            <div class="dns-label">Host / Name</div>
            <div class="dns-val hl">mail._domainkey</div>
            <div style="font-size:11px;color:var(--dim);margin-top:3px">.enigmaiq.ai</div>
          </div>
          <div>
            <div class="dns-label">Type</div>
            <div class="dns-val">TXT</div>
          </div>
          <div>
            <div class="dns-label">Value</div>
            <textarea id="dkim-val" rows="4" onclick="this.select()" readonly><?= htmlspecialchars($dkimRecord) ?></textarea>
            <button class="copy-btn" onclick="copyField('dkim-val', this)">Copy</button>
          </div>
        </div>
      </div>

      <!-- SPF -->
      <div class="dns-section">
        <div class="dns-section-label">SPF <span class="tag tag-orange">Required</span></div>
        <div class="dns-grid">
          <div>
            <div class="dns-label">Host / Name</div>
            <div class="dns-val hl">@</div>
            <div style="font-size:11px;color:var(--dim);margin-top:3px">enigmaiq.ai</div>
          </div>
          <div>
            <div class="dns-label">Type</div>
            <div class="dns-val">TXT</div>
          </div>
          <div>
            <div class="dns-label">Value</div>
            <textarea id="spf-val" rows="2" onclick="this.select()" readonly><?= htmlspecialchars($spfRecord) ?></textarea>
            <button class="copy-btn" onclick="copyField('spf-val', this)">Copy</button>
          </div>
        </div>
      </div>

      <!-- DMARC -->
      <div class="dns-section">
        <div class="dns-section-label">DMARC <span class="tag tag-green">Recommended</span></div>
        <div class="dns-grid">
          <div>
            <div class="dns-label">Host / Name</div>
            <div class="dns-val hl">_dmarc</div>
            <div style="font-size:11px;color:var(--dim);margin-top:3px">.enigmaiq.ai</div>
          </div>
          <div>
            <div class="dns-label">Type</div>
            <div class="dns-val">TXT</div>
          </div>
          <div>
            <div class="dns-label">Value</div>
            <textarea id="dmarc-val" rows="2" onclick="this.select()" readonly><?= htmlspecialchars($dmarcRecord) ?></textarea>
            <button class="copy-btn" onclick="copyField('dmarc-val', this)">Copy</button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div class="step">
    <div class="step-num">Step 2 — Verify (after DNS propagates)</div>
    <h3>Check your records at mxtoolbox.com</h3>
    <p>Use the DKIM Lookup tool with selector <strong>mail</strong> and domain <strong>enigmaiq.ai</strong>. All three records should show green.</p>
    <div style="background:#0a0a14;border:1px solid var(--border-bright);border-radius:10px;padding:16px;font-family:var(--mono);font-size:12px;line-height:2;color:var(--muted)">
      DKIM: mxtoolbox.com/dkim <span style="color:var(--dim)">— selector: mail</span><br/>
      SPF: mxtoolbox.com/spf<br/>
      DMARC: mxtoolbox.com/dmarc
    </div>
  </div>

  <div class="delete-warning">
    <strong>Security:</strong> Once DNS is set up, delete <code>admin/setup-dkim.php</code> from the server.
  </div>

  <a href="index.php" class="btn-back">← Back to CMS</a>
</div>

<script>
function copyField(id, btn) {
  navigator.clipboard.writeText(document.getElementById(id).value).then(() => {
    btn.textContent = 'Copied!';
    btn.classList.add('copied');
    setTimeout(() => { btn.textContent = 'Copy'; btn.classList.remove('copied'); }, 2000);
  });
}
</script>
</body>
</html>
