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
                    <!-- Will be populated from Suppliers table -->
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="statusFilter">
                    <option value="all">All Status</option>
                    <option value="Draft">Draft</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Received">Received</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="dateRangeFilter">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="custom">Custom Range</option>
                </select>
            </div>
        </div>

        <!-- Purchase Orders Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>PO Number</th>
                        <th>Supplier</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="poTableBody">
                    <!-- Table content will be populated by JavaScript -->
                </tbody>
            </table>
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
                                    <!-- Will be populated from Suppliers table -->
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
                                            <!-- Will be populated from Products table -->
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
                                <label for="expectedDelivery" class="form-label">Expected Delivery Date</label>
                                <input type="date" class="form-control" id="expectedDelivery" required>
                            </div>
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
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shippingCost" class="form-label">Shipping Cost</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="shippingCost" 
                                               step="0.01" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
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
    <div class="modal fade" id="viewPOModal" tabindex="-1" aria-labelledby="viewPOModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPOModalLabel">Purchase Order Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Purchase Order Information</h6>
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
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Supplier Information</h6>
                            <table class="table table-sm">
                                <tr>
                                    <th>Supplier:</th>
                                    <td id="viewPOSupplier"></td>
                                </tr>
                                <tr>
                                    <th>Contact:</th>
                                    <td id="viewPOSupplierContact"></td>
                                </tr>
                                <tr>
                                    <th>Payment Terms:</th>
                                    <td id="viewPOPaymentTerms"></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <h6>Order Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody id="viewPOItems">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Shipping Cost:</th>
                                    <td id="viewPOShippingCost"></td>
                                </tr>
                                <tr>
                                    <th colspan="3" class="text-end">Total Amount:</th>
                                    <td id="viewPOTotal"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <h6>Expected Delivery</h6>
                            <p id="viewPOExpectedDelivery"></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Notes</h6>
                            <p id="viewPONotes"></p>
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
    <!-- Custom JS -->
    
</body>
</html> 