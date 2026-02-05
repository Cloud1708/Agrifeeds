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
 * @param mixed $value Raw input (string or scalar)
 * @return bool True if value appears to contain path traversal
 */
function contains_path_traversal($value) {
    if ($value === null || !is_scalar($value)) {
        return false;
    }
    $s = (string) $value;
    // Directory separators and parent traversal
    if (strpos($s, '..') !== false || strpos($s, '/') !== false || strpos($s, '\\') !== false) {
        return true;
    }
    // Null byte (can bypass extension checks on some systems)
    if (strpos($s, "\0") !== false) {
        return true;
    }
    // URL-encoded variants (single decode; app should validate before double-decode)
    $decoded = rawurldecode($s);
    if (strpos($decoded, '..') !== false || strpos($decoded, '/') !== false || strpos($decoded, '\\') !== false || strpos($decoded, "\0") !== false) {
        return true;
    }
    return false;
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
