<?php
require_once __DIR__ . '/admin/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// ---- Collect & validate ----
$name    = trim(strip_tags($_POST['name']    ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));
$company = trim(strip_tags($_POST['company'] ?? ''));
$service = trim(strip_tags($_POST['service'] ?? ''));
$message = trim(strip_tags($_POST['message'] ?? ''));

if (!$name || !$email || !$message) {
    echo json_encode(['ok' => false, 'error' => 'Name, email, and message are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Please enter a valid email address.']);
    exit;
}

// ---- Build email ----
$subject = 'New Inquiry from ' . $name . ($company ? ' — ' . $company : '');

$body  = "New contact form submission from enigmaiq.ai\r\n";
$body .= str_repeat('-', 48) . "\r\n\r\n";
$body .= "Name:    " . $name    . "\r\n";
$body .= "Email:   " . $email   . "\r\n";
if ($company) $body .= "Company: " . $company . "\r\n";
if ($service) $body .= "Looking to build: " . $service . "\r\n";
$body .= "\r\nMessage:\r\n" . $message . "\r\n";

// ---- Send ----
if (MAIL_METHOD === 'smtp_auth') {
    $sent = send_smtp_auth($subject, $body, $name, $email);
} elseif (MAIL_METHOD === 'smtp') {
    $sent = send_smtp($subject, $body, $name, $email);
} else {
    $sent = send_mail($subject, $body, $name, $email);
}

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Message could not be sent. Please try again.']);
}

// ================================================================
// PHP mail() method
// ================================================================
function send_mail($subject, $body, $reply_name, $reply_email) {
    $headers  = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>' . "\r\n";
    $headers .= 'Reply-To: ' . $reply_name . ' <' . $reply_email . '>' . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    return mail(MAIL_TO, $subject, $body, $headers);
}

// ================================================================
// SMTP with AUTH LOGIN over SSL — mail13.ezhostingserver.com:465
// ================================================================
function send_smtp_auth($subject, $body, $reply_name, $reply_email) {
    $fp = @fsockopen('ssl://' . SMTP_HOST, SMTP_PORT, $errno, $errstr, 15);
    if (!$fp) return false;

    stream_set_timeout($fp, 15);
    $host = $_SERVER['HTTP_HOST'] ?? 'enigmaiq.ai';

    $conversation = [
        null,                                       // read server greeting
        'EHLO ' . $host,                            // introduce ourselves
        'AUTH LOGIN',                               // request auth
        base64_encode(SMTP_USER),                   // username
        base64_encode(SMTP_PASS),                   // password
        'MAIL FROM:<' . MAIL_FROM . '>',
        'RCPT TO:<' . MAIL_TO . '>',
        'DATA',
        implode("\r\n", [
            'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>',
            'To: ' . MAIL_TO_NAME . ' <' . MAIL_TO . '>',
            'Reply-To: ' . $reply_name . ' <' . $reply_email . '>',
            'Subject: ' . $subject,
            'Date: ' . date('r'),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            '',
            $body,
            '.',
        ]),
        'QUIT',
    ];

    foreach ($conversation as $cmd) {
        if ($cmd !== null) {
            fwrite($fp, $cmd . "\r\n");
        }
        $response = fgets($fp, 512);
        $code = (int) substr($response, 0, 3);
        if ($cmd === 'DATA' && $code !== 354) { fclose($fp); return false; }
        elseif ($cmd !== 'DATA' && $code >= 400) { fclose($fp); return false; }
    }

    fclose($fp);
    return true;
}

// ================================================================
// SMTP method — smtpmailer.hostek.net (no authentication required)
// ================================================================
function send_smtp($subject, $body, $reply_name, $reply_email) {
    $fp = @fsockopen(SMTP_NOAUTH_HOST, SMTP_NOAUTH_PORT, $errno, $errstr, 10);
    if (!$fp) return false;

    $steps = [
        null,                                           // read greeting
        'EHLO ' . ($_SERVER['HTTP_HOST'] ?? 'enigmaiq.ai'),
        'MAIL FROM:<' . MAIL_FROM . '>',
        'RCPT TO:<' . MAIL_TO . '>',
        'DATA',
        implode("\r\n", [
            'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>',
            'To: ' . MAIL_TO_NAME . ' <' . MAIL_TO . '>',
            'Reply-To: ' . $reply_name . ' <' . $reply_email . '>',
            'Subject: ' . $subject,
            'Date: ' . date('r'),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            '',
            $body,
            '.',
        ]),
        'QUIT',
    ];

    foreach ($steps as $cmd) {
        if ($cmd !== null) {
            fwrite($fp, $cmd . "\r\n");
        }
        $response = fgets($fp, 512);
        $code = (int) substr($response, 0, 3);
        // After DATA command, server returns 354; after message body (.), returns 250
        if ($cmd === 'DATA' && $code !== 354) { fclose($fp); return false; }
        elseif ($cmd !== 'DATA' && $code >= 400) { fclose($fp); return false; }
    }

    fclose($fp);
    return true;
}
