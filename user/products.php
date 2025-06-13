<?php
// Prevent PHP errors from being displayed
error_reporting(0);
ini_set('display_errors', 0);

ob_start();
require_once('../includes/db.php');
$con = new database();
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$userID = $_SESSION['user_id'];
$userInfo = $con->getUserInfo($userID);
$customerInfo = $con->getCustomerInfo($userID);
$products = $con->getAllProducts();

$promos = [];
if (method_exists($con, 'getAvailablePromos')) {
    $promos = $con->getAvailablePromos($userID);
}

// --- CART LOGIC (SESSION BASED) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    ob_clean();
    try {
        $prod = $con->getProductById($_POST['product_id']);
        if ($prod) {
            $cartItem = [
                'id' => $prod['ProductID'],
                'name' => $prod['Prod_Name'],
                'price' => $prod['Prod_Price'],
                'image' => $prod['Prod_Image'],
                'quantity' => (int)$_POST['quantity']
            ];
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
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    header("Location: products.php");
    exit();
}

// Handle checkout process (CONFIRM PURCHASE)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_method'])) {
    ob_clean();

    $promoCode = isset($_POST['promo_code']) ? $_POST['promo_code'] : '';
    $promoDiscount = 0;
    $promoLabel = '';

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    $discountRate = isset($customerInfo['Cust_DiscRate']) ? floatval($customerInfo['Cust_DiscRate']) : 0;
    $discountAmount = $discountRate > 0 ? $total * ($discountRate / 100) : 0;
    $totalAfterCustomerDiscount = $total - $discountAmount;

    if ($promoCode) {
        $allPromos = $con->getAvailablePromos($userID);
        $selectedPromo = null;
        foreach ($allPromos as $promo) {
            if (strcasecmp($promo['Prom_Code'], $promoCode) == 0) {
                $selectedPromo = $promo;
                break;
            }
        }
        if ($selectedPromo) {
            if (strcasecmp($selectedPromo['Promo_DiscountType'], "Percentage") == 0) {
                $promoDiscount = $totalAfterCustomerDiscount * (floatval($selectedPromo['Promo_DiscAmnt']) / 100);
                $promoLabel = $selectedPromo['Prom_Code'] . ' (-' . floatval($selectedPromo['Promo_DiscAmnt']) . '%)';
            } else {
                $promoDiscount = min(floatval($selectedPromo['Promo_DiscAmnt']), $totalAfterCustomerDiscount);
                $promoLabel = $selectedPromo['Prom_Code'] . ' (-₱' . number_format($selectedPromo['Promo_DiscAmnt'], 2) . ')';
            }
            $con->logPromoUsage($selectedPromo['PromotionID'], $_SESSION['user_id']);
        }
    }

    $finalTotal = $totalAfterCustomerDiscount - $promoDiscount;

    // --- DATABASE INSERTION FOR PAYMENT HISTORY ---
    // 1. Insert into Sales table (no Sale_Status)
    $conn = $con->opencon();
try {
    $conn->beginTransaction();

    // Insert sale
    if (strtolower($_POST['payment_method']) === 'card') {
    $saleStmt = $conn->prepare("INSERT INTO Sales (CustomerID, Sale_Date, Sale_Status) VALUES (?, NOW(), 'Completed')");
    $saleStmt->execute([$customerInfo['CustomerID']]);
} else {
    // cash
    $saleStmt = $conn->prepare("INSERT INTO Sales (CustomerID, Sale_Date, Sale_Status) VALUES (?, NOW(), 'Pending')");
    $saleStmt->execute([$customerInfo['CustomerID']]);
}
$saleID = $conn->lastInsertId();

    // Insert sale items
    foreach ($_SESSION['cart'] as $item) {
    $itemStmt = $conn->prepare("INSERT INTO Sale_Item (SaleID, ProductID, SI_Quantity, SI_Price) VALUES (?, ?, ?, ?)");
    $itemStmt->execute([$saleID, $item['id'], $item['quantity'], $item['price']]);
}

    // Insert payment history ONLY if payment method is card
if (strtolower($_POST['payment_method']) === 'card') {
    $payStmt = $conn->prepare("INSERT INTO Payment_History (SaleID, PT_PayAmount, PT_PayDate, PT_PayMethod) VALUES (?, ?, NOW(), ?)");
    $payStmt->execute([$saleID, $finalTotal, $_POST['payment_method']]);
}

    // --- INSERT INTO Order_Promotions IF PROMO USED ---
    if ($promoCode && $selectedPromo) {
        $promoID = $selectedPromo['PromotionID'];
        $orderPromoStmt = $conn->prepare("INSERT INTO Order_Promotions (SaleID, PromotionID, OrderP_DiscntApplied, OrderP_AppliedDate) VALUES (?, ?, ?, NOW())");
        $orderPromoStmt->execute([$saleID, $promoID, $promoDiscount]);
    }

    $conn->commit();

    // Clear the cart after successful order
    $_SESSION['cart'] = [];

    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'original_total' => $total,
        'customer_discount_rate' => $discountRate,
        'customer_discount_amount' => $discountAmount,
        'promo_code' => $promoCode,
        'promo_label' => $promoLabel,
        'promo_discount' => $promoDiscount,
        'final_total' => $finalTotal
    ]);
    exit();
} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Failed to complete purchase: ' . $e->getMessage()
    ]);
    exit();
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
                <form method="GET" action="products.php" style="display: inline;">
                    <input type="hidden" name="show_cart" value="1">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-cart"></i> View Cart
                    </button>
                </form>
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
            <?php if (!empty($_SESSION['cart'])): 
              $total = 0;
              foreach ($_SESSION['cart'] as $item) {
                  $subtotal = $item['price'] * $item['quantity'];
                  $total += $subtotal;
              }
              $discountRate = isset($customerInfo['Cust_DiscRate']) ? floatval($customerInfo['Cust_DiscRate']) : 0;
              $discountAmount = $discountRate > 0 ? $total * ($discountRate / 100) : 0;
              $finalTotal = $total - $discountAmount;
            ?>
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
            <div class="text-end fw-bold fs-5">
                Subtotal: ₱<?php echo number_format($total, 2); ?><br>
                <?php if ($discountRate > 0): ?>
                    Customer Discount (<?php echo $discountRate; ?>%): -₱<?php echo number_format($discountAmount, 2); ?><br>
                    <span class="text-success">Total after Discount: ₱<?php echo number_format($finalTotal, 2); ?></span>
                <?php else: ?>
                    <span>Total: ₱<?php echo number_format($total, 2); ?></span>
                <?php endif; ?>
            </div>
            <?php else: ?>
              <div class="text-center text-muted py-4">
                <i class="bi bi-cart-x" style="font-size:2rem;"></i><br>
                Your cart is empty.
              </div>
            <?php endif; ?>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <?php if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])): ?>
              <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#checkoutModal" data-bs-dismiss="modal">
                <i class="bi bi-credit-card"></i> Checkout
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="checkoutForm" method="POST">
            <div class="modal-header">
              <h5 class="modal-title" id="checkoutModalLabel"><i class="bi bi-credit-card"></i> Checkout</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="checkoutModalBody">
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
                <ul class="list-group" id="checkoutItems">
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
    <?php if (!empty($promos)): ?>
        <select class="form-select" name="promo_code" id="promoCodeSelect">
            <option value="">-- Select Promo Code --</option>
            <?php foreach ($promos as $promo): ?>
                <option value="<?php echo htmlspecialchars($promo['Prom_Code']); ?>">
    <?php
        echo htmlspecialchars($promo['Prom_Code']);
        if (strtolower($promo['Promo_DiscountType']) === 'percentage') {
            echo ' (' . floatval($promo['Promo_DiscAmnt']) . '%)';
        } else {
            echo ' (₱' . number_format($promo['Promo_DiscAmnt'], 2) . ')';
        }
    ?>
