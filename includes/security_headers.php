<?php
/**
 * Security-related HTTP response headers.
 * Use when the web server does not set these (e.g. mod_headers disabled or PHP built-in server).
 * When using Apache with mod_headers, the root .htaccess sets the same headers.
 */

if (!function_exists('security_headers_send')) {

/**
 * Send Content-Security-Policy and related security headers.
 * Safe to call on every request; only sends if headers not yet sent.
 */
function security_headers_send() {
    if (headers_sent()) {
        return;
    }

    $csp = implode('; ', [
        "default-src 'self'",
        "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com",
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com",
        "font-src 'self' https://cdn.jsdelivr.net https://fonts.gstatic.com",
        "img-src 'self' data: blob:",
        "connect-src 'self'",
        "frame-src 'self'",
        "frame-ancestors 'self'",
        "worker-src 'self'",
        "media-src 'self'",
        "manifest-src 'self'",
        "base-uri 'self'",
        "form-action 'self'",
        "object-src 'none'",
    ]);
    header('Content-Security-Policy: ' . $csp);
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

}
