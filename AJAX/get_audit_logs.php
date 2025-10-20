<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    die(json_encode(['error' => 'Unauthorized access']));
}

$db = new database();

// Get the request parameters
$draw = $_POST['draw'];
$start = $_POST['start'];
$length = $_POST['length'];
$search = $_POST['search']['value'];
$order_column = $_POST['order'][0]['column'];
$order_dir = $_POST['order'][0]['dir'];

// Define column names
$columns = ['timestamp', 'user', 'action', 'details', 'ip_address'];

// Build the query
$query = "SELECT a.*, u.User_Name 
          FROM audit_logs a 
          LEFT JOIN user_accounts u ON a.user_id = u.UserID";

// Add search condition if search value exists
if (!empty($search)) {
    $query .= " WHERE a.action LIKE :search 
                OR a.details LIKE :search 
                OR u.User_Name LIKE :search 
                OR a.ip_address LIKE :search";
}

// Add order by clause
$query .= " ORDER BY " . $columns[$order_column] . " " . $order_dir;

// Add limit clause
$query .= " LIMIT :start, :length";

try {
    $con = $db->opencon();
    
    // Get total records
    $total_query = "SELECT COUNT(*) FROM audit_logs";
    $total = $con->query($total_query)->fetchColumn();
    
    // Get filtered records
    $stmt = $con->prepare($query);
    if (!empty($search)) {
        $search_param = "%$search%";
        $stmt->bindParam(':search', $search_param);
    }
    $stmt->bindParam(':start', $start, PDO::PARAM_INT);
    $stmt->bindParam(':length', $length, PDO::PARAM_INT);
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the data
    $formatted_data = [];
    foreach ($data as $row) {
        $formatted_data[] = [
            'timestamp' => date('Y-m-d H:i:s', strtotime($row['timestamp'])),
            'user' => $row['User_Name'] ?? 'System',
            'action' => $row['action'],
            'details' => $row['details'],
            'ip_address' => $row['ip_address']
        ];
    }
    
    // Return the response
    echo json_encode([
        'draw' => intval($draw),
        'recordsTotal' => intval($total),
        'recordsFiltered' => intval($total),
        'data' => $formatted_data
    ]);
    
} catch (PDOException $e) {
    error_log("Error in get_audit_logs.php: " . $e->getMessage());
    echo json_encode(['error' => 'Database error occurred']);
}
?> 