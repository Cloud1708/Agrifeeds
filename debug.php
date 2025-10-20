<?php
// Debug page for troubleshooting login issues
require_once 'config.php';

// Enable debug mode
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "<h1>AgriFeeds Debug Page</h1>";

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    require_once 'includes/db.php';
    $db = new database();
    $con = $db->opencon();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Test user table
    $stmt = $con->prepare("SELECT COUNT(*) as count FROM USER_ACCOUNTS");
    $stmt->execute();
    $result = $stmt->fetch();
    echo "<p>Total users: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
}

// Test session
echo "<h2>Session Test</h2>";
session_start();
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session data: " . print_r($_SESSION, true) . "</p>";

// Test login function
echo "<h2>Login Function Test</h2>";
if (isset($_POST['test_username']) && isset($_POST['test_password'])) {
    $username = $_POST['test_username'];
    $password = $_POST['test_password'];
    
    echo "<p>Testing login with username: " . $username . "</p>";
    
    try {
        $user = $db->loginUser($username, $password);
        if ($user) {
            echo "<p style='color: green;'>✅ Login successful!</p>";
            echo "<p>User data: " . print_r($user, true) . "</p>";
        } else {
            echo "<p style='color: red;'>❌ Login failed</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ Login error: " . $e->getMessage() . "</p>";
    }
}

// Show all users
echo "<h2>All Users in Database</h2>";
try {
    $stmt = $con->prepare("SELECT UserID, User_Name, User_Role FROM USER_ACCOUNTS");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "<p>No users found</p>";
    } else {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Username</th><th>Role</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . $user['UserID'] . "</td>";
            echo "<td>" . $user['User_Name'] . "</td>";
            echo "<td>" . $user['User_Role'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Error fetching users: " . $e->getMessage() . "</p>";
}
?>

<h2>Test Login</h2>
<form method="POST">
    <p>
        <label>Username: <input type="text" name="test_username" required></label>
    </p>
    <p>
        <label>Password: <input type="password" name="test_password" required></label>
    </p>
    <p>
        <button type="submit">Test Login</button>
    </p>
</form>

<p><a href="index.php">Back to Login</a></p>
