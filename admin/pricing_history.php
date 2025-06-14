<?php

require_once '../includes/db.php';
$db = new database();
$sweetAlertConfig = "";

// Get pricing history data
$stats = $db->getPricingHistoryStats();

// Get all pricing history records
$history = $db->getAllPricingHistory();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (isset($_POST['productId'], $_POST['oldPrice'], $_POST['newPrice'], $_POST['changeDate'], $_POST['effectiveFrom'])) {
                    $result = $db->addPricingHistory(
                        $_POST['productId'],
                        $_POST['oldPrice'],
                        $_POST['newPrice'],
                        $_POST['changeDate'],
                        $_POST['effectiveFrom'],
                        $_POST['effectiveTo'] ?? null
                    );
                    $response['success'] = $result !== false;
                    $response['message'] = $result ? 'History added successfully' : 'Failed to add history';
                }
                break;
                
            case 'edit':
                if (isset($_POST['historyId'], $_POST['oldPrice'], $_POST['newPrice'], $_POST['changeDate'], $_POST['effectiveFrom'])) {
                    $result = $db->updatePricingHistory(
                        $_POST['historyId'],
                        $_POST['oldPrice'],
                        $_POST['newPrice'],
                        $_POST['changeDate'],
                        $_POST['effectiveFrom'],
                        $_POST['effectiveTo'] ?? null
                    );
                    $response['success'] = $result;
                    $response['message'] = $result ? 'History updated successfully' : 'Failed to update history';
                }
                break;
                
            case 'delete':
                if (isset($_POST['historyId'])) {
                    $result = $db->deletePricingHistory($_POST['historyId']);
                    $response['success'] = $result;
                    $response['message'] = $result ? 'History deleted successfully' : 'Failed to delete history';
                }
                break;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Debug information
