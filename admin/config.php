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
define('MAIL_TO',       'hello@enigmaiq.ai');
define('MAIL_TO_NAME',  'EnigmaIQ');

// Method: 'mail' uses PHP mail(), 'smtp' uses smtpmailer.hostek.net (no auth)
define('MAIL_METHOD',   'smtp');
define('SMTP_HOST',     'smtpmailer.hostek.net');
define('SMTP_PORT',     25);
define('MAIL_FROM',     'hello@enigmaiq.ai');
define('MAIL_FROM_NAME','EnigmaIQ Website');
