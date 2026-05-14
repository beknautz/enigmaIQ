<?php
// Prevent any PHP warnings from corrupting JSON output
error_reporting(0);
ini_set('display_errors', 0);

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

// ---- Send — try smtp_auth first, fall back to mail() ----
$debug = '';
$sent  = false;

try {
    if (MAIL_METHOD === 'smtp_auth') {
        $sent = send_smtp_auth($subject, $body, $name, $email, $debug);
        // If SMTP auth fails, fall back to PHP mail()
        if (!$sent) {
            $smtpDebug = $debug;
            $sent  = send_mail($subject, $body, $name, $email);
            $debug = $sent ? 'smtp_auth failed (' . $smtpDebug . '), mail() succeeded' : $smtpDebug;
        }
    } elseif (MAIL_METHOD === 'smtp') {
        $sent = send_smtp($subject, $body, $name, $email, $debug);
    } else {
        $sent = send_mail($subject, $body, $name, $email);
    }
} catch (Throwable $e) {
    $debug = $e->getMessage();
    $sent  = false;
}

if ($sent) {
    echo json_encode(['ok' => true]);
} else {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Message could not be sent. Please try again.', 'debug' => $debug]);
}

// ================================================================
// PHP mail()
// ================================================================
function send_mail($subject, $body, $reply_name, $reply_email) {
    $headers  = 'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM . '>' . "\r\n";
    $headers .= 'Reply-To: ' . $reply_name . ' <' . $reply_email . '>' . "\r\n";
    $headers .= 'X-Mailer: PHP/' . phpversion();
    return @mail(MAIL_TO, $subject, $body, $headers);
}

// ================================================================
// SMTP AUTH LOGIN over SSL — mail13.ezhostingserver.com:465
// ================================================================
function send_smtp_auth($subject, $body, $reply_name, $reply_email, &$error = '') {
    $ctx = stream_context_create([
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ],
    ]);

    $fp = @stream_socket_client(
        'ssl://' . SMTP_HOST . ':' . SMTP_PORT,
        $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx
    );

    if (!$fp) {
        $error = "Connection failed to " . SMTP_HOST . ":". SMTP_PORT . " — [$errno] $errstr";
        return false;
    }

    stream_set_timeout($fp, 10);
    $host = $_SERVER['HTTP_HOST'] ?? 'enigmaiq.ai';

    $conversation = [
        null,
        'EHLO ' . $host,
        'AUTH LOGIN',
        base64_encode(SMTP_USER),
        base64_encode(SMTP_PASS),
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
        if ($cmd === 'DATA' && $code !== 354) {
            $error = "DATA rejected: " . trim($response);
            fclose($fp); return false;
        } elseif ($cmd !== 'DATA' && $code >= 400) {
            $label = $cmd ?? 'greeting';
            $error = "Error at [" . trim($label) . "]: " . trim($response);
            fclose($fp); return false;
        }
    }

    fclose($fp);
    return true;
}

// ================================================================
// No-auth SMTP — smtpmailer.hostek.net
// ================================================================
function send_smtp($subject, $body, $reply_name, $reply_email, &$error = '') {
    $fp = @fsockopen(SMTP_NOAUTH_HOST, SMTP_NOAUTH_PORT, $errno, $errstr, 10);
    if (!$fp) {
        $error = "Connection failed: [$errno] $errstr";
        return false;
    }

    $steps = [
        null,
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
        if ($cmd === 'DATA' && $code !== 354) {
            $error = "DATA rejected: " . trim($response);
            fclose($fp); return false;
        } elseif ($cmd !== 'DATA' && $code >= 400) {
            $error = "Error: " . trim($response);
            fclose($fp); return false;
        }
    }

    fclose($fp);
    return true;
}
