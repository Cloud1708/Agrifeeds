<?php
require_once('../includes/db.php');
$con = new database();
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get user information
$userID = $_SESSION['user_id'];
$userInfo = $con->getUserInfo($userID);
$customerInfo = $con->getCustomerInfo($userID);

$success = '';
$error = '';

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        $name = trim($_POST['name']);
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

        if ($con->updateUserProfile($userID, $username, $name, $photo)) {
            $_SESSION['username'] = $username;
            $success = "Profile updated successfully!";
            $userInfo = $con->getUserInfo($userID);
            $customerInfo = $con->getCustomerInfo($userID);
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
            $user_data = $con->loginUser($userInfo['User_Name'], $current_password);
            if ($user_data) {
                if ($con->updateUserPassword($userID, $new_password)) {
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
    <title>AgriFeeds - Profile</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php" class="sidebar-brand">AgriFeeds</a>
        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-box me-2"></i> View Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-cart me-2"></i> My Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="perks.php">
                        <i class="bi bi-gift me-2"></i> My Perks
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link active" href="profile.php">
                        <i class="bi bi-person-circle me-2"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>My Profile</h1>
        </div>

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Profile Information</h5>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo htmlspecialchars($userInfo['User_Name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($customerInfo['Cust_FN'] . ' ' . $customerInfo['Cust_LN']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="photo" class="form-label">Profile Photo</label>
                                <?php if ($userInfo['User_Photo']): ?>
                                    <div class="mb-2">
                                        <img src="<?php echo htmlspecialchars($userInfo['User_Photo']); ?>" 
                                             alt="Profile Photo" class="img-thumbnail" style="max-width: 200px;">
                                    </div>
                                <?php endif; ?>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i> Update Profile
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Change Password</h5>
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
                            <button type="submit" name="update_password" class="btn btn-primary">
                                <i class="bi bi-key me-2"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Account Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Member Since:</strong> <?php echo date('F d, Y', strtotime($userInfo['User_CreatedAt'])); ?></p>
                                <p><strong>Loyalty Status:</strong> <?php echo htmlspecialchars($customerInfo['Cust_LoStat']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Loyalty Points:</strong> <?php echo number_format($customerInfo['LP_PtsBalance']); ?></p>
                                <p><strong>Discount Rate:</strong> <?php echo number_format($customerInfo['Cust_DiscRate'], 1); ?>%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 