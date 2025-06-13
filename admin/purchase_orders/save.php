<?php
require_once('../../includes/db.php');

// Set header for JSON response
header('Content-Type: application/json');

try {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data) {
        throw new Exception('Invalid JSON data received');
    }

    // Initialize database connection
    $con = new database();
    
    // Save the purchase order
    $result = $con->savePurchaseOrder($data);
    
    // Return the result
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Error in save.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $e->getMessage()
    ]);
}
?> 