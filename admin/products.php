<?php
// Set timezone to Manila
date_default_timezone_set('Asia/Manila');

session_start();
 
require_once('../includes/db.php');
$con = new database();
$sweetAlertConfig = "";

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 3])) { // 1 for admin, 3 for super admin
    header('Location: ../index.php');
    exit();
}

$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
$totalProducts = $con->getTotalProducts();
$totalPages = ceil($totalProducts / $perPage);
$allProducts = $con->getPaginatedProducts($currentPage, $perPage);

// Handle Discontinue Product
if (isset($_POST['discontinue']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $result = $con->discontinueProduct($id);
 
    if ($result) {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Product Discontinued',
            text: 'Product has been marked as discontinued!',
            confirmButtonText: 'OK'
        });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Operation Failed',
            text: 'Failed to discontinue product.',
            confirmButtonText: 'OK'
        });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle Restore Product
if (isset($_POST['restore']) && isset($_POST['id'])) {
    $id = $_POST['id'];
    $result = $con->restoreProduct($id);
 
    if ($result) {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'success',
            title: 'Product Restored',
            text: 'Product has been restored successfully!',
            confirmButtonText: 'OK'
        });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "
        <script>
        Swal.fire({
            icon: 'error',
            title: 'Operation Failed',
            text: 'Failed to restore product.',
            confirmButtonText: 'OK'
        });
        </script>";
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
 
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
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        // Define the upload directory
        $uploadDir = '../uploads/product_images/';
        error_log("Attempting to upload to directory: " . $uploadDir);
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            error_log("Directory does not exist, attempting to create: " . $uploadDir);
            if (!mkdir($uploadDir, 0777, true)) {
                error_log("Failed to create directory: " . $uploadDir);
                $_SESSION['sweetAlertConfig'] = "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to create upload directory'
                    });
                </script>";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            error_log("Directory created successfully");
        }
        
        // Verify directory is writable
        if (!is_writable($uploadDir)) {
            error_log("Directory not writable: " . $uploadDir);
            $_SESSION['sweetAlertConfig'] = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Upload directory is not writable'
                });
            </script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['sweetAlertConfig'] = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.'
                });
            </script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        // Debug upload information
        error_log("File upload details:");
        error_log("Original name: " . $_FILES['product_image']['name']);
        error_log("Temporary path: " . $_FILES['product_image']['tmp_name']);
        error_log("Target path: " . $targetPath);
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
            // Set the relative path for database storage
            $imagePath = 'uploads/product_images/' . $fileName;
            error_log("Image uploaded successfully. Path: " . $imagePath);
            
            // Verify file exists after upload
            if (file_exists($targetPath)) {
                error_log("File exists after upload: " . $targetPath);
            } else {
                error_log("File does not exist after upload: " . $targetPath);
            }
        } else {
            $uploadError = error_get_last();
            error_log("Failed to upload image: " . ($uploadError ? $uploadError['message'] : 'Unknown error'));
            $_SESSION['sweetAlertConfig'] = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to upload image: " . addslashes($uploadError ? $uploadError['message'] : 'Unknown error') . "'
                });
            </script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    // Debug the image path before database insertion
    error_log("Image path before database insertion: " . ($imagePath ?? 'null'));
    
    $result = $con->addProduct($productName, $category, $description, $price, $stock, $imagePath);
    
    if ($result['success']) {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Product added successfully'
            });
        </script>";
    } else {
        $_SESSION['sweetAlertConfig'] = "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . addslashes($result['message']) . "'
            });
        </script>";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
 
// Handle Edit Product
if (isset($_POST['edit_product'])) {
    $id = $_POST['productID'];
    $name = $_POST['productName'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    // Always use the current price from the database
    $product = $con->getProductById($id);
    $price = $product['Prod_Price'];
    $addStock = $_POST['stock'];
    
    // Handle image upload
    $imagePath = null;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        // Define the upload directory
        $uploadDir = '../uploads/product_images/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                $_SESSION['sweetAlertConfig'] = "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to create upload directory'
                    });
                </script>";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        }
        
        // Verify directory is writable
        if (!is_writable($uploadDir)) {
            $_SESSION['sweetAlertConfig'] = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Upload directory is not writable'
                });
            </script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        $fileExtension = strtolower(pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            $_SESSION['sweetAlertConfig'] = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.'
                });
            </script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        
        $fileName = uniqid() . '.' . $fileExtension;
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetPath)) {
            // Set the relative path for database storage
            $imagePath = 'uploads/product_images/' . $fileName;
        } else {
            $uploadError = error_get_last();
            $_SESSION['sweetAlertConfig'] = "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to upload image: " . addslashes($uploadError ? $uploadError['message'] : 'Unknown error') . "'
                });
            </script>";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
    
    // Fetch current stock
    $currentStock = isset($product['Prod_Stock']) ? (int)$product['Prod_Stock'] : 0;
    $newStock = $currentStock + (int)$addStock;
    
    // Update product with or without new image
    $result = $con->updateProduct($name, $category, $description, $price, $newStock, $id, $imagePath);
 
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

