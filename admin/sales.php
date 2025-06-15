<?php


session_start();

require_once('../includes/db.php');
$con = new database();
$conn = $con->opencon();
$sweetAlertConfig = '';

// --- SALES SUMMARY QUERIES ---
$todaySales = 0;
$weekSales = 0;
$monthSales = 0;
$totalOrders = 0;

// Today's Sales
$stmt = $conn->prepare("SELECT SUM(si.SI_Quantity * si.SI_Price) AS total 
    FROM Sales s 
    JOIN Sale_Item si ON s.SaleID = si.SaleID 
    WHERE DATE(s.Sale_Date) = CURDATE() AND s.Sale_Status = 'Completed'");
$stmt->execute();
$todaySales = $stmt->fetchColumn() ?: 0;

// This Week's Sales
$stmt = $conn->prepare("SELECT SUM(si.SI_Quantity * si.SI_Price) AS total 
    FROM Sales s 
    JOIN Sale_Item si ON s.SaleID = si.SaleID 
    WHERE YEARWEEK(s.Sale_Date, 1) = YEARWEEK(CURDATE(), 1) AND s.Sale_Status = 'Completed'");
$stmt->execute();
$weekSales = $stmt->fetchColumn() ?: 0;

// This Month's Sales
$stmt = $conn->prepare("SELECT SUM(si.SI_Quantity * si.SI_Price) AS total 
    FROM Sales s 
    JOIN Sale_Item si ON s.SaleID = si.SaleID 
    WHERE YEAR(s.Sale_Date) = YEAR(CURDATE()) AND MONTH(s.Sale_Date) = MONTH(CURDATE()) AND s.Sale_Status = 'Completed'");
$stmt->execute();
$monthSales = $stmt->fetchColumn() ?: 0;

// Total Orders (only completed)
$stmt = $conn->prepare("SELECT COUNT(*) FROM Sales WHERE Sale_Status = 'Completed'");
$stmt->execute();
$totalOrders = $stmt->fetchColumn() ?: 0;

if ($_SESSION['user_role'] != 1 && $_SESSION['user_role'] != 3) {
    error_log("Invalid role " . $_SESSION['user_role'] . " - redirecting to appropriate page");
    if ($_SESSION['user_role'] == 2) {
        header('Location: ../user/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_completed'], $_POST['sale_id'])) {
    $saleId = intval($_POST['sale_id']);
    $adminId = $_SESSION['user_id']; // Get the current admin's UserID

    try {
        $conn->beginTransaction();

        // 1. Get sale items and their quantities
        $itemsStmt = $conn->prepare("
            SELECT si.ProductID, si.SI_Quantity, p.Prod_Stock 
            FROM Sale_Item si
            JOIN products p ON si.ProductID = p.ProductID
            WHERE si.SaleID = ?
        ");
        $itemsStmt->execute([$saleId]);
        $saleItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Update stock for each item
        foreach ($saleItems as $item) {
            // Calculate new stock level
            $newStock = $item['Prod_Stock'] - $item['SI_Quantity'];
            if ($newStock < 0) $newStock = 0;

            // Update stock
            $updateStmt = $conn->prepare("UPDATE products SET Prod_Stock = ? WHERE ProductID = ?");
            $updateStmt->execute([$newStock, $item['ProductID']]);

            // Log inventory change
            $logStmt = $conn->prepare("
                INSERT INTO inventory_history 
                (ProductID, IH_QtyChange, IH_NewStckLvl, IH_ChangeDate) 
                VALUES (?, ?, ?, NOW())
            ");
            $logStmt->execute([
                $item['ProductID'],
                -$item['SI_Quantity'],
                $newStock
            ]);
        }

        // 3. Update Sale_Status and set Sale_Per to the admin's UserID
        $stmt = $conn->prepare("UPDATE Sales SET Sale_Status = 'Completed', Sale_Per = ? WHERE SaleID = ?");
        $stmt->execute([$adminId, $saleId]);

        // 4. Get the total amount of the sale
        $totalStmt = $conn->prepare("SELECT SUM(SI_Quantity * SI_Price) as total FROM Sale_Item WHERE SaleID = ?");
        $totalStmt->execute([$saleId]);
        $total = $totalStmt->fetchColumn();

        // 5. Insert into Payment_History (cash)
        $payStmt = $conn->prepare("INSERT INTO Payment_History (SaleID, PT_PayAmount, PT_PayDate, PT_PayMethod) VALUES (?, ?, NOW(), 'cash')");
        $payStmt->execute([$saleId, $total]);

        // 6. Award loyalty points if eligible
        // Get the customer ID for this sale
        $custStmt = $conn->prepare("SELECT CustomerID FROM Sales WHERE SaleID = ?");
        $custStmt->execute([$saleId]);
        $customerId = $custStmt->fetchColumn();
        if ($customerId) {
            // Get loyalty settings
            $settings = $conn->query("SELECT min_purchase, points_per_peso FROM loyalty_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
            $minPurchase = (float)$settings['min_purchase'];
            $pointsPerPeso = (float)$settings['points_per_peso'];
            if ($total >= $minPurchase) {
                $pointsEarned = floor($total * $pointsPerPeso);
                if ($pointsEarned > 0) {
                    // Check if customer is enrolled in loyalty program
                    $stmt = $conn->prepare("SELECT LoyaltyID FROM loyalty_program WHERE CustomerID = ?");
                    $stmt->execute([$customerId]);
                    $loyaltyId = $stmt->fetchColumn();
                    if ($loyaltyId) {
                        // Update points and last update
                        $stmt = $conn->prepare("UPDATE loyalty_program SET LP_PtsBalance = LP_PtsBalance + ?, LP_LastUpdt = NOW() WHERE CustomerID = ?");
                        $result = $stmt->execute([$pointsEarned, $customerId]);
                        // Log the transaction
                        if ($result) {
                            $stmt = $conn->prepare("INSERT INTO loyalty_transaction_history (LoyaltyID, LoTranH_PtsEarned, LoTranH_TransDesc) VALUES (?, ?, ?)");
                            $stmt->execute([$loyaltyId, $pointsEarned, "Points earned from order #$saleId"]);
                        }
                    }
                }
            }
        }

        $conn->commit();

        // Store success message in session
        $_SESSION['sweet_alert'] = [
            'title' => 'Success!',
            'text' => "Sale #$saleId has been marked as completed and stock has been updated.",
            'icon' => 'success'
        ];

        // Redirect to prevent resubmission
        header("Location: sales.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['sweet_alert'] = [
            'title' => 'Error!',
            'text' => "Failed to complete sale: " . $e->getMessage(),
            'icon' => 'error'
        ];
        header("Location: sales.php");
        exit();
    }
}

// Get and clear any stored alert message
if (isset($_SESSION['sweet_alert'])) {
    $alert = $_SESSION['sweet_alert'];
    $sweetAlertConfig = "
        Swal.fire({
            title: '{$alert['title']}',
            text: '{$alert['text']}',
            icon: '{$alert['icon']}',
            confirmButtonText: 'OK'
        });
    ";
    unset($_SESSION['sweet_alert']);
}

?>


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
    <!-- Add SweetAlert2 CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                        <p class="card-text" id="todaySales">
                            ₱<?php echo number_format($todaySales, 2); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">This Week</h5>
                        <p class="card-text" id="weekSales">
                            ₱<?php echo number_format($weekSales, 2); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">This Month</h5>
                        <p class="card-text" id="monthSales">
                            ₱<?php echo number_format($monthSales, 2); ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Orders</h5>
                        <p class="card-text" id="totalOrders">
                            <?php echo $totalOrders; ?>
                        </p>
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
        <option value="all">All</option>
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
                        <th>Sale Person</th>
                        <th>Customer</th>
                        <th>Promotion</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="salesTableBody">
<?php
    $data = $con->viewSales();

    // Pagination logic
    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 10; // Fixed items per page
    $totalRecords = count($data);
    $totalPages = ceil($totalRecords / $perPage);
    $paginatedSales = array_slice($data, ($currentPage - 1) * $perPage, $perPage);

    foreach ($paginatedSales as $sale) {
?>
<tr>
    <td><?php echo $sale['SaleID']; ?></td>
    <td><?php echo $sale['Sale_Date']; ?></td>
    <td>
        <?php 
        // Get admin name from User_Accounts table
        $adminStmt = $conn->prepare("SELECT User_Name FROM User_Accounts WHERE UserID = ?");
        $adminStmt->execute([$sale['Sale_Per']]);
        $adminName = $adminStmt->fetchColumn();
        echo htmlspecialchars($adminName ?: 'Not Assigned');
        ?>
    </td>
    <td><?php echo htmlspecialchars($sale['CustomerName']); ?></td>
    <td>
        <?php echo !empty($sale['PromotionName']) ? htmlspecialchars($sale['PromotionName']) : '-'; ?>
    </td>
    <td><?php echo isset($sale['Sale_Status']) ? htmlspecialchars($sale['Sale_Status']) : '-'; ?></td>
    <td class="text-center align-middle">
        <div class="btn-group" role="group">
    <?php if (isset($sale['Sale_Status']) && $sale['Sale_Status'] === 'Pending'): ?>
        <form method="POST" action="sales.php" style="display:inline;" id="completeForm_<?php echo $sale['SaleID']; ?>">
            <input type="hidden" name="sale_id" value="<?php echo $sale['SaleID']; ?>">
            <input type="hidden" name="mark_completed" value="1">
            <button type="button" onclick="confirmComplete(<?php echo $sale['SaleID']; ?>)" class="btn btn-success btn-sm" title="Mark as Completed">
                <i class="bi bi-check-lg"></i>
            </button>
        </form>
    <?php elseif (isset($sale['Sale_Status']) && $sale['Sale_Status'] === 'Completed'): ?>
                <span class="badge bg-success align-middle" style="font-size: 1em; padding: 0.5em 0.75em;">Completed</span>
    <?php endif; ?>
            <button type="button" class="btn btn-outline-info btn-sm ms-1" onclick="viewSaleItems(<?php echo $sale['SaleID']; ?>)" title="View Items" style="border-radius: 50%;">
                <i class="bi bi-box"></i>
            </button>
        </div>
    </td>
</tr>
<?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="d-flex justify-content-center align-items-center mt-4 mb-4">
                        <nav aria-label="Page navigation" class="mx-auto">
                            <ul class="pagination mb-0 justify-content-center">
                                <!-- First Page -->
                                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=1" aria-label="First">
                                        <span aria-hidden="true">&laquo;&laquo;</span>
                                    </a>
                                </li>
                                <!-- Previous Page -->
                                <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                                <?php
                                $range = 2;
                                $startPage = max(1, $currentPage - $range);
                                $endPage = min($totalPages, $currentPage + $range);
                                if ($startPage > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                    if ($startPage > 2) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                }
                                for($i = $startPage; $i <= $endPage; $i++) {
                                    echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
                                    echo '<a class="page-link" href="?page=' . $i . '">' . $i . '</a>';
                                    echo '</li>';
                                }
                                if ($endPage < $totalPages) {
                                    if ($endPage < $totalPages - 1) {
                                        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '">' . $totalPages . '</a></li>';
                                }
                                ?>
                                <!-- Next Page -->
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                                <!-- Last Page -->
                                <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $totalPages; ?>" aria-label="Last">
                                        <span aria-hidden="true">&raquo;&raquo;</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add this before the other modals -->
    <script>
    function confirmComplete(saleId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you want to mark Sale #" + saleId + " as completed?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, mark as completed!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Add a loading state
                Swal.fire({
                    title: 'Processing...',
                    text: 'Please wait while we update the sale status.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                // Submit the form
                document.getElementById('completeForm_' + saleId).submit();
            }
        });
    }
    </script>

    <!-- Sale_Item View Modal -->
    <div class="modal fade" id="saleItemViewModal" tabindex="-1" aria-labelledby="saleItemViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saleItemViewModalLabel">Sale_Item Table</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- Add search/filter input here -->
               <div class="modal-body">
    <div class="row mb-3">
        <div class="col-md-4 mb-2">
            <div class="input-group">
                <span class="input-group-text">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control" id="saleItemSearch" placeholder="Search Sale Items...">
            </div>
        </div>
        <div class="col-md-3 mb-2">
            <select class="form-select" id="productFilter">
                <option value="">All Products</option>
                <?php
                // Dynamically populate product options from Sale_Item join Products
                $productStmt = $conn->prepare("SELECT DISTINCT p.ProductID, p.Prod_Name FROM Sale_Item si JOIN Products p ON si.ProductID = p.ProductID ORDER BY p.Prod_Name ASC");
                $productStmt->execute();
                $productNames = $productStmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($productNames as $prod) {
                    echo '<option value="' . htmlspecialchars($prod['Prod_Name']) . '">' . htmlspecialchars($prod['Prod_Name']) . '</option>';
                }
                ?>
            </select>
        </div>
    </div>
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
                $stmt = $conn->prepare("SELECT si.SaleItemID, si.SaleID, p.Prod_Name AS ProductName, si.SI_Quantity, si.SI_Price FROM Sale_Item si JOIN Products p ON si.ProductID = p.ProductID");
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
                        <input type="date" class="form-control" id="paymentStartDate" placeholder="mm/dd/yyyy">
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        var dateOnly = dateVal.split(' ')[0];
        var show = true;
        if (start && dateOnly < start) show = false;
        if (end && dateOnly > end) show = false;
        row.style.display = show ? '' : 'none';
    });
}
    </script>

    <!-- SALES TABLE SEARCH & FILTER -->
    <script>
    // Helper: Get date string in YYYY-MM-DD from a table cell
    function getCellDate(row, colIdx) {
        let val = row.children[colIdx]?.textContent.trim();
        if (!val) return null;
        // Accepts 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS'
        return val.split(' ')[0];
    }

    // Helper: Get today's, week's, month's date range
    function getDateRange(type) {
        const today = new Date();
        let start, end;
        if (type === 'today') {
            start = end = today;
        } else if (type === 'week') {
            const first = today.getDate() - today.getDay();
            start = new Date(today.setDate(first));
            end = new Date();
        } else if (type === 'month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
            end = new Date();
        }
        return {
            start: start ? start.toISOString().slice(0,10) : null,
            end: end ? end.toISOString().slice(0,10) : null
        };
    }

    function filterSalesTable() {
    const search = document.getElementById('saleSearch').value.toLowerCase();
    const dateRange = document.getElementById('dateRangeFilter').value;
    let startDate = '', endDate = '';

    if (dateRange === 'custom') {
        startDate = document.getElementById('startDate').value;
        endDate = document.getElementById('endDate').value;
    } else if (dateRange !== 'all') {
        const range = getDateRange(dateRange);
        startDate = range.start;
        endDate = range.end;
    }

    const rows = document.querySelectorAll('#salesTableBody tr');
    rows.forEach(row => {
        let cells = row.children;
        let saleId = cells[0]?.textContent.toLowerCase() || '';
        let saleDate = getCellDate(row, 1);
        let salePerson = cells[2]?.textContent.toLowerCase() || '';
        let customer = cells[3]?.textContent.toLowerCase() || '';
        let promotion = cells[4]?.textContent.toLowerCase() || '';
        let status = cells[5]?.textContent.toLowerCase() || '';

        // Search filter
        let searchMatch = (
            saleId.includes(search) ||
            salePerson.includes(search) ||
            customer.includes(search) ||
            promotion.includes(search) ||
            status.includes(search)
        );

        // Date filter
        let dateMatch = true;
        if (dateRange !== 'all') {
            if (startDate && saleDate && saleDate < startDate) dateMatch = false;
            if (endDate && saleDate && saleDate > endDate) dateMatch = false;
        }

        row.style.display = (searchMatch && dateMatch) ? '' : 'none';
    });
}

// Sale Item Modal Search/Filter with Product Filter
document.addEventListener('DOMContentLoaded', function() {
    var saleItemSearch = document.getElementById('saleItemSearch');
    var productFilter = document.getElementById('productFilter');
    function filterSaleItems() {
        const search = saleItemSearch ? saleItemSearch.value.toLowerCase() : '';
        const product = productFilter ? productFilter.value.toLowerCase() : '';
        const rows = document.querySelectorAll('#saleItemTableBody tr');
        rows.forEach(row => {
            const rowText = row.textContent.toLowerCase();
            const prodName = row.children[2]?.textContent.toLowerCase() || '';
            const searchMatch = rowText.includes(search);
            const productMatch = !product || prodName === product;
            row.style.display = (searchMatch && productMatch) ? '' : 'none';
        });
    }
    if (saleItemSearch) saleItemSearch.addEventListener('input', filterSaleItems);
    if (productFilter) productFilter.addEventListener('change', filterSaleItems);
});

    // Show/hide custom date range inputs
    document.getElementById('dateRangeFilter').addEventListener('change', function() {
        const custom = this.value === 'custom';
        document.getElementById('customDateRange').style.display = custom ? '' : 'none';
        filterSalesTable();
    });
    document.getElementById('saleSearch').addEventListener('input', filterSalesTable);
    document.getElementById('startDate').addEventListener('change', filterSalesTable);
    document.getElementById('endDate').addEventListener('change', filterSalesTable);
    document.getElementById('dateRangeFilter').addEventListener('change', filterSalesTable);
 

    // Initial filter on page load
    window.addEventListener('DOMContentLoaded', filterSalesTable);
    </script>

    <!-- Add this before the closing body tag -->
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
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="saleItemsTableBody">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                    <td id="saleSubtotal"></td>
                                </tr>
                                <tr id="discountRow" style="display:none;">
                                    <td colspan="3" class="text-end"><strong>Discount:</strong> <span id="discountDetails"></span></td>
                                    <td id="saleDiscountAmount"></td>
                                </tr>
                                <tr>
                                    <td colspan="3" class="text-end"><strong>Final Total:</strong></td>
                                    <td id="saleFinalTotal"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    <div id="promotionInfo" class="mt-2"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function viewSaleItems(saleId) {
        // Show loading state
        Swal.fire({
            title: 'Loading...',
            text: 'Please wait while we fetch the sale items.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Fetch sale items using AJAX
        fetch(`get_sale_items.php?sale_id=${saleId}`)
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                // Update modal title
                document.getElementById('saleItemsModalLabel').textContent = `Sale #${saleId} Items`;
                
                // Clear existing table content
                const tableBody = document.getElementById('saleItemsTableBody');
                tableBody.innerHTML = '';
                
                let totalAmount = 0;
                
                // Add items to table
                data.items.forEach(item => {
                    const row = document.createElement('tr');
                    const itemTotal = item.quantity * item.price;
                    totalAmount += itemTotal;
                    
                    row.innerHTML = `
                        <td>${item.product_name}</td>
                        <td>${item.quantity}</td>
                        <td>₱${parseFloat(item.price).toFixed(2)}</td>
                        <td>₱${itemTotal.toFixed(2)}</td>
                    `;
                    tableBody.appendChild(row);
                });
                
                // Subtotal
                document.getElementById('saleSubtotal').textContent = `₱${parseFloat(data.subtotal).toFixed(2)}`;
                // Discount
                if (data.discount_amount && data.discount_amount > 0) {
                    document.getElementById('discountRow').style.display = '';
                    document.getElementById('saleDiscountAmount').textContent = `-₱${parseFloat(data.discount_amount).toFixed(2)}`;
                    let details = '';
                    if (data.promotion_name) {
                        details += `<span class='badge bg-info text-dark me-1'>Promo: ${data.promotion_name}</span> `;
                    }
                    if (data.discount_type) {
                        details += `<span class='badge bg-secondary me-1'>${data.discount_type}</span> `;
                    }
                    if (data.discount_rate && data.discount_rate > 0) {
                        details += `<span class='badge bg-warning text-dark'>${parseFloat(data.discount_rate).toFixed(2)}% Customer Discount</span>`;
                    }
                    document.getElementById('discountDetails').innerHTML = details;
                } else {
                    document.getElementById('discountRow').style.display = 'none';
                    document.getElementById('discountDetails').innerHTML = '';
                    document.getElementById('saleDiscountAmount').textContent = '';
                }
                // Final total
                document.getElementById('saleFinalTotal').textContent = `₱${parseFloat(data.final_total).toFixed(2)}`;
                // Promotion info (if any)
                document.getElementById('promotionInfo').innerHTML = data.promotion_name ? `<span class='badge bg-info text-dark'>Promotion: ${data.promotion_name}</span>` : '';
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('saleItemsModal'));
                modal.show();
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to load sale items. Please try again.',
                    icon: 'error'
                });
                console.error('Error:', error);
            });
    }
    </script>

    <?php if (!empty($sweetAlertConfig)): ?>
    <script>
        <?php echo $sweetAlertConfig; ?>
    </script>
    <?php endif; ?>
</body>
</html>