<?php
session_start();
require_once 'includes/db.php';

// Handle AJAX login
if (
    isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' &&
    $_SERVER['REQUEST_METHOD'] === 'POST'
) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user = loginUser($username, $password);
    if ($user) {
        $_SESSION['UserID'] = $user['UserID'];
        $_SESSION['User_Name'] = $user['User_Name'];
        $_SESSION['User_Role'] = $user['User_Role'];
        $redirect = '';
        if ($user['User_Role'] == 1) {
            $redirect = 'pages/dashboard.php';
        } elseif ($user['User_Role'] == 2) {
            $redirect = 'pages/customer_home.php';
        } elseif ($user['User_Role'] == 3) {
            $redirect = 'pages/superadmin_home.php';
        }
        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid username or password.']);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Login</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/custom.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card">
                    <div class="card-body">
                        <h2 class="text-center mb-4">AgriFeeds</h2>
                        <div id="errorMessage" class="alert alert-danger d-none" role="alert"></div>
                        <form id="loginForm" action="" method="POST" autocomplete="off">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       aria-label="Username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       aria-label="Password" required>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Login</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="#" class="text-decoration-none">Forgot Password?</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        fetch('', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location = data.redirect;
            } else {
                const errorDiv = document.getElementById('errorMessage');
                errorDiv.textContent = data.error || 'Login failed.';
                errorDiv.classList.remove('d-none');
            }
        })
        .catch(() => {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = 'An error occurred. Please try again.';
            errorDiv.classList.remove('d-none');
        });
    });
    </script>
    <script src="js/scripts.js"></script>
</body>
</html> 