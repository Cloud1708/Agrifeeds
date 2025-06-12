<?php
require_once '../includes/db.php';

header('Content-Type: application/json');

if (isset($_POST['username'])) {
    $username = trim($_POST['username']);
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