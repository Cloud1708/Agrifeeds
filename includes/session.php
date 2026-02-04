<?php
/**
 * Central session bootstrap: secure cookie settings and session start.
 * Include this instead of calling session_start() directly.
 * - HttpOnly: prevents JavaScript from reading the session cookie (mitigates cookie theft via XSS).
 * - SameSite=Lax: reduces CSRF from cross-site requests.
 * - Secure: cookie sent only over HTTPS when applicable.
 */
if (session_status() === PHP_SESSION_ACTIVE) {
    return;
}

$secure = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();
