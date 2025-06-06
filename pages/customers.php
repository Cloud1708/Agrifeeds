<?php
session_start();
 
require_once('../includes/db.php');
$con = new database();
$sweetAlertConfig = "";
 
// Get SweetAlert config from session after redirect
if (isset($_SESSION['sweetAlertConfig'])) {
    $sweetAlertConfig = $_SESSION['sweetAlertConfig'];
    unset($_SESSION['sweetAlertConfig']);
}

if (isset($_POST['add'])) {
 
  $customerName = $_POST['Cust_Name'];
  $contactInfo = $_POST['Cust_CoInfo'];
  $discountRate = $_POST['discountRate'];
  $custID = $con->addCustomer($customerName, $contactInfo, $discountRate);
 
  if ($custID) {
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
  // Redirect to avoid resubmission
  header("Location: " . $_SERVER['PHP_SELF']);
  exit();
}

 
$customers = $con->viewCustomers();
 
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Customers</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
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
                        <p class="card-text"><?php echo count(array_filter($customers, function($c) { return !empty($c['LP_MbspTier']); })); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Gold Members</h5>
                        <p class="card-text"><?php echo count(array_filter($customers, function($c) { return isset($c['LP_MbspTier']) && $c['LP_MbspTier'] === 'Gold'; })); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Silver Members</h5>
                        <p class="card-text"><?php echo count(array_filter($customers, function($c) { return isset($c['LP_MbspTier']) && $c['LP_MbspTier'] === 'Silver'; })); ?></p>
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
    ?>
    <tr>
        <td><?php echo $customer['CustomerID']?></td>
        <td><?php echo $customer['Cust_Name']?></td>
        <td><?php echo $customer['Cust_CoInfo']?></td>
        <td>
        <?php
        $points = isset($customer['Cust_Points']) ? (int)$customer['Cust_Points'] : 0;
        if ($points >= 1000) {
            echo '<span class="badge bg-warning text-dark">Gold</span>';
        } elseif ($points >= 500) {
            echo '<span class="badge bg-secondary">Silver</span>';
        } else {
            echo '<span class="badge bg-light text-dark">None</span>';
        }
        ?>
        </td>
        <td><?php echo number_format($customer['Cust_DiscRate'], 0) . '%'; ?></td>
        <td>
            <button class="btn btn-sm btn-info" onclick="viewCustomer(<?php echo $customer['CustomerID']; ?>)">
                <i class="bi bi-eye"></i>
            </button>
            <button class="btn btn-sm btn-primary" onclick="editCustomer(<?php echo $customer['CustomerID']; ?>)">
                <i class="bi bi-pencil"></i>
            </button>
            <button class="btn btn-sm btn-success" onclick="viewLoyaltyHistory(<?php echo $customer['CustomerID']; ?>)">
                <i class="bi bi-star"></i>
            </button>
        </td>
    </tr>
    <?php
    }
    ?>
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

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php echo $sweetAlertConfig; ?>
</body>
</html>