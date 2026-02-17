<?php
require_once __DIR__ . '/csrf.php';

/**
 * Server-side input validation and sanitization.
 * Never trust client input; always validate and type-check on the server.
 * Use allow-lists (whitelist) for known-good values; reject or sanitize the rest.
 */

/**
 * Escape string for safe HTML output (XSS prevention).
 * Use for any dynamic data echoed into HTML body or attributes.
 * @param string|null $value
 * @param int $flags ENT_QUOTES to escape single and double quotes
 * @return string
 */
function h($value, $flags = ENT_QUOTES) {
    if ($value === null || !is_scalar($value)) {
        return '';
    }
    return htmlspecialchars((string) $value, $flags, 'UTF-8');
}

/**
 * Validate and return an integer within optional bounds.
 * @param mixed $value
 * @param int|null $min Minimum allowed (inclusive), or null for no minimum
 * @param int|null $max Maximum allowed (inclusive), or null for no maximum
 * @return int|null The validated integer, or null if invalid
 */
function validate_int($value, $min = null, $max = null) {
    if ($value === null || $value === '') {
        return null;
    }
    $v = filter_var($value, FILTER_VALIDATE_INT);
    if ($v === false) {
        return null;
    }
    if ($min !== null && $v < $min) {
        return null;
    }
    if ($max !== null && $v > $max) {
        return null;
    }
    return $v;
}

/**
 * Validate sort direction for SQL ORDER BY. Allow-list only ASC/DESC.
 * @param string $value
 * @return string 'ASC' or 'DESC', or 'ASC' as safe default if invalid
 */
function validate_order_dir($value) {
    $v = strtoupper(trim((string) $value));
    return ($v === 'ASC' || $v === 'DESC') ? $v : 'ASC';
}

/**
 * Validate value is one of allowed (allow-list).
 * @param mixed $value
 * @param array $allowed List of allowed scalar values
 * @param mixed $default Value to return if not in allow-list
 * @return mixed
 */
function validate_enum($value, array $allowed, $default = null) {
    if ($value === null || $value === '') {
        return $default;
    }
    return in_array($value, $allowed, true) ? $value : $default;
}

/**
 * Sanitize string for display/DB: trim, limit length, strip tags.
 * For user-facing text (names, descriptions) - allow letters, numbers, spaces, common punctuation.
 * @param string $value
 * @param int $maxLength Maximum length (default 500)
 * @return string
 */
function sanitize_string($value, $maxLength = 500) {
    if ($value === null || !is_scalar($value)) {
        return '';
    }
    $s = trim((string) $value);
    $s = strip_tags($s);
    if (mb_strlen($s) > $maxLength) {
        $s = mb_substr($s, 0, $maxLength);
    }
    return $s;
}

/**
 * Stricter sanitization: allow only alphanumeric, spaces, and a small set of safe characters.
 * Use for fields that must not contain SQL/script fragments.
 * @param string $value
 * @param int $maxLength
 * @param string $extraAllowed Regex-safe extra characters (e.g. '.,\-@')
 * @return string
 */
function sanitize_string_allowlist($value, $maxLength = 500, $extraAllowed = '') {
    $s = sanitize_string($value, $maxLength);
    // Remove any character not in allow-list: letters, digits, space, and $extraAllowed
    $pattern = '/[^\p{L}\p{N}\s' . preg_quote($extraAllowed, '/') . ']/u';
    $s = preg_replace($pattern, '', $s);
    return $s;
}

/**
 * Validate and return a positive integer ID (e.g. primary key).
 * @param mixed $value
 * @return int|null
 */
function validate_id($value) {
    return validate_int($value, 1, null);
}

/**
 * Validate and return a non-negative float (e.g. price).
 * @param mixed $value
 * @param float|null $min Minimum (inclusive), or null for 0
 * @param float|null $max Maximum (inclusive), or null for no maximum
 * @return float|null
 */
