<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriFeeds - Products</title>
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
    <?php
    include '../includes/db.php';
    
    // Handle product update
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
        $id = $_POST['id'];
        $name = $_POST['name'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];

        if (updateProduct($id, $name, $category, $description, $price, $stock)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Product updated successfully!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location = 'products.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to update product. Please try again.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location = 'products.php';
                });
            </script>";
        }
    }

    // Handle adding new product
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $description = $_POST['description'];
        $price = $_POST['price'];
        $stock = $_POST['stock'];

        if (addProduct($name, $category, $description, $price, $stock)) {
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Product added successfully!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location = 'products.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Failed to add product. Please try again.',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location = 'products.php';
                });
            </script>";
        }
    }

    $products = viewProducts();
    // Calculate summary counts at the top so they are available for the cards
    $totalProducts = count($products);
    $lowStockItems = 0;
    $outOfStock = 0;
    $activeProducts = 0;
    foreach ($products as $product) {
        if ($product['Prod_Stock'] == 0) {
            $outOfStock++;
        }
        if ($product['Prod_Stock'] <= 10) {
            $lowStockItems++;
        }
        if ($product['Prod_Stock'] > 0) {
            $activeProducts++;
        }
    }
    ?>

    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        <?php if (isset($_GET['success'])) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Product updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        <?php if (isset($_GET['error'])) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Error updating product. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Products</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-lg"></i> Add Product
            </button>
        </div>

        <!-- Product Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Total Products</h5>
                        <p class="card-text" id="totalProducts"><?php echo $totalProducts; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Low Stock Items</h5>
                        <p class="card-text" id="lowStockItems"><?php echo $lowStockItems; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Out of Stock</h5>
                        <p class="card-text" id="outOfStock"><?php echo $outOfStock; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Products</h5>
                        <p class="card-text" id="activeProducts"><?php echo $activeProducts; ?></p>
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
                    <input type="text" class="form-control" id="productSearch" 
                           placeholder="Search products..." aria-label="Search products">
                </div>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="categoryFilter">
                    <option value="all">All Categories</option>
                    <option value="feed">Animal Feed</option>
                    <option value="supplements">Supplements</option>
                    <option value="equipment">Equipment</option>
                    <option value="accessories">Accessories</option>
                </select>
            </div>
            <div class="col-md-3">
                <select class="form-select" id="stockFilter">
                    <option value="all">All Stock Status</option>
                    <option value="in_stock">In Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
        </div>

        <!-- Products Table -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($products as $product) {
                        // Status logic
                        if ($product['Prod_Stock'] == 0) {
                            $statusClass = 'bg-danger';
                            $status = 'Out of Stock';
                        } elseif ($product['Prod_Stock'] > 0 && $product['Prod_Stock'] <= 10) {
                            $statusClass = 'bg-warning text-dark';
                            $status = 'Low Stock';
                        } else {
                            $statusClass = 'bg-success';
                            $status = 'In Stock';
                        }
                    ?>
                        <tr>
                            <td><?php echo $product['ProductID']; ?></td>
                            <td><?php echo $product['Prod_Name']; ?></td>
                            <td><?php echo $product['Prod_Cat']; ?></td>
                            <td><?php echo $product['Prod_Desc']; ?></td>
                            <td>₱<?php echo number_format($product['Prod_Price'], 2); ?></td>
                            <td><?php echo $product['Prod_Stock']; ?></td>
                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#historyModal<?php echo $product['ProductID']; ?>">
                                        <i class="bi bi-clock-history"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $product['ProductID']; ?>">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <form method="POST" class="mx-1">
                                        <input type="hidden" name="id" value="<?php echo $product['ProductID']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">
                                            <i class="bi bi-x-square"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- History Modal -->
                                <div class="modal fade" id="historyModal<?php echo $product['ProductID']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Product History</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <h6>Created</h6>
                                                    <p>Date: <?php echo date('F j, Y g:i a', strtotime($product['UserID'])); ?></p>
                                                </div>
                                                <?php if ($product['Prod_Updated_at']) { ?>
                                                <div class="mb-3">
                                                    <h6>Last Updated</h6>
                                                    <p>Date: <?php echo date('F j, Y g:i a', strtotime($product['Prod_Updated_at'])); ?></p>
                                                </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Edit Product Modal -->
                                <div class="modal fade" id="editProductModal<?php echo $product['ProductID']; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Product</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <form id="editProductForm<?php echo $product['ProductID']; ?>" method="POST" onsubmit="return handleUpdate(event, <?php echo $product['ProductID']; ?>)">
                                                    <input type="hidden" name="update_product" value="1">
                                                    <input type="hidden" name="id" value="<?php echo $product['ProductID']; ?>">
                                                    <div class="mb-3">
                                                        <label for="editName<?php echo $product['ProductID']; ?>" class="form-label">Product Name</label>
                                                        <input type="text" class="form-control" id="editName<?php echo $product['ProductID']; ?>" 
                                                               name="name" value="<?php echo $product['Prod_Name']; ?>" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="editCategory<?php echo $product['ProductID']; ?>" class="form-label">Category</label>
                                                        <select class="form-select" id="editCategory<?php echo $product['ProductID']; ?>" name="category" required>
                                                            <option value="feed" <?php echo $product['Prod_Cat'] === 'feed' ? 'selected' : ''; ?>>Animal Feed</option>
                                                            <option value="supplements" <?php echo $product['Prod_Cat'] === 'supplements' ? 'selected' : ''; ?>>Supplements</option>
                                                            <option value="equipment" <?php echo $product['Prod_Cat'] === 'equipment' ? 'selected' : ''; ?>>Equipment</option>
                                                            <option value="accessories" <?php echo $product['Prod_Cat'] === 'accessories' ? 'selected' : ''; ?>>Accessories</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="editDescription<?php echo $product['ProductID']; ?>" class="form-label">Description</label>
                                                        <textarea class="form-control" id="editDescription<?php echo $product['ProductID']; ?>" 
                                                                  name="description" rows="3"><?php echo $product['Prod_Desc']; ?></textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="editPrice<?php echo $product['ProductID']; ?>" class="form-label">Price</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">₱</span>
                                                            <input type="number" class="form-control" id="editPrice<?php echo $product['ProductID']; ?>" 
                                                                   name="price" value="<?php echo $product['Prod_Price']; ?>" min="0" step="0.01" required>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="editStock<?php echo $product['ProductID']; ?>" class="form-label">Stock</label>
                                                        <input type="number" class="form-control" id="editStock<?php echo $product['ProductID']; ?>" 
                                                               name="stock" value="<?php echo $product['Prod_Stock']; ?>" min="0" required>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addProductForm" method="POST" onsubmit="return handleAdd(event)">
                        <input type="hidden" name="add_product" value="1">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category</option>
                                <option value="feed">Animal Feed</option>
                                <option value="supplements">Supplements</option>
                                <option value="equipment">Equipment</option>
                                <option value="accessories">Accessories</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Initial Stock</label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->
    <script>
    function handleUpdate(event, productId) {
        event.preventDefault();
        const form = document.getElementById('editProductForm' + productId);
        const formData = new FormData(form);

        fetch('products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editProductModal' + productId));
            modal.hide();

            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Product updated successfully!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Reload the page to show updated data
                window.location.reload();
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to update product. Please try again.',
                showConfirmButton: false,
                timer: 1500
            });
        });

        return false;
    }

    function handleAdd(event) {
        event.preventDefault();
        const form = document.getElementById('addProductForm');
        const formData = new FormData(form);

        fetch('products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addProductModal'));
            modal.hide();

            // Show success message
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Product added successfully!',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                // Reload the page to show updated data
                window.location.reload();
            });
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Failed to add product. Please try again.',
                showConfirmButton: false,
                timer: 1500
            });
        });

        return false;
    }

    // Custom search and sort for products table
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.querySelector('table.table');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        const searchInput = document.getElementById('productSearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const stockFilter = document.getElementById('stockFilter');
        let currentSort = { col: null, dir: 1 };

        // SEARCH & FILTER
        function filterRows() {
            const search = searchInput.value.toLowerCase();
            const category = categoryFilter.value;
            const stock = stockFilter.value;
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const name = cells[1].textContent.toLowerCase();
                const cat = cells[2].textContent.toLowerCase();
                const desc = cells[3].textContent.toLowerCase();
                const stockVal = parseInt(cells[5].textContent);
                let show = true;
                if (search && !(name.includes(search) || cat.includes(search) || desc.includes(search))) {
                    show = false;
                }
                if (category !== 'all' && cat !== category) {
                    show = false;
                }
                if (stock !== 'all') {
                    if (stock === 'in_stock' && !(stockVal > 10)) show = false;
                    if (stock === 'low_stock' && !(stockVal > 0 && stockVal <= 10)) show = false;
                    if (stock === 'out_of_stock' && stockVal !== 0) show = false;
                }
                row.style.display = show ? '' : 'none';
            });
        }
        searchInput.addEventListener('input', filterRows);
        categoryFilter.addEventListener('change', filterRows);
        stockFilter.addEventListener('change', filterRows);

        // SORTING
        table.querySelectorAll('th').forEach((th, idx) => {
            if (idx === 7) return; // Skip Actions column
            th.style.cursor = 'pointer';
            th.addEventListener('click', function() {
                let dir = 1;
                if (currentSort.col === idx) dir = -currentSort.dir;
                currentSort = { col: idx, dir };
                rows.sort((a, b) => {
                    let aText = a.children[idx].textContent.trim();
                    let bText = b.children[idx].textContent.trim();
                    // Numeric sort for price, stock, id
                    if (idx === 0 || idx === 4 || idx === 5) {
                        aText = parseFloat(aText.replace(/[^\d.\-]/g, ''));
                        bText = parseFloat(bText.replace(/[^\d.\-]/g, ''));
                        if (isNaN(aText)) aText = 0;
                        if (isNaN(bText)) bText = 0;
                        return dir * (aText - bText);
                    }
                    // Text sort
                    return dir * aText.localeCompare(bText);
                });
                // Re-append sorted rows
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
    </script>
</body>
</html> 