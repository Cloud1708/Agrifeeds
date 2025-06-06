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
                <a class="nav-link <?php echo $current_page === 'pricing_history.php' ? 'active' : ''; ?>" href="pricing_history.php">
                    <i class="bi bi-clock-history me-2"></i> Pricing History
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
            <!-- Inventory Dropdown -->
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" data-bs-toggle="collapse" href="#inventorySubmenu" role="button" 
                   aria-expanded="<?php echo in_array($current_page, ['inventory_history.php', 'inventory_alerts.php']) ? 'true' : 'false'; ?>" 
                   aria-controls="inventorySubmenu">
                    <i class="bi bi-boxes me-2"></i> Inventory
                </a>
                <div class="collapse <?php echo in_array($current_page, ['inventory_history.php', 'inventory_alerts.php']) ? 'show' : ''; ?>" id="inventorySubmenu">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'inventory_history.php' ? 'active' : ''; ?>" href="inventory_history.php">
                                <i class="bi bi-clock-history me-2"></i> History
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $current_page === 'inventory_alerts.php' ? 'active' : ''; ?>" href="inventory_alerts.php">
                                <i class="bi bi-bell me-2"></i> Alerts
                            </a>
                        </li>
                    </ul>
                </div>
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

<style>
/* Add these styles to handle the dropdown menu */
.nav-link.dropdown-toggle::after {
    float: right;
    margin-top: 8px;
}

#inventorySubmenu {
    background-color: rgba(255, 255, 255, 0.05);
    border-radius: 0.25rem;
    margin: 0.2rem 0;
}

#inventorySubmenu .nav-link {
    padding-left: 2.5rem;
    font-size: 0.9rem;
}

#inventorySubmenu .nav-link:hover {
    background-color: rgba(255, 255, 255, 0.1);
}

#inventorySubmenu .nav-link.active {
    background-color: rgba(255, 255, 255, 0.2);
}
</style> 