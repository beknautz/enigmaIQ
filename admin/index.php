<?php
require_once __DIR__ . '/auth.php';
require_login();

$raw = file_exists(CONTENT_FILE) ? file_get_contents(CONTENT_FILE) : false;
$content = $raw ? json_decode($raw, true) : null;
if (!$content) {
    die('<h2 style="font-family:sans-serif;padding:40px">content.json missing or unreadable.<br>Upload it to the site root and set permissions to 644.</h2>');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EnigmaIQ CMS</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #07070d; --bg-2: #0d0d1a; --bg-3: #111120;
      --border: rgba(255,255,255,0.08); --border-bright: rgba(255,255,255,0.14);
      --surface: rgba(255,255,255,0.04); --surface-2: rgba(255,255,255,0.07);
      --text: #f0f0f8; --muted: #8888a8; --dim: #55556a;
      --purple: #7c3aed; --purple-light: #a78bfa;
      --blue: #2563eb; --green: #10b981; --red: #ef4444;
      --grad: linear-gradient(135deg, #7c3aed, #2563eb);
      --sidebar-w: 240px;
    }
    html, body { height: 100%; }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text);
      display: flex; -webkit-font-smoothing: antialiased; }

    /* ---- SIDEBAR ---- */
    .sidebar {
      width: var(--sidebar-w); flex-shrink: 0; background: var(--bg-2);
      border-right: 1px solid var(--border); display: flex; flex-direction: column;
      position: fixed; top: 0; bottom: 0; left: 0; overflow-y: auto; z-index: 10;
    }
    .sidebar-logo {
      display: flex; align-items: center; gap: 10px; padding: 24px 20px;
      border-bottom: 1px solid var(--border); flex-shrink: 0;
    }
    .logo-mark {
      width: 30px; height: 30px; border-radius: 7px; background: var(--grad);
      display: flex; align-items: center; justify-content: center;
      font-weight: 900; font-size: 14px; color: #fff; flex-shrink: 0;
    }
    .logo-name { font-weight: 800; font-size: 15px; letter-spacing: -0.03em; }
    .logo-badge {
      margin-left: auto; font-size: 10px; font-weight: 700; letter-spacing: 0.08em;
      text-transform: uppercase; color: var(--purple-light); background: rgba(124,58,237,0.15);
      padding: 3px 7px; border-radius: 4px;
    }
    .sidebar-nav { padding: 16px 12px; flex: 1; }
    .nav-label {
      font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
      color: var(--dim); padding: 0 8px; margin: 16px 0 8px;
    }
    .nav-label:first-child { margin-top: 0; }
    .nav-item {
      display: flex; align-items: center; gap: 10px; padding: 9px 12px;
      border-radius: 8px; font-size: 13px; font-weight: 500; color: var(--muted);
      cursor: pointer; transition: 0.15s; margin-bottom: 2px; border: none; background: none;
      width: 100%; text-align: left;
    }
    .nav-item:hover { background: var(--surface); color: var(--text); }
    .nav-item.active { background: rgba(124,58,237,0.15); color: var(--purple-light); }
    .nav-item svg { width: 15px; height: 15px; flex-shrink: 0; opacity: 0.7; }
    .nav-item.active svg { opacity: 1; }
    .sidebar-footer {
      padding: 16px; border-top: 1px solid var(--border); display: flex; gap: 8px;
    }
    .sidebar-footer a {
      flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
      padding: 8px; border-radius: 8px; font-size: 12px; font-weight: 600;
      text-decoration: none; transition: 0.15s;
    }
    .btn-preview {
      background: var(--surface); color: var(--muted); border: 1px solid var(--border);
    }
    .btn-preview:hover { background: var(--surface-2); color: var(--text); }
    .btn-logout { color: var(--dim); }
    .btn-logout:hover { background: rgba(239,68,68,0.1); color: #fca5a5; }

    /* ---- MAIN ---- */
    .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
    .topbar {
      position: sticky; top: 0; z-index: 5;
      background: rgba(7,7,13,0.9); backdrop-filter: blur(16px);
      border-bottom: 1px solid var(--border);
      padding: 16px 32px; display: flex; align-items: center; justify-content: space-between;
    }
    .topbar-title { font-size: 15px; font-weight: 700; }
    .topbar-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }
    .topbar-actions { display: flex; align-items: center; gap: 12px; }
    .save-btn {
      display: flex; align-items: center; gap: 8px; padding: 10px 22px;
      background: var(--grad); color: #fff; border: none; border-radius: 9px;
      font-size: 14px; font-weight: 700; font-family: inherit; cursor: pointer; transition: 0.2s;
    }
    .save-btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .save-btn:disabled { opacity: 0.5; cursor: default; transform: none; }
    .save-status { font-size: 13px; font-weight: 500; }
    .save-status.saving { color: var(--muted); }
    .save-status.saved { color: var(--green); }
    .save-status.error { color: #fca5a5; }

    /* ---- CONTENT ---- */
    .content-area { padding: 32px; flex: 1; }
    .section-panel { display: none; }
    .section-panel.active { display: block; }

    /* ---- CARDS ---- */
    .edit-card {
      background: var(--bg-2); border: 1px solid var(--border);
      border-radius: 14px; padding: 28px; margin-bottom: 20px;
    }
    .edit-card-header {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border);
    }
    .edit-card-title { font-size: 14px; font-weight: 700; }
    .edit-card-badge {
      font-size: 11px; font-weight: 600; letter-spacing: 0.06em; text-transform: uppercase;
      color: var(--purple-light); background: rgba(124,58,237,0.12);
      padding: 3px 8px; border-radius: 5px;
    }
    .card-grid { display: grid; gap: 20px; }
    .card-grid-2 { grid-template-columns: 1fr 1fr; }
    .card-grid-3 { grid-template-columns: 1fr 1fr 1fr; }

    /* ---- FIELDS ---- */
    .field { margin-bottom: 16px; }
    .field:last-child { margin-bottom: 0; }
    .field-label {
      display: block; font-size: 11px; font-weight: 700; letter-spacing: 0.08em;
      text-transform: uppercase; color: var(--dim); margin-bottom: 7px;
    }
    .field-hint { font-size: 11px; color: var(--dim); margin-top: 5px; }
    input[type="text"], textarea, select {
      width: 100%; padding: 11px 14px; background: var(--surface);
      border: 1px solid var(--border-bright); border-radius: 8px;
      color: var(--text); font-size: 14px; font-family: inherit; outline: none; transition: 0.2s;
    }
    input[type="text"]:focus, textarea:focus, select:focus {
      border-color: var(--purple); background: rgba(124,58,237,0.06);
      box-shadow: 0 0 0 3px rgba(124,58,237,0.12);
    }
    textarea { resize: vertical; min-height: 80px; line-height: 1.6; }
    select option { background: var(--bg-2); }

    /* ---- REPEATER ---- */
    .repeater { border: 1px solid var(--border); border-radius: 10px; overflow: hidden; }
    .repeater-item {
      background: var(--bg-3); border-bottom: 1px solid var(--border); padding: 20px;
      position: relative;
    }
    .repeater-item:last-child { border-bottom: none; }
    .repeater-header {
      display: flex; align-items: center; gap: 10px; margin-bottom: 14px;
    }
    .repeater-handle { cursor: grab; color: var(--dim); font-size: 16px; user-select: none; }
    .repeater-title { font-size: 12px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 0.06em; }
    .repeater-remove {
      margin-left: auto; width: 26px; height: 26px; border-radius: 6px;
      background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
      color: #fca5a5; display: flex; align-items: center; justify-content: center;
      cursor: pointer; font-size: 14px; transition: 0.15s; flex-shrink: 0;
    }
    .repeater-remove:hover { background: rgba(239,68,68,0.2); }
    .add-item-btn {
      width: 100%; padding: 12px; background: var(--surface); border: 1px dashed var(--border-bright);
      border-radius: 8px; color: var(--muted); font-size: 13px; font-weight: 600;
      font-family: inherit; cursor: pointer; transition: 0.15s; margin-top: 12px;
    }
    .add-item-btn:hover { background: var(--surface-2); color: var(--text); }

    /* ---- IMAGE UPLOAD ---- */
    .image-field { display: flex; align-items: flex-start; gap: 16px; }
    .image-preview {
      width: 80px; height: 80px; border-radius: 10px; border: 1px solid var(--border-bright);
      background: var(--surface); overflow: hidden; flex-shrink: 0;
      display: flex; align-items: center; justify-content: center;
    }
    .image-preview img { width: 100%; height: 100%; object-fit: cover; }
    .image-placeholder { font-size: 24px; color: var(--dim); }
    .image-actions { flex: 1; }
    .image-url-wrap { display: flex; gap: 8px; margin-bottom: 10px; }
    .upload-btn {
      padding: 10px 16px; background: var(--surface); border: 1px solid var(--border-bright);
      border-radius: 8px; color: var(--muted); font-size: 13px; font-weight: 600;
      font-family: inherit; cursor: pointer; transition: 0.15s; white-space: nowrap; flex-shrink: 0;
    }
    .upload-btn:hover { background: var(--surface-2); color: var(--text); }
    .remove-img-btn {
      padding: 6px 12px; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2);
      border-radius: 6px; color: #fca5a5; font-size: 12px; font-weight: 600;
      font-family: inherit; cursor: pointer; transition: 0.15s; display: none;
    }
    .remove-img-btn:hover { background: rgba(239,68,68,0.2); }
    .upload-progress {
      font-size: 12px; color: var(--muted); display: none;
    }
    input[type="file"] { display: none; }

    /* ---- TOGGLE ---- */
    .toggle-wrap { display: flex; align-items: center; gap: 10px; }
    .toggle {
      position: relative; width: 40px; height: 22px; flex-shrink: 0;
    }
    .toggle input { opacity: 0; width: 0; height: 0; position: absolute; }
    .toggle-track {
      position: absolute; inset: 0; background: var(--surface-2); border-radius: 11px;
      border: 1px solid var(--border-bright); cursor: pointer; transition: 0.2s;
    }
    .toggle-track::after {
      content: ''; position: absolute; width: 16px; height: 16px; border-radius: 50%;
      background: var(--dim); top: 2px; left: 2px; transition: 0.2s;
    }
    .toggle input:checked + .toggle-track { background: rgba(124,58,237,0.3); border-color: var(--purple); }
    .toggle input:checked + .toggle-track::after { transform: translateX(18px); background: var(--purple-light); }
    .toggle-label { font-size: 13px; color: var(--muted); }

    /* ---- LIST EDITOR ---- */
    .list-editor { border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
    .list-item {
      display: flex; align-items: center; gap: 8px; padding: 8px 12px;
      border-bottom: 1px solid var(--border); background: var(--bg-3);
    }
    .list-item:last-of-type { border-bottom: none; }
    .list-item input { border: none; background: none; padding: 4px 0; font-size: 14px; flex: 1; }
    .list-item input:focus { box-shadow: none; }
    .list-item-remove {
      width: 20px; height: 20px; border-radius: 4px; background: rgba(239,68,68,0.1);
      border: none; color: #fca5a5; cursor: pointer; display: flex;
      align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0;
    }
    .add-list-btn {
      width: 100%; padding: 9px; background: var(--surface); border: none;
      color: var(--muted); font-size: 12px; font-weight: 600; font-family: inherit;
      cursor: pointer; transition: 0.15s; text-align: center; margin-top: 8px;
      border-radius: 6px; border: 1px dashed var(--border-bright);
    }
    .add-list-btn:hover { background: var(--surface-2); color: var(--text); }

    /* ---- SECTION TITLE ---- */
    .section-intro { margin-bottom: 28px; }
    .section-intro h2 { font-size: 1.2rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 6px; }
    .section-intro p { font-size: 13px; color: var(--muted); line-height: 1.5; }

    /* ---- NOTIFICATION ---- */
    .notification {
      position: fixed; bottom: 24px; right: 24px; z-index: 100;
      padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 600;
      display: flex; align-items: center; gap: 10px;
      transform: translateY(80px); opacity: 0; transition: 0.3s cubic-bezier(0.34,1.56,0.64,1);
      pointer-events: none;
    }
    .notification.show { transform: none; opacity: 1; }
    .notification.success { background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.3); color: #34d399; }
    .notification.error { background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.3); color: #fca5a5; }
  </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="logo-mark">E</div>
    <span class="logo-name">EnigmaIQ</span>
    <span class="logo-badge">CMS</span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">Sections</div>
    <button class="nav-item active" data-section="hero" onclick="showSection('hero')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/></svg>
      Hero
    </button>
    <button class="nav-item" data-section="services" onclick="showSection('services')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Services
    </button>
    <button class="nav-item" data-section="why" onclick="showSection('why')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
      Why AI
    </button>
    <button class="nav-item" data-section="process" onclick="showSection('process')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
      Process
    </button>
    <button class="nav-item" data-section="results" onclick="showSection('results')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/></svg>
      Results
    </button>
    <button class="nav-item" data-section="contact" onclick="showSection('contact')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      Contact
    </button>
    <button class="nav-item" data-section="footer" onclick="showSection('footer')">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      Footer & Meta
    </button>
  </nav>
  <div class="sidebar-footer">
    <a href="../index.php" target="_blank" class="btn-preview">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
      Preview
    </a>
    <a href="logout.php" class="btn-logout">
      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
      Sign out
    </a>
  </div>
</aside>

<!-- MAIN -->
<main class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title" id="topbarTitle">Hero Section</div>
      <div class="topbar-sub">Edit content and click Save Changes</div>
    </div>
    <div class="topbar-actions">
      <span class="save-status" id="saveStatus"></span>
      <button class="save-btn" id="saveBtn" onclick="saveContent()">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Save Changes
      </button>
    </div>
  </div>

  <div class="content-area">

    <!-- =================== HERO =================== -->
    <div class="section-panel active" id="panel-hero">
      <div class="section-intro">
        <h2>Hero Section</h2>
        <p>The first thing visitors see. Make it count.</p>
      </div>

      <div class="edit-card">
        <div class="edit-card-header">
          <div class="edit-card-title">Badge & Headline</div>
        </div>
        <div class="field">
          <label class="field-label">Badge Text</label>
          <input type="text" id="hero.badge" value="<?= htmlspecialchars($content['hero']['badge']) ?>" />
        </div>
        <div class="card-grid card-grid-2">
          <div class="field">
            <label class="field-label">Headline Line 1</label>
            <input type="text" id="hero.headline_1" value="<?= htmlspecialchars($content['hero']['headline_1']) ?>" />
          </div>
          <div class="field">
            <label class="field-label">Headline Line 2 <span style="color:var(--purple-light)">(gradient)</span></label>
            <input type="text" id="hero.headline_2" value="<?= htmlspecialchars($content['hero']['headline_2']) ?>" />
          </div>
        </div>
        <div class="field">
          <label class="field-label">Subtext</label>
          <textarea id="hero.subtext"><?= htmlspecialchars($content['hero']['subtext']) ?></textarea>
        </div>
      </div>

      <div class="edit-card">
        <div class="edit-card-header">
          <div class="edit-card-title">CTA Buttons</div>
        </div>
        <div class="card-grid card-grid-2">
          <div class="field">
            <label class="field-label">Primary Button</label>
            <input type="text" id="hero.cta_primary" value="<?= htmlspecialchars($content['hero']['cta_primary']) ?>" />
          </div>
          <div class="field">
            <label class="field-label">Secondary Button</label>
            <input type="text" id="hero.cta_secondary" value="<?= htmlspecialchars($content['hero']['cta_secondary']) ?>" />
          </div>
        </div>
      </div>

      <div class="edit-card">
        <div class="edit-card-header">
          <div class="edit-card-title">Stats Bar</div>
        </div>
        <div class="card-grid card-grid-3">
          <div>
            <div class="field"><label class="field-label">Stat 1 Number</label>
              <input type="text" id="hero.stat_1_num" value="<?= htmlspecialchars($content['hero']['stat_1_num']) ?>" /></div>
            <div class="field"><label class="field-label">Stat 1 Label</label>
              <input type="text" id="hero.stat_1_label" value="<?= htmlspecialchars($content['hero']['stat_1_label']) ?>" /></div>
          </div>
          <div>
            <div class="field"><label class="field-label">Stat 2 Number</label>
              <input type="text" id="hero.stat_2_num" value="<?= htmlspecialchars($content['hero']['stat_2_num']) ?>" /></div>
            <div class="field"><label class="field-label">Stat 2 Label</label>
              <input type="text" id="hero.stat_2_label" value="<?= htmlspecialchars($content['hero']['stat_2_label']) ?>" /></div>
          </div>
          <div>
            <div class="field"><label class="field-label">Stat 3 Number</label>
              <input type="text" id="hero.stat_3_num" value="<?= htmlspecialchars($content['hero']['stat_3_num']) ?>" /></div>
            <div class="field"><label class="field-label">Stat 3 Label</label>
              <input type="text" id="hero.stat_3_label" value="<?= htmlspecialchars($content['hero']['stat_3_label']) ?>" /></div>
          </div>
        </div>
      </div>

      <div class="edit-card">
        <div class="edit-card-header">
          <div class="edit-card-title">Background Image</div>
          <div class="edit-card-badge">Optional</div>
        </div>
        <div class="image-field">
          <div class="image-preview" id="preview-hero.bg_image">
            <?php if ($content['hero']['bg_image']): ?>
              <img src="<?= htmlspecialchars($content['hero']['bg_image']) ?>" />
            <?php else: ?>
              <span class="image-placeholder">🖼</span>
            <?php endif; ?>
          </div>
          <div class="image-actions">
            <div class="image-url-wrap">
              <input type="text" id="hero.bg_image" value="<?= htmlspecialchars($content['hero']['bg_image']) ?>" placeholder="Image URL or upload below" oninput="updateImgPreview('hero.bg_image')" />
            </div>
            <input type="file" id="file-hero.bg_image" accept="image/*" onchange="uploadImage('hero.bg_image')" />
            <button class="upload-btn" onclick="document.getElementById('file-hero.bg_image').click()">Upload Image</button>
            <button class="remove-img-btn" id="rmv-hero.bg_image" onclick="removeImage('hero.bg_image')" <?= $content['hero']['bg_image'] ? 'style="display:inline-block"' : '' ?>>Remove</button>
            <div class="upload-progress" id="prog-hero.bg_image">Uploading…</div>
            <p class="field-hint">Overlays on the hero grid background. JPG, PNG, WebP — max 5MB.</p>
          </div>
        </div>
      </div>
    </div><!-- /hero -->

    <!-- =================== SERVICES =================== -->
    <div class="section-panel" id="panel-services">
      <div class="section-intro">
        <h2>Services Section</h2>
        <p>Edit the section header and each of the 6 service cards.</p>
      </div>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Section Header</div></div>
        <div class="card-grid card-grid-2">
          <div class="field"><label class="field-label">Tag</label>
            <input type="text" id="services.tag" value="<?= htmlspecialchars($content['services']['tag']) ?>" /></div>
          <div class="field"><label class="field-label">Subtitle</label>
            <input type="text" id="services.subtitle" value="<?= htmlspecialchars($content['services']['subtitle']) ?>" /></div>
        </div>
        <div class="field"><label class="field-label">Title</label>
          <input type="text" id="services.title" value="<?= htmlspecialchars($content['services']['title']) ?>" /></div>
      </div>

      <?php foreach ($content['services']['cards'] as $i => $card): ?>
      <div class="edit-card">
        <div class="edit-card-header">
          <div class="edit-card-title">Card <?= $i + 1 ?></div>
          <?php if ($card['featured']): ?><div class="edit-card-badge">Featured</div><?php endif; ?>
        </div>
        <div class="field"><label class="field-label">Title</label>
          <input type="text" id="services.cards.<?= $i ?>.title" value="<?= htmlspecialchars($card['title']) ?>" /></div>
        <div class="field"><label class="field-label">Description</label>
          <textarea id="services.cards.<?= $i ?>.description"><?= htmlspecialchars($card['description']) ?></textarea></div>
        <div class="field">
          <label class="field-label">Bullet Points</label>
          <div class="list-editor" id="list-services-<?= $i ?>">
            <?php foreach ($card['items'] as $j => $item): ?>
            <div class="list-item">
              <input type="text" id="services.cards.<?= $i ?>.items.<?= $j ?>" value="<?= htmlspecialchars($item) ?>" />
              <button class="list-item-remove" onclick="removeListItem(this)">✕</button>
            </div>
            <?php endforeach; ?>
          </div>
          <button class="add-list-btn" onclick="addListItem('list-services-<?= $i ?>', 'services.cards.<?= $i ?>.items')">+ Add bullet</button>
        </div>
      </div>
      <?php endforeach; ?>
    </div><!-- /services -->

    <!-- =================== WHY =================== -->
    <div class="section-panel" id="panel-why">
      <div class="section-intro">
        <h2>Why AI Section</h2>
        <p>The four business case cards alongside the left column text.</p>
      </div>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Left Column</div></div>
        <div class="card-grid card-grid-2">
          <div class="field"><label class="field-label">Tag</label>
            <input type="text" id="why.tag" value="<?= htmlspecialchars($content['why']['tag']) ?>" /></div>
          <div class="field"><label class="field-label">CTA Button</label>
            <input type="text" id="why.cta_label" value="<?= htmlspecialchars($content['why']['cta_label']) ?>" /></div>
        </div>
        <div class="field"><label class="field-label">Title</label>
          <textarea id="why.title"><?= htmlspecialchars($content['why']['title']) ?></textarea></div>
        <div class="field"><label class="field-label">Subtitle</label>
          <textarea id="why.subtitle"><?= htmlspecialchars($content['why']['subtitle']) ?></textarea></div>
      </div>

      <?php foreach ($content['why']['cards'] as $i => $card): ?>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Card <?= $card['num'] ?></div></div>
        <div class="field"><label class="field-label">Title</label>
          <input type="text" id="why.cards.<?= $i ?>.title" value="<?= htmlspecialchars($card['title']) ?>" /></div>
        <div class="field"><label class="field-label">Body</label>
          <textarea id="why.cards.<?= $i ?>.body"><?= htmlspecialchars($card['body']) ?></textarea></div>
      </div>
      <?php endforeach; ?>
    </div><!-- /why -->

    <!-- =================== PROCESS =================== -->
    <div class="section-panel" id="panel-process">
      <div class="section-intro">
        <h2>Process Section</h2>
        <p>The 5-step timeline explaining how you work.</p>
      </div>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Section Header</div></div>
        <div class="card-grid card-grid-2">
          <div class="field"><label class="field-label">Tag</label>
            <input type="text" id="process.tag" value="<?= htmlspecialchars($content['process']['tag']) ?>" /></div>
          <div class="field"><label class="field-label">Subtitle</label>
            <input type="text" id="process.subtitle" value="<?= htmlspecialchars($content['process']['subtitle']) ?>" /></div>
        </div>
        <div class="field"><label class="field-label">Title</label>
          <input type="text" id="process.title" value="<?= htmlspecialchars($content['process']['title']) ?>" /></div>
      </div>

      <?php foreach ($content['process']['steps'] as $i => $step): ?>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Step <?= $step['num'] ?></div></div>
        <div class="field"><label class="field-label">Step Title</label>
          <input type="text" id="process.steps.<?= $i ?>.title" value="<?= htmlspecialchars($step['title']) ?>" /></div>
        <div class="field"><label class="field-label">Description</label>
          <textarea id="process.steps.<?= $i ?>.body"><?= htmlspecialchars($step['body']) ?></textarea></div>
      </div>
      <?php endforeach; ?>
    </div><!-- /process -->

    <!-- =================== RESULTS =================== -->
    <div class="section-panel" id="panel-results">
      <div class="section-intro">
        <h2>Results Section</h2>
        <p>Impact stats and capability checklist.</p>
      </div>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Section Header</div></div>
        <div class="card-grid card-grid-2">
          <div class="field"><label class="field-label">Tag</label>
            <input type="text" id="results.tag" value="<?= htmlspecialchars($content['results']['tag']) ?>" /></div>
          <div class="field"><label class="field-label">Title</label>
            <input type="text" id="results.title" value="<?= htmlspecialchars($content['results']['title']) ?>" /></div>
        </div>
      </div>

      <?php foreach ($content['results']['cards'] as $i => $card): ?>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Stat Card <?= $i + 1 ?></div></div>
        <div class="card-grid card-grid-2">
          <div class="field"><label class="field-label">Number / Value</label>
            <input type="text" id="results.cards.<?= $i ?>.num" value="<?= htmlspecialchars($card['num']) ?>" /></div>
          <div class="field"><label class="field-label">Label</label>
            <input type="text" id="results.cards.<?= $i ?>.label" value="<?= htmlspecialchars($card['label']) ?>" /></div>
        </div>
        <div class="field"><label class="field-label">Description</label>
          <textarea id="results.cards.<?= $i ?>.body"><?= htmlspecialchars($card['body']) ?></textarea></div>
      </div>
      <?php endforeach; ?>

      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Capability Checklist</div></div>
        <div class="list-editor" id="list-capabilities">
          <?php foreach ($content['results']['capabilities'] as $i => $cap): ?>
          <div class="list-item">
            <input type="text" id="results.capabilities.<?= $i ?>" value="<?= htmlspecialchars($cap) ?>" />
            <button class="list-item-remove" onclick="removeListItem(this)">✕</button>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="add-list-btn" onclick="addListItem('list-capabilities', 'results.capabilities')">+ Add item</button>
      </div>
    </div><!-- /results -->

    <!-- =================== CONTACT =================== -->
    <div class="section-panel" id="panel-contact">
      <div class="section-intro">
        <h2>Contact Section</h2>
        <p>CTA copy and form settings.</p>
      </div>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">CTA Copy</div></div>
        <div class="card-grid card-grid-2">
          <div class="field"><label class="field-label">Tag</label>
            <input type="text" id="contact.tag" value="<?= htmlspecialchars($content['contact']['tag']) ?>" /></div>
          <div class="field"><label class="field-label">Reply-to Email</label>
            <input type="text" id="contact.form_email" value="<?= htmlspecialchars($content['contact']['form_email']) ?>" placeholder="hello@enigmaiq.ai" /></div>
        </div>
        <div class="field"><label class="field-label">Title</label>
          <textarea id="contact.title"><?= htmlspecialchars($content['contact']['title']) ?></textarea></div>
        <div class="field"><label class="field-label">Subtitle</label>
          <textarea id="contact.subtitle"><?= htmlspecialchars($content['contact']['subtitle']) ?></textarea></div>
      </div>

      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Service Dropdown Options</div></div>
        <div class="list-editor" id="list-services-options">
          <?php foreach ($content['contact']['services_options'] as $i => $opt): ?>
          <div class="list-item">
            <input type="text" id="contact.services_options.<?= $i ?>" value="<?= htmlspecialchars($opt) ?>" />
            <button class="list-item-remove" onclick="removeListItem(this)">✕</button>
          </div>
          <?php endforeach; ?>
        </div>
        <button class="add-list-btn" onclick="addListItem('list-services-options', 'contact.services_options')">+ Add option</button>
      </div>
    </div><!-- /contact -->

    <!-- =================== FOOTER =================== -->
    <div class="section-panel" id="panel-footer">
      <div class="section-intro">
        <h2>Footer & Meta</h2>
        <p>Site metadata and footer copy.</p>
      </div>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">SEO / Meta</div></div>
        <div class="field"><label class="field-label">Page Title</label>
          <input type="text" id="meta.title" value="<?= htmlspecialchars($content['meta']['title']) ?>" /></div>
        <div class="field"><label class="field-label">Meta Description</label>
          <textarea id="meta.description"><?= htmlspecialchars($content['meta']['description']) ?></textarea></div>
      </div>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Nav Bar</div></div>
        <div class="card-grid card-grid-2">
          <div class="field"><label class="field-label">Logo Text</label>
            <input type="text" id="nav.logo_text" value="<?= htmlspecialchars($content['nav']['logo_text']) ?>" /></div>
          <div class="field"><label class="field-label">Nav CTA Button</label>
            <input type="text" id="nav.cta_label" value="<?= htmlspecialchars($content['nav']['cta_label']) ?>" /></div>
        </div>
      </div>
      <div class="edit-card">
        <div class="edit-card-header"><div class="edit-card-title">Footer</div></div>
        <div class="field"><label class="field-label">Brand Tagline</label>
          <textarea id="footer.brand_text"><?= htmlspecialchars($content['footer']['brand_text']) ?></textarea></div>
        <div class="card-grid card-grid-2">
          <div class="field"><label class="field-label">Copyright</label>
            <input type="text" id="footer.copyright" value="<?= htmlspecialchars($content['footer']['copyright']) ?>" /></div>
          <div class="field"><label class="field-label">Domain</label>
            <input type="text" id="footer.domain" value="<?= htmlspecialchars($content['footer']['domain']) ?>" /></div>
        </div>
      </div>
    </div><!-- /footer -->

  </div><!-- /content-area -->
</main>

<div class="notification" id="notification"></div>

<script>
const sectionTitles = {
  hero: 'Hero Section', services: 'Services', why: 'Why AI',
  process: 'Process', results: 'Results', contact: 'Contact', footer: 'Footer & Meta'
};

function showSection(name) {
  document.querySelectorAll('.section-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  document.getElementById('panel-' + name).classList.add('active');
  document.querySelector('[data-section="' + name + '"]').classList.add('active');
  document.getElementById('topbarTitle').textContent = sectionTitles[name];
}

// ---- Collect all field values into a nested object ----
function collectContent() {
  const data = {};
  document.querySelectorAll('[id*="."]').forEach(el => {
    if (!el.id || el.tagName === 'BUTTON') return;
    const keys = el.id.split('.');
    let cur = data;
    for (let i = 0; i < keys.length - 1; i++) {
      const k = isNaN(keys[i]) ? keys[i] : parseInt(keys[i]);
      if (cur[k] === undefined) cur[k] = isNaN(keys[i+1]) ? {} : [];
      cur = cur[k];
    }
    const last = keys[keys.length - 1];
    const key = isNaN(last) ? last : parseInt(last);
    cur[key] = el.value;
  });
  return data;
}

// Deep merge: original content + overrides from form
function deepMerge(base, overrides) {
  if (typeof base !== 'object' || base === null) return overrides ?? base;
  if (Array.isArray(base)) {
    const result = [];
    const len = Math.max(base.length, Array.isArray(overrides) ? overrides.length : 0);
    for (let i = 0; i < len; i++) {
      result[i] = overrides && overrides[i] !== undefined ? deepMerge(base[i], overrides[i]) : base[i];
    }
    return result;
  }
  const result = Object.assign({}, base);
  if (overrides && typeof overrides === 'object') {
    Object.keys(overrides).forEach(k => { result[k] = deepMerge(base[k], overrides[k]); });
  }
  return result;
}

const originalContent = <?= json_encode($content, JSON_UNESCAPED_UNICODE) ?>;

async function saveContent() {
  const btn = document.getElementById('saveBtn');
  const status = document.getElementById('saveStatus');
  btn.disabled = true;
  status.textContent = 'Saving…';
  status.className = 'save-status saving';

  const formData = collectContent();
  const merged = deepMerge(originalContent, formData);

  try {
    const res = await fetch('save.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(merged)
    });
    const json = await res.json();
    if (json.ok) {
      status.textContent = 'Saved';
      status.className = 'save-status saved';
      showNotification('Changes saved successfully', 'success');
      setTimeout(() => { status.textContent = ''; }, 3000);
    } else {
      throw new Error(json.error || 'Save failed');
    }
  } catch (e) {
    status.textContent = 'Error';
    status.className = 'save-status error';
    showNotification('Save failed: ' + e.message, 'error');
  }
  btn.disabled = false;
}

// ---- Image upload ----
function updateImgPreview(fieldId) {
  const val = document.getElementById(fieldId).value;
  const preview = document.getElementById('preview-' + fieldId);
  const rmvBtn = document.getElementById('rmv-' + fieldId);
  if (val) {
    preview.innerHTML = '<img src="' + val + '" />';
    if (rmvBtn) rmvBtn.style.display = 'inline-block';
  } else {
    preview.innerHTML = '<span class="image-placeholder">🖼</span>';
    if (rmvBtn) rmvBtn.style.display = 'none';
  }
}

function removeImage(fieldId) {
  document.getElementById(fieldId).value = '';
  updateImgPreview(fieldId);
}

async function uploadImage(fieldId) {
  const fileInput = document.getElementById('file-' + fieldId);
  const prog = document.getElementById('prog-' + fieldId);
  if (!fileInput.files[0]) return;
  prog.style.display = 'block';
  prog.textContent = 'Uploading…';
  const fd = new FormData();
  fd.append('image', fileInput.files[0]);
  try {
    const res = await fetch('upload.php', { method: 'POST', body: fd });
    const json = await res.json();
    if (json.ok) {
      document.getElementById(fieldId).value = json.url;
      updateImgPreview(fieldId);
      prog.textContent = 'Uploaded!';
      setTimeout(() => { prog.style.display = 'none'; }, 2000);
    } else {
      prog.textContent = 'Error: ' + json.error;
    }
  } catch (e) {
    prog.textContent = 'Upload failed';
  }
  fileInput.value = '';
}

// ---- List editors ----
function removeListItem(btn) {
  btn.closest('.list-item').remove();
}

function addListItem(listId, prefix) {
  const list = document.getElementById(listId);
  const items = list.querySelectorAll('.list-item');
  const idx = items.length;
  const div = document.createElement('div');
  div.className = 'list-item';
  div.innerHTML = `<input type="text" id="${prefix}.${idx}" value="" /><button class="list-item-remove" onclick="removeListItem(this)">✕</button>`;
  list.appendChild(div);
  div.querySelector('input').focus();
}

// ---- Notification ----
function showNotification(msg, type) {
  const n = document.getElementById('notification');
  n.textContent = msg;
  n.className = 'notification ' + type + ' show';
  setTimeout(() => { n.classList.remove('show'); }, 3000);
}

// Keyboard shortcut: Cmd/Ctrl+S to save
document.addEventListener('keydown', e => {
  if ((e.metaKey || e.ctrlKey) && e.key === 's') { e.preventDefault(); saveContent(); }
});
</script>
</body>
</html>
