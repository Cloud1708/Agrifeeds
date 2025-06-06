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

if (isset($_POST['add_product'])) {

  $productName = $_POST['productName'];
  $category = $_POST['category'];
  $description = $_POST['description'];
  $price = $_POST['price'];
  $stock = $_POST['stock'];
  $productID = $con->addProduct($productName, $category, $description, $price, $stock);

  if ($productID) {
    $_SESSION['sweetAlertConfig'] = "
    <script>
    Swal.fire({
        icon: 'success',
        title: 'Product Added Successfully',
        text: 'A new product has been added!',
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

// Handle Edit Product
if (isset($_POST['edit_product'])) {
    $id = $_POST['productID'];
    $name = $_POST['productName'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $result = $con->updateProduct($name, $category, $description, $price, $stock, $id);

    if ($result) {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Product Updated',
            text: 'Product updated successfully!',
            confirmButtonText: 'OK'
        });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: 'Failed to update product.',
            confirmButtonText: 'OK'
        });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['delete']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $result = $con->deleteProduct($id);

    if ($result) {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Product Deleted',
            text: 'Product deleted successfully!',
            confirmButtonText: 'OK'
        });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Delete Failed',
            text: 'Failed to delete product.',
            confirmButtonText: 'OK'
        });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

$allProducts = $con->viewProducts();
$totalProducts = count($allProducts);

$lowStockItems = 0;
$outOfStock = 0;
$activeProducts = 0;

foreach ($allProducts as $prod) {
    if ($prod['Prod_Stock'] == 0) {
        $outOfStock++;
    } elseif ($prod['Prod_Stock'] > 0 && $prod['Prod_Stock'] <= 10) {
        $lowStockItems++;
        $activeProducts++;
    } else {
        $activeProducts++;
    }
}


?>
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
    <?php include '../includes/sidebar.php';  ?>

    <!-- Main Content -->
    <div class="main-content">
        
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
            
            $data = $con->viewProducts();
            foreach ($data as $rows) {

                if ($rows['Prod_Stock'] == 0) {
                            $statusClass = 'bg-danger';
                            $status = 'Out of Stock';
                        } elseif ($rows['Prod_Stock'] > 0 && $rows['Prod_Stock'] <= 10) {
                            $statusClass = 'bg-warning text-dark';
                            $status = 'Low Stock';
                        } else {
                            $statusClass = 'bg-success';
                            $status = 'In Stock';
                        }

            ?>

              <tr>
                <td><?php echo $rows['ProductID']?></td>
                <td><?php echo $rows['Prod_Name']?></td>
                <td><?php echo $rows['Prod_Cat']?></td>
                <td><?php echo $rows['Prod_Desc']?></td>
                <td>₱<?php echo number_format($rows['Prod_Price'], 2); ?></td>
                <td><?php echo $rows['Prod_Stock']?></td>
                <td><span class="badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                <td>
  <div class="btn-group" role="group">
    <!-- EDIT BUTTON -->
    <button 
        type="button" 
        class="btn btn-warning btn-sm editProductBtn"
        data-id="<?php echo $rows['ProductID']; ?>"
        data-name="<?php echo htmlspecialchars($rows['Prod_Name']); ?>"
        data-category="<?php echo htmlspecialchars($rows['Prod_Cat']); ?>"
        data-description="<?php echo htmlspecialchars($rows['Prod_Desc']); ?>"
        data-price="<?php echo $rows['Prod_Price']; ?>"
        data-stock="<?php echo $rows['Prod_Stock']; ?>"
        data-bs-toggle="modal" 
        data-bs-target="#editProductModal"
    >
      <i class="bi bi-pencil-square"></i>
    </button>
    <!-- DELETE BUTTON -->
    <form method="POST" class="mx-1" style="display:inline;">
      <input type="hidden" name="id" value="<?php echo $rows['ProductID']; ?>">
      <button type="submit" name="delete" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">
        <i class="bi bi-x-square"></i>
      </button>
    </form>
  </div>
</td>
              </tr>

              <?php
            }
            ?>
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
                    <form id="addProductForm" method="POST">
                        <input type="hidden" name="add_product" value="1">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="productName" required>
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
                            <button type="submit" name="add_products" class="btn btn-primary">Save Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form id="editProductForm" method="POST">
            <input type="hidden" name="edit_product" value="1">
            <div class="modal-header">
              <h5 class="modal-title" id="editProductModalLabel">Edit Product</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label for="editProductName" class="form-label">Product Name</label>
                <input type="text" class="form-control" id="editProductName" name="productName" required>
              </div>
              <div class="mb-3">
                <label for="editCategory" class="form-label">Category</label>
                <select class="form-select" id="editCategory" name="category" required>
                  <option value="">Select Category</option>
                  <option value="feed">Animal Feed</option>
                  <option value="supplements">Supplements</option>
                  <option value="equipment">Equipment</option>
                  <option value="accessories">Accessories</option>
                </select>
              </div>
              <div class="mb-3">
                <label for="editDescription" class="form-label">Description</label>
                <textarea class="form-control" id="editDescription" name="description" rows="3"></textarea>
              </div>
              <div class="mb-3">
                <label for="editPrice" class="form-label">Price</label>
                <div class="input-group">
                  <span class="input-group-text">₱</span>
                  <input type="number" class="form-control" id="editPrice" name="price" min="0" step="0.01" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="editStock" class="form-label">Stock</label>
                <input type="number" class="form-control" id="editStock" name="stock" min="0" required>
              </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="productID" id="editProductID">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Update Product</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Custom JS -->

    <script>
    // Fill Edit Modal with product data
    document.querySelectorAll('.editProductBtn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('editProductID').value = this.dataset.id;
            document.getElementById('editProductName').value = this.dataset.name;
            document.getElementById('editCategory').value = this.dataset.category;
            document.getElementById('editDescription').value = this.dataset.description;
            document.getElementById('editPrice').value = this.dataset.price;
            document.getElementById('editStock').value = this.dataset.stock;
        });
    });
    </script>

    <script>
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
<?php echo $sweetAlertConfig; ?>
</body>
</html>
