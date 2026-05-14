<?php
/**
 * One-time DKIM key generator.
 * Run this once at enigmaiq.ai/admin/setup-dkim.php (while logged in),
 * then DELETE this file from the server.
 */
require_once __DIR__ . '/auth.php';
require_login();

$keyDir  = __DIR__ . '/keys/';
$privKey = $keyDir . 'dkim_private.pem';
$pubKey  = $keyDir . 'dkim_public.pem';
$message = '';
$dnsRecord = '';
$alreadyExists = file_exists($privKey);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$alreadyExists) {
    if (!is_dir($keyDir)) mkdir($keyDir, 0700, true);

    $config = [
        'digest_alg'       => 'sha256',
        'private_key_bits' => 2048,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];

    $res = openssl_pkey_new($config);
    if (!$res) {
        $message = 'OpenSSL is not available on this server. Ask Hostek to generate DKIM keys for enigmaiq.ai through cPanel.';
    } else {
        openssl_pkey_export($res, $privPem);
        $details = openssl_pkey_get_details($res);
        $pubPem  = $details['key'];

        file_put_contents($privKey, $privPem);
        chmod($privKey, 0600);
        file_put_contents($pubKey, $pubPem);

        // Strip PEM headers and whitespace for the DNS TXT record
        $pubStripped = preg_replace('/-----.*?-----|\s/', '', $pubPem);
        $dnsRecord = 'v=DKIM1; k=rsa; p=' . $pubStripped;
        $message = 'success';
    }
}

