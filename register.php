<?php
session_start();
require_once 'includes/db.php';

// Remove the authentication check since we want to allow new users to register
// if (!isset($_SESSION['user_id'])) {
//     header('Location: index.php');
//     exit();
// }

$db = new database();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $photo = null;

    // Validate input
    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif ($db->checkUsernameExists($username)) {
        $error = "Username already exists";
    } else {
        // Handle file upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = 'uploads/profile_photos/' . $new_filename;
                
                if (!is_dir('uploads/profile_photos')) {
                    mkdir('uploads/profile_photos', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $photo = $upload_path;
                }
            }
        }

        // Register user (role 2 for normal user)
        $userID = $db->registerUser($username, $password, 2, $photo);
        
        if ($userID) {
            $success = "Registration successful! You can now login.";
            // Redirect to login page after 2 seconds
            header('refresh:2;url=index.php');
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AgriFeeds</title>
    <link href="bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/auth.css" rel="stylesheet">
</head>
<body class="auth-bg">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="glass-container">
                    <h3 class="text-center auth-title">Register New Account</h3>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control glass-input" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control glass-input" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control glass-input" id="confirm_password" name="confirm_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Profile Photo (Optional)</label>
                            <input type="file" class="form-control glass-input" id="photo" name="photo" accept="image/*">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn glass-button">Register</button>
                        </div>
                    </form>
                    <div class="text-center mt-3">
                        <p>Already have an account? <a href="index.php" class="auth-link">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/auth.js"></script>
</body>
</html> 