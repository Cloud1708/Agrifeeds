<?php
session_start();
require_once '../includes/db.php';

// Check if user is logged in and is a normal user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) {
    header('Location: ../index.php');
    exit();
}

$db = new database();
$user = $db->getUserById($_SESSION['user_id']);
$success = '';
$error = '';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $photo = null;

        // Handle file upload
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png'];
            $filename = $_FILES['photo']['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = '../uploads/profile_photos/' . $new_filename;
                
                if (!is_dir('../uploads/profile_photos')) {
                    mkdir('../uploads/profile_photos', 0777, true);
                }
                
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    $photo = $upload_path;
                }
            }
        }

        if ($db->updateUserProfile($_SESSION['user_id'], $username, $photo)) {
            $_SESSION['username'] = $username;
            $success = "Profile updated successfully!";
            $user = $db->getUserById($_SESSION['user_id']); // Refresh user data
        } else {
            $error = "Failed to update profile.";
        }
    } elseif (isset($_POST['update_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long";
        } else {
            // Verify current password
            $user_data = $db->loginUser($user['User_Name'], $current_password);
            if ($user_data) {
                if ($db->updateUserPassword($_SESSION['user_id'], $new_password)) {
                    $success = "Password updated successfully!";
                } else {
                    $error = "Failed to update password.";
                }
            } else {
                $error = "Current password is incorrect";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - AgriFeeds</title>
    <link href="../bootstrap-5.3.3-dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">AgriFeeds</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">My Orders</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#profile" data-bs-toggle="tab">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-12">
                <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Profile Information</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($user['User_Name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">Profile Photo</label>
                                <?php if ($user['User_Photo']): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo htmlspecialchars($user['User_Photo']); ?>" 
                                             alt="Profile Photo" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" 
                                       name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" 
                                       name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" 
                                       name="confirm_password" required>
                            </div>
                            <button type="submit" name="update_password" class="btn btn-primary">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 