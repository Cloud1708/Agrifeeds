<?php
session_start();
require_once('../../includes/db.php');
$con = new database();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    if ($_POST['status'] === 'success') {
        // Store payment success in session
        $_SESSION['payment_success'] = true;
        
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
}

header('Content-    Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']);
exit(); 