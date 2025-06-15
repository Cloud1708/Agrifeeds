<?php
require_once('../includes/db.php');
$con = new database();
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) { // 2 for customer
    header('Location: ../index.php');
    exit();
}

// Get user information
$userID = $_SESSION['user_id'];
$userInfo = $con->getUserInfo($userID);
$customerInfo = $con->getCustomerInfo($userID);

// Get user's orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$itemsPerPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$offset = ($page - 1) * $itemsPerPage;

// Get total orders count
$totalOrders = $con->getUserTotalOrders($userID);
$totalPages = ceil($totalOrders / $itemsPerPage);

// Get paginated orders
$orders = $con->getUserOrders($userID, $itemsPerPage, $offset);

// Handle order details view
$orderDetails = null;
if (isset($_GET['view'])) {
    // First check if the order belongs to the current user
    $orderId = $_GET['view'];
    $orderBelongsToUser = false;
    
    foreach ($orders as $order) {
        if ($order['OrderID'] == $orderId) {
            $orderBelongsToUser = true;
            break;
        }
    }
    
    if ($orderBelongsToUser) {
        $orderDetails = $con->getOrderDetails($orderId);
    } else {
        // If order doesn't belong to user, redirect to orders page
        header('Location: orders.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - My Orders</title>
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
                    <a class="nav-link active" href="orders.php">
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
            <h1>My Orders</h1>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchOrder" placeholder="Search orders...">
                </div>
                <select class="form-select" id="statusFilter" style="width: auto;">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <?php if ($orderDetails): ?>
        <!-- Order Details -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Order Details</h5>
                    <a href="orders.php" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Back to Orders
                    </a>
                </div>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <p><strong>Order ID:</strong> #<?php echo str_pad($orderDetails[0]['OrderID'], 6, '0', STR_PAD_LEFT); ?></p>
                        <p><strong>Date:</strong> <?php echo date('F d, Y', strtotime($orderDetails[0]['Order_Date'])); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-<?php 
                                echo match($orderDetails[0]['Order_Status']) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo ucfirst($orderDetails[0]['Order_Status']); ?>
                            </span>
                        </p>
                        <p><strong>Promotion:</strong> 
                            <?php 
                            if (!empty($orderDetails[0]['PromotionName'])) {
                                echo '<span class="badge bg-success">' . htmlspecialchars($orderDetails[0]['PromotionName']) . '</span>';
                            } else {
                                echo '<span class="badge bg-secondary">None</span>';
                            }
                            ?>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Payment Method:</strong> <?php echo $orderDetails[0]['Payment_Method_Display']; ?></p>
                        <p><strong>Total Amount:</strong> ₱<?php echo number_format($orderDetails[0]['Order_Total'], 2); ?></p>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Original Price</th>
                                <th>Discount</th>
                                <th>Final Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderDetails as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo $item['Quantity']; ?></td>
                                <td>₱<?php echo number_format($item['Price'] / (1 - ($item['discount_applied'] / 100)), 2); ?></td>
                                <td>
                                    <?php if ($item['discount_applied'] > 0): ?>
                                        <span class="badge bg-success"><?php echo $item['discount_applied']; ?>%</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>₱<?php echo number_format($item['Price'], 2); ?></td>
                                <td>₱<?php echo number_format($item['Quantity'] * $item['Price'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                <td><strong>₱<?php echo number_format($orderDetails[0]['Order_Total'], 2); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Orders Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Promotion</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['OrderID'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['Order_Date'])); ?></td>
                                <td><?php echo $order['item_count']; ?> items</td>
                                <td>₱<?php echo number_format($order['Order_Total'], 2); ?></td>
                                <td>
                                    <?php 
                                    if (!empty($order['PromotionName'])) {
                                        echo '<span class="badge bg-success">' . htmlspecialchars($order['PromotionName']) . '</span>';
                                    } else {
                                        echo '<span class="badge bg-secondary">None</span>';
                                    }
                                    ?>
                                </td>
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
                                    <a href="?view=<?php echo $order['OrderID']; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination Controls -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="d-flex align-items-center">
                        <label for="perPage" class="me-2">Items per page:</label>
                        <select class="form-select form-select-sm" id="perPage" style="width: auto;">
                            <option value="10" <?php echo $itemsPerPage == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $itemsPerPage == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="30" <?php echo $itemsPerPage == 30 ? 'selected' : ''; ?>>30</option>
                            <option value="50" <?php echo $itemsPerPage == 50 ? 'selected' : ''; ?>>50</option>
                        </select>
                    </div>
                    
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&per_page=<?php echo $itemsPerPage; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&per_page=<?php echo $itemsPerPage; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&per_page=<?php echo $itemsPerPage; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        // Search and filter functionality
        document.getElementById('searchOrder').addEventListener('input', filterOrders);
        document.getElementById('statusFilter').addEventListener('change', filterOrders);

        function filterOrders() {
            const searchTerm = document.getElementById('searchOrder').value.toLowerCase();
            const status = document.getElementById('statusFilter').value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const orderId = row.cells[0].textContent.toLowerCase();
                const orderStatus = row.cells[5].textContent.toLowerCase();
                
                const matchesSearch = orderId.includes(searchTerm);
                const matchesStatus = !status || orderStatus.includes(status);

                row.style.display = matchesSearch && matchesStatus ? '' : 'none';
            });
        }

        document.getElementById('perPage').addEventListener('change', function() {
            window.location.href = '?page=1&per_page=' + this.value;
        });
    </script>
</body>
</html> 