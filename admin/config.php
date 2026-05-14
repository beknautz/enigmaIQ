<?php
// Change this password before deploying
define('ADMIN_PASSWORD_HASH', password_hash('enigmaiq2025', PASSWORD_DEFAULT));
define('SESSION_NAME', 'eiq_admin');
define('CONTENT_FILE', __DIR__ . '/../content.json');
define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('UPLOADS_URL', '../uploads/');
define('MAX_UPLOAD_MB', 5);

// ---- Email settings ----
// Destination: where contact form submissions are sent
define('MAIL_TO',       'brent@enigmamarketing.com');
define('MAIL_TO_NAME',  'Brent');

// Method: 'smtp_auth' = authenticated SSL (recommended), 'smtp' = no-auth, 'mail' = PHP mail()
define('MAIL_METHOD',    'smtp_auth');
define('MAIL_FROM',      'mailsend@enigmaiq.ai');
define('MAIL_FROM_NAME', 'EnigmaIQ Website');

// Authenticated SMTP (SSL, port 465)
define('SMTP_HOST',     'mail13.ezhostingserver.com');
define('SMTP_PORT',     465);
define('SMTP_USER',     'mailsend@enigmaiq.ai');
define('SMTP_PASS',     'YOUR_EMAIL_PASSWORD_HERE');  // ← set this

// Fallback no-auth SMTP
define('SMTP_NOAUTH_HOST', 'smtpmailer.hostek.net');
define('SMTP_NOAUTH_PORT', 25);
