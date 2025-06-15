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

// Get filter from URL parameter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Pagination settings
$items_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Get total users count
$total_users = $db->getTotalUsers();
$total_pages = ceil($total_users / $items_per_page);

// Get users for current page
$users = $db->getAllUsers($offset, $items_per_page);

// Calculate user role counts
$role_counts = [
    'admin' => 0,
    'customer' => 0,
    'super_admin' => 0
];

foreach ($users as $user) {
    switch($user['User_Role']) {
        case 1:
            $role_counts['admin']++;
            break;
        case 2:
            $role_counts['customer']++;
            break;
        case 3:
            $role_counts['super_admin']++;
            break;
    }
}

// Filter users based on selected role
if ($filter !== 'all') {
    $users = array_filter($users, function($user) use ($filter) {
        switch($filter) {
            case 'admin':
                return $user['User_Role'] == 1;
            case 'customer':
                return $user['User_Role'] == 2;
            case 'super_admin':
                return $user['User_Role'] == 3;
            default:
                return true;
        }
    });
}
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
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="../css/audit_log.css" rel="stylesheet">
    <link href="../css/manage_accounts.css" rel="stylesheet">
    <link href="../css/components.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Header -->
        <div class="page-header">
            <h1>Manage User Accounts</h1>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card">
                <div class="number"><?php echo $total_users; ?></div>
                <div class="label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $role_counts['admin']; ?></div>
                <div class="label">Admin Users</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $role_counts['customer']; ?></div>
                <div class="label">Customer Users</div>
            </div>
            <div class="stat-card">
                <div class="number"><?php echo $role_counts['super_admin']; ?></div>
                <div class="label">Super Admins</div>
            </div>
        </div>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="bi bi-people"></i> All Users
            </a>
            <a href="?filter=admin" class="filter-btn <?php echo $filter === 'admin' ? 'active' : ''; ?>">
                <i class="bi bi-person-badge"></i> Admins
            </a>
            <a href="?filter=customer" class="filter-btn <?php echo $filter === 'customer' ? 'active' : ''; ?>">
                <i class="bi bi-person"></i> Customers
            </a>
            <a href="?filter=super_admin" class="filter-btn <?php echo $filter === 'super_admin' ? 'active' : ''; ?>">
                <i class="bi bi-shield-check"></i> Super Admins
            </a>
        </div>

        <!-- User List -->
        <div class="user-list">
            <?php if ($users && count($users) > 0): ?>
                <?php foreach ($users as $user): ?>
                    <div class="user-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="user-info">
                                <img src="<?php echo $user['User_Photo'] ? '../uploads/' . htmlspecialchars($user['User_Photo']) : '../assets/default-avatar.png'; ?>" 
                                     alt="User Avatar" class="user-avatar">
                                <div class="user-details">
                                    <div class="user-name"><?php echo htmlspecialchars($user['User_Name']); ?></div>
                                    <div class="user-role">
                                        <?php
                                        $roleClass = '';
                                        $roleText = '';
                                        switch($user['User_Role']) {
                                            case 1:
                                                $roleClass = 'role-admin';
                                                $roleText = 'Admin';
                                                break;
                                            case 2:
                                                $roleClass = 'role-customer';
                                                $roleText = 'Customer';
                                                break;
                                            case 3:
                                                $roleClass = 'role-super-admin';
                                                $roleText = 'Super Admin';
                                                break;
                                        }
                                        ?>
                                        <span class="role-badge <?php echo $roleClass; ?>"><?php echo $roleText; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="user-date me-3">
                                    <i class="bi bi-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($user['User_CreatedAt'])); ?>
                                </div>
                                <button type="button" class="btn btn-primary btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editRoleModal<?php echo $user['UserID']; ?>">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                            </div>
                        </div>
                    </div>

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
                <div class="user-item text-center py-4">
                    <p class="mb-0">No users found</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-container">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&filter=<?php echo $filter; ?>">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    </li>
                <?php endif; ?>

                <?php
                $start_page = max(1, $page - 2);
                $end_page = min($total_pages, $page + 2);

                if ($start_page > 1) {
                    echo '<li class="page-item"><a class="page-link" href="?page=1&filter=' . $filter . '">1</a></li>';
                    if ($start_page > 2) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                }

                for ($i = $start_page; $i <= $end_page; $i++) {
                    echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '">';
                    echo '<a class="page-link" href="?page=' . $i . '&filter=' . $filter . '">' . $i . '</a>';
                    echo '</li>';
                }

                if ($end_page < $total_pages) {
                    if ($end_page < $total_pages - 1) {
                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&filter=' . $filter . '">' . $total_pages . '</a></li>';
                }
                ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&filter=<?php echo $filter; ?>">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php if ($success): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '<?php echo addslashes($success); ?>',
            confirmButtonColor: '#3085d6'
        });
    </script>
    <?php elseif ($error): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo addslashes($error); ?>',
            confirmButtonColor: '#d33'
        });
    </script>
    <?php endif; ?>
</body>
</html>
