<?php
// Test database connection for Hostinger
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h2>Database Connection Test</h2>";

try {
    require_once 'includes/db.php';
    
    echo "<p>✅ Database class loaded successfully</p>";
    
    $db = new database();
    echo "<p>✅ Database object created successfully</p>";
    
    $con = $db->opencon();
    echo "<p>✅ Database connection established successfully</p>";
    
    // Test a simple query
    $stmt = $con->prepare("SELECT COUNT(*) as user_count FROM USER_ACCOUNTS");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>✅ Query executed successfully</p>";
    echo "<p>Total users in database: " . $result['user_count'] . "</p>";
    
    // Test user table structure
    $stmt = $con->prepare("DESCRIBE USER_ACCOUNTS");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>USER_ACCOUNTS Table Structure:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>" . $column['Field'] . " - " . $column['Type'] . "</li>";
    }
    echo "</ul>";
    
    // Test if we can find any users
    $stmt = $con->prepare("SELECT UserID, User_Name, User_Role FROM USER_ACCOUNTS LIMIT 5");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Sample Users:</h3>";
    if (empty($users)) {
        echo "<p>⚠️ No users found in database</p>";
    } else {
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li>ID: " . $user['UserID'] . ", Username: " . $user['User_Name'] . ", Role: " . $user['User_Role'] . "</li>";
        }
        echo "</ul>";
    }
    
    echo "<p style='color: green; font-weight: bold;'>✅ All tests passed! Database connection is working.</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Error details: " . $e->getTraceAsString() . "</p>";
}
?>
