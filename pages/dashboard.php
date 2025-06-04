<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Dashboard</title>
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
            <h1>Dashboard</h1>
            <div>
                <button class="btn btn-primary me-2" id="refreshBtn">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
                <button class="btn btn-secondary" id="exportBtn">
                    <i class="bi bi-download"></i> Export
                </button>
            </div>
        </div>
        <!-- Quick Actions (moved to top) -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2 justify-content-start align-items-center">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newSaleModal">
                        <i class="bi bi-cart-plus"></i> New Sale
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#newPurchaseModal">
                        <i class="bi bi-bag-plus"></i> New Purchase
                    </button>
                    <button class="btn btn-info" data-bs-toggle="modal" data-bs-target="#newCustomerModal">
                        <i class="bi bi-person-plus"></i> New Customer
                    </button>
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#newProductModal">
                        <i class="bi bi-box-seam"></i> New Product
            </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Sales</h5>
                        <p class="card-text" id="totalSales">$0.00</p>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Orders</h5>
                        <p class="card-text" id="totalOrders">0</p>
                        <small class="text-muted">Last 30 days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Customers</h5>
                        <p class="card-text" id="totalCustomers">0</p>
                        <small class="text-muted">Active customers</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <p class="card-text" id="totalProducts">0</p>
                        <small class="text-muted">In stock</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Sales Overview</h5>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Top Products</h5>
                        <canvas id="productsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity and Low Stock -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Orders</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recentOrdersTable">
                                    <!-- Table content will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Alerts</h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Current Stock</th>
                                        <th>Threshold</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="lowStockTable">
                                    <!-- Table content will be populated by JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
    <!-- Quick Actions Modals -->
    <!-- New Sale Modal -->
    <div class="modal fade" id="newSaleModal" tabindex="-1" aria-labelledby="newSaleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newSaleModalLabel">New Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newSaleForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="customerSelect" class="form-label">Customer</label>
                                <select class="form-select" id="customerSelect" required>
                                    <option value="">Select Customer</option>
                                    <!-- Customer options will be populated by JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="saleDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="saleDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Products</label>
                            <div id="productList">
                                <div class="row mb-2 product-item">
                                    <div class="col-md-4">
                                        <select class="form-select product-select" required>
                                            <option value="">Select Product</option>
                                            <!-- Product options will be populated by JavaScript -->
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control quantity-input" 
                                               placeholder="Qty" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <input type="number" class="form-control price-input" 
                                               placeholder="Price" step="0.01" min="0" readonly>
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
                                <label for="paymentMethod" class="form-label">Payment Method</label>
                                <select class="form-select" id="paymentMethod" required>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="saleStatus" class="form-label">Status</label>
                                <select class="form-select" id="saleStatus" required>
                                    <option value="completed">Completed</option>
                                    <option value="pending">Pending</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="discount" class="form-label">Discount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="discount" 
                                               step="0.01" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="totalAmount" class="form-label">Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
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
                    <button type="button" class="btn btn-primary" id="saveSaleBtn">Save Sale</button>
                </div>
            </div>
        </div>
    </div>
    <!-- New Purchase Modal -->
    <div class="modal fade" id="newPurchaseModal" tabindex="-1" aria-labelledby="newPurchaseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newPurchaseModalLabel">New Purchase Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newPOForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="supplierSelect" class="form-label">Supplier</label>
                                <select class="form-select" id="supplierSelect" required>
                                    <option value="">Select Supplier</option>
                                    <!-- Supplier options will be populated by JavaScript -->
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="poDate" class="form-label">Order Date</label>
                                <input type="date" class="form-control" id="poDate" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Products</label>
                            <div id="productList">
                                <div class="row mb-2 product-item">
                                    <div class="col-md-4">
                                        <select class="form-select product-select" required>
                                            <option value="">Select Product</option>
                                            <!-- Product options will be populated by JavaScript -->
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
                                    <option value="immediate">Immediate</option>
                                    <option value="net15">Net 15</option>
                                    <option value="net30">Net 30</option>
                                    <option value="net60">Net 60</option>
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
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="shippingCost" 
                                               step="0.01" min="0" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="totalAmount" class="form-label">Total Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
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
    <!-- New Customer Modal -->
    <div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCustomerModalLabel">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addCustomerForm">
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="customerName" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerType" class="form-label">Customer Type</label>
                            <select class="form-select" id="customerType" required>
                                <option value="">Select Type</option>
                                <option value="retail">Retail</option>
                                <option value="wholesale">Wholesale</option>
                                <option value="farm">Farm</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="taxId" class="form-label">Tax ID</label>
                            <input type="text" class="form-control" id="taxId">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="loyaltyProgram">
                                <label class="form-check-label" for="loyaltyProgram">
                                    Enroll in Loyalty Program
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveCustomerBtn">Save Customer</button>
                </div>
            </div>
        </div>
    </div>
    <!-- New Product Modal -->
    <div class="modal fade" id="newProductModal" tabindex="-1" aria-labelledby="newProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" required>
                                <option value="">Select Category</option>
                                <option value="feed">Animal Feed</option>
                                <option value="supplements">Supplements</option>
                                <option value="equipment">Equipment</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="sku" class="form-label">SKU</label>
                            <input type="text" class="form-control" id="sku" required>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" id="stock" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="unit" class="form-label">Unit</label>
                            <select class="form-select" id="unit" required>
                                <option value="">Select Unit</option>
                                <option value="kg">Kilogram (kg)</option>
                                <option value="g">Gram (g)</option>
                                <option value="l">Liter (L)</option>
                                <option value="ml">Milliliter (ml)</option>
                                <option value="pcs">Pieces (pcs)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="productImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="productImage" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="activeStatus" checked>
                                <label class="form-check-label" for="activeStatus">
                                    Active Product
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveProductBtn">Save Product</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="../js/scripts.js"></script>
</body>
</html> 