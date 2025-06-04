<?php
// Get the current page name to set active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<div class="sidebar">
    <a href="dashboard.php" class="sidebar-brand">AgriFeeds</a>
    <div class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'products.php' ? 'active' : ''; ?>" href="products.php">
                    <i class="bi bi-box me-2"></i> Products
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                    <i class="bi bi-people me-2"></i> Customers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'sales.php' ? 'active' : ''; ?>" href="sales.php">
                    <i class="bi bi-cart me-2"></i> Sales
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'suppliers.php' ? 'active' : ''; ?>" href="suppliers.php">
                    <i class="bi bi-truck me-2"></i> Suppliers
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'purchase_orders.php' ? 'active' : ''; ?>" href="purchase_orders.php">
                    <i class="bi bi-file-text me-2"></i> Purchase Orders
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'promotions.php' ? 'active' : ''; ?>" href="promotions.php">
                    <i class="bi bi-gift me-2"></i> Promotions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'loyalty_program.php' ? 'active' : ''; ?>" href="loyalty_program.php">
                    <i class="bi bi-star me-2"></i> Loyalty Program
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'inventory_alerts.php' ? 'active' : ''; ?>" href="inventory_alerts.php">
                    <i class="bi bi-bell me-2"></i> Inventory Alerts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>" href="reports.php">
                    <i class="bi bi-graph-up me-2"></i> Reports
                </a>
            </li>
        </ul>
    </div>
    <div class="sidebar-footer">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" href="profile.php">
                    <i class="bi bi-person-circle me-2"></i> Profile
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div> 