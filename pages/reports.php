<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Reports</title>
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
            <h1>Reports</h1>
            <div>
                <button class="btn btn-primary me-2" id="generateReportBtn">
                    <i class="bi bi-file-earmark-text"></i> Generate Report
                </button>
                <button class="btn btn-secondary" id="exportDataBtn">
                    <i class="bi bi-download"></i> Export Data
                </button>
            </div>
        </div>

        <!-- Report Type Selection -->
        <div class="row mb-4">
            <div class="col-md-3">
                <select class="form-select" id="reportType">
                    <option value="">Select Report Type</option>
                    <option value="sales">Sales Report</option>
                    <option value="inventory">Inventory Report</option>
                    <option value="purchases">Purchase Report</option>
                    <option value="customers">Customer Report</option>
                    <option value="suppliers">Supplier Report</option>
                    <option value="products">Product Performance</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="dateRange">
                    <option value="today">Today</option>
                    <option value="yesterday">Yesterday</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="quarter">This Quarter</option>
                    <option value="year">This Year</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
            <div class="col-md-4" id="customDateRange" style="display: none;">
                <div class="row">
                    <div class="col-md-6">
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="col-md-6">
                        <input type="date" class="form-control" id="endDate">
                    </div>
                </div>
            </div>
        </div>

        <!-- Report Content Area -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Sales</h5>
                                <p class="card-text" id="totalSales">$0.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Orders</h5>
                                <p class="card-text" id="totalOrders">0</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Average Order Value</h5>
                                <p class="card-text" id="avgOrderValue">$0.00</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card dashboard-card">
                            <div class="card-body">
                                <h5 class="card-title">Total Products Sold</h5>
                                <p class="card-text" id="totalProductsSold">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Order ID</th>
                                <th>Customer</th>
                                <th>Products</th>
                                <th>Quantity</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            <!-- Table content will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Report Filters -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Additional Filters</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label for="productFilter" class="form-label">Product</label>
                        <select class="form-select" id="productFilter">
                            <option value="">All Products</option>
                            <!-- Product options will be populated by JavaScript -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="customerFilter" class="form-label">Customer</label>
                        <select class="form-select" id="customerFilter">
                            <option value="">All Customers</option>
                            <!-- Customer options will be populated by JavaScript -->
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="statusFilter" class="form-label">Status</label>
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="paymentMethodFilter" class="form-label">Payment Method</label>
                        <select class="form-select" id="paymentMethodFilter">
                            <option value="">All Methods</option>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
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