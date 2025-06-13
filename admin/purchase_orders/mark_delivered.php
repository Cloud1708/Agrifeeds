<?php
require_once('../../includes/db.php');

// Set header for JSON response
header('Content-Type: application/json');

try {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['po_id'])) {
        throw new Exception('Purchase order ID is required');
    }

    // Initialize database connection
    $con = new database();
    
    // Mark the purchase order as delivered
    $result = $con->markPurchaseOrderDelivered($data['po_id']);
    
    // Return the result
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Error in mark_delivered.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_details' => $e->getMessage()
    ]);
}
?> 