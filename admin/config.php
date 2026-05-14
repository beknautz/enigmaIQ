<?php
// Change this password before deploying
define('ADMIN_PASSWORD_HASH', password_hash('enigmaiq2025', PASSWORD_DEFAULT));
define('SESSION_NAME', 'eiq_admin');
define('CONTENT_FILE', __DIR__ . '/../content.json');
define('UPLOADS_DIR', __DIR__ . '/../uploads/');
define('UPLOADS_URL', '../uploads/');
define('MAX_UPLOAD_MB', 5);
