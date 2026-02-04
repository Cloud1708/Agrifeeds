<?php
 
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
$con = new database();
$sweetAlertConfig = "";

if ($_SESSION['user_role'] != 1 && $_SESSION['user_role'] != 3) {
    error_log("Invalid role " . $_SESSION['user_role'] . " - redirecting to appropriate page");
    if ($_SESSION['user_role'] == 2) {
        header('Location: ../user/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}
 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['action']) && $input['action'] === 'save_settings') {
        $_SESSION['lowStockThresholdHigh'] = (int)$input['high'];
        $_SESSION['lowStockThresholdMedium'] = (int)$input['medium'];
        $_SESSION['lowStockThresholdLow'] = (int)$input['low'];
        $_SESSION['expirationDays'] = (int)$input['expDays'];
        echo json_encode(['success' => true]);
        exit;
    }
}
 
// Fetch products from the database
$products = $con->getAllProducts(); // You need to implement this method in your db.php
 
// Use session values for thresholds, or defaults
$highThreshold = $_SESSION['lowStockThresholdHigh'] ?? 5;
$mediumThreshold = $_SESSION['lowStockThresholdMedium'] ?? 10;
$lowThreshold = $_SESSION['lowStockThresholdLow'] ?? 15;
$expiringDays = $_SESSION['expirationDays'] ?? 30;
 
// Prepare alerts array
$alerts = [];
$alertId = 1; // Initialize AlertID
foreach ($products as $product) {
    $alertType = null;
    $priority = 'low';
    $currentStock = $product['Prod_Stock'];
    $expirationDate = $product['Prod_ExpDate'] ?? null;
   
    if ($currentStock == 0) {
        $alertType = 'Out of Stock';
        $priority = 'high';
        $threshold = 0;
    }
    // Low stock logic with different thresholds and priorities
    elseif ($currentStock <= $highThreshold) {
        $alertType = 'Low Stock';
        $priority = 'high';
        $threshold = $highThreshold;
    } elseif ($currentStock <= $mediumThreshold) {
        $alertType = 'Low Stock';
        $priority = 'medium';
        $threshold = $mediumThreshold;
    } elseif ($currentStock <= $lowThreshold) {
        $alertType = 'Low Stock';
        $priority = 'low';
        $threshold = $lowThreshold;
    }
 
    if ($alertType) {
        // Save to inventory_alerts table if not already saved for this product and alert type today
        $productId = $product['ProductID'];
        $today = date('Y-m-d');
        $pdo = $con->opencon(); // Get PDO connection
 
        $checkSql = "SELECT 1 FROM inventory_alerts WHERE ProductID = ? AND IA_AlertType = ? AND IA_Threshold = ? AND DATE(IA_AlertDate) = ?";
        $stmt = $pdo->prepare($checkSql);
        $stmt->execute([$productId, $alertType, $threshold, $today]);
        if ($stmt->rowCount() === 0) {
            // Insert new alert
            $insertSql = "INSERT INTO inventory_alerts (ProductID, IA_AlertType, IA_Threshold, IA_AlertDate) VALUES (?, ?, ?, NOW())";
            $insertStmt = $pdo->prepare($insertSql);
            $insertStmt->execute([$productId, $alertType, $threshold]);
        }
 
        $alerts[] = [
            'AlertID' => $alertId++,
            'ProductID' => $product['ProductID'],
            'ProductName' => $product['Prod_Name'],
            'AlertType' => $alertType,
            'CurrentStock' => $currentStock,
            'Threshold' => $threshold,
            'Priority' => $priority,
            'AlertDate' => date('Y-m-d H:i:s'),
        ];
    }
}
?>
 
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
     <link href="../css/background.css" rel="stylesheet">
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
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Low Stock Items</h5>
                <p class="card-text" id="lowStockCount">
                    <?php echo count(array_filter($alerts, fn($a) => $a['AlertType'] === 'Low Stock')); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Out of Stock</h5>
                <p class="card-text" id="outOfStockCount">
                    <?php echo count(array_filter($alerts, fn($a) => $a['AlertType'] === 'Out of Stock')); ?>
                </p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card dashboard-card">
            <div class="card-body">
                <h5 class="card-title">Total Alerts</h5>
                <p class="card-text" id="totalAlerts">
                    <?php echo count($alerts); ?>
                </p>
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
                    <option value="Low Stock">Low Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
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
        <th>Alert ID</th>
        <th>Product Name</th>
        <th>Alert Type</th>
        <th>Current Stock</th>
        <th>Threshold</th>
        <th>Priority</th>      
        <th>Alert Date</th>
    </tr>
