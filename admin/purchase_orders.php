<?php
session_start();

require_once('../includes/db.php');
$con = new database();

if ($_SESSION['user_role'] != 1 && $_SESSION['user_role'] != 3) {
    error_log("Invalid role " . $_SESSION['user_role'] . " - redirecting to appropriate page");
    if ($_SESSION['user_role'] == 2) {
        header('Location: ../user/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit();
}


// Get all suppliers for the dropdowns
$suppliers = $con->viewSuppliers();

// Get all products for the dropdown
$products = $con->getAllProducts();

// Only this line is needed:
$purchaseOrders = $con->getPurchaseOrders();

// Pagination logic
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10; // Fixed items per page
$totalRecords = count($purchaseOrders);
$totalPages = ceil($totalRecords / $perPage);
$paginatedPurchaseOrders = array_slice($purchaseOrders, ($currentPage - 1) * $perPage, $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Purchase Orders</title>
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
</head>
<body>
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Purchase Orders</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newPOModal">
                <i class="bi bi-plus-lg"></i> New Purchase Order
            </button>
        </div>

        <!-- Search and Filter -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="poSearch" 
                           placeholder="Search POs..." aria-label="Search purchase orders">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="supplierFilter">
                    <option value="">All Suppliers</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo htmlspecialchars($supplier['SupplierID']); ?>">
                            <?php echo htmlspecialchars($supplier['Sup_Name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="Waiting">Waiting</option>
                    <option value="Delivered">Delivered</option>
                </select>
            </div>
            
        </div>

        <!-- Purchase Orders Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>PO ID</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Stock Details</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paginatedPurchaseOrders as $po): ?>
                    <tr>
                        <td><?php echo $po['Pur_OrderID']; ?></td>
                        <td>
                            <strong><?php echo $po['Sup_Name']; ?></strong><br>
                            <small class="text-muted"><?php echo $po['Sup_CoInfo']; ?></small>
                        </td>
                        <td><?php echo $po['PO_Order_Date']; ?></td>
                        <td>
                            <?php 
                            if ($po['items_list']) {
                                $items = explode(', ', $po['items_list']);
                                foreach ($items as $item) {
                                    echo '<div class="mb-1">' . $item . '</div>';
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php 
                            if ($po['items_list']) {
                                $items = explode(', ', $po['items_list']);
                                foreach ($items as $item) {
                                    $itemName = explode(' (', $item)[0];
                                    $currentStock = $po['current_stock'][$itemName] ?? 0;
                                    echo '<div class="mb-1">';
                                    echo '<strong>' . $itemName . ':</strong> ';
                                    echo '<span class="badge bg-info">' . $currentStock . ' in stock</span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </td>
                        <td>
                            <?php if ($po['PO_Order_Stat'] == 'Delivered'): ?>
                                <span class="badge bg-success">Delivered</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Waiting</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-bold">₱<?php echo number_format($po['PO_Total_Amount'], 2); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <button class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#viewPOModal" data-po-id="<?= $po['Pur_OrderID'] ?>">
                                    <i class="bi bi-eye me-1"></i> View
                                </button>
                                <?php if ($po['PO_Order_Stat'] === 'Waiting'): ?>
                                    <button class="btn btn-success btn-sm d-flex align-items-center mark-delivered" data-po-id="<?= $po['Pur_OrderID'] ?>">
                                        <i class="bi bi-check-circle me-1"></i> Mark as Delivered
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
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

    <!-- New Purchase Order Modal -->
    <div class="modal fade" id="newPOModal" tabindex="-1" aria-labelledby="newPOModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newPOModalLabel">New Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newPOForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="supplierSelect" class="form-label">Supplier</label>
                                <select class="form-select" id="supplierSelect" required>
                                    <option value="">Select Supplier</option>
                                    <?php foreach ($suppliers as $supplier): ?>
                                        <option value="<?php echo htmlspecialchars($supplier['SupplierID']); ?>">
                                            <?php echo htmlspecialchars($supplier['Sup_Name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="poDate" class="form-label">Order Date</label>
                                <input type="date" class="form-control" id="poDate" 
                                       value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Products</label>
                            <div id="productList">
                                <div class="row mb-2 product-item">
                                    <div class="col-md-4">
                                        <select class="form-select product-select" required>
                                            <option value="">Select Product</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?php echo htmlspecialchars($product['ProductID']); ?>" 
                                                        data-price="<?php echo htmlspecialchars($product['Prod_Price']); ?>">
                                                    <?php echo htmlspecialchars($product['Prod_Name']); ?> 
                                                    (₱<?php echo number_format($product['Prod_Price'], 2); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control quantity-input" 
                                               placeholder="Qty" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control price-input" 
                                               placeholder="Price" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control subtotal-input" 
                                               placeholder="Subtotal" readonly>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger remove-product">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn btn-secondary mt-2" id="addProductBtn">
                                <i class="bi bi-plus-lg"></i> Add Product
                            </button>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="paymentTerms" class="form-label">Payment Terms</label>
                                <select class="form-select" id="paymentTerms" required>
                                    <option value="Immediate">Immediate</option>
                                    <option value="Net15">Net 15</option>
                                    <option value="Net30">Net 30</option>
                                    <option value="Net60">Net 60</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="totalAmount" class="form-label">Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="totalAmount" 
                                               step="0.01" min="0" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="savePOBtn">Save Purchase Order</button>
                </div>
            </div>
        </div>
    </div>

    <!-- View Purchase Order Modal -->
    <div class="modal fade" id="viewPOModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Purchase Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">Purchase Order Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>PO Number:</th>
                                    <td id="viewPONumber"></td>
                                </tr>
                                <tr>
                                    <th>Date:</th>
                                    <td id="viewPODate"></td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td id="viewPOStatus"></td>
                                </tr>
                                <tr>
                                    <th>Payment Terms:</th>
                                    <td id="viewPOPaymentTerms"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Supplier Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td id="viewPOSupplier"></td>
                                </tr>
                                <tr>
                                    <th>Contact:</th>
                                    <td id="viewPOSupplierContact"></td>
                                </tr>
                                <tr>
                                    <th>Delivery Schedule:</th>
                                    <td id="viewPOSupplierDelivery"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <h6 class="mb-3">Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                    <th>Current Stock</th>
                                </tr>
                            </thead>
                            <tbody id="viewPOItems"></tbody>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6 class="mb-3">Notes</h6>
                            <p id="viewPONotes" class="text-muted"></p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-3">Cost Summary</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Total Amount:</th>
                                    <td id="viewPOTotal"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <h6 class="mb-3 mt-4">Inventory History</h6>
                    <div id="viewPOInventoryHistory" class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        <div class="list-group">
                            <!-- Inventory history items will be inserted here -->
                        </div>
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
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Pass PHP PO data to JS -->
    <script>
        const purchaseOrders = <?php echo json_encode($purchaseOrders); ?>;
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded');
        console.log('Purchase Orders:', purchaseOrders);

        function normalizeText(text) {
    return (text || '').toString().toLowerCase().trim();
}

        // Handle product selection and price calculation
        function setupProductRow(row) {
            const productSelect = row.querySelector('.product-select');
            const quantityInput = row.querySelector('.quantity-input');
            const priceInput = row.querySelector('.price-input');
            const subtotalInput = row.querySelector('.subtotal-input');

            function updateSubtotal() {
                const quantity = parseFloat(quantityInput.value) || 0;
                const price = parseFloat(priceInput.value) || 0;
                subtotalInput.value = (quantity * price).toFixed(2);
                updateTotalAmount();
            }

            productSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const price = selectedOption.getAttribute('data-price');
                priceInput.value = price;
                updateSubtotal();
            });

            quantityInput.addEventListener('input', updateSubtotal);
            priceInput.addEventListener('input', updateSubtotal);
        }

        // Filter function
function filterTable() {
    const searchValue = normalizeText(document.getElementById('poSearch').value);
    const supplierValue = document.getElementById('supplierFilter').value;
    const statusValue = document.getElementById('statusFilter').value;

    document.querySelectorAll('tbody tr').forEach(row => {
        let show = true;

        // Search by PO ID, Supplier, or Items
        const poId = normalizeText(row.children[0]?.textContent);
        const supplier = normalizeText(row.children[1]?.textContent);
        const items = normalizeText(row.children[3]?.textContent);

        if (searchValue) {
            show = poId.includes(searchValue) ||
                   supplier.includes(searchValue) ||
                   items.includes(searchValue);
        }

        // Filter by supplier
        if (show && supplierValue) {
            // SupplierID is in a data attribute for easier matching
            const rowSupplierId = row.getAttribute('data-supplier-id');
            if (rowSupplierId !== supplierValue) show = false;
        }

        // Filter by status
        if (show && statusValue && statusValue !== 'all') {
            const status = normalizeText(row.children[5]?.textContent);
            if (!status.includes(normalizeText(statusValue))) show = false;
        }

        row.style.display = show ? '' : 'none';
    });
}

// Attach event listeners
document.getElementById('poSearch').addEventListener('input', filterTable);
document.getElementById('supplierFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

// Add data-supplier-id attribute to each row for supplier filtering
document.querySelectorAll('tbody tr').forEach(row => {
    // The supplier ID is not in the table, so we need to add it from purchaseOrders
    const poId = row.children[0]?.textContent;
    const po = purchaseOrders.find(p => p.Pur_OrderID == poId);
    if (po) {
        row.setAttribute('data-supplier-id', po.SupplierID);
    }
});

        

        // Setup initial product row
        setupProductRow(document.querySelector('.product-item'));

        // Add new product row
        document.getElementById('addProductBtn').addEventListener('click', function() {
            const productList = document.getElementById('productList');
            const newRow = document.querySelector('.product-item').cloneNode(true);
            
            // Clear values
            newRow.querySelector('.product-select').value = '';
            newRow.querySelector('.quantity-input').value = '';
            newRow.querySelector('.price-input').value = '';
            newRow.querySelector('.subtotal-input').value = '';
            
            productList.appendChild(newRow);
            setupProductRow(newRow);
        });

        // Remove product row
        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-product')) {
                const productList = document.getElementById('productList');
                if (productList.children.length > 1) {
                    e.target.closest('.product-item').remove();
                    updateTotalAmount();
                }
            }
        });

        // Update total amount
        function updateTotalAmount() {
            const subtotals = Array.from(document.querySelectorAll('.subtotal-input'))
                .map(input => parseFloat(input.value) || 0);
            const total = subtotals.reduce((sum, subtotal) => sum + subtotal, 0);
            document.getElementById('totalAmount').value = total.toFixed(2);
        }

        // Save Purchase Order
        document.getElementById('savePOBtn').addEventListener('click', function() {
            const form = document.getElementById('newPOForm');
            const supplierId = document.getElementById('supplierSelect').value;
            const orderDate = document.getElementById('poDate').value;
            const totalAmount = document.getElementById('totalAmount').value;

            // Collect items
            const items = [];
            document.querySelectorAll('.product-item').forEach(row => {
                const productSelect = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                const priceInput = row.querySelector('.price-input');

                if (productSelect.value && quantityInput.value && priceInput.value) {
                    items.push({
                        product_id: productSelect.value,
                        quantity: quantityInput.value,
                        price: priceInput.value
                    });
                }
            });

            // Validate form
            if (!supplierId || !orderDate || items.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Please fill in all required fields and add at least one product.'
                });
                return;
            }

            // Send data to server
            const requestData = {
                supplier_id: supplierId,
                order_date: orderDate,
                total_amount: totalAmount,
                items: items
            };

            console.log('Sending request:', requestData);

            fetch('purchase_orders/save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(requestData)
            })
            .then(async response => {
                console.log('Response status:', response.status);
                const text = await response.text();
                console.log('Raw response:', text);
                
                if (!text) {
                    throw new Error('Server returned empty response');
                }
                
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', e);
                    throw new Error('Server returned invalid JSON: ' + text);
                }
            })
            .then(data => {
                console.log('Parsed response:', data);
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Purchase order has been saved successfully!'
                    }).then(() => {
                        // Close modal and refresh page
                        const modal = bootstrap.Modal.getInstance(document.getElementById('newPOModal'));
                        modal.hide();
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to save purchase order.',
                        footer: data.error_details || ''
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred while saving the purchase order.',
                    footer: error.toString()
                });
            });
        });

        // View PO Modal
        document.querySelectorAll('button[data-bs-target="#viewPOModal"]').forEach(btn => {
            btn.addEventListener('click', function() {
                const poId = this.getAttribute('data-po-id');
                const po = purchaseOrders.find(p => p.Pur_OrderID == poId);
                if (!po) return;

                // Basic PO Information
                document.getElementById('viewPONumber').textContent = po.Pur_OrderID ?? '';
                document.getElementById('viewPODate').textContent = po.PO_Order_Date ?? '';
                document.getElementById('viewPOStatus').textContent = po.PO_Order_Stat ?? '';
                document.getElementById('viewPOSupplier').textContent = po.Sup_Name ?? '';
                document.getElementById('viewPOSupplierContact').textContent = po.Sup_CoInfo ?? '';
                document.getElementById('viewPOSupplierDelivery').textContent = po.Sup_DeSched ?? '';
                document.getElementById('viewPOPaymentTerms').textContent = po.PO_Payment_Terms ?? '';
                document.getElementById('viewPONotes').textContent = po.PO_Notes ?? '';
                document.getElementById('viewPOTotal').textContent = po.PO_Total_Amount ? '₱' + parseFloat(po.PO_Total_Amount).toFixed(2) : '₱0.00';
                
                // Items with current stock
                let itemsHtml = '';
                if (po.items_list) {
                    const items = po.items_list.split(', ');
                    items.forEach(item => {
                        const [itemName, itemDetails] = item.split(' (');
                        const quantity = itemDetails ? itemDetails.replace(')', '') : '';
                        const currentStock = po.current_stock?.[itemName] || 0;
                        const unitPrice = po.item_prices?.[itemName] || 0;
                        const total = parseFloat(quantity) * parseFloat(unitPrice);
                        
                        itemsHtml += `<tr>
                            <td>${itemName}</td>
                            <td>${quantity}</td>
                            <td>₱${parseFloat(unitPrice).toFixed(2)}</td>
                            <td>₱${total.toFixed(2)}</td>
                            <td>
                                <span class="badge bg-${currentStock > 0 ? 'success' : 'danger'}">
                                    ${currentStock} in stock
                                </span>
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('viewPOItems').innerHTML = itemsHtml;

                // Inventory History
                if (po.inventory_history && po.inventory_history.length > 0) {
                    let historyHtml = '';
                    po.inventory_history.forEach(history => {
                        const changeDate = new Date(history.change_date).toLocaleString();
                        const changeColor = history.quantity_change > 0 ? 'success' : 'danger';
                        const changePrefix = history.quantity_change > 0 ? '+' : '';
                        
                        historyHtml += `
                            <div class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${history.product_name}</h6>
                                    <small class="text-muted">${changeDate}</small>
                                </div>
                                <p class="mb-1">
                                    <span class="badge bg-${changeColor}">
                                        ${changePrefix}${history.quantity_change}
                                    </span>
                                    <span class="ms-2">
                                        New Stock Level: ${history.new_stock_level}
                                    </span>
                                </p>
                                <small class="text-muted">${history.description}</small>
                            </div>`;
                    });
                    document.getElementById('viewPOInventoryHistory').innerHTML = historyHtml;
                } else {
                    document.getElementById('viewPOInventoryHistory').innerHTML = `
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i>
                            No inventory history available for this purchase order.
                        </div>`;
                }
            });
        });

        // Handle Mark as Delivered button clicks
        const markDeliveredButtons = document.querySelectorAll('.mark-delivered');
        console.log('Mark Delivered Buttons:', markDeliveredButtons.length);

        markDeliveredButtons.forEach(button => {
            console.log('Setting up button for PO:', button.getAttribute('data-po-id'));
            button.addEventListener('click', function() {
                const poId = this.getAttribute('data-po-id');
                console.log('Mark as Delivered clicked for PO:', poId);
                
                Swal.fire({
                    title: 'Mark as Delivered?',
                    html: `
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <strong>Warning:</strong> This action will:
                            <ul class="text-start mt-2">
                                <li>Update the purchase order status to "Delivered"</li>
                                <li>Add the ordered quantities to the current stock of each product</li>
                                <li>This action cannot be undone</li>
                            </ul>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, mark as delivered',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('User confirmed delivery for PO:', poId);
                        // Send request to mark as delivered
                        fetch('purchase_orders/mark_delivered.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                po_id: poId
                            })
                        })
                        .then(async response => {
                            console.log('Response status:', response.status);
                            const text = await response.text();
                            console.log('Raw response:', text);
                            
                            if (!text) {
                                throw new Error('Server returned empty response');
                            }
                            
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Failed to parse JSON:', e);
                                throw new Error('Server returned invalid JSON: ' + text);
                            }
                        })
                        .then(data => {
                            console.log('Parsed response:', data);
                            if (data.success) {
                                Swal.fire({
                                    title: 'Success!',
                                    text: data.message || 'Purchase order has been marked as delivered and stock has been updated.',
                                    icon: 'success'
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message || 'Failed to mark purchase order as delivered.',
                                    icon: 'error',
                                    footer: data.error_details || ''
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                title: 'Error!',
                                text: 'An error occurred while processing your request.',
                                icon: 'error',
                                footer: error.toString()
                            });
                        });
                    }
                });
            });
        });
    });
    </script>
</body>
</html>