<?php
require_once __DIR__ . '/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (login($_POST['password'] ?? '')) {
        header('Location: index.php');
        exit;
    }
    $error = 'Incorrect password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>EnigmaIQ CMS — Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #07070d; --bg-2: #0d0d1a; --border: rgba(255,255,255,0.08);
      --border-bright: rgba(255,255,255,0.14); --surface: rgba(255,255,255,0.04);
      --text: #f0f0f8; --muted: #8888a8; --purple: #7c3aed;
      --grad: linear-gradient(135deg, #7c3aed, #2563eb);
    }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text);
      display: flex; align-items: center; justify-content: center; min-height: 100vh;
      -webkit-font-smoothing: antialiased; }
    .login-card {
      width: 100%; max-width: 400px; padding: 48px 40px;
      background: var(--bg-2); border: 1px solid var(--border);
      border-radius: 20px; box-shadow: 0 32px 80px rgba(0,0,0,0.5);
    }
    .logo { display: flex; align-items: center; gap: 10px; margin-bottom: 36px; }
    .logo-mark {
      width: 36px; height: 36px; border-radius: 9px; background: var(--grad);
      display: flex; align-items: center; justify-content: center;
      font-weight: 900; font-size: 17px; color: #fff;
    }
    .logo-text { font-weight: 800; font-size: 18px; letter-spacing: -0.03em; }
    .logo-sub { font-size: 12px; color: var(--muted); font-weight: 500; margin-left: auto; }
    h1 { font-size: 1.4rem; font-weight: 800; letter-spacing: -0.03em; margin-bottom: 8px; }
    p { font-size: 14px; color: var(--muted); margin-bottom: 32px; }
    label { display: block; font-size: 13px; font-weight: 600; color: var(--muted);
      margin-bottom: 8px; letter-spacing: 0.04em; text-transform: uppercase; }
    input[type="password"] {
      width: 100%; padding: 13px 16px; background: var(--surface);
      border: 1px solid var(--border-bright); border-radius: 10px;
      color: var(--text); font-size: 15px; font-family: inherit; outline: none;
      transition: 0.2s; margin-bottom: 20px;
    }
    input[type="password"]:focus {
      border-color: var(--purple); background: rgba(124,58,237,0.06);
      box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
    }
    .btn {
      width: 100%; padding: 14px; background: var(--grad); color: #fff;
      border: none; border-radius: 10px; font-size: 15px; font-weight: 700;
      font-family: inherit; cursor: pointer; transition: 0.2s;
    }
    .btn:hover { opacity: 0.9; transform: translateY(-1px); }
    .error {
      background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.3);
      border-radius: 8px; padding: 12px 16px; font-size: 14px; color: #fca5a5;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <div class="logo">
      <div class="logo-mark">E</div>
      <span class="logo-text">EnigmaIQ</span>
      <span class="logo-sub">CMS</span>
    </div>
    <h1>Admin Login</h1>
    <p>Enter your password to access the content manager.</p>
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="••••••••••••" autofocus required />
      <button type="submit" class="btn">Sign In</button>
    </form>
  </div>
</body>
</html>
