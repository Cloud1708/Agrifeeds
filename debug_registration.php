<?php
// Debug script for registration issues
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>AgriFeeds Registration Debug</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Registration Attempt Debug</h3>";
    
    try {
        require_once 'includes/db.php';
        $db = new database();
        
        $username = trim($_POST['username']);
        $firstName = trim($_POST['firstName']);
        $lastName = trim($_POST['lastName']);
        $countryCode = trim($_POST['countryCode']);
        $phoneNumber = trim($_POST['phoneNumber']);
        $phoneNumber = ltrim($phoneNumber, '0');
        $contactInfo = $countryCode . $phoneNumber;
        $password = $_POST['password'];
        
        echo "<p><strong>Input Data:</strong></p>";
        echo "<ul>";
        echo "<li>Username: " . htmlspecialchars($username) . "</li>";
        echo "<li>First Name: " . htmlspecialchars($firstName) . "</li>";
        echo "<li>Last Name: " . htmlspecialchars($lastName) . "</li>";
        echo "<li>Contact Info: " . htmlspecialchars($contactInfo) . "</li>";
        echo "<li>Password Length: " . strlen($password) . "</li>";
        echo "</ul>";
        
        // Test database connection
        echo "<p><strong>Database Connection Test:</strong></p>";
        $con = $db->opencon();
        echo "<p>✓ Database connection successful</p>";
        
        // Test username availability
        echo "<p><strong>Username Availability Test:</strong></p>";
        $exists = $db->checkUsernameExists($username);
        echo "<p>Username '$username' exists: " . ($exists ? 'Yes' : 'No') . "</p>";
        
        if (!$exists) {
            echo "<p><strong>Registration Test:</strong></p>";
            $userID = $db->registerUser($username, $password, 2, null, $firstName, $lastName, $contactInfo);
            
            if ($userID) {
                echo "<p style='color: green;'>✓ Registration successful! User ID: " . $userID . "</p>";
            } else {
                echo "<p style='color: red;'>✗ Registration failed</p>";
            }
        } else {
            echo "<p style='color: orange;'>⚠ Username already exists</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<form method='POST'>";
    echo "<h3>Test Registration</h3>";
    echo "<p>Username: <input type='text' name='username' value='test_user_" . time() . "' required></p>";
    echo "<p>First Name: <input type='text' name='firstName' value='Test' required></p>";
    echo "<p>Last Name: <input type='text' name='lastName' value='User' required></p>";
    echo "<p>Country Code: <select name='countryCode'><option value='+63'>+63 (PH)</option></select></p>";
    echo "<p>Phone: <input type='tel' name='phoneNumber' value='9123456789' required></p>";
    echo "<p>Password: <input type='password' name='password' value='testpass123' required></p>";
    echo "<p><input type='submit' value='Test Registration'></p>";
    echo "</form>";
}

echo "<p><a href='index.php'>Go to Login</a> | <a href='register.php'>Go to Registration</a></p>";
?>
