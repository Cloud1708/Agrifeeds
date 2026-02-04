<?php
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/db.php';
$con = new database();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get JSON data from request
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    // Store order details in session
    $_SESSION['order_details'] = $data;
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit(); 