</thead>
<tbody id="alertsTableBody">
<?php foreach ($alerts as $alert): ?>
    <tr>
        <td><?php echo htmlspecialchars($alert['AlertID']); ?></td>
        <td>
            <?php echo htmlspecialchars($alert['ProductName']); ?>
            <span class="text-muted small">(ID: <?php echo htmlspecialchars($alert['ProductID']); ?>)</span>
        </td>
        <td><?php echo htmlspecialchars($alert['AlertType']); ?></td>
        <td><?php echo htmlspecialchars($alert['CurrentStock']); ?></td>
        <td><?php echo htmlspecialchars($alert['Threshold']); ?></td>
        <td>
            <?php
            $priority = $alert['Priority'];
            $badge = 'secondary';
            if (strtolower($priority) === 'high') $badge = 'danger';
            elseif (strtolower($priority) === 'medium') $badge = 'warning';
            elseif (strtolower($priority) === 'low') $badge = 'info';
            ?>
            <span class="badge bg-<?php echo $badge; ?>">
                <?php echo htmlspecialchars(ucfirst($priority)); ?>
            </span>
        </td>
        <td><?php echo htmlspecialchars($alert['AlertDate']); ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
            </table>
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
        <div class="col-md-4">
            <!-- Palit: Low Priority Threshold -->
            <label for="lowStockThresholdHigh" class="form-label">Low Priority Threshold</label>
            <input type="number" class="form-control" id="lowStockThresholdHigh" min="1" value="<?php echo htmlspecialchars($highThreshold); ?>" required>
        </div>
        <div class="col-md-4">
            <label for="lowStockThresholdMedium" class="form-label">Medium Priority Threshold</label>
            <input type="number" class="form-control" id="lowStockThresholdMedium" min="1" value="<?php echo htmlspecialchars($mediumThreshold); ?>" required>
        </div>
        <div class="col-md-4">
            <!-- Palit: High Priority Threshold -->
            <label for="lowStockThresholdLow" class="form-label">High Priority Threshold</label>
            <input type="number" class="form-control" id="lowStockThresholdLow" min="1" value="<?php echo htmlspecialchars($lowThreshold); ?>" required>
        </div>
    </div>
</div>
                    <div class="mb-4">
                        <h6>Expiration Alerts</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="expirationDays" class="form-label">Days Before Expiration</label>
                                <input type="number" class="form-control" id="expirationDays" min="1" value="<?php echo htmlspecialchars($expiringDays); ?>" required>
                            </div>
                        </div>
                    </div>
                    <!-- Removed Notification Settings and Alert Frequency -->
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const alertSearch = document.getElementById('alertSearch');
    const alertTypeFilter = document.getElementById('alertTypeFilter');
    const priorityFilter = document.getElementById('priorityFilter');
    const tableRows = document.querySelectorAll('#alertsTableBody tr');

    function filterTable() {
        const searchTerm = alertSearch.value.toLowerCase();
        const selectedType = alertTypeFilter.value;
        const selectedPriority = priorityFilter.value;

        tableRows.forEach(row => {
            const cells = row.getElementsByTagName('td');
            // Correct column indexes:
            // 0: Alert ID, 1: Product Name, 2: Alert Type, 3: Current Stock, 4: Threshold, 5: Priority, 6: Alert Date, 7: Actions
            const productName = cells[1].textContent.toLowerCase();
            const alertType = cells[2].textContent.trim();
            const priority = cells[5].textContent.trim().toLowerCase();

            // Search matches Product Name or Alert Type
            const matchesSearch = productName.includes(searchTerm) || alertType.toLowerCase().includes(searchTerm);

            // Filter by Alert Type
            const matchesType = selectedType === 'all' || alertType === selectedType;

            // Filter by Priority
            const matchesPriority = selectedPriority === 'all' || priority === selectedPriority;

            row.style.display = (matchesSearch && matchesType && matchesPriority) ? '' : 'none';
        });
    }

    alertSearch.addEventListener('input', filterTable);
    alertTypeFilter.addEventListener('change', filterTable);
    priorityFilter.addEventListener('change', filterTable);
});

document.getElementById('saveSettingsBtn').addEventListener('click', function() {
    const high = document.getElementById('lowStockThresholdHigh').value;
    const medium = document.getElementById('lowStockThresholdMedium').value;
    const low = document.getElementById('lowStockThresholdLow').value;
    const expDays = document.getElementById('expirationDays').value;

    Swal.fire({
        title: 'Save Alert Settings?',
        text: "Are you sure you want to save these alert settings?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, save it!',
        cancelButtonText: 'No, cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('inventory_alerts.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'save_settings',
                    high, medium, low, expDays
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Settings Saved',
                        text: 'Alert settings have been updated successfully!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed',
                        text: 'Failed to save settings.',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving settings.',
                    confirmButtonText: 'OK'
                });
            });
        } else {
            Swal.fire({
                icon: 'info',
                title: 'Cancelled',
                text: 'No changes were saved.',
                confirmButtonText: 'OK'
            });
        }
    });
});
</script>

</body>
</html>