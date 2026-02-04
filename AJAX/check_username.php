<?php
require_once '../includes/db.php';
require_once '../includes/validation.php';

header('Content-Type: application/json');

if (isset($_POST['username'])) {
    // Server-side: do not trust client input; sanitize before use
    $username = sanitize_string_allowlist(trim((string) $_POST['username']), 50, '._');
    $db = new database();
    
    if ($db->checkUsernameExists($username)) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }
} else {
    echo json_encode(['error' => 'No username provided']);
}
?> 