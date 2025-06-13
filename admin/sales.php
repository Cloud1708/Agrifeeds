<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Sales</title>
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
            <h1>Sales</h1>
            <div>
                <button class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#paymentHistoryModal">
                    <i class="bi bi-credit-card"></i> Payment History
                </button>
                <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#saleItemViewModal">
                    <i class="bi bi-eye"></i> View Sale_Item
                </button>
            </div>
        </div>
 
        <!-- Sales Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Today's Sales</h5>
                        <p class="card-text" id="todaySales">$0.00</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">This Week</h5>
                        <p class="card-text" id="weekSales">$0.00</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">This Month</h5>
                        <p class="card-text" id="monthSales">$0.00</p>
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
        </div>
 
        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="saleSearch"
                           placeholder="Search sales..." aria-label="Search sales">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="dateRangeFilter">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
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
 
        <!-- Sales Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Sale Date</th>
                        <th>Sale Method</th>
                        <th>Sale Person</th>
                        <th>Customer</th>
                        <th>Promotion</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
                    <!-- Table content will be populated by JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sale_Item View Modal -->
    <div class="modal fade" id="saleItemViewModal" tabindex="-1" aria-labelledby="saleItemViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saleItemViewModalLabel">Sale_Item Table</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Sale_Item Table -->
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>SaleItemID</th>
                                    <th>SaleID</th>
                                    <th>Product Name</th>
                                    <th>SI_Quantity</th>
                                    <th>SI_Price</th>
                                </tr>
                            </thead>
                            <tbody id="saleItemTableBody">
                                <?php
                                include_once '../includes/db.php';
                                $db = new database();
                                $con = $db->opencon();
                                $stmt = $con->prepare("SELECT si.SaleItemID, si.SaleID, p.Prod_Name AS ProductName, si.SI_Quantity, si.SI_Price FROM Sale_Item si JOIN Products p ON si.ProductID = p.ProductID");
                                $stmt->execute();
                                $saleItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($saleItems as $item) {
                                    echo "<tr>";
                                    echo "<td>{$item['SaleItemID']}</td>";
                                    echo "<td>{$item['SaleID']}</td>";
                                    echo "<td>{$item['ProductName']}</td>";
                                    echo "<td>{$item['SI_Quantity']}</td>";
                                    echo "<td>{$item['SI_Price']}</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Sale Items Modal -->
    <div class="modal fade" id="saleItemsModal" tabindex="-1" aria-labelledby="saleItemsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="saleItemsModalLabel">Sale Items</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Sale Item ID</th>
                                    <th>Product ID</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="saleItemsTableBody">
                                <!-- Sale items will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Payment History Modal -->
<div class="modal fade" id="paymentHistoryModal" tabindex="-1" aria-labelledby="paymentHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentHistoryModalLabel">Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Date Sort/Filter Inputs -->
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="paymentStartDate" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="paymentStartDate" placeholder="mm/dd/yyyy"> <!-- ![image1](image1) -->
                    </div>
                    <div class="col-md-4">
                        <label for="paymentEndDate" class="form-label">End Date</label>
                        <input type="date" class="form-control" id="paymentEndDate" placeholder="mm/dd/yyyy">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="button" class="btn btn-primary w-100" onclick="filterPaymentHistory()">Filter by Date</button>
                    </div>
                </div>

                <!-- Payment_History Table -->
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>PaytoryID</th>
                                <th>SaleID</th>
                                <th>PT_PayAmount</th>
                                <th>PT_PayDate</th>
                                <th>PT_PayMethod</th>
                            </tr>
                        </thead>
                        <tbody id="paymentHistoryTableBody">
                            <?php
                            include_once '../includes/db.php';
                            $db = new database();
                            $con = $db->opencon();
                            $stmt = $con->prepare("SELECT PaytoryID, SaleID, PT_PayAmount, PT_PayDate, PT_PayMethod FROM Payment_History ORDER BY PT_PayDate DESC");
                            $stmt->execute();
                            $paymentHistory = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($paymentHistory as $payment) {
                                echo "<tr>";
                                echo "<td>{$payment['PaytoryID']}</td>";
                                echo "<td>{$payment['SaleID']}</td>";
                                echo "<td>{$payment['PT_PayAmount']}</td>";
                                echo "<td>{$payment['PT_PayDate']}</td>";
                                echo "<td>{$payment['PT_PayMethod']}</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

 
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="../js/scripts.js"></script>
    <script>
// JS client-side filtering for Payment_History, works with the pre-rendered table above
function filterPaymentHistory() {
    var start = document.getElementById('paymentStartDate').value;
    var end = document.getElementById('paymentEndDate').value;
    var rows = document.querySelectorAll('#paymentHistoryTableBody tr');

    rows.forEach(function(row) {
        var dateCell = row.children[3];
        if (!dateCell) return;
        var dateVal = dateCell.textContent.trim();
        // Accepts 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'
        var dateOnly = dateVal.split(' ')[0];
        if (!start && !end) {
            row.style.display = '';
            return;
        }
        var show = true;
        if (start && dateOnly < start) show = false;
        if (end && dateOnly > end) show = false;
        row.style.display = show ? '' : 'none';
    });
}
</script>
</body>
</html>