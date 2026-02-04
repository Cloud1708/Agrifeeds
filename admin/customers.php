<?php

session_start();

require_once('../includes/db.php');
require_once('../includes/validation.php');
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

// Allow-list for GET actions (never trust client)
$allowed_actions = ['get_customer', 'check_username', 'get_sales_history', 'get_discount_history'];

// Handle AJAX request for customer data (validate ID server-side)
if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions, true) && $_GET['action'] === 'get_customer' && isset($_GET['id'])) {
    $customerId = validate_id($_GET['id']);
    if ($customerId === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid customer ID']);
        exit();
    }
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

// Handle username check request (sanitize username; DB uses prepared statement)
if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions, true) && $_GET['action'] === 'check_username' && isset($_GET['username'])) {
    $username = sanitize_string_allowlist($_GET['username'], 50, '._');
    $exists = $con->checkUsernameExists($username);
    
    header('Content-Type: application/json');
    echo json_encode(['exists' => $exists]);
    exit();
}
 
// Handle sales history request (validate ID)
if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions, true) && $_GET['action'] === 'get_sales_history' && isset($_GET['id'])) {
    $customerId = validate_id($_GET['id']);
    if ($customerId === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid customer ID']);
        exit();
    }
 
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
 
// Handle discount history request (validate ID)
if (isset($_GET['action']) && in_array($_GET['action'], $allowed_actions, true) && $_GET['action'] === 'get_discount_history' && isset($_GET['id'])) {
    $customerId = validate_id($_GET['id']);
    if ($customerId === null) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid customer ID']);
        exit();
    }
 
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
 
// Handle Add Customer (sanitize and validate all input server-side)
if (isset($_POST['add'])) {
    $username = sanitize_string_allowlist($_POST['username'] ?? '', 50, '._');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $firstName = sanitize_string_allowlist($_POST['Cust_FN'] ?? '', 100, ".-,'");
    $lastName = sanitize_string_allowlist($_POST['Cust_LN'] ?? '', 100, ".-,'");
    $contactInfo = sanitize_string_allowlist($_POST['Cust_CoInfo'] ?? '', 500, ".-,@/():;+ ");
    $enrollLoyalty = isset($_POST['enroll_loyalty']);

    if ($username === '' || strlen($password) < 6) {
        $_SESSION['sweetAlertConfig'] = "<script>Swal.fire({icon:'error',title:'Validation Error',text:'Username required and password at least 6 characters.'});</script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Validate passwords match
    if ($password !== $confirmPassword) {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Password Mismatch',
            text: 'The passwords do not match!',
            confirmButtonText: 'OK'
        });
        </script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Check if username already exists
    if ($con->checkUsernameExists($username)) {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Username Exists',
            text: 'This username is already taken!',
            confirmButtonText: 'OK'
        });
        </script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Register the user first
    $userID = $con->registerUser($username, $password, 'customer', null, $firstName, $lastName, $contactInfo);

    if ($userID) {
        // Get the customer ID from the newly created user
        $stmt = $con->opencon()->prepare("SELECT CustomerID FROM customers WHERE UserID = ?");
        $stmt->execute([$userID]);
        $customerID = $stmt->fetchColumn();

        // Update tier if enrolled in loyalty
        if ($enrollLoyalty) {
            $con->updateMemberTier($customerID);
        }

        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Customer Added Successfully',
            text: 'A new customer account has been created!',
            confirmButtonText: 'Continue'
        });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Something went wrong',
            text: 'Failed to create customer account. Please try again.',
            confirmButtonText: 'OK'
        });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
 