$debug = false;
if ($debug) {
    echo "<pre>";
    print_r($_POST);
    print_r($history);
    echo "</pre>";
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing History</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .dashboard-card .card-title { font-size: 1rem; }
        .dashboard-card .card-text { font-size: 1.3rem; font-weight: 600; }
        .main-content { margin-left: 260px; padding: 2rem 2.5rem; }
    </style>
</head>
<body>
    <!-- Sidebar include -->
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Pricing History</h1>
           
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Records</h5>
                        <p class="card-text" id="totalRecords"><?php echo $stats['totalRecords'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Current Prices</h5>
                        <p class="card-text" id="currentPrices"><?php echo $stats['currentPrices'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Upcoming Changes</h5>
                        <p class="card-text" id="upcomingChanges"><?php echo $stats['upcomingChanges'] ?? 0; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Expired Prices</h5>
                        <p class="card-text" id="expiredPrices"><?php echo $stats['expiredPrices'] ?? 0; ?></p>
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
                    <input type="text" class="form-control" id="historySearch" placeholder="Search pricing history..." aria-label="Search pricing history">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="productFilter">
                    <option value="all">All Products</option>
                    <?php
                    $products = $db->viewProducts();
                    foreach ($products as $product) {
                        echo "<option value='{$product['ProductID']}'>{$product['Prod_Name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="current">Current</option>
                    <option value="upcoming">Upcoming</option>
                    <option value="expired">Expired</option>
                </select>
            </div>
        </div>

        <!-- Pricing History Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead>
                    <tr>
                        <th>HistoryID</th>
                        <th>Product</th>
                        <th>Old Price</th>
                        <th>New Price</th>
                        <th>Change Date</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                        <th>Created At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $record): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($record['HistoryID']); ?></td>
                        <td><?php echo htmlspecialchars($record['Prod_Name']); ?></td>
                        <td>₱<?php echo number_format($record['PH_OldPrice'], 2); ?></td>
                        <td>₱<?php echo number_format($record['PH_NewPrice'], 2); ?></td>
                        <td><?php echo htmlspecialchars($record['PH_ChangeDate']); ?></td>
                        <td><?php echo htmlspecialchars($record['PH_Effective_from']); ?></td>
                        <td><?php echo htmlspecialchars($record['PH_Effective_to'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($record['PH_Created_at']); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button 
                                    type="button" 
                                    class="btn btn-warning btn-sm editHistoryBtn"
                                    data-id="<?php echo $record['HistoryID']; ?>"
                                    data-product="<?php echo $record['ProductID']; ?>"
                                    data-oldprice="<?php echo $record['PH_OldPrice']; ?>"
                                    data-newprice="<?php echo $record['PH_NewPrice']; ?>"
                                    data-changedate="<?php echo $record['PH_ChangeDate']; ?>"
                                    data-effectivefrom="<?php echo $record['PH_Effective_from']; ?>"
                                    data-effectiveto="<?php echo $record['PH_Effective_to']; ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editHistoryModal"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm deleteHistoryBtn" data-id="<?php echo $record['HistoryID']; ?>">
                                    <i class="bi bi-x-square"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add History Modal -->
    <div class="modal fade" id="addHistoryModal" tabindex="-1" aria-labelledby="addHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addHistoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addHistoryModalLabel">Add Pricing History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="addProductID" class="form-label">Product</label>
                            <select class="form-select" id="addProductID" required>
                                <?php foreach ($products as $product): ?>
                                <option value="<?php echo $product['ProductID']; ?>">
                                    <?php echo htmlspecialchars($product['Prod_Name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="addOldPrice" class="form-label">Old Price</label>
                            <input type="number" class="form-control" id="addOldPrice" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="addNewPrice" class="form-label">New Price</label>
                            <input type="number" class="form-control" id="addNewPrice" min="0" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="addChangeDate" class="form-label">Change Date</label>
                            <input type="date" class="form-control" id="addChangeDate" required>
                        </div>
                        <div class="mb-3">
                            <label for="addEffectiveFrom" class="form-label">Effective From</label>
                            <input type="date" class="form-control" id="addEffectiveFrom" required>
                        </div>
                        <div class="mb-3">
                            <label for="addEffectiveTo" class="form-label">Effective To</label>
                            <input type="date" class="form-control" id="addEffectiveTo">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save History</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit History Modal -->
    <div class="modal fade" id="editHistoryModal" tabindex="-1" aria-labelledby="editHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editHistoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editHistoryModalLabel">Edit Pricing History</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="editHistoryID">
                        <div class="mb-3">
                            <label for="editProductID" class="form-label">Product</label>
                            <input type="text" class="form-control" id="editProductName" disabled>
                            <input type="hidden" id="editProductID">
                        </div>
                        <div class="mb-3">
                            <label for="editOldPrice" class="form-label">Old Price</label>
                            <input type="number" class="form-control" id="editOldPrice" min="0" step="0.01" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="editNewPrice" class="form-label">New Price</label>
                            <input type="number" class="form-control" id="editNewPrice" min="0" step="0.01" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="editChangeDate" class="form-label">Change Date</label>
                            <input type="date" class="form-control" id="editChangeDate" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="editEffectiveFrom" class="form-label">Effective From</label>
                            <input type="date" class="form-control" id="editEffectiveFrom" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEffectiveTo" class="form-label">Effective To</label>
                            <input type="date" class="form-control" id="editEffectiveTo">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update History</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Fill Edit Modal with pricing history data
        document.querySelectorAll('.editHistoryBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.getElementById('editHistoryID').value = this.dataset.id;
                document.getElementById('editProductID').value = this.dataset.product;
                document.getElementById('editProductName').value = this.closest('tr').querySelectorAll('td')[1].textContent;
                document.getElementById('editOldPrice').value = this.dataset.oldprice;
                document.getElementById('editNewPrice').value = this.dataset.newprice;
                document.getElementById('editChangeDate').value = this.dataset.changedate;
                document.getElementById('editEffectiveFrom').value = this.dataset.effectivefrom;
                document.getElementById('editEffectiveTo').value = this.dataset.effectiveto || '';
            });
        });

        // Handle form submissions
        document.getElementById('addHistoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('productId', document.getElementById('addProductID').value);
            formData.append('oldPrice', document.getElementById('addOldPrice').value);
            formData.append('newPrice', document.getElementById('addNewPrice').value);
            formData.append('changeDate', document.getElementById('addChangeDate').value);
            formData.append('effectiveFrom', document.getElementById('addEffectiveFrom').value);
            formData.append('effectiveTo', document.getElementById('addEffectiveTo').value);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred while processing your request', 'error');
            });
        });

        document.getElementById('editHistoryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData();
            formData.append('action', 'edit');
            formData.append('historyId', document.getElementById('editHistoryID').value);
            formData.append('productId', document.getElementById('editProductID').value);
            formData.append('oldPrice', document.getElementById('editOldPrice').value);
            formData.append('newPrice', document.getElementById('editNewPrice').value);
            formData.append('changeDate', document.getElementById('editChangeDate').value);
            formData.append('effectiveFrom', document.getElementById('editEffectiveFrom').value);
            formData.append('effectiveTo', document.getElementById('editEffectiveTo').value);

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Success', data.message, 'success').then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'An error occurred while processing your request', 'error');
            });
        });

        // Handle delete buttons
        document.querySelectorAll('.deleteHistoryBtn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const historyId = this.dataset.id;
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const formData = new FormData();
                        formData.append('action', 'delete');
                        formData.append('historyId', historyId);

                        fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire('Deleted!', data.message, 'success').then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'An error occurred while processing your request', 'error');
                        });
                    }
                });
            });
        });

        // Search and filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('table.table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const searchInput = document.getElementById('historySearch');
            const productFilter = document.getElementById('productFilter');
            const statusFilter = document.getElementById('statusFilter');

            function filterRows() {
                const search = searchInput.value.toLowerCase();
                const product = productFilter.value;
                const status = statusFilter.value;

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const prodName = cells[1].textContent.toLowerCase();
                    // Remove statusText and status filter if you removed the status column
                    let show = true;

                    if (search && !row.textContent.toLowerCase().includes(search)) {
                        show = false;
                    }
                    if (product !== 'all' && !prodName.includes(product)) {
                        show = false;
                    }
                    // Remove status filter if not needed
                    row.style.display = show ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', filterRows);
            productFilter.addEventListener('change', filterRows);
            statusFilter.addEventListener('change', filterRows);
        });
    </script>
    <?php echo $sweetAlertConfig; ?>
</body>
</html>