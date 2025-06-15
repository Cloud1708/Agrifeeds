<?php
// Prevent PHP errors from being displayed
error_reporting(0);
ini_set('display_errors', 0);

ob_start();
require_once('../includes/db.php');
$con = new database();
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 2) { // 2 for customer
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
                // Check if user has already used this promo code
                $usageCount = $con->getPromoUsageCount($promo['PromotionID']);
                $userUsageCount = $con->getUserPromoUsageCount($promo['PromotionID'], $userID);
                
                // Check if promo has reached its maximum usage limit
                if ($promo['Promo_MaxUsage'] > 0 && $usageCount >= $promo['Promo_MaxUsage']) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'This promotion has reached its maximum usage limit.'
                    ]);
                    exit();
                }
                
                // Check if user has already used this promo
                if ($userUsageCount > 0) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'You have already used this promotion code.'
                    ]);
                    exit();
                }
                
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
            // Calculate the discounted price for this item
            $itemTotal = $item['price'] * $item['quantity'];
            $itemDiscountRate = $discountRate > 0 ? $discountRate / 100 : 0;
            $itemCustomerDiscount = $itemTotal * $itemDiscountRate;
            $itemTotalAfterCustomerDiscount = $itemTotal - $itemCustomerDiscount;
           
            // Apply promo discount proportionally to each item
            $itemPromoDiscount = 0;
            if ($promoDiscount > 0) {
                $itemPromoDiscount = ($itemTotalAfterCustomerDiscount / $totalAfterCustomerDiscount) * $promoDiscount;
            }
           
            $finalItemPrice = ($itemTotalAfterCustomerDiscount - $itemPromoDiscount) / $item['quantity'];
           
            $itemStmt = $conn->prepare("INSERT INTO Sale_Item (SaleID, ProductID, SI_Quantity, SI_Price) VALUES (?, ?, ?, ?)");
            $itemStmt->execute([$saleID, $item['id'], $item['quantity'], $finalItemPrice]);

            // Only update stock and log inventory if payment is card (completed)
            if (strtolower($_POST['payment_method']) === 'card') {
                // Update product stock and log inventory history
                $stmt = $conn->prepare("SELECT Prod_Stock FROM products WHERE ProductID = ?");
                $stmt->execute([$item['id']]);
                $currentStock = $stmt->fetchColumn();
                if ($currentStock === false) $currentStock = 0;
                $newStock = $currentStock - $item['quantity'];
                if ($newStock < 0) $newStock = 0;

                // Update stock
                $updateStmt = $conn->prepare("UPDATE products SET Prod_Stock = ? WHERE ProductID = ?");
                $updateStmt->execute([$newStock, $item['id']]);

                // Log inventory history
                $logStmt = $conn->prepare("INSERT INTO inventory_history (ProductID, IH_QtyChange, IH_NewStckLvl, IH_ChangeDate) VALUES (?, ?, ?, NOW())");
                $logStmt->execute([$item['id'], -$item['quantity'], $newStock]);
            }
        }

        // Insert payment history ONLY if payment method is card (completed)
        if (strtolower($_POST['payment_method']) === 'card') {
            $payStmt = $conn->prepare("INSERT INTO Payment_History (SaleID, PT_PayAmount, PT_PayDate, PT_PayMethod) VALUES (?, ?, NOW(), ?)");
            $payStmt->execute([$saleID, $finalTotal, $_POST['payment_method']]);
        }

        // --- Award loyalty points for completed orders (only for card payment) ---
        if (strtolower($_POST['payment_method']) === 'card' && isset($customerInfo['CustomerID'])) {
            $settings = $con->opencon()->query("SELECT min_purchase, points_per_peso FROM loyalty_settings WHERE id = 1")->fetch(PDO::FETCH_ASSOC);
            $minPurchase = (float)$settings['min_purchase'];
            $pointsPerPeso = (float)$settings['points_per_peso'];

            if ($finalTotal >= $minPurchase) {
                $pointsEarned = floor($finalTotal * $pointsPerPeso);
                if ($pointsEarned > 0) {
                    // First check if customer is enrolled in loyalty program
                    $stmt = $con->opencon()->prepare("SELECT LoyaltyID FROM loyalty_program WHERE CustomerID = ?");
                    $stmt->execute([$customerInfo['CustomerID']]);
                    $loyaltyId = $stmt->fetchColumn();

                    if ($loyaltyId) {
                        // Update points balance and last update timestamp
                        $stmt = $con->opencon()->prepare("UPDATE loyalty_program SET LP_PtsBalance = LP_PtsBalance + ?, LP_LastUpdt = NOW() WHERE CustomerID = ?");
                        $result = $stmt->execute([$pointsEarned, $customerInfo['CustomerID']]);

                        // Log the transaction
                        if ($result) {
                            $stmt = $con->opencon()->prepare("INSERT INTO loyalty_transaction_history (LoyaltyID, LoTranH_PtsEarned, LoTranH_TransDesc) VALUES (?, ?, ?)");
                            $stmt->execute([$loyaltyId, $pointsEarned, "Points earned from order #$saleID"]);
                        }
                    }
                }
            }
        }

        // Insert into Order_Promotions if promo used
        if ($promoCode && $selectedPromo) {
            $promoID = $selectedPromo['PromotionID'];
            $orderPromoStmt = $conn->prepare("INSERT INTO Order_Promotions (SaleID, PromotionID, OrderP_DiscntApplied, OrderP_AppliedDate) VALUES (?, ?, ?, NOW())");
            $orderPromoStmt->execute([$saleID, $promoID, $promoDiscount]);
        }

        $conn->commit();

        // Prepare order details for response
        $orderDetails = [
            'sale_id' => $saleID,
            'items' => $_SESSION['cart'],
            'subtotal' => $total,
            'customer_discount_rate' => $discountRate,
            'customer_discount_amount' => $discountAmount,
            'promo_code' => $promoCode,
            'promo_label' => $promoLabel,
            'promo_discount' => $promoDiscount,
            'final_total' => $finalTotal,
            'payment_method' => $_POST['payment_method'],
            'customer_name' => trim($customerInfo['Cust_FN'] . ' ' . $customerInfo['Cust_LN']),
            'order_date' => date('Y-m-d H:i:s')
        ];

        // Clear the cart after successful order
        $_SESSION['cart'] = [];

        echo json_encode([
            'success' => true,
            'message' => 'Order placed successfully',
            'order_details' => $orderDetails
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
            <?php foreach ($promos as $promo): 
                $usageCount = $con->getPromoUsageCount($promo['PromotionID']);
                $userUsageCount = $con->getUserPromoUsageCount($promo['PromotionID'], $userID);
                $isDisabled = ($promo['Promo_MaxUsage'] > 0 && $usageCount >= $promo['Promo_MaxUsage']) || $userUsageCount > 0;
            ?>
                <option value="<?php echo htmlspecialchars($promo['Prom_Code']); ?>" 
                        <?php echo $isDisabled ? 'disabled' : ''; ?>>
                    <?php
                    echo htmlspecialchars($promo['Prom_Code']);
                    if (strtolower($promo['Promo_DiscountType']) === 'percentage') {
                        echo ' (' . floatval($promo['Promo_DiscAmnt']) . '%)';
                    } else {
                        echo ' (₱' . number_format($promo['Promo_DiscAmnt'], 2) . ')';
                    }
                    if ($isDisabled) {
                        echo ' - ' . ($userUsageCount > 0 ? 'Already Used' : 'Unavailable');
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

    <!-- Order Success Modal -->
    <div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-labelledby="orderSuccessModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderSuccessModalLabel">Order Processing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Loading Animation -->
                    <div id="orderProcessing" class="text-center py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h4 class="mb-3">Processing Your Order...</h4>
                        <div class="progress mb-3" style="height: 5px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                        </div>
                        <p class="text-muted">Please wait while we process your order...</p>
                    </div>

                    <!-- Order Details -->
                    <div id="orderDetails" style="display: none;">
                        <div class="text-center mb-4">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                            <h4 class="mt-3">Order Placed Successfully!</h4>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">Order Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Order ID:</strong> <span id="orderId"></span></p>
                                        <p class="mb-1"><strong>Date:</strong> <span id="orderDate"></span></p>
                                        <p class="mb-1"><strong>Customer:</strong> <span id="customerName"></span></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Payment Method:</strong> <span id="paymentMethod"></span></p>
                                        <p class="mb-1"><strong>Status:</strong> <span id="orderStatus"></span></p>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Items Ordered:</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-center">Quantity</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody id="orderItems">
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                                                <td class="text-end" id="orderSubtotal"></td>
                                            </tr>
                                            <tr id="customerDiscountRow" style="display: none;">
                                                <td colspan="3" class="text-end"><strong>Customer Discount:</strong></td>
                                                <td class="text-end" id="customerDiscount"></td>
                                            </tr>
                                            <tr id="promoDiscountRow" style="display: none;">
                                                <td colspan="3" class="text-end"><strong>Promo Discount:</strong></td>
                                                <td class="text-end" id="promoDiscount"></td>
                                            </tr>
                                            <tr>
                                                <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                                <td class="text-end" id="orderTotal"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill"></i> Important Notice</h5>
                            <p class="mb-0">Please visit our store to complete your order. Your order will be marked as cancelled within 72 hours if not completed.</p>
                            <hr>
                            <p class="mb-0"><strong>Store Address:</strong><br>
                            123 Main Street, City, Province<br>
                            Business Hours: Monday - Saturday, 8:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="orders.php" class="btn btn-primary">View My Orders</a>
                </div>
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
        const checkoutDiscountBreakdown = document.getElementById('checkoutDiscountBreakdown');
        if (!checkoutItems || !checkoutDiscountBreakdown) return;

        // First update the items list
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

            // Calculate and update totals
            let subtotal = 0;
            const items = checkoutItems.querySelectorAll('.list-group-item');
            items.forEach(item => {
                const priceText = item.querySelector('span').textContent;
                const price = parseFloat(priceText.replace('₱', '').replace(',', ''));
                if (!isNaN(price)) {
                    subtotal += price;
                }
            });

            // Get customer discount rate
            const discountRate = <?php echo isset($customerInfo['Cust_DiscRate']) ? floatval($customerInfo['Cust_DiscRate']) : 0; ?>;
            const customerDiscount = discountRate > 0 ? subtotal * (discountRate / 100) : 0;
            const totalAfterCustomerDiscount = subtotal - customerDiscount;

            // Update the discount breakdown
            let discountHtml = `Subtotal: ₱${subtotal.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}<br>`;
            if (discountRate > 0) {
                discountHtml += `Customer Discount (${discountRate}%): -₱${customerDiscount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}<br>`;
            }
            discountHtml += `<span class="text-success">Total after Discount: ₱${totalAfterCustomerDiscount.toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2})}</span>`;
            
            checkoutDiscountBreakdown.innerHTML = discountHtml;

            // Update the discount display for promo code calculations
            if (typeof updateDiscountDisplay === 'function') {
                updateDiscountDisplay();
            }
        })
        .catch(error => {
            console.error('Error updating checkout content:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to update checkout information. Please try again.',
                confirmButtonText: 'Close'
            });
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
                            
                            // Add event listener for when cart modal is shown
                            document.getElementById('cartModal').addEventListener('shown.bs.modal', function () {
                                // Update checkout content when cart modal is shown
                                updateCheckoutContent();
                            });
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

    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Check if selected promo is disabled
        const promoSelect = document.getElementById('promoCodeSelect');
        if (promoSelect) {
            const selectedOption = promoSelect.options[promoSelect.selectedIndex];
            if (selectedOption && selectedOption.disabled) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Promo Code',
                    text: 'The selected promotion code is not available.',
                    confirmButtonText: 'Close'
                });
                return;
            }
        }
        
        const formData = new FormData(this);
        const paymentMethod = formData.get('payment_method');
        
        // Hide the checkout modal
        const checkoutModal = bootstrap.Modal.getInstance(document.getElementById('checkoutModal'));
        checkoutModal.hide();
        
        // Show the order success modal
        const orderSuccessModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
        orderSuccessModal.show();
        
        // Start the loading animation
        const progressBar = document.querySelector('.progress-bar');
        const loadingText = document.querySelector('#orderProcessing h4');
        const loadingSubtext = document.querySelector('#orderProcessing p');
        let progress = 0;
        let currentStep = 0;
        let orderData = null;
        
        const loadingSteps = [
            { message: "Processing Your Order...", subtext: "Please wait while we process your order..." },
            { message: "Validating Order Details...", subtext: "Checking order information..." },
            { message: "Confirming Order Items...", subtext: "Verifying product availability..." },
            { message: "Applying Discounts...", subtext: "Calculating final price..." },
            { message: "Finalizing Order...", subtext: "Almost done..." }
        ];

        function showOrderDetails(order) {
            document.getElementById('orderProcessing').style.display = 'none';
            document.getElementById('orderDetails').style.display = 'block';
            
            document.getElementById('orderId').textContent = order.sale_id;
            document.getElementById('orderDate').textContent = new Date(order.order_date).toLocaleString();
            document.getElementById('customerName').textContent = order.customer_name;
            document.getElementById('paymentMethod').textContent = paymentMethod === 'card' ? 'Completed' : 'Pending';
            document.getElementById('orderStatus').textContent = paymentMethod === 'card' ? 'Completed' : 'Pending';
            
            // Populate items
            const itemsTable = document.getElementById('orderItems');
            itemsTable.innerHTML = '';
            order.items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${item.name}</td>
                    <td class="text-center">${item.quantity}</td>
                    <td class="text-end">₱${parseFloat(item.price).toFixed(2)}</td>
                    <td class="text-end">₱${(item.price * item.quantity).toFixed(2)}</td>
                `;
                itemsTable.appendChild(row);
            });
            
            // Populate totals
            document.getElementById('orderSubtotal').textContent = '₱' + order.subtotal.toFixed(2);
            
            // Show customer discount if applicable
            if (order.customer_discount_amount > 0) {
                document.getElementById('customerDiscountRow').style.display = '';
                document.getElementById('customerDiscount').textContent = 
                    `-₱${order.customer_discount_amount.toFixed(2)} (${order.customer_discount_rate}%)`;
            }
            
            // Show promo discount if applicable
            if (order.promo_discount > 0) {
                document.getElementById('promoDiscountRow').style.display = '';
                document.getElementById('promoDiscount').textContent = 
                    `-₱${order.promo_discount.toFixed(2)} ${order.promo_label ? `(${order.promo_label})` : ''}`;
            }
            
            document.getElementById('orderTotal').textContent = '₱' + order.final_total.toFixed(2);
        }

        const interval = setInterval(() => {
            progress += 2;
            progressBar.style.width = progress + '%';
            
            // Update loading message every 20%
            if (progress % 20 === 0 && currentStep < loadingSteps.length) {
                loadingText.textContent = loadingSteps[currentStep].message;
                loadingSubtext.textContent = loadingSteps[currentStep].subtext;
                currentStep++;
            }
            
            if (progress >= 100) {
                clearInterval(interval);
                if (paymentMethod === 'card') {
                    loadingText.textContent = "Waiting for Payment...";
                    loadingSubtext.textContent = "Please complete your payment in the new window to proceed.";
                } else if (orderData) {
                    // For cash payments, show order details after loading completes
                    showOrderDetails(orderData);
                }
            }
        }, 100);

        // Submit the form
        fetch('products.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (paymentMethod === 'card') {
                    // Store order details in session for payment page
                    fetch('payment/store_order_session.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data.order_details)
                    })
                    .then(() => {
                        // Open payment window
                        const paymentWindow = window.open('payment/mock_payment.php', '_blank');
                        
                        // Listen for payment completion
                        window.addEventListener('message', function(event) {
                            if (event.data === 'payment_completed') {
                                paymentWindow.close();
                                showOrderDetails(data.order_details);
                            }
                        });
                    });
                } else {
                    // For cash payments, store the order data and wait for loading to complete
                    orderData = data.order_details;
                }
            } else {
                clearInterval(interval);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Failed to process order. Please try again.',
                    confirmButtonText: 'Close'
                }).then(() => {
                    orderSuccessModal.hide();
                    checkoutModal.show();
                });
            }
        })
        .catch(error => {
            clearInterval(interval);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while processing your order. Please try again.',
                confirmButtonText: 'Close'
            }).then(() => {
                orderSuccessModal.hide();
                checkoutModal.show();
            });
        });
    });
    </script>
</body>
</html>