function validate_float($value, $min = 0.0, $max = null) {
    if ($value === null || $value === '') {
        return null;
    }
    $v = filter_var($value, FILTER_VALIDATE_FLOAT);
    if ($v === false) {
        return null;
    }
    if ($min !== null && $v < $min) {
        return null;
    }
    if ($max !== null && $v > $max) {
        return null;
    }
    return $v;
}

/**
 * Check if a value contains path traversal or dangerous path characters.
 * Use before using input in file paths, includes, or when accepting filenames.
 * Covers: ../, ..\, encoded variants, Unicode, double-encoding, null byte.
 * @param mixed $value Raw input (string or scalar)
 * @return bool True if value appears to contain path traversal
 */
function contains_path_traversal($value) {
    if ($value === null || !is_scalar($value)) {
        return false;
    }
    $s = (string) $value;

    // Fast checks for null bytes.
    if (strpos($s, "\0") !== false || strpos($s, "\x00") !== false) {
        return true;
    }

    // Canonicalize by repeatedly URL-decoding a small number of times.
    // This catches single + double encoding without risking an infinite loop.
    $canonical = $s;
    for ($i = 0; $i < 2; $i++) {
        $decoded = rawurldecode($canonical);
        if ($decoded === $canonical) {
            break;
        }
        $canonical = $decoded;
    }

    // Normalize directory separators for matching.
    $canonical = str_replace("\\\\", "/", $canonical);

    // Core traversal patterns. We intentionally do NOT treat any ".." as traversal
    // unless it is used as a path segment (e.g., "../" or "..\\").
    if (preg_match('#(^|/)\.\.(/|$)#', $canonical)) {
        return true;
    }

    // Any path separator is suspicious for inputs that should be plain fields
    // (names, prices, IDs). It is safer to reject at validation time.
    if (strpos($canonical, '/') !== false) {
        return true;
    }

    // Encoded slash/backslash indicators (in case canonicalization didn't fully decode).
    if (preg_match('/%2f|%5c|%252f|%255c/i', $s)) {
        return true;
    }

    // Unicode/overlong encodings for slash.
    if (preg_match('/%(?:c0%af|e0%80%af|u2216|u2215)/i', $s)) {
        return true;
    }

    return false;
}

/**
 * Validate uploaded file name for path traversal (filename only, no path components).
 * Use before using $_FILES['x']['name'] in any path construction.
 * @param string|null $filename Original filename from $_FILES['x']['name']
 * @return bool True if filename is safe (no path traversal)
 */
function is_upload_filename_safe($filename) {
    if ($filename === null || $filename === '') {
        return false;
    }
    // Reject path traversal in filename (e.g. "../../etc/passwd")
    if (contains_path_traversal($filename)) {
        return false;
    }
    // Filename must not contain path separators - use basename to get last component only
    $base = basename($filename);
    if ($base !== $filename) {
        return false; // Had path components
    }
    return true;
}

/**
 * Validate ID from POST/GET: must be a positive integer and must not contain path traversal.
 * Use for productID, customerID, etc. Rejects values like "/update_price.php" or "..\\file".
 * @param mixed $value
 * @return int|null Valid ID or null
 */
function validate_id_safe($value) {
    if (contains_path_traversal($value)) {
        return null;
    }
    return validate_id($value);
}

/**
 * Validate float from POST/GET: must be numeric and must not contain path traversal.
 * @param mixed $value
 * @param float|null $min
 * @param float|null $max
 * @return float|null
 */
function validate_float_safe($value, $min = 0.0, $max = null) {
    if (contains_path_traversal($value)) {
        return null;
    }
    return validate_float($value, $min, $max);
}

/**
 * Sanitize string for display/DB with no path separators (prevents path traversal).
 * Use for contact info, names, etc. Excludes / and \ from allowed characters.
 * @param string $value
 * @param int $maxLength
 * @param string $extraAllowed Regex-safe extra characters (must not include / or \)
 * @return string
 */
