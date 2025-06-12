<?php
require_once('../includes/db.php');
session_start();

// Check if user is logged in and is a super admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 3) {
    header('Location: ../index.php');
    exit();
}

$db = new database();
$success = '';
$error = '';

// Handle user role, profile pic, and password updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $userID = $_POST['user_id'];
    $newRole = $_POST['new_role'];
    $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $photo = null;
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $filename = 'profile_' . $userID . '_' . time() . '.' . $ext;
        $target = '../uploads/' . $filename;
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target)) {
            $photo = $filename;
        }
    }
    // Update role
    $roleUpdated = $db->updateUserRole($userID, $newRole);
    // Update password if provided
    $passwordUpdated = true;
    if ($newPassword !== '') {
        $passwordUpdated = $db->updateUserPassword($userID, $newPassword);
    }
    // Update profile picture if uploaded
    $photoUpdated = true;
    if ($photo) {
        $photoUpdated = $db->updateUserProfile($userID, null, $photo);
    }
    if ($roleUpdated && $passwordUpdated && $photoUpdated) {
        $success = "User updated successfully!";
    } else {
        $error = "Failed to update user.";
    }
}

// Fetch all users (no pagination)
$users = $db->getAllUsers();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage User Accounts - AgriFeeds</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Manage User Accounts</h1>
        </div>

        <!-- SweetAlert2 Success/Error -->
        <?php if ($success): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: '<?php echo addslashes($success); ?>',
                        confirmButtonColor: '#3085d6'
                    });
                });
            </script>
        <?php elseif ($error): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: '<?php echo addslashes($error); ?>',
                        confirmButtonColor: '#d33'
                    });
                });
            </script>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($users && count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                                        <td><?php echo htmlspecialchars($user['User_Name']); ?></td>
                                        <td>
                                            <?php
                                            switch($user['User_Role']) {
                                                case 1:
                                                    echo 'Admin';
                                                    break;
                                                case 2:
                                                    echo 'Customer';
                                                    break;
                                                case 3:
                                                    echo 'Super Admin';
                                                    break;
                                                default:
                                                    echo 'Unknown';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($user['User_CreatedAt'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editRoleModal<?php echo $user['UserID']; ?>">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Role Modal -->
                                    <div class="modal fade" id="editRoleModal<?php echo $user['UserID']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit User</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="" enctype="multipart/form-data">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['UserID']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Username</label>
                                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['User_Name']); ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Current Role</label>
                                                            <input type="text" class="form-control" value="<?php 
                                                                switch($user['User_Role']) {
                                                                    case 1: echo 'Admin'; break;
                                                                    case 2: echo 'Customer'; break;
                                                                    case 3: echo 'Super Admin'; break;
                                                                    default: echo 'Unknown';
                                                                }
                                                            ?>" readonly>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">New Role</label>
                                                            <select name="new_role" class="form-select" required>
                                                                <option value="1" <?php echo $user['User_Role'] == 1 ? 'selected' : ''; ?>>Admin</option>
                                                                <option value="2" <?php echo $user['User_Role'] == 2 ? 'selected' : ''; ?>>Customer</option>
                                                                <option value="3" <?php echo $user['User_Role'] == 3 ? 'selected' : ''; ?>>Super Admin</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">New Profile Picture</label>
                                                            <input type="file" name="profile_pic" class="form-control">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">New Password</label>
                                                            <input type="password" name="new_password" class="form-control" placeholder="Leave blank to keep unchanged">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" name="update_role" class="btn btn-primary">Update User</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