// If keys already exist, just show the DNS record
if ($alreadyExists) {
    $pubPem = file_get_contents($pubKey);
    $pubStripped = preg_replace('/-----.*?-----|\s/', '', $pubPem);
    $dnsRecord = 'v=DKIM1; k=rsa; p=' . $pubStripped;
}
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
      --purple:#7c3aed; --purple-light:#a78bfa; --green:#10b981; --red:#ef4444;
      --grad:linear-gradient(135deg,#7c3aed,#2563eb); --mono:'JetBrains Mono',monospace;
    }
    body { font-family:'Inter',sans-serif; background:var(--bg); color:var(--text);
      min-height:100vh; display:flex; align-items:center; justify-content:center;
      padding:40px 24px; -webkit-font-smoothing:antialiased; }
    .card { width:100%; max-width:760px; background:var(--bg-2); border:1px solid var(--border);
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
    .step h3 { font-size:1rem; font-weight:700; margin-bottom:8px; }
    .step p { font-size:14px; color:var(--muted); line-height:1.6; margin-bottom:14px; }
    .step p:last-child { margin-bottom:0; }
    .dns-block { background:#0a0a14; border:1px solid var(--border-bright); border-radius:10px;
      padding:20px; margin-top:14px; }
    .dns-row { display:grid; grid-template-columns:160px 80px 1fr; gap:16px;
      align-items:start; margin-bottom:14px; font-family:var(--mono); font-size:12px; }
    .dns-row:last-child { margin-bottom:0; }
    .dns-label { color:var(--dim); font-size:11px; font-weight:700; letter-spacing:0.06em;
      text-transform:uppercase; margin-bottom:4px; }
    .dns-val { color:var(--text); word-break:break-all; }
    .dns-val.highlight { color:var(--purple-light); }
    label { display:block; font-size:11px; font-weight:700; letter-spacing:0.08em;
      text-transform:uppercase; color:var(--dim); margin-bottom:7px; }
    textarea { width:100%; padding:14px; background:var(--surface); border:1px solid var(--border-bright);
      border-radius:8px; color:var(--text); font-family:var(--mono); font-size:12px;
      resize:none; outline:none; line-height:1.6; }
    .btn { display:inline-flex; align-items:center; gap:8px; padding:12px 24px;
      background:var(--grad); color:#fff; border:none; border-radius:9px;
      font-size:14px; font-weight:700; font-family:inherit; cursor:pointer; transition:0.2s; }
    .btn:hover { opacity:0.9; transform:translateY(-1px); }
    .btn-back { background:var(--surface); border:1px solid var(--border-bright);
      color:var(--muted); text-decoration:none; }
    .alert { padding:14px 18px; border-radius:10px; font-size:14px; font-weight:600;
      margin-bottom:24px; }
    .alert-success { background:rgba(16,185,129,0.12); border:1px solid rgba(16,185,129,0.3); color:#34d399; }
    .alert-warn { background:rgba(245,158,11,0.12); border:1px solid rgba(245,158,11,0.3); color:#fcd34d; }
    .alert-error { background:rgba(239,68,68,0.12); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; }
    .tag { display:inline-block; font-size:10px; font-weight:700; letter-spacing:0.08em;
      text-transform:uppercase; padding:3px 8px; border-radius:4px; margin-left:8px; vertical-align:middle; }
    .tag-green { background:rgba(16,185,129,0.15); color:#34d399; }
    .tag-orange { background:rgba(245,158,11,0.15); color:#fcd34d; }
    .delete-warning { margin-top:24px; padding:14px 18px; border-radius:10px; font-size:13px;
      background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#fca5a5; }
    .col2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  </style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-mark">E</div>
    <span class="logo-text">EnigmaIQ — DKIM Setup</span>
  </div>

  <h1>Email Authentication Setup</h1>
  <p class="subtitle">Configure DKIM, DMARC, and SPF to improve deliverability and prevent spoofing for enigmaiq.ai.</p>

  <?php if ($message === 'success'): ?>
    <div class="alert alert-success">DKIM key pair generated and saved. Add the DNS records below.</div>
  <?php elseif ($message && $message !== 'success'): ?>
    <div class="alert alert-error"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <?php if (!$alreadyExists && $message !== 'success'): ?>
  <!-- GENERATE KEYS -->
  <div class="step">
    <div class="step-num">Step 1 of 1</div>
    <h3>Generate DKIM Key Pair</h3>
    <p>This creates a 2048-bit RSA key pair. The private key stays on this server (never shared). The public key goes into your DNS as a TXT record.</p>
    <form method="POST">
      <button type="submit" class="btn">Generate Keys Now</button>
    </form>
  </div>
  <?php else: ?>

  <!-- DNS RECORDS -->
  <div class="step">
    <div class="step-num">Step 1 — Add DNS Records</div>
    <h3>Add these 3 records in your DNS manager</h3>
    <p>Log into your domain registrar or Hostek's DNS manager and add the following TXT records for <strong>enigmaiq.ai</strong>.</p>

    <div class="dns-block">

      <p style="font-size:12px;color:var(--dim);margin-bottom:16px;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">DKIM <span class="tag tag-orange">Required</span></p>
      <div style="margin-bottom:6px">
        <div class="dns-label">Host / Name</div>
        <div class="dns-val highlight" style="font-family:var(--mono);font-size:13px">mail._domainkey.enigmaiq.ai</div>
      </div>
      <div style="margin-bottom:6px">
        <div class="dns-label">Type</div>
        <div class="dns-val" style="font-family:var(--mono);font-size:13px">TXT</div>
      </div>
      <div style="margin-bottom:0">
        <div class="dns-label">Value</div>
        <textarea rows="4" onclick="this.select()" readonly><?= htmlspecialchars($dnsRecord) ?></textarea>
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:20px 0" />

      <p style="font-size:12px;color:var(--dim);margin-bottom:16px;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">SPF <span class="tag tag-orange">Required</span></p>
      <div class="col2">
        <div>
          <div class="dns-label">Host / Name</div>
          <div class="dns-val highlight" style="font-family:var(--mono);font-size:13px">enigmaiq.ai</div>
        </div>
        <div>
          <div class="dns-label">Type</div>
          <div class="dns-val" style="font-family:var(--mono);font-size:13px">TXT</div>
        </div>
      </div>
      <div style="margin-top:10px">
        <div class="dns-label">Value</div>
        <textarea rows="2" onclick="this.select()" readonly>v=spf1 a mx include:ezhostingserver.com ~all</textarea>
      </div>

      <hr style="border:none;border-top:1px solid var(--border);margin:20px 0" />

      <p style="font-size:12px;color:var(--dim);margin-bottom:16px;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;">DMARC <span class="tag tag-green">Recommended</span></p>
      <div class="col2">
        <div>
          <div class="dns-label">Host / Name</div>
          <div class="dns-val highlight" style="font-family:var(--mono);font-size:13px">_dmarc.enigmaiq.ai</div>
        </div>
        <div>
          <div class="dns-label">Type</div>
          <div class="dns-val" style="font-family:var(--mono);font-size:13px">TXT</div>
        </div>
      </div>
      <div style="margin-top:10px">
        <div class="dns-label">Value</div>
        <textarea rows="2" onclick="this.select()" readonly>v=DMARC1; p=quarantine; rua=mailto:brent@enigmamarketing.com; fo=1</textarea>
      </div>

    </div>
  </div>

  <div class="step">
    <div class="step-num">Step 2 — Verify (after DNS propagates ~24–48hrs)</div>
    <h3>Check your records are live</h3>
    <p>Run these in a terminal or use <strong>mxtoolbox.com</strong>:</p>
    <div class="dns-block" style="font-family:var(--mono);font-size:12px;line-height:2">
      dig TXT mail._domainkey.enigmaiq.ai<br/>
      dig TXT _dmarc.enigmaiq.ai<br/>
      dig TXT enigmaiq.ai
    </div>
  </div>

  <div class="delete-warning">
    <strong>Security:</strong> Delete this file from the server once DNS is configured — <code>admin/setup-dkim.php</code>
  </div>

  <?php endif; ?>

  <div style="margin-top:24px">
    <a href="index.php" class="btn btn-back">← Back to CMS</a>
  </div>
</div>
</body>
</html>
