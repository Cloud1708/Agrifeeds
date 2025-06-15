<?php
session_start();
require_once('../includes/db.php');

// Check if user is authorized
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] != 1 && $_SESSION['user_role'] != 3)) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Check if sale_id is provided
if (!isset($_GET['sale_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Sale ID is required']);
    exit();
}

$saleId = intval($_GET['sale_id']);

try {
    $con = new database();
    $conn = $con->opencon();

    // Get sale items with product names
    $stmt = $conn->prepare("
        SELECT 
            p.Prod_Name as product_name,
            si.SI_Quantity as quantity,
            si.SI_Price as price
        FROM Sale_Item si
        JOIN Products p ON si.ProductID = p.ProductID
        WHERE si.SaleID = ?
        ORDER BY p.Prod_Name ASC
    ");
    
    $stmt->execute([$saleId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get sale discount and promotion info
    $saleInfoStmt = $conn->prepare("
        SELECT 
            s.SaleID,
            s.Sale_Date,
            s.CustomerID,
            s.Sale_Status,
            c.Cust_DiscRate,
            p.Prom_Code AS promotion_name,
            op.OrderP_DiscntApplied AS discount_applied,
            p.Promo_DiscAmnt AS promo_discount_amount,
            p.Promo_DiscountType AS promo_discount_type
        FROM Sales s
        LEFT JOIN Customers c ON s.CustomerID = c.CustomerID
        LEFT JOIN Order_Promotions op ON s.SaleID = op.SaleID
        LEFT JOIN promotions p ON op.PromotionID = p.PromotionID
        WHERE s.SaleID = ?
        LIMIT 1
    ");
    $saleInfoStmt->execute([$saleId]);
    $saleInfo = $saleInfoStmt->fetch(PDO::FETCH_ASSOC);

    // Calculate totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['quantity'] * $item['price'];
    }
    $discountAmount = 0;
    $finalTotal = $subtotal;
    $discountType = null;
    $discountRate = null;
    $promotionName = null;
    if ($saleInfo) {
        $promotionName = $saleInfo['promotion_name'];
        $discountType = $saleInfo['promo_discount_type'];
        $discountRate = $saleInfo['Cust_DiscRate'];
        // If there is a promotion discount applied
        if (!empty($saleInfo['discount_applied'])) {
            $discountAmount = $saleInfo['discount_applied'];
            $finalTotal = $subtotal - $discountAmount;
        } elseif (!empty($saleInfo['Cust_DiscRate']) && $saleInfo['Cust_DiscRate'] > 0) {
            $discountAmount = $subtotal * ($saleInfo['Cust_DiscRate'] / 100);
            $finalTotal = $subtotal - $discountAmount;
        }
    }

    // Return the items and discount info as JSON
    echo json_encode([
        'items' => $items,
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'discount_type' => $discountType,
        'discount_rate' => $discountRate,
        'promotion_name' => $promotionName,
        'final_total' => $finalTotal
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch sale items: ' . $e->getMessage()]);
} 