function sanitize_string_no_path_chars($value, $maxLength = 500, $extraAllowed = '.-,@():;+') {
    $s = sanitize_string($value, $maxLength);
    // Explicitly remove path separators and parent traversal
    $s = str_replace(['..', '/', '\\', "\0"], '', $s);
    $pattern = '/[^\p{L}\p{N}\s' . preg_quote($extraAllowed, '/') . ']/u';
    $s = preg_replace($pattern, '', $s);
    return $s;
}

/**
 * Validate an uploaded image using server-side content inspection.
 *
 * Why: Relying on client-provided filename/extension is not sufficient.
 * This function ensures the upload is a real image and within constraints.
 *
 * @param array $file The $_FILES['field'] array.
 * @param array $options Optional:
 *   - max_bytes (int) Maximum allowed size in bytes (default 3MB)
 *   - allowed_ext (string[]) Allowed extensions (default jpg/jpeg/png/gif)
 *   - allowed_mime (string[]) Allowed MIME types (default image/jpeg,image/png,image/gif)
 * @return array{ok:bool,message?:string,ext?:string,mime?:string}
 */
function validate_uploaded_image(array $file, array $options = []) {
    $maxBytes = isset($options['max_bytes']) ? (int) $options['max_bytes'] : (3 * 1024 * 1024);
    $allowedExt = isset($options['allowed_ext']) ? (array) $options['allowed_ext'] : ['jpg', 'jpeg', 'png', 'gif'];
    $allowedMime = isset($options['allowed_mime']) ? (array) $options['allowed_mime'] : ['image/jpeg', 'image/png', 'image/gif'];

    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'message' => 'Upload failed.'];
    }
    if (empty($file['tmp_name']) || !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'message' => 'Invalid upload.'];
    }
    if (!isset($file['size']) || (int) $file['size'] <= 0) {
        return ['ok' => false, 'message' => 'Empty upload.'];
    }
    if ((int) $file['size'] > $maxBytes) {
        return ['ok' => false, 'message' => 'File too large.'];
    }

    // Validate extension from original name (still allow-list), but do not trust it alone.
    $origName = isset($file['name']) && is_string($file['name']) ? $file['name'] : '';
    if ($origName === '' || !is_upload_filename_safe($origName)) {
        return ['ok' => false, 'message' => 'Invalid file name.'];
    }
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if ($ext === '' || !in_array($ext, $allowedExt, true)) {
        return ['ok' => false, 'message' => 'Invalid file type.'];
    }

    // MIME sniffing using server-side file inspection.
    $mime = null;
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }
    if (!is_string($mime) || $mime === '') {
        // Fallback to getimagesize which also validates image structure.
        $info = @getimagesize($file['tmp_name']);
        if ($info && isset($info['mime'])) {
            $mime = $info['mime'];
        }
    }
    if (!is_string($mime) || $mime === '' || !in_array($mime, $allowedMime, true)) {
        return ['ok' => false, 'message' => 'File content is not a valid image.'];
    }

    // Final structural check.
    if (@getimagesize($file['tmp_name']) === false) {
        return ['ok' => false, 'message' => 'Corrupt or invalid image.'];
    }

    return ['ok' => true, 'ext' => $ext, 'mime' => $mime];
}

/**
 * Generate a safe random filename for an uploaded image.
 * User-provided names are never used.
 * @param string $ext Lowercase extension without dot.
 * @return string
 */
function generate_safe_image_filename($ext) {
    $ext = strtolower((string) $ext);
    if (!preg_match('/^(jpe?g|png|gif)$/', $ext)) {
        $ext = 'jpg';
    }
    $rand = bin2hex(random_bytes(16));
    return $rand . '.' . $ext;
}

/**
 * Validate date string in Y-m-d format (no XSS, valid date only).
 * @param string|null $value
 * @return string|null The date string if valid, null otherwise
 */
function validate_date_ymd($value) {
    if ($value === null || !is_scalar($value)) {
        return null;
    }
    $s = trim((string) $value);
    if ($s === '') {
        return null;
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) {
        return null;
    }
    $dt = DateTime::createFromFormat('Y-m-d', $s);
    return ($dt && $dt->format('Y-m-d') === $s) ? $s : null;
}
