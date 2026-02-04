<?php
require_once __DIR__ . '/includes/session.php';
require_once __DIR__ . '/includes/security_headers.php';
security_headers_send();
session_destroy();
header('Location: index.php');
exit();
?> 