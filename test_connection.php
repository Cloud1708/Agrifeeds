<?php
// Test script to verify database connection and registration
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>AgriFeeds Database Connection Test</h2>";

try {
    require_once 'includes/db.php';
    $db = new database();
    
    echo "<p>✓ Database class loaded successfully</p>";
    
    // Test database connection
    $con = $db->opencon();
    echo "<p>✓ Database connection successful</p>";
    
    // Test a simple query
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM USER_ACCOUNTS");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✓ Database query successful. Total users: " . $result['count'] . "</p>";
    
    // Test username check
    $testUsername = 'test_user_' . time();
    $exists = $db->checkUsernameExists($testUsername);
    echo "<p>✓ Username check function working. Test username '$testUsername' exists: " . ($exists ? 'Yes' : 'No') . "</p>";
    
    echo "<h3>Database Connection Test: PASSED</h3>";
    echo "<p><a href='index.php'>Go to Login</a> | <a href='register.php'>Go to Registration</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<h3>Database Connection Test: FAILED</h3>";
    echo "<p>Please check your database configuration in includes/db.php</p>";
}
?>
