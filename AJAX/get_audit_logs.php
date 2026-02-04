<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/validation.php';

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    die(json_encode(['error' => 'Unauthorized access']));
}

$db = new database();

// Get and validate request parameters (never trust client input)
$draw = validate_int($_POST['draw'] ?? null, 0, null) ?? 0;
$start = validate_int($_POST['start'] ?? null, 0, null) ?? 0;
$length = validate_int($_POST['length'] ?? null, 1, 100) ?? 10;
$search = isset($_POST['search']['value']) ? sanitize_string($_POST['search']['value'], 200) : '';
$order_column_raw = isset($_POST['order'][0]['column']) ? $_POST['order'][0]['column'] : 0;
$order_dir_raw = isset($_POST['order'][0]['dir']) ? $_POST['order'][0]['dir'] : 'ASC';

// Allow-list for sort column index (maps to valid column names only)
$columns = ['timestamp', 'user', 'action', 'details', 'ip_address'];
$order_column_index = validate_int($order_column_raw, 0, count($columns) - 1);
if ($order_column_index === null) {
    $order_column_index = 0;
}
$order_dir = validate_order_dir($order_dir_raw);

// Use only allow-listed column name - never concatenate user input into SQL
$order_column_name = $columns[$order_column_index];

// Build the query (no string concatenation of user input into SQL)
$query = "SELECT a.*, u.User_Name 
          FROM audit_logs a 
          LEFT JOIN user_accounts u ON a.user_id = u.UserID";

// Add search condition if search value exists (search is bound as parameter below)
if ($search !== '') {
    $query .= " WHERE a.action LIKE :search 
                OR a.details LIKE :search 
                OR u.User_Name LIKE :search 
                OR a.ip_address LIKE :search";
}

// ORDER BY uses only allow-listed column name and validated direction
$query .= " ORDER BY " . $order_column_name . " " . $order_dir;

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