// Handle Edit Customer (validate ID and sanitize text; type-check discount rate)
if (isset($_POST['edit_customer'])) {
    $id = validate_id($_POST['customerID'] ?? null);
    $firstName = sanitize_string_allowlist($_POST['Cust_FN'] ?? '', 100, ".-,'");
    $lastName = sanitize_string_allowlist($_POST['Cust_LN'] ?? '', 100, ".-,'");
    $contactInfo = sanitize_string_allowlist($_POST['Cust_CoInfo'] ?? '', 500, ".-,@/():;+ ");
    $discountRate = filter_var($_POST['discountRate'] ?? 0, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
    $enrollLoyalty = isset($_POST['enroll_loyalty']);

    if ($id === null) {
        $_SESSION['sweetAlertConfig'] = "<script>Swal.fire({icon:'error',title:'Validation Error',text:'Invalid customer ID.'});</script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    if ($discountRate === false) {
        $discountRate = 0;
    }

    $result = $con->updateCustomer($id, $firstName, $lastName, $contactInfo, $discountRate, $enrollLoyalty);
 
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
 
// Handle Edit Modal Open (validate customer ID)
$editCustomerData = null;
$showEditModal = false;
if (isset($_POST['edit_customer_modal']) && isset($_POST['customerID'])) {
    $editId = validate_id($_POST['customerID']);
    if ($editId !== null) {
        $editCustomerData = $con->getCustomerById($editId);
        $showEditModal = true;
    }
}

$allCustomers = $con->viewCustomers();

// Pagination logic (validate page server-side)
$currentPage = validate_int($_GET['page'] ?? null, 1, null) ?? 1;
$perPage = 10; // Fixed items per page
$totalRecords = count($allCustomers);
$totalPages = ceil($totalRecords / $perPage);
$paginatedCustomers = array_slice($allCustomers, ($currentPage - 1) * $perPage, $perPage);
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
     <link href="../css/background.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>
 
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Customers</h1>
            <?php if ($_SESSION['user_role'] == 3): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                <i class="bi bi-plus-lg"></i> Add Customer
            </button>
            <?php endif; ?>
        </div>
 
        <!-- Customer Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Customers</h5>
                        <p class="card-text"><?php echo count($allCustomers); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Loyalty Members</h5>
                        <p class="card-text"><?php echo count(array_filter($allCustomers, function($c) {
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
                            echo count(array_filter($allCustomers, function($c) {
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
                            echo count(array_filter($allCustomers, function($c) {
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
                            echo count(array_filter($allCustomers, function($c) {
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
                    </tr>
                </thead>
                <tbody>
                <?php
                foreach ($paginatedCustomers as $customer) {
                    // Fetch the updated tier for display
                    $stmt = $con->opencon()->prepare("SELECT LP_MbspTier FROM loyalty_program WHERE CustomerID = ?");
                    $stmt->execute([$customer['CustomerID']]);
                    $tier = $stmt->fetchColumn();
                ?>
                <tr>
                    <td><?php echo $customer['CustomerID']?></td>
                    <td><?php echo $customer['Cust_FN'] . ' ' . $customer['Cust_LN']?></td>
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
                        <!-- User Account Section -->
                        <h6 class="mb-3">User Account Information</h6>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                        </div>

                        <!-- Customer Details Section -->
                        <h6 class="mb-3 mt-4">Customer Details</h6>
                        <div class="mb-3">
                            <label for="firstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" name="Cust_FN" required>
                        </div>
                        <div class="mb-3">
                            <label for="lastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" name="Cust_LN" required>
                        </div>
                        <div class="mb-3">
                            <label for="contactInfo" class="form-label">Contact Information</label>
                            <input type="text" class="form-control" id="contactInfo" name="Cust_CoInfo" required>
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
                            <label for="editFirstName" class="form-label">First Name</label>
                            <input type="text" class="form-control" id="editFirstName" name="Cust_FN" required
                                value="<?php echo isset($editCustomerData['Cust_FN']) ? htmlspecialchars($editCustomerData['Cust_FN']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="editLastName" class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="editLastName" name="Cust_LN" required
                                value="<?php echo isset($editCustomerData['Cust_LN']) ? htmlspecialchars($editCustomerData['Cust_LN']) : ''; ?>">
                        </div>
                        <div class="mb-3">
                            <label for="editContactInfo" class="form-label">Contact Information</label>
                            <input type="text" class="form-control" id="editContactInfo" name="Cust_CoInfo" required
                                value="<?php echo isset($editCustomerData['Cust_CoInfo']) ? htmlspecialchars($editCustomerData['Cust_CoInfo']) : ''; ?>">
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
 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php echo $sweetAlertConfig; ?>
 
    <?php if ($showEditModal): ?>
    <script>
        var editModal = new bootstrap.Modal(document.getElementById('editCustomerModal'));
        editModal.show();
    </script>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const addCustomerForm = document.getElementById('addCustomerForm');
        let usernameTimeout;

        // Username availability check
        usernameInput.addEventListener('input', function() {
            clearTimeout(usernameTimeout);
            const username = this.value.trim();
            
            if (username.length < 3) {
                this.setCustomValidity('Username must be at least 3 characters long');
                return;
            }

            usernameTimeout = setTimeout(() => {
                fetch('../AJAX/check_username.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'username=' + encodeURIComponent(username)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        this.setCustomValidity('This username is already taken');
                    } else {
                        this.setCustomValidity('');
                    }
                })
                .catch(error => {
                    console.error('Error checking username:', error);
                });
            }, 500);
        });

        // Password match validation
        confirmPasswordInput.addEventListener('input', function() {
            if (this.value !== passwordInput.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });

        passwordInput.addEventListener('input', function() {
            if (confirmPasswordInput.value && this.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Passwords do not match');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        });

        // Form submission validation
        addCustomerForm.addEventListener('submit', function(e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            this.classList.add('was-validated');
        });

        // --- Customer Search and Filter ---
    const searchInput = document.getElementById('customerSearch');
    const loyaltyFilter = document.getElementById('loyaltyFilter');
    const table = document.querySelector('table.table tbody');

    function getLoyaltyFromRow(row) {
        const badge = row.querySelector('td:nth-child(4) .badge');
        if (!badge) return 'none';
        const text = badge.textContent.trim().toLowerCase();
        if (text === 'gold') return 'gold';
        if (text === 'silver') return 'silver';
        if (text === 'bronze') return 'bronze';
        return 'none';
    }

    function filterTable() {
        const search = searchInput.value.trim().toLowerCase();
        const loyalty = loyaltyFilter.value;

        Array.from(table.rows).forEach(row => {
            const name = row.cells[1].textContent.toLowerCase();
            const contact = row.cells[2].textContent.toLowerCase();
            const loyaltyStatus = getLoyaltyFromRow(row);

            // Search matches name or contact
            const matchesSearch = name.includes(search) || contact.includes(search);

            // Loyalty filter
            const matchesLoyalty = (loyalty === 'all') || (loyalty === loyaltyStatus);

            row.style.display = (matchesSearch && matchesLoyalty) ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    loyaltyFilter.addEventListener('change', filterTable);
    
    });
    </script>
</body>
</html>