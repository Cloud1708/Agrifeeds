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

// Handle Add Supplier
if (isset($_POST['add'])) {
    $Sup_Name = $_POST['Sup_Name'];
    $Sup_CoInfo = $_POST['Sup_CoInfo'];
    $Sup_PayTerm = $_POST['Sup_PayTerm'];
    $Sup_DeSched = $_POST['Sup_DeSched'];

    $supplierID = $con->addSupplier($Sup_Name, $Sup_CoInfo, $Sup_PayTerm, $Sup_DeSched);

    if ($supplierID) {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Supplier Added Successfully',
            text: 'A new supplier has been added!',
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

// Handle Edit Supplier
if (isset($_POST['edit_supplier'])) {
    $SupplierID = $_POST['SupplierID'];
    $Sup_Name = $_POST['Sup_Name'];
    $Sup_CoInfo = $_POST['Sup_CoInfo'];
    $Sup_PayTerm = $_POST['Sup_PayTerm'];
    $Sup_DeSched = $_POST['Sup_DeSched'];

    $updated = $con->updateSupplier( $Sup_Name, $Sup_CoInfo, $Sup_PayTerm, $Sup_DeSched, $SupplierID);

    if ($updated) {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Supplier Updated',
            text: 'Supplier details updated successfully!',
            confirmButtonText: 'Continue'
        });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Please try again.'
            });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$suppliers = $con->viewSuppliers();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Suppliers</title>
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
            <h1>Suppliers</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="bi bi-plus-lg"></i> Add Supplier
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="supplierSearch" 
                           placeholder="Search suppliers..." aria-label="Search suppliers">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="supplierTypeFilter">
                    <option value="">All Types</option>
                    <option value="manufacturer">Manufacturer</option>
                    <option value="distributor">Distributor</option>
                    <option value="wholesaler">Wholesaler</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
        </div>

        <!-- Suppliers Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Supplier ID</th>
                        <th>Supplier Name</th>
                        <th>Contact Information</th>
                        <th>Payment Terms</th>
                        <th>Delivery Schedule</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($suppliers)): ?>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($supplier['SupplierID']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['Sup_Name']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['Sup_CoInfo']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['Sup_PayTerm']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($supplier['Sup_DeSched'])); ?></td>
                                <td>
                                    <button 
                                        type="button"
                                        class="btn btn-sm btn-warning editSupplierBtn"
                                        data-id="<?php echo $supplier['SupplierID']; ?>"
                                        data-name="<?php echo htmlspecialchars($supplier['Sup_Name']); ?>"
                                        data-coinf="<?php echo htmlspecialchars($supplier['Sup_CoInfo']); ?>"
                                        data-payterm="<?php echo htmlspecialchars($supplier['Sup_PayTerm']); ?>"
                                        data-desched="<?php echo htmlspecialchars($supplier['Sup_DeSched']); ?>"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editSupplierModal"
                                        title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No suppliers found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Add Supplier Modal -->
        <div class="modal fade" id="addSupplierModal" tabindex="-1" aria-labelledby="addSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="addSupplierForm" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addSupplierModalLabel">Add New Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="add" value="1">
                            <div class="mb-3">
                                <label for="Sup_Name" class="form-label">Supplier Name</label>
                                <input type="text" class="form-control" id="Sup_Name" name="Sup_Name" required>
                            </div>
                            <div class="mb-3">
                                <label for="Sup_CoInfo" class="form-label">Contact Information</label>
                                <input type="text" class="form-control" id="Sup_CoInfo" name="Sup_CoInfo" required>
                            </div>
                            <div class="mb-3">
                                <label for="Sup_PayTerm" class="form-label">Payment Terms</label>
                                <select class="form-select" id="Sup_PayTerm" name="Sup_PayTerm" required>
                                    <option value="">Select Payment Terms</option>
                                    <option value="Immediate">Immediate</option>
                                    <option value="Net 15">Net 15</option>
                                    <option value="Net 30">Net 30</option>
                                    <option value="Net 60">Net 60</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="Sup_DeSched" class="form-label">Delivery Schedule</label>
                                <select class="form-select" id="Sup_DeSched" name="Sup_DeSched" required>
                                    <option value="">Select Schedule</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Supplier Modal -->
        <div class="modal fade" id="editSupplierModal" tabindex="-1" aria-labelledby="editSupplierModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="editSupplierForm" method="POST">
                        <input type="hidden" name="SupplierID"  id="edit_Sup_ID" value="1">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editSupplierModalLabel">Edit Supplier</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_Sup_Name" class="form-label">Supplier Name</label>
                                <input type="text" class="form-control" id="edit_Sup_Name" name="Sup_Name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_Sup_CoInfo" class="form-label">Contact Information</label>
                                <input type="text" class="form-control" id="edit_Sup_CoInfo" name="Sup_CoInfo" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_Sup_PayTerm" class="form-label">Payment Terms</label>
                                <select class="form-select" id="edit_Sup_PayTerm" name="Sup_PayTerm" required>
                                    <option value="">Select Payment Terms</option>
                                    <option value="Immediate">Immediate</option>
                                    <option value="Net 15">Net 15</option>
                                    <option value="Net 30">Net 30</option>
                                    <option value="Net 60">Net 60</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_Sup_DeSched" class="form-label">Delivery Schedule</label>
                                <select class="form-select" id="edit_Sup_DeSched" name="Sup_DeSched" required>
                                    <option value="">Select Schedule</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="weekly">Weekly</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="edit_supplier" class="btn btn-primary">Update Supplier</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Fill Edit Modal with supplier data
    document.querySelectorAll('.editSupplierBtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit_Sup_ID').value = this.dataset.id;
            document.getElementById('edit_Sup_Name').value = this.dataset.name;
            document.getElementById('edit_Sup_CoInfo').value = this.dataset.coinf;
            document.getElementById('edit_Sup_PayTerm').value = this.dataset.payterm;
            document.getElementById('edit_Sup_DeSched').value = this.dataset.desched;
        });
    });
    </script>
    <?php echo $sweetAlertConfig; ?>
</body>
</html>