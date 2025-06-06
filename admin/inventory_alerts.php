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
                        <p class="card-text" id="lowStockCount">5</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Out of Stock</h5>
                        <p class="card-text" id="outOfStockCount">2</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Expiring Soon</h5>
                        <p class="card-text" id="expiringCount">3</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Alerts</h5>
                        <p class="card-text" id="totalAlerts">10</p>
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
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mock alerts data
        const alerts = [
            {
                product: 'Layer Feed',
                type: 'low_stock',
                currentStock: 8,
                threshold: 10,
                priority: 'high',
                lastUpdated: '2024-03-15 10:30:00'
            },
            {
                product: 'Broiler Feed',
                type: 'out_of_stock',
                currentStock: 0,
                threshold: 0,
                priority: 'high',
                lastUpdated: '2024-03-15 09:15:00'
            },
            {
                product: 'Pig Feed',
                type: 'expiring',
                currentStock: 25,
                threshold: 30,
                priority: 'medium',
                lastUpdated: '2024-03-14 16:45:00'
            },
            {
                product: 'Cattle Feed',
                type: 'low_stock',
                currentStock: 12,
                threshold: 15,
                priority: 'medium',
                lastUpdated: '2024-03-14 14:20:00'
            },
            {
                product: 'Fish Feed',
                type: 'out_of_stock',
                currentStock: 0,
                threshold: 0,
                priority: 'high',
                lastUpdated: '2024-03-14 11:30:00'
            }
        ];

        // Render alerts table
        function renderAlertsTable() {
            const tbody = document.getElementById('alertsTableBody');
            tbody.innerHTML = '';
            
            alerts.forEach(alert => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${alert.product}</td>
                    <td>
                        <span class="badge bg-${getAlertTypeBadgeClass(alert.type)}">
                            ${formatAlertType(alert.type)}
                        </span>
                    </td>
                    <td>${alert.currentStock}</td>
                    <td>${alert.threshold}</td>
                    <td>
                        <span class="badge bg-${getPriorityBadgeClass(alert.priority)}">
                            ${alert.priority.charAt(0).toUpperCase() + alert.priority.slice(1)}
                        </span>
                    </td>
                    <td>${formatDateTime(alert.lastUpdated)}</td>
                    <td>
                        <button class="btn btn-sm btn-info">
                            <i class="bi bi-eye"></i> View
                        </button>
                        <button class="btn btn-sm btn-warning">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        function getAlertTypeBadgeClass(type) {
            switch(type) {
                case 'low_stock': return 'warning';
                case 'out_of_stock': return 'danger';
                case 'expiring': return 'info';
                default: return 'secondary';
            }
        }

        function getPriorityBadgeClass(priority) {
            switch(priority) {
                case 'high': return 'danger';
                case 'medium': return 'warning';
                case 'low': return 'info';
                default: return 'secondary';
            }
        }

        function formatAlertType(type) {
            return type.split('_').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1)
            ).join(' ');
        }

        function formatDateTime(dateTimeStr) {
            const date = new Date(dateTimeStr);
            return date.toLocaleString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        // Filter functionality
        const alertSearch = document.getElementById('alertSearch');
        const alertTypeFilter = document.getElementById('alertTypeFilter');
        const priorityFilter = document.getElementById('priorityFilter');
        const tableRows = document.querySelectorAll('tbody tr');

        function filterTable() {
            const searchTerm = alertSearch.value.toLowerCase();
            const selectedType = alertTypeFilter.value;
            const selectedPriority = priorityFilter.value;

            tableRows.forEach(row => {
                const product = row.cells[0].textContent.toLowerCase();
                const type = row.cells[1].textContent.trim().toLowerCase();
                const priority = row.cells[4].textContent.trim().toLowerCase();

                const matchesSearch = product.includes(searchTerm);
                const matchesType = selectedType === 'all' || type.includes(selectedType);
                const matchesPriority = selectedPriority === 'all' || priority === selectedPriority;

                row.style.display = matchesSearch && matchesType && matchesPriority ? '' : 'none';
            });
        }

        alertSearch.addEventListener('input', filterTable);
        alertTypeFilter.addEventListener('change', filterTable);
        priorityFilter.addEventListener('change', filterTable);

        // Initial render
        renderAlertsTable();
    });
    </script>
</body>
</html> 