<?php
session_start();
require_once('../includes/db.php');
$con = new database();
$inventoryHistory = $con->viewInventoryHistory();

if ($_SESSION['user_role'] != 1 && $_SESSION['user_role'] != 3) {
    error_log("Invalid role " . $_SESSION['user_role'] . " - redirecting to appropriate page");
    if ($_SESSION['user_role'] == 2) {
        header('Location: ../user/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Inventory History</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
     <link href="../css/background.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .filter-section {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .data-table {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .quantity-change {
            font-weight: 500;
        }
        .quantity-increase {
            color: #198754;
        }
        .quantity-decrease {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Inventory History</h1>
            <div>
                <button class="btn btn-primary">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filter-section">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Product</label>
                    <select class="form-select" id="productFilter">
                        <option value="">All Products</option>
                        <?php
                        // Dynamically populate product options
                        $productNames = [];
                        foreach ($inventoryHistory as $row) {
                            $productNames[$row['ProductID']] = $row['Prod_Name'];
                        }
                        foreach ($productNames as $pid => $pname) {
                            echo '<option value="' . htmlspecialchars($pname) . '">' . htmlspecialchars($pname) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="date" class="form-control" id="dateFilter">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Change Type</label>
                    <select class="form-select" id="changeTypeFilter">
                        <option value="">All Changes</option>
                        <option value="increase">Increase</option>
                        <option value="decrease">Decrease</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100" id="applyFiltersBtn" type="button">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div class="data-table">
            <table id="inventoryHistoryTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>IH ID</th>
                        <th>Product</th>
                        <th>Quantity Change</th>
                        <th>New Stock Level</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventoryHistory as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['IHID']); ?></td>
                        <td><?php echo htmlspecialchars($row['Prod_Name']); ?>
                            <span class="text-muted small">(ID: <?php echo $row['ProductID']; ?>)</span>
                        </td>
                        <td class="quantity-change <?php echo ($row['IH_QtyChange'] >= 0) ? 'quantity-increase' : 'quantity-decrease'; ?>">
                            <?php echo ($row['IH_QtyChange'] >= 0 ? '+' : '') . htmlspecialchars($row['IH_QtyChange']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['IH_NewStckLvl']); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($row['IH_ChangeDate'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
    $(document).ready(function() {
        var table = $('#inventoryHistoryTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                search: "Search records:"
            }
        });

        // Custom filter function
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                // Product filter
                var product = $('#productFilter').val();
                var productName = data[1].split('(ID:')[0].trim(); // Product column

                if (product && productName !== product) {
                    return false;
                }

                // Date filter
                var dateFilter = $('#dateFilter').val();
                var rowDate = data[4].split(' ')[0]; // Date column (Y-m-d)
                if (dateFilter && rowDate !== dateFilter) {
                    return false;
                }

                // Change type filter
                var changeType = $('#changeTypeFilter').val();
                var qtyChange = data[2].replace('+','');
                qtyChange = parseInt(qtyChange, 10);

                if (changeType === 'increase' && qtyChange < 0) {
                    return false;
                }
                if (changeType === 'decrease' && qtyChange >= 0) {
                    return false;
                }

                return true;
            }
        );

        // Apply filters on button click
        $('#applyFiltersBtn').on('click', function() {
            table.draw();
        });

        // Optional: auto-apply on change
        // $('#productFilter, #dateFilter, #changeTypeFilter').on('change', function() {
        //     table.draw();
        // });
    });
    </script>
</body>
</html>