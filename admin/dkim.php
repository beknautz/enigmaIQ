<?php
/**
 * Signs an email with DKIM-Signature using RSA-SHA256 (relaxed/relaxed canonicalization).
 * Returns the DKIM-Signature header line, or empty string if signing is unavailable.
 */
function dkim_sign($headers_str, $body, $domain = 'enigmaiq.ai', $selector = 'mail') {
    $privKeyFile = __DIR__ . '/keys/dkim_private.pem';
    if (!file_exists($privKeyFile)) return '';

    $privKey = openssl_pkey_get_private(file_get_contents($privKeyFile));
    if (!$privKey) return '';

    // ---- Canonicalize body (relaxed) ----
    $body = preg_replace('/\r\n/', "\n", $body);         // normalize line endings
    $body = preg_replace('/[ \t]+\n/', "\n", $body);     // strip trailing whitespace per line
    $body = rtrim($body) . "\r\n";                       // single trailing CRLF
    $bodyHash = base64_encode(hash('sha256', $body, true));

    // ---- Headers to sign ----
    $signHeaders = ['from', 'to', 'subject', 'date', 'mime-version', 'content-type'];
    $headerLines = [];
    foreach (explode("\r\n", $headers_str) as $line) {
        if ($line === '') continue;
        $name = strtolower(explode(':', $line, 2)[0]);
        if (in_array($name, $signHeaders, true)) {
            // Relaxed: lowercase name, unfold, trim value
            [$n, $v] = explode(':', $line, 2);
            $headerLines[strtolower(trim($n))] = strtolower(trim($n)) . ':' . preg_replace('/\s+/', ' ', trim($v));
        }
    }

    // ---- Build DKIM-Signature header (without b= value) ----
    $timestamp = time();
    $sigHeader  = 'v=1; a=rsa-sha256; c=relaxed/relaxed;';
    $sigHeader .= ' d=' . $domain . '; s=' . $selector . ';';
    $sigHeader .= ' t=' . $timestamp . ';';
    $sigHeader .= ' bh=' . $bodyHash . ';';
    $sigHeader .= ' h=' . implode(':', array_keys($headerLines)) . ';';
    $sigHeader .= ' b=';

    // The DKIM-Signature header itself is included in the signing input
    $headerLines['dkim-signature'] = 'dkim-signature:' . $sigHeader;

    $signingInput = implode("\r\n", array_values($headerLines));

    // ---- Sign ----
    openssl_sign($signingInput, $signature, $privKey, OPENSSL_ALGO_SHA256);
    $b = base64_encode($signature);

    return 'DKIM-Signature: ' . $sigHeader . $b;
}
