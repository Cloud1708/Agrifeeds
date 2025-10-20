<?php
// Database Connection Diagnostic Tool
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Connection Diagnostic</h2>";

// Test basic PHP PDO extension
echo "<h3>PHP Environment Check</h3>";
if (extension_loaded('pdo')) {
    echo "<p>✓ PDO extension is loaded</p>";
} else {
    echo "<p style='color: red;'>✗ PDO extension is NOT loaded</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p>✓ PDO MySQL extension is loaded</p>";
} else {
    echo "<p style='color: red;'>✗ PDO MySQL extension is NOT loaded</p>";
}

// Test connection parameters
echo "<h3>Connection Parameters</h3>";
$host = 'mysql.hostinger.com';
$dbname = 'u689218423_agrifeeds';
$username = 'u689218423_agrifeeds';
$password = '@Afrifeeds12345';

echo "<p>Host: $host</p>";
echo "<p>Database: $dbname</p>";
echo "<p>Username: $username</p>";
echo "<p>Password: " . str_repeat('*', strlen($password)) . "</p>";

// Test different connection methods
echo "<h3>Connection Method Tests</h3>";

// Method 1: Basic connection
try {
    $dsn = "mysql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✓ Basic connection successful</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Basic connection failed: " . $e->getMessage() . "</p>";
}

// Method 2: With charset
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✓ Connection with charset successful</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Connection with charset failed: " . $e->getMessage() . "</p>";
}

// Method 3: With port
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4;port=3306";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p>✓ Connection with port successful</p>";
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Connection with port failed: " . $e->getMessage() . "</p>";
}

// Test if we can connect at all
echo "<h3>Final Connection Test</h3>";
try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
    
    // Test a simple query
    $stmt = $pdo->query("SELECT 1 as test");
    $result = $stmt->fetch();
    
    if ($result['test'] == 1) {
        echo "<p style='color: green;'>✓ Database connection and query test successful!</p>";
        echo "<p><a href='index.php'>Go to Login</a> | <a href='register.php'>Go to Registration</a></p>";
    } else {
        echo "<p style='color: red;'>✗ Query test failed</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Final connection test failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Possible solutions:</strong></p>";
    echo "<ul>";
    echo "<li>Check your Hostinger database credentials</li>";
    echo "<li>Verify your hosting plan includes database access</li>";
    echo "<li>Check if your IP is whitelisted in Hostinger</li>";
    echo "<li>Contact Hostinger support if the issue persists</li>";
    echo "</ul>";
}
?>
