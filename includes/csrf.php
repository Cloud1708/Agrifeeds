<?php
/**
 * CSRF (Cross-Site Request Forgery) protection.
 *
 * - Generates a unique, unpredictable nonce per session (CWE-330).
 * - Use csrf_field() in every state-changing form and csrf_validate() when processing POST.
 * - Optional Referer check (can break if users/proxies disable Referer).
 *
 * Note: CSRF defenses can be bypassed via XSS; keep the application free of XSS.
 * Do not use GET for any request that triggers a state change.
 */

if (!function_exists('csrf_init')) {

/**
 * Ensure session is started and CSRF token exists. Call early on any page that shows a form.
 */
function csrf_init() {
    if (session_status() === PHP_SESSION_NONE) {
        require_once __DIR__ . '/session.php';
    }
    if (empty($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
        csrf_rotate();
    }
}

/**
 * Generate a new unpredictable CSRF token and store in session.
 * Uses random_bytes for cryptographic unpredictability (CWE-330).
 */
function csrf_rotate() {
    if (session_status() === PHP_SESSION_NONE) {
        require_once __DIR__ . '/session.php';
    }
    $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf_token'];
}

/**
 * Get the current CSRF token (creates one if missing).
 * @return string
 */
function csrf_token() {
    csrf_init();
    return $_SESSION['_csrf_token'];
}

/**
 * Output a hidden input for use in HTML forms.
 * Usage: <form method="POST"><?php echo csrf_field(); ?>
 */
function csrf_field() {
    $name = '_csrf_token';
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="' . $name . '" value="' . $token . '">';
}

/**
 * Validate CSRF token for the current request.
 * Call this at the start of any handler that processes state-changing POST requests.
 *
 * @param array $options Optional:
 *   - check_referer: (bool) If true, also require Referer to be same-origin. Default false (Referer can be disabled by users/proxies).
 *   - allow_referer_missing: (bool) When check_referer is true, if Referer is missing, allow request anyway. Default true to avoid breaking legitimate users.
 * @return bool True if valid; false otherwise.
 */
function csrf_validate(array $options = []) {
    if (session_status() === PHP_SESSION_NONE) {
        require_once __DIR__ . '/session.php';
    }

    $check_referer = !empty($options['check_referer']);
    $allow_referer_missing = isset($options['allow_referer_missing']) ? (bool) $options['allow_referer_missing'] : true;

    $token = null;
    if (!empty($_POST['_csrf_token'])) {
        $token = $_POST['_csrf_token'];
    } elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (!empty($_SERVER['HTTP_X_CSRF_TOKEN_HEADER'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN_HEADER'];
    }

    if (!is_string($token) || $token === '') {
        return false;
    }

    $stored = isset($_SESSION['_csrf_token']) ? $_SESSION['_csrf_token'] : null;
    if ($stored === null || !hash_equals($stored, $token)) {
        return false;
    }

    if ($check_referer) {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if ($referer === '') {
            return $allow_referer_missing;
        }
        $base = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . ($_SERVER['HTTP_HOST'] ?? '')
            . (isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '');
        $base = rtrim($base, '/');
        if (strpos($referer, $base) !== 0) {
            return false;
        }
    }

    return true;
}

/**
 * Require valid CSRF; otherwise send 403 and exit.
 * Use for state-changing POST handlers.
 *
 * @param array $options Same as csrf_validate().
 */
function csrf_require(array $options = []) {
    if (!csrf_validate($options)) {
        if (php_sapi_name() === 'cli') {
            return;
        }
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Invalid or missing security token. Please refresh the page and try again.';
        exit;
    }
}
}
