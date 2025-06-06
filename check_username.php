<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'])) {
    $username = trim($_POST['username']);
    $db = new database();
    
    $exists = $db->checkUsernameExists($username);
    
    echo json_encode(['exists' => $exists]);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
} 