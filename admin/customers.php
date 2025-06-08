<?php
 
session_start();
 
require_once('../includes/db.php');
$con = new database();
$sweetAlertConfig = "";
 
// Handle AJAX request for customer data
if (isset($_GET['action']) && $_GET['action'] === 'get_customer' && isset($_GET['id'])) {
    $customerId = $_GET['id'];
    $customer = $con->getCustomerById($customerId);
 
    if (!$customer) {
        http_response_code(404);
        echo json_encode(['error' => 'Customer not found']);
        exit();
    }
 
    header('Content-Type: application/json');
    echo json_encode($customer);
    exit();
}
 
// Handle sales history request
if (isset($_GET['action']) && $_GET['action'] === 'get_sales_history' && isset($_GET['id'])) {
    $customerId = $_GET['id'];
 
    $stmt = $con->opencon()->prepare("
        SELECT s.SaleID, s.Sale_Date, s.Sale_Total, s.Sale_Status,
               GROUP_CONCAT(CONCAT(p.Prod_Name, ' (', sd.SaleDet_Qty, ')') SEPARATOR ', ') as items
        FROM sales s
        JOIN sale_details sd ON s.SaleID = sd.SaleID
        JOIN products p ON sd.ProductID = p.ProductID
        WHERE s.CustomerID = ?
        GROUP BY s.SaleID
        ORDER BY s.Sale_Date DESC
    ");
    $stmt->execute([$customerId]);
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    header('Content-Type: application/json');
    echo json_encode($sales);
    exit();
}
 
// Handle discount history request
if (isset($_GET['action']) && $_GET['action'] === 'get_discount_history' && isset($_GET['id'])) {
    $customerId = $_GET['id'];
 
    $stmt = $con->opencon()->prepare("
        SELECT
            s.SaleID,
            s.Sale_Date,
            s.Sale_Total as original_total,
            (s.Sale_Total * (1 - c.Cust_DiscRate/100)) as discounted_total,
            c.Cust_DiscRate as discount_rate,
            (s.Sale_Total * c.Cust_DiscRate/100) as discount_amount
        FROM sales s
        JOIN customers c ON s.CustomerID = c.CustomerID
        WHERE s.CustomerID = ? AND c.Cust_DiscRate > 0
        ORDER BY s.Sale_Date DESC
    ");
    $stmt->execute([$customerId]);
    $discounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    header('Content-Type: application/json');
    echo json_encode($discounts);
    exit();
}
 
// Get SweetAlert config from session after redirect
if (isset($_SESSION['sweetAlertConfig'])) {
    $sweetAlertConfig = $_SESSION['sweetAlertConfig'];
    unset($_SESSION['sweetAlertConfig']);
}
 
// Handle Add Customer
if (isset($_POST['add'])) {
    $customerName = $_POST['Cust_Name'];
    $contactInfo = $_POST['Cust_CoInfo'];
    $discountRate = $_POST['discountRate'];
    $enrollLoyalty = isset($_POST['enroll_loyalty']) ? true : false;
 
    $custID = $con->addCustomer($customerName, $contactInfo, $discountRate, $enrollLoyalty);
 
    if ($custID) {
        // Update tier if enrolled in loyalty
        if ($enrollLoyalty) {
            $con->updateMemberTier($custID);
        }
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Customer Added Successfully',
            text: 'A new customer has been added!',
            confirmButtonText: 'Continue'
         });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Something went wrong',
                    text: 'Please try again.'
                });
            </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
 
// Handle Edit Customer
if (isset($_POST['edit_customer'])) {
    $id = $_POST['customerID'];
    $name = $_POST['Cust_Name'];
    $contactInfo = $_POST['Cust_CoInfo'];
    $discountRate = $_POST['discountRate'];
    $enrollLoyalty = isset($_POST['enroll_loyalty']) ? true : false;
 
    $result = $con->updateCustomer($id, $name, $contactInfo, $discountRate, $enrollLoyalty);
 
    if ($result) {
        // Update tier if enrolled in loyalty
        if ($enrollLoyalty) {
            $con->updateMemberTier($id);
        }
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Customer Updated',
            text: 'Customer updated successfully!',
            confirmButtonText: 'OK'
        });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: 'Failed to update customer.',
            confirmButtonText: 'OK'
        });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
 
