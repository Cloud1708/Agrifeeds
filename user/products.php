<?php
// Prevent PHP errors from being displayed
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

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
 
// --- CART LOGIC (SESSION BASED) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    // Ensure no output before JSON response
    ob_clean();
    
    try {
        // Fetch product info from DB using product_id
        $prod = $con->getProductById($_POST['product_id']);
        if ($prod) {
            $cartItem = [
                'id' => $prod['ProductID'],
                'name' => $prod['Prod_Name'],
                'price' => $prod['Prod_Price'],
                'image' => $prod['Prod_Image'],
                'quantity' => (int)$_POST['quantity']
            ];
            // Add or update cart
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            $found = false;
            foreach ($_SESSION['cart'] as &$item) {
                if ($item['id'] == $cartItem['id']) {
                    $item['quantity'] += $cartItem['quantity'];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $_SESSION['cart'][] = $cartItem;
            }
            
            // AJAX response for add to cart
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit();
            }
            header("Location: products.php");
            exit();
        } else {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Product not found']);
                exit();
            }
        }
    } catch (Exception $e) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'An error occurred while adding to cart: ' . $e->getMessage()]);
            exit();
        }
    }
}
 
// Remove from cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_from_cart'])) {
    foreach ($_SESSION['cart'] as $k => $item) {
        if ($item['id'] == $_POST['remove_from_cart']) {
            unset($_SESSION['cart'][$k]);
            break;
        }
    }
    // Re-index array
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    // AJAX response for remove from cart
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    header("Location: products.php");
    exit();
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
    <!-- Custom CSS -->
    <link href="../css/custom.css" rel="stylesheet">
    <link href="../css/sidebar.css" rel="stylesheet">
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        #promoSuggestions {
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
        }
    </style>
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
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cartModal">
                    <i class="bi bi-cart"></i> View Cart
                </button>
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
            <div class="col-md-4 mb-4" data-category="<?php echo strtolower(htmlspecialchars($product['Prod_Cat'])); ?>">
                <div class="card h-100">
                    <?php if (!empty($product['Prod_Image'])): ?>
                        <img src="../<?php echo htmlspecialchars($product['Prod_Image']); ?>" class="card-img-top" alt="Product Image" style="height:200px;object-fit:contain;">
                    <?php else: ?>
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height:200px;">
                            <span class="text-muted">No Image</span>
                        </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['Prod_Name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($product['Prod_Desc']); ?></p>
                        <p class="card-text"><strong>₱<?php echo number_format($product['Prod_Price'], 2); ?></strong></p>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($product['Prod_Cat']); ?></span>
                        <span class="badge <?php echo ($product['Prod_Stock'] == 0) ? 'bg-danger' : (($product['Prod_Stock'] <= 10) ? 'bg-warning text-dark' : 'bg-success'); ?>">
                            <?php
                                if ($product['Prod_Stock'] == 0) echo 'Out of Stock';
                                elseif ($product['Prod_Stock'] <= 10) echo 'Low Stock';
                                else echo 'In Stock';
                            ?>
                        </span>
                        <span class="ms-2 text-muted small">(<?php echo $product['Prod_Stock']; ?> left)</span>
                        <?php if ($product['Prod_Stock'] > 0): ?>
                        <div class="mt-3 d-flex align-items-center gap-2">
                            <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addToCartModal<?php echo $product['ProductID']; ?>">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
           
            <!-- Add to Cart Modal -->
            <div class="modal fade" id="addToCartModal<?php echo $product['ProductID']; ?>" tabindex="-1" aria-labelledby="addToCartModalLabel<?php echo $product['ProductID']; ?>" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST" action="">
                    <div class="modal-header">
                      <h5 class="modal-title" id="addToCartModalLabel<?php echo $product['ProductID']; ?>">Add to Cart</h5>
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 text-center">
                            <?php if (!empty($product['Prod_Image'])): ?>
                                <img src="../<?php echo htmlspecialchars($product['Prod_Image']); ?>" alt="Product Image" style="max-width:120px;max-height:120px;object-fit:contain;">
                            <?php endif; ?>
                            <h5 class="mt-2"><?php echo htmlspecialchars($product['Prod_Name']); ?></h5>
                            <div class="text-muted"><?php echo htmlspecialchars($product['Prod_Desc']); ?></div>
                            <div class="fw-bold mt-2">₱<?php echo number_format($product['Prod_Price'], 2); ?></div>
                        </div>
                        <div class="mb-3">
                            <label for="quantity<?php echo $product['ProductID']; ?>" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity<?php echo $product['ProductID']; ?>" name="quantity" value="1" min="1" max="<?php echo $product['Prod_Stock']; ?>" required>
                        </div>
                        <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-success"><i class="bi bi-cart-plus"></i> Add to Cart</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
 
    <!-- Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="cartModalLabel"><i class="bi bi-cart"></i> My Cart</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <?php if (!empty($_SESSION['cart'])): $total = 0; ?>
            <div class="table-responsive">
              <table class="table align-middle">
                <thead>
                  <tr>
                    <th>Image</th>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($_SESSION['cart'] as $item):
                    $subtotal = $item['price'] * $item['quantity'];
                    $total += $subtotal;
                  ?>
                  <tr>
                    <td>
                      <?php if (!empty($item['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($item['image']); ?>" style="width:50px;height:50px;object-fit:contain;">
                      <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₱<?php echo number_format($subtotal, 2); ?></td>
                    <td>
                      <form method="POST" action="products.php" class="remove-from-cart-form" style="display:inline;">
                        <input type="hidden" name="remove_from_cart" value="<?php echo $item['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
            <div class="text-end fw-bold fs-5">Total: ₱<?php echo number_format($total, 2); ?></div>
            <?php else: ?>
              <div class="text-center text-muted py-4">
                <i class="bi bi-cart-x" style="font-size:2rem;"></i><br>
                Your cart is empty.
              </div>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <?php if (!empty($_SESSION['cart'])): ?>
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#checkoutModal" data-bs-dismiss="modal">Checkout</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
 
    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="checkoutForm" method="POST" action="checkout.php">
            <div class="modal-header">
              <h5 class="modal-title" id="checkoutModalLabel"><i class="bi bi-credit-card"></i> Checkout</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="mb-3">
                <label class="form-label fw-bold">Customer Name:</label>
                <div>
                  <?php
                    $fname = isset($customerInfo['Cust_FN']) ? htmlspecialchars($customerInfo['Cust_FN']) : '';
                    $lname = isset($customerInfo['Cust_LN']) ? htmlspecialchars($customerInfo['Cust_LN']) : '';
                    echo trim($fname . ' ' . $lname) ?: '<span class="text-danger">No customer name found</span>';
                  ?>
                </div>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Items:</label>
                <ul class="list-group">
                  <?php foreach ($_SESSION['cart'] as $item): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                      <?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?>
                      <span>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></span>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Payment Method:</label>
                <select class="form-select" name="payment_method" required>
                  <option value="cash">Cash</option>
                  <option value="card">Card</option>
                </select>
              </div>
              <div class="mb-3 position-relative">
                <label class="form-label fw-bold">Promo Code:</label>
                <input type="text" class="form-control" id="promoCodeInput" name="promo_code" autocomplete="off" placeholder="Enter promo code">
                <div id="promoSuggestions" class="list-group position-absolute" style="z-index: 1000;"></div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn btn-success">Confirm Purchase</button>
            </div>
          </form>
        </div>
      </div>
    </div>
 
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    // Search and filter functionality
    document.getElementById('searchProduct').addEventListener('input', filterProducts);
    document.getElementById('categoryFilter').addEventListener('change', filterProducts);

    function filterProducts() {
        const searchTerm = document.getElementById('searchProduct').value.toLowerCase();
        const category = document.getElementById('categoryFilter').value.toLowerCase();
        const products = document.querySelectorAll('#productsGrid .col-md-4');

        products.forEach(product => {
            const title = product.querySelector('.card-title').textContent.toLowerCase();
            const description = product.querySelector('.card-text').textContent.toLowerCase();
            const productCategory = product.getAttribute('data-category') || '';

            const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
            const matchesCategory = !category || productCategory === category;

            product.style.display = matchesSearch && matchesCategory ? '' : 'none';
        });
    }

    // Function to update cart content
    function updateCartContent() {
        const cartModal = document.getElementById('cartModal');
        const modalBody = cartModal.querySelector('.modal-body');
        const checkoutButton = cartModal.querySelector('.modal-footer .btn-success');

        fetch('products.php', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newCartContent = doc.querySelector('#cartModal .modal-body');
            
            if (newCartContent) {
                modalBody.innerHTML = newCartContent.innerHTML;
                
                // Update checkout button visibility
                if (checkoutButton) {
                    checkoutButton.style.display = newCartContent.querySelector('table') ? 'block' : 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error updating cart:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update cart. Please try again.',
                confirmButtonText: 'Close'
            });
        });
    }

    // SweetAlert for Add to Cart
    document.querySelectorAll('form[action=""]').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            
            fetch('products.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart content
                    updateCartContent();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        text: 'Product has been added to your cart successfully.',
                        showCancelButton: true,
                        confirmButtonText: 'View Cart',
                        cancelButtonText: 'Close',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show cart modal
                            const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
                            cartModal.show();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to add product to cart. Please try again.',
                        confirmButtonText: 'Close'
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred: ' + error.message,
                    confirmButtonText: 'Close'
                });
            });
        });
    });

    // SweetAlert for Remove from Cart
    document.querySelectorAll('.remove-from-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            
            fetch('products.php', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update cart content
                    updateCartContent();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Removed from Cart!',
                        showConfirmButton: false,
                        timer: 1200
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to remove item from cart. Please try again.',
                    confirmButtonText: 'Close'
                });
            });
        });
    });

    // Promo code autocomplete
    document.addEventListener('DOMContentLoaded', function() {
        const promoInput = document.getElementById('promoCodeInput');
        const suggestions = document.getElementById('promoSuggestions');
        if (promoInput) {
            promoInput.addEventListener('input', function() {
                const query = promoInput.value.trim();
                if (query.length === 0) {
                    suggestions.innerHTML = '';
                    suggestions.style.display = 'none';
                    return;
                }
                fetch('../promotions.php?search=' + encodeURIComponent(query))
                    .then(res => res.json())
                    .then(data => {
                        suggestions.innerHTML = '';
                        if (Array.isArray(data) && data.length > 0) {
                            data.forEach(promo => {
                                const item = document.createElement('button');
                                item.type = 'button';
                                item.className = 'list-group-item list-group-item-action';
                                item.textContent = promo.code + ' - ' + promo.description;
                                item.onclick = function() {
                                    promoInput.value = promo.code;
                                    suggestions.innerHTML = '';
                                    suggestions.style.display = 'none';
                                };
                                suggestions.appendChild(item);
                            });
                            suggestions.style.display = 'block';
                        } else {
                            suggestions.style.display = 'none';
                        }
                    });
            });
            // Hide suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!promoInput.contains(e.target) && !suggestions.contains(e.target)) {
                    suggestions.innerHTML = '';
                    suggestions.style.display = 'none';
                }
            });
        }
    });
    </script>
</body>
</html>