<?php
session_start();
require_once('../includes/db.php');
require_once('../includes/validation.php');
$con = new database();
$conn = $con->opencon();

if ($_SESSION['user_role'] != 1 && $_SESSION['user_role'] != 3) {
    error_log("Invalid role " . $_SESSION['user_role'] . " - redirecting to appropriate page");
    if ($_SESSION['user_role'] == 2) {
        header('Location: ../user/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}


// Function to convert role number to name
function getRoleName($roleNumber) {
    switch ($roleNumber) {
        case 1:
            return 'Admin';
        case 2:
            return 'Customer';
        case 3:
            return 'Super Admin';
        default:
            return 'Unknown Role';
    }
}

$userID = $_SESSION['user_id'];
$userInfo = $con->getUserInfo($userID);

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_validate()) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid security token. Please refresh and try again.']);
        exit();
    }
    ob_clean(); // Clear any output buffers
    header('Content-Type: application/json');
    
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_profile':
                $username = $_POST['username'];
                $photo = null;
                
                // Handle photo upload
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../uploads/profile/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileExtension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = uniqid('profile_') . '.' . $fileExtension;
                        $uploadFile = $uploadDir . $newFileName;
                        
                        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadFile)) {
                            $photo = 'uploads/profile/' . $newFileName;
                        }
                    }
                }
                
                $result = $con->updateUserProfile($userID, $username, $photo);
                $response['success'] = $result;
                $response['message'] = $result ? 'Profile updated successfully' : 'Failed to update profile';
                break;
                
            case 'update_password':
                $currentPassword = $_POST['current_password'];
                $newPassword = $_POST['new_password'];
                $confirmPassword = $_POST['confirm_password'];
                
                if ($newPassword !== $confirmPassword) {
                    $response['success'] = false;
                    $response['message'] = 'New passwords do not match';
                    break;
                }
                
                // Verify current password
                $user = $con->loginUser($userInfo['User_Name'], $currentPassword);
                if (!$user) {
                    $response['success'] = false;
                    $response['message'] = 'Current password is incorrect';
                    break;
                }
                
                $result = $con->updateUserPassword($userID, $newPassword);
                $response['success'] = $result;
                $response['message'] = $result ? 'Password updated successfully' : 'Failed to update password';
                break;
        }
    }
    
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - AgriFeeds</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
     <link href="../css/background.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        .profile-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .profile-image-container {
            position: relative;
            display: inline-block;
        }
        .profile-image-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #fff;
            border-radius: 50%;
            padding: 5px;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="container py-4">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="profile-image-container mb-3">
                                <?php if (!empty($userInfo['User_Photo'])): ?>
                                    <img src="../<?php echo htmlspecialchars($userInfo['User_Photo']); ?>" 
                                         alt="Profile Photo" 
                                         class="profile-image">
                                <?php else: ?>
                                    <img src="../assets/images/default-profile.png" 
                                         alt="Default Profile" 
                                         class="profile-image">
                                <?php endif; ?>
                                <label for="photo" class="profile-image-upload">
                                    <i class="bi bi-camera-fill"></i>
                                </label>
                            </div>
                            <h4 class="mb-1"><?php echo htmlspecialchars($userInfo['User_Name']); ?></h4>
                            <p class="text-muted mb-3"><?php echo getRoleName($userInfo['User_Role']); ?></p>
                            <div class="d-grid">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#updateProfileModal">
                                    <i class="bi bi-pencil-square"></i> Edit Profile
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Account Information</h5>
                            <hr>
                            <div class="mb-3">
                                <label class="form-label text-muted">Username</label>
                                <p class="mb-0"><?php echo htmlspecialchars($userInfo['User_Name']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Role</label>
                                <p class="mb-0"><?php echo getRoleName($userInfo['User_Role']); ?></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Member Since</label>
                                <p class="mb-0"><?php echo date('F j, Y', strtotime($userInfo['User_CreatedAt'])); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Change Password</h5>
                            <hr>
                            <form id="changePasswordForm" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">New Password</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-key"></i> Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Profile Modal -->
    <div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="updateProfileForm" enctype="multipart/form-data" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateProfileModalLabel">Update Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($userInfo['User_Name']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="photo" class="form-label">Profile Photo</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            <small class="text-muted">Leave empty to keep current photo</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Handle profile update form submission
        document.getElementById('updateProfileForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating your profile. Please try again.',
                    confirmButtonText: 'OK'
                });
            });
        });

        // Handle password change form submission
        document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'update_password');
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: data.message,
                        confirmButtonText: 'OK'
                    }).then(() => {
                        this.reset();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while updating your password. Please try again.',
                    confirmButtonText: 'OK'
                });
            });
        });

        // Preview image before upload
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-image').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 