<?php
session_start();
require_once 'includes/db.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

error_log("Session started. Current session data: " . print_r($_SESSION, true));

if (isset($_SESSION['user_id'])) {
    error_log("User already logged in. User ID: " . $_SESSION['user_id'] . ", Role: " . $_SESSION['user_role']);
    switch ($_SESSION['user_role']) {
        case 1:
        case 3:
            header('Location: admin/dashboard.php');
            break;
        case 2:
            header('Location: user/dashboard.php');
            break;
    }
    exit();
}

$db = new database();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        error_log("Login attempt - Username: " . $username);

        if (empty($username) || empty($password)) {
            $error = "Please enter both username and password";
            error_log("Login failed - Empty username or password");
        } else {
            // Test database connection first
            try {
                $testConnection = $db->opencon();
                error_log("Database connection successful");
                
                // Test a simple query to ensure database is accessible
                $testQuery = $testConnection->prepare("SELECT 1");
                $testQuery->execute();
                error_log("Database query test successful");
                
            } catch (Exception $e) {
                error_log("Database connection failed: " . $e->getMessage());
                $error = "Database connection failed. Please try again later.";
            }
            
            if (empty($error)) {
                $user = $db->loginUser($username, $password);
                
                if ($user) {
                    error_log("Login successful. Setting session variables:");
                    error_log("User ID: " . $user['UserID']);
                    error_log("Username: " . $user['User_Name']);
                    error_log("User Role: " . $user['User_Role']);
                    
                    // Regenerate session ID for security
                    session_regenerate_id(true);
                    
                    $_SESSION['user_id'] = $user['UserID'];
                    $_SESSION['username'] = $user['User_Name'];
                    $_SESSION['user_role'] = $user['User_Role'];
                    
                    error_log("Session after login: " . print_r($_SESSION, true));
                    
                    // Add audit log for successful login
                    $db->addAuditLog($user['UserID'], 'LOGIN', 'User logged in successfully');
                    
                    switch ($user['User_Role']) {
                        case 1:
                        case 3:
                            header('Location: admin/dashboard.php');
                            break;
                        case 2:
                            header('Location: user/dashboard.php');
                            break;
                        default:
                            $error = "Invalid user role";
                            break;
                    }
                    exit();
                } else {
                    $error = "Invalid username or password";
                    error_log("Login failed - Invalid credentials for username: " . $username);
                }
            }
        }
    } catch (Exception $e) {
        $error = "An error occurred during login: " . $e->getMessage();
        error_log("Login error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
     
<head>
    <meta charset="UTF-8">
    <title>Login - AgriFeeds</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f5f7;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
        }

        .card {
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 128, 0, 0.1);
            padding: 30px;
            max-width: 460px;
            width: 100%;
            background-color: #fff;
        }

        .card h3 {
            color: #1e4d2b;
            font-weight: bold;
            text-align: center;
            margin-bottom: 25px;
        }

        .form-label {
            color: #333;
            font-weight: 600;
        }

        .form-control {
            border-radius: 10px;
        }

        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .btn-primary-custom {
            background-color: #28a745;
            border: none;
            border-radius: 10px;
            padding: 10px 16px;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .btn-primary-custom:hover {
            background-color: #218838;
        }

        .alert {
            border-radius: 8px;
        }

        .text-muted small {
            display: block;
            margin-top: 15px;
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        .form-footer a {
            color: #28a745;
            text-decoration: none;
            font-weight: 500;
        }

        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
     <link href="./css/background.css" rel="stylesheet">
</head>
<body>
    <div class="card">
        <h3><i class="bi bi-box-arrow-in-right"></i> Login to Your Account</h3>

        <?php if ($error): ?>
            <div class="alert alert-danger text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary-custom">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </button>
            </div>
        </form>

        <div class="form-footer">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
        </div>
    </div>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>