</option>
            <?php endforeach; ?>
        </select>
    <?php else: ?>
        <input type="text" class="form-control" id="promoCodeInput" name="promo_code" placeholder="No available promos" disabled>
    <?php endif; ?>
</div>
              <div id="checkoutDiscountBreakdown" class="mb-3 text-end fw-bold fs-5">
                <!-- Discount breakdown will be updated by JS -->
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
        const modalFooter = cartModal.querySelector('.modal-footer');

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
            const newFooterContent = doc.querySelector('#cartModal .modal-footer');
            
            if (newCartContent) {
                modalBody.innerHTML = newCartContent.innerHTML;
            }
            
            if (newFooterContent) {
                modalFooter.innerHTML = newFooterContent.innerHTML;
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

    // Function to update checkout modal content
    function updateCheckoutContent() {
        const checkoutItems = document.getElementById('checkoutItems');
        if (!checkoutItems) return;

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
            const newCheckoutItems = doc.querySelector('#checkoutItems');
            
            if (newCheckoutItems) {
                checkoutItems.innerHTML = newCheckoutItems.innerHTML;
            }
        })
        .catch(error => {
            console.error('Error updating checkout content:', error);
        });
    }

    // Update checkout content when modal is shown
    document.getElementById('checkoutModal').addEventListener('show.bs.modal', function () {
        updateCheckoutContent();
    });

    // Update both cart and checkout content when adding to cart
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
                    // Update both cart and checkout content
                    updateCartContent();
                    updateCheckoutContent();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        text: 'Product has been added to your cart successfully.',
                        showCancelButton: true,
                        confirmButtonText: 'View Cart',
                        cancelButtonText: 'Close',
                        reverseButtons: true,
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show cart modal
                            const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
                            cartModal.show();
                        } else {
                            // Refresh and redirect to products page
                            setTimeout(() => {
                                window.location.href = 'products.php';
                            }, 100);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to add product to cart. Please try again.',
                        confirmButtonText: 'Close',
                        allowOutsideClick: false,
                        allowEscapeKey: false
                    }).then(() => {
                        // Refresh and redirect to products page
                        setTimeout(() => {
                            window.location.href = 'products.php';
                        }, 100);
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'An error occurred: ' + error.message,
                    confirmButtonText: 'Close'
                }).then(() => {
                    // Refresh and redirect to products page
                    setTimeout(() => {
                        window.location.href = 'products.php';
                    }, 100);
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
                    }).then(() => {
                        // Refresh and redirect to products page
                        setTimeout(() => {
                            window.location.href = 'products.php';
                        }, 100);
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to remove item from cart. Please try again.',
                    confirmButtonText: 'Close'
                }).then(() => {
                    // Refresh and redirect to products page
                    setTimeout(() => {
                        window.location.href = 'products.php';
                    }, 100);
                });
            });
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Check if we should show the cart modal
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('show_cart') === '1') {
            // Show the cart modal
            const cartModal = new bootstrap.Modal(document.getElementById('cartModal'));
            cartModal.show();
            
            // Remove the show_cart parameter from the URL without refreshing
            const newUrl = window.location.pathname + window.location.search.replace(/[?&]show_cart=1/, '');
            window.history.replaceState({}, '', newUrl);
        }
    });

    // Live update of discount and total in checkout modal when promo code is selected
    document.addEventListener("DOMContentLoaded", function() {
        var promoSelect = document.getElementById('promoCodeSelect');
        if (!promoSelect) return;
        promoSelect.addEventListener('change', function() {
            updateDiscountDisplay();
        });

        function updateDiscountDisplay() {
            var subtotal = 0;
            <?php if (!empty($_SESSION['cart'])): ?>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    subtotal += <?php echo $item['price'] * $item['quantity']; ?>;
                <?php endforeach; ?>
            <?php endif; ?>

            var discountRate = <?php echo isset($customerInfo['Cust_DiscRate']) ? floatval($customerInfo['Cust_DiscRate']) : 0; ?>;
            var customerDiscount = subtotal * (discountRate / 100);
            var totalAfterCustomerDiscount = subtotal - customerDiscount;

            var promos = {};
            <?php foreach ($promos as $promo): ?>
                promos["<?php echo addslashes($promo['Prom_Code']); ?>"] = {
                    type: "<?php echo addslashes($promo['Promo_DiscountType']); ?>",
                    amount: <?php echo floatval($promo['Promo_DiscAmnt']); ?>,
                    label: "<?php echo addslashes($promo['Prom_Code'] . (strcasecmp($promo['Promo_DiscountType'], 'Percentage') == 0 ? ' (-' . floatval($promo['Promo_DiscAmnt']) . '%)' : ' (-₱' . number_format($promo['Promo_DiscAmnt'], 2) . ')')); ?>"
                };
            <?php endforeach; ?>

            var selectedPromoCode = promoSelect.value;
            var promoDiscount = 0;
            var promoLabel = '';
            if (promos[selectedPromoCode]) {
                if (promos[selectedPromoCode].type.toLowerCase() === "percentage") {
                    promoDiscount = totalAfterCustomerDiscount * (promos[selectedPromoCode].amount / 100);
                } else {
                    promoDiscount = Math.min(promos[selectedPromoCode].amount, totalAfterCustomerDiscount);
                }
                promoLabel = promos[selectedPromoCode].label;
            }
            var finalTotal = totalAfterCustomerDiscount - promoDiscount;

            // Update the DOM
            var discountBox = document.getElementById('checkoutDiscountBreakdown');
            if (discountBox) {
                discountBox.innerHTML =
                    'Subtotal: ₱' + subtotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '<br>' +
                    (discountRate > 0 ? 'Customer Discount (' + discountRate + '%): -₱' + customerDiscount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '<br>' : '') +
                    (promoDiscount > 0 ? 'Promo Discount' + (promoLabel ? ' (' + promoLabel + ')' : '') + ': -₱' + promoDiscount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '<br>' : '') +
                    '<span class="text-success">Total after Discounts: ₱' + finalTotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2}) + '</span>';
            }
        }

        // Initial display
        updateDiscountDisplay();
    });
    </script>
</body>
</html>