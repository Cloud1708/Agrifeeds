<?php
require_once('../includes/db.php');
$con = new database();
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Get user information
$userID = $_SESSION['user_id'];
$userInfo = $con->getUserInfo($userID);
$customerInfo = $con->getCustomerInfo($userID);

// Get all available products
$products = $con->getAllProducts();
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
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php" class="sidebar-brand">AgriFeeds</a>
        <div class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="products.php">
                        <i class="bi bi-box me-2"></i> View Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="bi bi-cart me-2"></i> My Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="perks.php">
                        <i class="bi bi-gift me-2"></i> My Perks
                    </a>
                </li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="profile.php">
                        <i class="bi bi-person-circle me-2"></i> Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Available Products</h1>
            <div class="d-flex gap-2">
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control" id="searchProduct" placeholder="Search products...">
                </div>
                <select class="form-select" id="categoryFilter" style="width: auto;">
                    <option value="">All Categories</option>
                    <option value="feed">Feed</option>
                    <option value="supplements">Supplements</option>
                    <option value="accessories">Accessories</option>
                </select>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4" id="productsGrid">
            <?php foreach ($products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                         style="height: 200px;">
                        <i class="bi bi-box text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['Prod_Name']); ?></h5>
                        <p class="card-text text-muted">
                            <?php echo htmlspecialchars($product['Prod_Desc']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0">₱<?php echo number_format($product['Prod_Price'], 2); ?></span>
                            <span class="badge bg-<?php echo $product['Prod_Stock'] > 0 ? 'success' : 'danger'; ?>">
                                <?php echo $product['Prod_Stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="input-group" style="width: 130px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity(this)">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <input type="number" class="form-control text-center" value="1" min="1" 
                                       max="<?php echo $product['Prod_Stock']; ?>" 
                                       onchange="validateQuantity(this, <?php echo $product['Prod_Stock']; ?>)">
                                <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity(this)">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                            <button class="btn btn-primary" onclick="addToCart(<?php echo $product['ProductID']; ?>, this)"
                                    <?php echo $product['Prod_Stock'] <= 0 ? 'disabled' : ''; ?>>
                                <i class="bi bi-cart-plus"></i> Buy Now
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script>
        function incrementQuantity(button) {
            const input = button.parentElement.querySelector('input');
            const max = parseInt(input.getAttribute('max'));
            const currentValue = parseInt(input.value);
            if (currentValue < max) {
                input.value = currentValue + 1;
            }
        }

        function decrementQuantity(button) {
            const input = button.parentElement.querySelector('input');
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue - 1;
            }
        }

        function validateQuantity(input, max) {
            let value = parseInt(input.value);
            if (isNaN(value) || value < 1) {
                input.value = 1;
            } else if (value > max) {
                input.value = max;
            }
        }

        function addToCart(productId, button) {
            const card = button.closest('.card');
            const quantity = parseInt(card.querySelector('input[type="number"]').value);
            
            // Add to cart logic here
            console.log(`Adding product ${productId} to cart with quantity ${quantity}`);
            // You can implement the actual cart functionality later
        }

        // Search and filter functionality
        document.getElementById('searchProduct').addEventListener('input', filterProducts);
        document.getElementById('categoryFilter').addEventListener('change', filterProducts);

        function filterProducts() {
            const searchTerm = document.getElementById('searchProduct').value.toLowerCase();
            const category = document.getElementById('categoryFilter').value.toLowerCase();
            const products = document.querySelectorAll('#productsGrid .col');

            products.forEach(product => {
                const title = product.querySelector('.card-title').textContent.toLowerCase();
                const description = product.querySelector('.card-text').textContent.toLowerCase();
                const productCategory = product.getAttribute('data-category') || '';

                const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                const matchesCategory = !category || productCategory === category;

                product.style.display = matchesSearch && matchesCategory ? '' : 'none';
            });
        }
    </script>
</body>
</html> 