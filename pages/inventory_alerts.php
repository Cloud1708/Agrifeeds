<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Inventory Alerts</title>
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
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Inventory Alerts</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#alertSettingsModal">
                <i class="bi bi-gear"></i> Alert Settings
            </button>
        </div>

        <!-- Alert Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Items</h5>
                        <p class="card-text" id="lowStockCount">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Out of Stock</h5>
                        <p class="card-text" id="outOfStockCount">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Expiring Soon</h5>
                        <p class="card-text" id="expiringCount">0</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Alerts</h5>
                        <p class="card-text" id="totalAlerts">0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="alertSearch" 
                           placeholder="Search alerts..." aria-label="Search alerts">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="alertTypeFilter">
                    <option value="all">All Types</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                    <option value="expiring">Expiring Soon</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="priorityFilter">
                    <option value="all">All Priorities</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </div>

        <!-- Alerts Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Alert Type</th>
                        <th>Current Stock</th>
                        <th>Threshold</th>
                        <th>Priority</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="alertsTableBody">
                    <!-- Table content will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Alert Settings Modal -->
    <div class="modal fade" id="alertSettingsModal" tabindex="-1" aria-labelledby="alertSettingsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertSettingsModalLabel">Alert Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="alertSettingsForm">
                        <div class="mb-4">
                            <h6>Low Stock Thresholds</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="lowStockThreshold" class="form-label">Low Stock Alert</label>
                                    <input type="number" class="form-control" id="lowStockThreshold" 
                                           min="1" value="10" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="outOfStockThreshold" class="form-label">Out of Stock Alert</label>
                                    <input type="number" class="form-control" id="outOfStockThreshold" 
                                           min="0" value="0" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Expiration Alerts</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="expirationDays" class="form-label">Days Before Expiration</label>
                                    <input type="number" class="form-control" id="expirationDays" 
                                           min="1" value="30" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Notification Settings</h6>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label" for="emailNotifications">
                                    Email Notifications
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="smsNotifications">
                                <label class="form-check-label" for="smsNotifications">
                                    SMS Notifications
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="dashboardNotifications" checked>
                                <label class="form-check-label" for="dashboardNotifications">
                                    Dashboard Notifications
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6>Alert Frequency</h6>
                            <select class="form-select" id="alertFrequency">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveSettingsBtn">Save Settings</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/scripts.js"></script>
</body>
</html> 