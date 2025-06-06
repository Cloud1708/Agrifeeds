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
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- DataTables CSS -->
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

    <!-- Main Content -->
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
                    <select class="form-select">
                        <option value="">All Products</option>
                        <option value="1">B-MEG Chicken Feed</option>
                        <option value="2">Purina Pig Feed</option>
                        <option value="3">Nutri-Mix Cattle Feed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <input type="date" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Change Type</label>
                    <select class="form-select">
                        <option value="">All Changes</option>
                        <option value="increase">Increase</option>
                        <option value="decrease">Decrease</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button class="btn btn-primary w-100">
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
                        <th>Date</th>
                        <th>Product</th>
                        <th>Quantity Change</th>
                        <th>New Stock Level</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>2024-03-15 14:30</td>
                        <td>B-MEG Chicken Feed</td>
                        <td class="quantity-change quantity-increase">+50</td>
                        <td>150</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2024-03-15 13:45</td>
                        <td>Purina Pig Feed</td>
                        <td class="quantity-change quantity-decrease">-20</td>
                        <td>80</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <td>2024-03-15 11:20</td>
                        <td>Nutri-Mix Cattle Feed</td>
                        <td class="quantity-change quantity-increase">+30</td>
                        <td>120</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </button>
                        </td>
                    </tr>
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
            $('#inventoryHistoryTable').DataTable({
                order: [[0, 'desc']],
                pageLength: 10,
                language: {
                    search: "Search records:"
                }
            });
        });
    </script>
</body>
</html> 