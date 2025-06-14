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

// Get dashboard data
$totalOrders = $con->getTotalOrders($userID);
$recentOrders = $con->getUserRecentOrders($userID, 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - User Dashboard</title>
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
                    <a class="nav-link active" href="dashboard.php">
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
                    <a class="nav-link" href="profile.php">
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
            <h1>Welcome, <?php echo htmlspecialchars($customerInfo['Cust_FN'] . ' ' . $customerInfo['Cust_LN']); ?>!</h1>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Loyalty Points</h5>
                        <p class="card-text"><?php echo number_format($customerInfo['LP_PtsBalance'] ?? 0); ?></p>
                        <small class="text-muted">Available points</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Discount Rate</h5>
                        <p class="card-text"><?php echo $customerInfo['Cust_DiscRate']; ?>%</p>
                        <small class="text-muted">Current discount</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Orders</h5>
                        <p class="card-text"><?php echo number_format($totalOrders ?? 0); ?></p>
                        <small class="text-muted">All time</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Loyalty Status</h5>
                        <p class="card-text"><?php echo htmlspecialchars($customerInfo['Cust_LoStat'] ?? 'None'); ?></p>
                        <small class="text-muted">Current tier</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Recent Orders</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recentOrders)): ?>
                                <?php foreach ($recentOrders as $order): ?>
                                <tr>
                                    <td>#<?php echo str_pad($order['SaleID'], 6, '0', STR_PAD_LEFT); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($order['Order_Date'])); ?></td>
                                    <td><?php echo $order['item_count']; ?> items</td>
                                    <td>₱<?php echo number_format($order['Order_Total'], 2); ?></td>
                                    <td>
    <?php
        if (isset($order['Order_Status'])) {
            if ($order['Order_Status'] === 'Pending') {
                echo '<span class="badge bg-warning text-dark">Pending</span>';
            } elseif ($order['Order_Status'] === 'Completed') {
                echo '<span class="badge bg-success">Completed</span>';
            } else {
                echo '<span class="badge bg-secondary">' . htmlspecialchars($order['Order_Status']) . '</span>';
            }
        } else {
            echo '<span class="badge bg-secondary">-</span>';
        }
    ?>
</td>
                                    <td>
                                        <a href="orders.php?view=<?php echo $order['SaleID']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No recent orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Quick Actions</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="products.php" class="btn btn-primary">
                        <i class="bi bi-box me-2"></i> Browse Products
                    </a>
                    <a href="profile.php" class="btn btn-info">
                        <i class="bi bi-person-circle me-2"></i> Update Profile
                    </a>
                    <a href="perks.php" class="btn btn-success">
                        <i class="bi bi-gift me-2"></i> View Perks
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 