// Handle Edit Modal Open (like products.php)
$editCustomerData = null;
$showEditModal = false;
if (isset($_POST['edit_customer_modal']) && isset($_POST['customerID'])) {
    $editCustomerData = $con->getCustomerById($_POST['customerID']);
    $showEditModal = true;
}
 
$customers = $con->viewCustomers();
 
// Update each customer's tier before displaying
foreach ($customers as $customer) {
    $con->updateMemberTier($customer['CustomerID']);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Customers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
 
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Customers</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-plus-lg"></i> Add Customer
            </button>
        </div>
 
        <!-- Customer Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Customers</h5>
                        <p class="card-text"><?php echo count($customers); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Loyalty Members</h5>
                        <p class="card-text"><?php echo count(array_filter($customers, function($c) {
                            return isset($c['LP_PtsBalance']) && $c['LP_PtsBalance'] > 0;
                        })); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Gold Members</h5>
                        <p class="card-text"><?php
                            echo count(array_filter($customers, function($c) {
                                $points = isset($c['LP_PtsBalance']) ? (int)$c['LP_PtsBalance'] : 0;
                                return $points >= 15000;
                            }));
                        ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Silver Members</h5>
                        <p class="card-text"><?php
                            echo count(array_filter($customers, function($c) {
                                $points = isset($c['LP_PtsBalance']) ? (int)$c['LP_PtsBalance'] : 0;
                                return $points >= 10000 && $points < 15000;
                            }));
                        ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Bronze Members</h5>
                        <p class="card-text"><?php
                            echo count(array_filter($customers, function($c) {
                                $points = isset($c['LP_PtsBalance']) ? (int)$c['LP_PtsBalance'] : 0;
                                return $points >= 5000 && $points < 10000;
                            }));
                        ?></p>
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
                    <input type="text" class="form-control" id="customerSearch"
                           placeholder="Search customers..." aria-label="Search customers">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="loyaltyFilter">
                    <option value="all">All Loyalty Status</option>
                    <option value="gold">Gold</option>
                    <option value="silver">Silver</option>
                    <option value="bronze">Bronze</option>
                    <option value="none">No Loyalty</option>
                </select>
            </div>
        </div>
 
        <!-- Customers Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Customer ID</th>
                        <th>Name</th>
                        <th>Contact Info</th>
                        <th>Loyalty Status</th>
                        <th>Discount Rate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($customers as $customer) {
                    // Fetch the updated tier for display
                    $stmt = $con->opencon()->prepare("SELECT LP_MbspTier FROM loyalty_program WHERE CustomerID = ?");
                    $stmt->execute([$customer['CustomerID']]);
                    $tier = $stmt->fetchColumn();
                ?>
                <tr>
                    <td><?php echo $customer['CustomerID']?></td>
                    <td><?php echo $customer['Cust_Name']?></td>
                    <td><?php echo $customer['Cust_CoInfo']?></td>
                    <td>
                        <?php
                        switch ($tier) {
                            case 'Gold':
                                echo '<span class="badge bg-warning text-dark">Gold</span>';
                                break;
                            case 'Silver':
                                echo '<span class="badge bg-secondary">Silver</span>';
                                break;
                            case 'Bronze':
                                echo '<span class="badge bg-dark text-light">Bronze</span>';
                                break;
                            default:
                                echo '<span class="badge bg-light text-dark">None</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo number_format($customer['Cust_DiscRate'], 0) . '%'; ?></td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewCustomer(<?php echo $customer['CustomerID']; ?>)">
                            <i class="bi bi-eye"></i>
                        </button>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="edit_customer_modal" value="1">
                            <input type="hidden" name="customerID" value="<?php echo $customer['CustomerID']; ?>">
                            <button type="submit" class="btn btn-sm btn-warning">
                                <i class="bi bi-pencil"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
 
    <!-- Add Customer Modal -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalLabel">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCustomerForm" method="POST">
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="customerName" name="Cust_Name" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactInfo" class="form-label">Contact Information</label>
                            <input type="text" class="form-control" id="contactInfo" name="Cust_CoInfo" required>
                        </div>
                        <div class="mb-3">
                            <label for="discountRate" class="form-label">Discount Rate (%)</label>
                            <input type="number" class="form-control" id="discountRate" name="discountRate" step="0.01" min="0" max="100">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="enrollLoyalty" name="enroll_loyalty">
                                <label class="form-check-label" for="enrollLoyalty">
                                    Enroll in Loyalty Program
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addCustomerForm" name="add" class="btn btn-primary">Save Customer</button>
                </div>
            </div>
        </div>
    </div>
 
    <!-- Edit Customer Modal -->
    <div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editCustomerForm" method="POST">
                    <input type="hidden" name="edit_customer" value="1">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editCustomerModalLabel">Edit Customer</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editCustomerName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="editCustomerName" name="Cust_Name" required
                                value="<?php echo isset($editCustomerData['Cust_Name']) ? htmlspecialchars($editCustomerData['Cust_Name']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="editContactInfo" class="form-label">Contact Information</label>
                            <input type="text" class="form-control" id="editContactInfo" name="Cust_CoInfo" required
                                value="<?php echo isset($editCustomerData['Cust_CoInfo']) ? htmlspecialchars($editCustomerData['Cust_CoInfo']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="editDiscountRate" class="form-label">Discount Rate (%)</label>
                            <input type="number" class="form-control" id="editDiscountRate" name="discountRate" step="0.01" min="0" max="100"
                                value="<?php echo isset($editCustomerData['Cust_DiscRate']) ? htmlspecialchars($editCustomerData['Cust_DiscRate']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="editEnrollLoyalty" name="enroll_loyalty"
                                    <?php echo (isset($editCustomerData['LP_PtsBalance']) && $editCustomerData['LP_PtsBalance'] !== null) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="editEnrollLoyalty">
                                    Enroll in Loyalty Program
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" name="customerID" id="editCustomerID"
                            value="<?php echo isset($editCustomerData['CustomerID']) ? htmlspecialchars($editCustomerData['CustomerID']) : ''; ?>">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
 
    <!-- View Customer Modal -->
    <div class="modal fade" id="viewCustomerModal" tabindex="-1" aria-labelledby="viewCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCustomerModalLabel">Customer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Customer Information Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Customer Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Customer ID:</strong> <span id="viewCustomerId"></span></p>
                                    <p><strong>Name:</strong> <span id="viewCustomerName"></span></p>
                                    <p><strong>Contact Info:</strong> <span id="viewCustomerContact"></span></p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Discount Rate:</strong> <span id="viewCustomerDiscount"></span></p>
                                    <p><strong>Loyalty Status:</strong> <span id="viewCustomerLoyalty"></span></p>
                                    <p><strong>Loyalty Points:</strong> <span id="viewCustomerPoints"></span></p>
                                    <p><strong>Last Updated:</strong> <span id="viewCustomerLastUpdate"></span></p>
                                </div>
                            </div>
                        </div>
                    </div>
 
                    <!-- Sales History Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Sales History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="salesHistoryTable">
                                    <thead>
                                        <tr>
                                            <th>Sale ID</th>
                                            <th>Date</th>
                                            <th>Items</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
 
                    <!-- Discount History Card -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Discount History</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped" id="discountHistoryTable">
                                    <thead>
                                        <tr>
                                            <th>Sale ID</th>
                                            <th>Date</th>
                                            <th>Original Total</th>
                                            <th>Discount Rate</th>
                                            <th>Discount Amount</th>
                                            <th>Final Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
 
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('customerSearch');
        const loyaltyFilter = document.getElementById('loyaltyFilter');
        const tableRows = document.querySelectorAll('tbody tr');
 
        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const loyaltyValue = loyaltyFilter.value;
 
            tableRows.forEach(row => {
                const name = row.children[1].textContent.toLowerCase();
                const loyalty = row.children[3].textContent.toLowerCase();
 
                // Check search match
                const matchesSearch = name.includes(searchValue);
 
                // Check loyalty filter match
                let matchesLoyalty = false;
                if (loyaltyValue === 'all') {
                    matchesLoyalty = true;
                } else if (loyaltyValue === 'none') {
                    matchesLoyalty = loyalty.includes('none');
                } else if (loyaltyValue === 'bronze') {
                    matchesLoyalty = loyalty.includes('bronze');
                } else {
                    matchesLoyalty = loyalty.includes(loyaltyValue);
                }
 
                if (matchesSearch && matchesLoyalty) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
 
        searchInput.addEventListener('input', filterTable);
        loyaltyFilter.addEventListener('change', filterTable);
    });
 
    // View Customer Functionality
    function viewCustomer(customerId) {
        // Fetch customer details
        fetch(`customers.php?action=get_customer&id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                // Update customer information
                document.getElementById('viewCustomerId').textContent = data.CustomerID;
                document.getElementById('viewCustomerName').textContent = data.Cust_Name;
                document.getElementById('viewCustomerContact').textContent = data.Cust_CoInfo;
                document.getElementById('viewCustomerDiscount').textContent = data.Cust_DiscRate + '%';
               
                // Update loyalty information
                const tier = data.LP_MbspTier || 'None';
                const badgeClass = tier === 'Gold' ? 'bg-warning text-dark' :
                                 tier === 'Silver' ? 'bg-secondary' :
                                 tier === 'Bronze' ? 'bg-dark text-light' :
                                 'bg-light text-dark';
                document.getElementById('viewCustomerLoyalty').innerHTML =
                    `<span class="badge ${badgeClass}">${tier}</span>`;
               
                document.getElementById('viewCustomerPoints').textContent =
                    data.LP_PtsBalance ? number_format(data.LP_PtsBalance) : '0';
                document.getElementById('viewCustomerLastUpdate').textContent =
                    data.LP_LastUpdt ? new Date(data.LP_LastUpdt).toLocaleDateString() : 'N/A';
 
                // Fetch sales history
                fetch(`customers.php?action=get_sales_history&id=${customerId}`)
                    .then(response => response.json())
                    .then(sales => {
                        const tbody = document.querySelector('#salesHistoryTable tbody');
                        tbody.innerHTML = '';
                       
                        if (sales.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No sales history found</td></tr>';
                            return;
                        }
 
                        sales.forEach(sale => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${sale.SaleID}</td>
                                <td>${new Date(sale.Sale_Date).toLocaleDateString()}</td>
                                <td>${sale.items}</td>
                                <td>₱${number_format(sale.Sale_Total, 2)}</td>
                                <td><span class="badge ${sale.Sale_Status === 'Completed' ? 'bg-success' : 'bg-warning'}">${sale.Sale_Status}</span></td>
                            `;
                            tbody.appendChild(row);
                        });
                    });
 
                // Fetch discount history
                fetch(`customers.php?action=get_discount_history&id=${customerId}`)
                    .then(response => response.json())
                    .then(discounts => {
                        const tbody = document.querySelector('#discountHistoryTable tbody');
                        tbody.innerHTML = '';
                       
                        if (discounts.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="6" class="text-center">No discount history found</td></tr>';
                            return;
                        }
 
                        discounts.forEach(discount => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${discount.SaleID}</td>
                                <td>${new Date(discount.Sale_Date).toLocaleDateString()}</td>
                                <td>₱${number_format(discount.original_total, 2)}</td>
                                <td>${number_format(discount.discount_rate, 0)}%</td>
                                <td>₱${number_format(discount.discount_amount, 2)}</td>
                                <td>₱${number_format(discount.discounted_total, 2)}</td>
                            `;
                            tbody.appendChild(row);
                        });
                    });
 
                // Show the modal
                new bootstrap.Modal(document.getElementById('viewCustomerModal')).show();
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load customer data'
                });
            });
    }
 
    // Helper function for number formatting
    function number_format(number, decimals = 0) {
        return Number(number).toLocaleString('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        });
    }
    </script>
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php echo $sweetAlertConfig; ?>
 
    <?php if ($showEditModal): ?>
    <script>
        var editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        editModal.show();
    </script>
    <?php endif; ?>
</body>
</html>