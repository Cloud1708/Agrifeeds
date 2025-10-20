<?php
// Production configuration for Hostinger deployment

// Database configuration
define('DB_HOST', 'mysql.hostinger.com');
define('DB_NAME', 'u689218423_agrifeeds');
define('DB_USER', 'u689218423_agrifeeds');
define('DB_PASS', '@Agrifeeds12345');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.gc_maxlifetime', 3600); // 1 hour
ini_set('session.cookie_lifetime', 3600); // 1 hour

// Error reporting (disable in production)
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Timezone
date_default_timezone_set('Asia/Manila');

// File upload settings
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
ini_set('max_execution_time', 300);
ini_set('memory_limit', '256M');
?>