$showDiscontinued = isset($_GET['show_discontinued']) && $_GET['show_discontinued'] == 1;
$allProducts = $con->viewProducts($showDiscontinued);
$totalProducts = count($allProducts);
 
$lowStockItems = 0;
$outOfStock = 0;
$activeProducts = 0;
$discontinuedProducts = 0;
 
foreach ($allProducts as $prod) {
    if ($prod['discontinued']) {
        $discontinuedProducts++;
        continue;
    }
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
            <div>
                <button class="btn btn-secondary me-2" data-bs-toggle="modal" data-bs-target="#discontinuedProductsModal">
                    <i class="bi bi-archive"></i> View Discontinued Products
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="bi bi-plus-lg"></i> Add Product
                </button>
            </div>
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
                        <th>Image</th>
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
                    foreach ($allProducts as $rows) {
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
                        <td>
                            <?php if (!empty($rows['Prod_Image'])): ?>
                                <img src="../<?php echo $rows['Prod_Image']; ?>" alt="Product Image" style="width:50px;height:50px;object-fit:cover;">
                            <?php else: ?>
                                <span class="text-muted">No Image</span>
                            <?php endif; ?>
                        </td>
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
                                    data-image="<?php echo htmlspecialchars($rows['Prod_Image']); ?>"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editProductModal"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <!-- DISCONTINUE BUTTON -->
                                <form method="POST" class="mx-1 discontinueProductForm" style="display:inline;">
                                    <input type="hidden" name="id" value="<?php echo $rows['ProductID']; ?>">
                                    <input type="hidden" name="discontinue" value="1">
                                    <button type="button" name="discontinueBtn" class="btn btn-secondary btn-sm discontinueProductBtn">
                                        <i class="bi bi-x-circle"></i>
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

<!-- Pagination -->
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-4 mb-4">
                <div class="d-flex align-items-center mb-3 mb-md-0">
                    <label for="perPage" class="me-2">Items per page:</label>
                    <select class="form-select form-select-sm" id="perPage" style="width: auto;">
                        <option value="10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo $perPage == 20 ? 'selected' : ''; ?>>20</option>
                        <option value="30" <?php echo $perPage == 30 ? 'selected' : ''; ?>>30</option>
                        <option value="50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
                    </select>
                </div>
                <nav aria-label="Page navigation" class="mx-auto">
                    <ul class="pagination mb-0 justify-content-center">
                        <!-- First Page -->
                        <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=1&per_page=<?php echo $perPage; ?>" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <!-- Previous Page -->
                        <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        <?php
                        $range = 2;
                        $startPage = max(1, $currentPage - $range);
                        $endPage = min($totalPages, $currentPage + $range);
                        if ($startPage > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?page=1&per_page=' . $perPage . '">1</a></li>';
                            if ($startPage > 2) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                        }
                        for($i = $startPage; $i <= $endPage; $i++) {
                            echo '<li class="page-item ' . ($currentPage == $i ? 'active' : '') . '">';
                            echo '<a class="page-link" href="?page=' . $i . '&per_page=' . $perPage . '">' . $i . '</a>';
                            echo '</li>';
                        }
                        if ($endPage < $totalPages) {
                            if ($endPage < $totalPages - 1) {
                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            }
                            echo '<li class="page-item"><a class="page-link" href="?page=' . $totalPages . '&per_page=' . $perPage . '">' . $totalPages . '</a></li>';
                        }
                        ?>
                        <!-- Next Page -->
                        <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>&per_page=<?php echo $perPage; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <!-- Last Page -->
                        <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $totalPages; ?>&per_page=<?php echo $perPage; ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
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
                    <form id="addProductForm" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="add_product" value="1">
                        <div class="mb-3">
                            <label for="productName" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="productName" name="productName" required>
                        </div>
                        <div class="mb-3">
                            <label for="productImage" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="productImage" name="product_image" accept="image/*">
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
          <form id="editProductForm" method="POST" enctype="multipart/form-data">
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
                <label for="editProductImage" class="form-label">Product Image</label>
                <input type="file" class="form-control" id="editProductImage" name="product_image" accept="image/*">
                <div class="mt-2">
                  <img id="currentProductImage" src="" alt="Current Product Image" style="max-width: 100px; max-height: 100px; display: none;">
                  <small class="text-muted">Leave empty to keep current image</small>
                </div>
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
                <label class="form-label">Price</label>
                <div class="input-group">
                  <span class="input-group-text">₱</span>
                  <input type="text" class="form-control" id="editPriceDisplay" name="price_display" readonly>
                  <button type="button" class="btn btn-outline-primary" id="openPriceModalBtn"><i class="bi bi-pencil"></i> Edit</button>
                </div>
              </div>
              <div class="mb-3">
                <label for="editStock" class="form-label">Add Stock</label>
                <input type="number" class="form-control" id="editStock" name="stock" min="0" value="0" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Current Stock</label>
                <input type="number" class="form-control" id="currentStockDisplay" value="" readonly>
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
   
    <!-- Discontinued Products Modal -->
    <div class="modal fade" id="discontinuedProductsModal" tabindex="-1" aria-labelledby="discontinuedProductsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="discontinuedProductsModalLabel">Discontinued Products</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Image</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $discontinuedProducts = $con->getDiscontinuedProducts();
                                foreach ($discontinuedProducts as $rows) {
                                ?>
                                <tr>
                                    <td><?php echo $rows['ProductID']?></td>
                                    <td>
                                        <?php if (!empty($rows['Prod_Image'])): ?>
                                            <img src="../<?php echo $rows['Prod_Image']; ?>" alt="Product Image" style="width:50px;height:50px;object-fit:cover;">
                                        <?php else: ?>
                                            <span class="text-muted">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $rows['Prod_Name']?></td>
                                    <td><?php echo $rows['Prod_Cat']?></td>
                                    <td><?php echo $rows['Prod_Desc']?></td>
                                    <td>₱<?php echo number_format($rows['Prod_Price'], 2); ?></td>
                                    <td><?php echo $rows['Prod_Stock']?></td>
                                    <td>
                                        <form method="POST" class="restoreProductForm" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo $rows['ProductID']; ?>">
                                            <input type="hidden" name="restore" value="1">
                                            <button type="button" name="restoreBtn" class="btn btn-success btn-sm restoreProductBtn">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
   
    <!-- New Price Edit Modal -->
    <div class="modal fade" id="editPriceModal" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <form id="editPriceForm" method="POST" action="update_price.php">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="editPriceModalLabel">Edit Product Price</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="productID" id="editPriceProductID">
              <div class="mb-3">
                <label for="newPrice" class="form-label">New Price</label>
                <div class="input-group">
                  <span class="input-group-text">₱</span>
                  <input type="number" class="form-control" id="newPrice" name="newPrice" min="0" step="0.01" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="effectiveFrom" class="form-label">Date Effective From</label>
                <input type="date" class="form-control" id="effectiveFrom" name="effectiveFrom" required>
              </div>
              <div class="mb-3">
                <label for="effectiveTo" class="form-label">Date Effective To <span class="text-muted">(optional)</span></label>
                <input type="date" class="form-control" id="effectiveTo" name="effectiveTo">
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-primary">Save Price</button>
            </div>
          </div>
        </form>
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
            document.getElementById('editPriceDisplay').value = this.dataset.price;
            document.getElementById('editStock').value = 0;
            document.getElementById('currentStockDisplay').value = this.dataset.stock;
            
            // Handle current product image
            const currentImage = document.getElementById('currentProductImage');
            const imagePath = this.dataset.image;
            if (imagePath) {
                currentImage.src = '../' + imagePath;
                currentImage.style.display = 'block';
            } else {
                currentImage.style.display = 'none';
            }
        });
    });

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
                const name = cells[2].textContent.toLowerCase();
                const cat = cells[3].textContent.toLowerCase();
                const desc = cells[4].textContent.toLowerCase();
                const stockVal = parseInt(cells[6].textContent);
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
            if (idx === 8) return; // Skip Actions column
            th.style.cursor = 'pointer';
            th.addEventListener('click', function() {
                let dir = 1;
                if (currentSort.col === idx) dir = -currentSort.dir;
                currentSort = { col: idx, dir };
                rows.sort((a, b) => {
                    let aText = a.children[idx].textContent.trim();
                    let bText = b.children[idx].textContent.trim();
                    // Numeric sort for price, stock, id
                    if (idx === 0 || idx === 5 || idx === 6) {
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

    // Handle Discontinue Product
    document.querySelectorAll('.discontinueProductBtn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = btn.closest('form');
            Swal.fire({
                title: 'Discontinue Product?',
                text: "This product will be marked as discontinued!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, discontinue it!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Handle Restore Product
    document.querySelectorAll('.restoreProductBtn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const form = btn.closest('form');
            Swal.fire({
                title: 'Restore Product?',
                text: "This product will be restored to active status!",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, restore it!',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Handle items per page change
document.getElementById('perPage').addEventListener('change', function() {
    window.location.href = '?page=1&per_page=' + this.value;
});

    // Add JS to open the price modal and prefill it
    document.getElementById('openPriceModalBtn').addEventListener('click', function() {
        document.getElementById('editPriceProductID').value = document.getElementById('editProductID').value;
        document.getElementById('newPrice').value = document.getElementById('editPriceDisplay').value;
        // Set today's date as default for effectiveFrom using Manila timezone
        const now = new Date();
        const manilaDate = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Manila' }));
        document.getElementById('effectiveFrom').value = manilaDate.toISOString().split('T')[0];
        document.getElementById('effectiveTo').value = '';
        var priceModal = new bootstrap.Modal(document.getElementById('editPriceModal'));
        priceModal.show();
    });
    </script>
    <?php echo $sweetAlertConfig; ?>
    
</body>
</html>