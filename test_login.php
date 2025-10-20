<?php
// Test login functionality
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h2>Login Test</h2>";

try {
    require_once 'includes/db.php';
    
    $db = new database();
    echo "<p>✅ Database object created</p>";
    
    // Test with a sample username and password
    $test_username = "admin"; // Change this to a known username
    $test_password = "admin123"; // Change this to the correct password
    
    echo "<h3>Testing login with username: " . $test_username . "</h3>";
    
    $user = $db->loginUser($test_username, $test_password);
    
    if ($user) {
        echo "<p style='color: green;'>✅ Login successful!</p>";
        echo "<p>User ID: " . $user['UserID'] . "</p>";
        echo "<p>Username: " . $user['User_Name'] . "</p>";
        echo "<p>Role: " . $user['User_Role'] . "</p>";
        echo "<p>Password hash: " . substr($user['User_Password'], 0, 20) . "...</p>";
    } else {
        echo "<p style='color: red;'>❌ Login failed</p>";
        
        // Let's check if the user exists
        $con = $db->opencon();
        $stmt = $con->prepare("SELECT * FROM USER_ACCOUNTS WHERE User_Name = ?");
        $stmt->execute([$test_username]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user_data) {
            echo "<p>User found in database:</p>";
            echo "<p>User ID: " . $user_data['UserID'] . "</p>";
            echo "<p>Username: " . $user_data['User_Name'] . "</p>";
            echo "<p>Role: " . $user_data['User_Role'] . "</p>";
            echo "<p>Password hash: " . substr($user_data['User_Password'], 0, 20) . "...</p>";
            
            // Test password verification
            $password_valid = password_verify($test_password, $user_data['User_Password']);
            echo "<p>Password verification: " . ($password_valid ? "✅ Valid" : "❌ Invalid") . "</p>";
            
            // Check password hash info
            $password_info = password_get_info($user_data['User_Password']);
            echo "<p>Password hash info: " . print_r($password_info, true) . "</p>";
        } else {
            echo "<p>❌ User not found in database</p>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Error details: " . $e->getTraceAsString() . "</p>";
}
?>
