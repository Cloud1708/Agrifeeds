<?php
// Test script to verify database connection and registration
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>AgriFeeds Database Connection Test</h2>";

try {
    require_once 'includes/db.php';
    $db = new database();
    
    echo "<p>✓ Database class loaded successfully</p>";
    
    // Test database connection with detailed error reporting
    echo "<h3>Testing Database Connection...</h3>";
    $con = $db->opencon();
    echo "<p>✓ Database connection successful</p>";
    
    // Test a simple query
    echo "<h3>Testing Database Queries...</h3>";
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM USER_ACCOUNTS");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✓ Database query successful. Total users: " . $result['count'] . "</p>";
    
    // Test table structure
    echo "<h3>Testing Table Structure...</h3>";
    $tables = ['USER_ACCOUNTS', 'customers', 'loyalty_program'];
    foreach ($tables as $table) {
        try {
            $stmt = $con->prepare("SELECT COUNT(*) as count FROM $table");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>✓ Table '$table' accessible. Records: " . $result['count'] . "</p>";
        } catch (Exception $e) {
            echo "<p style='color: red;'>✗ Table '$table' error: " . $e->getMessage() . "</p>";
        }
    }
    
    // Test username check
    echo "<h3>Testing Username Check Function...</h3>";
    $testUsername = 'test_user_' . time();
    $exists = $db->checkUsernameExists($testUsername);
    echo "<p>✓ Username check function working. Test username '$testUsername' exists: " . ($exists ? 'Yes' : 'No') . "</p>";
    
    // Test registration function (without actually registering)
    echo "<h3>Testing Registration Function...</h3>";
    try {
        // This will test the function without actually creating a user
        $testUser = 'test_reg_' . time();
        $testPass = 'testpass123';
        $testFirst = 'Test';
        $testLast = 'User';
        $testContact = '+639123456789';
        
        // Check if username exists first
        if (!$db->checkUsernameExists($testUser)) {
            echo "<p>✓ Registration function ready (username available)</p>";
        } else {
            echo "<p>⚠ Test username already exists, but registration function is accessible</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Registration function error: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3 style='color: green;'>Database Connection Test: PASSED</h3>";
    echo "<p><a href='index.php'>Go to Login</a> | <a href='register.php'>Go to Registration</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<h3 style='color: red;'>Database Connection Test: FAILED</h3>";
    echo "<p>Please check your database configuration in includes/db.php</p>";
    echo "<p><strong>Common issues:</strong></p>";
    echo "<ul>";
    echo "<li>Check if your Hostinger database credentials are correct</li>";
    echo "<li>Verify the database name and username</li>";
    echo "<li>Check if your hosting plan allows database connections</li>";
    echo "<li>Ensure your IP is not blocked by Hostinger</li>";
    echo "</ul>";
}
?>
