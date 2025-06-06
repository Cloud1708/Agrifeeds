<?php
session_start();
require_once 'includes/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 1: // Admin
        case 3: // SuperAdmin
            header('Location: admin/dashboard.php');
            break;
        case 2: // Normal User
            header('Location: user/dashboard.php');
            break;
    }
    exit();
}

$db = new database();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password";
    } else {
        $user = $db->loginUser($username, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user['UserID'];
            $_SESSION['username'] = $user['User_Name'];
            $_SESSION['user_role'] = $user['User_Role'];
            
            // Redirect based on role
            switch ($user['User_Role']) {
                case 1: // Admin
                case 3: // SuperAdmin
                    header('Location: admin/dashboard.php');
                    break;
                case 2: // Normal User
                    header('Location: user/dashboard.php');
                    break;
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AgriFeeds</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/auth.css" rel="stylesheet">
</head>
<body class="auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="glass-container">
                    <h3 class="text-center auth-title">Login</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control glass-input" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control glass-input" id="password" name="password" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn glass-button">Login</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p>Don't have an account? <a href="register.php" class="auth-link">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 