<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/db.php';
require_once('../includes/validation.php');

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 3])) {
    header('Location: products.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_require();
    // Reject path traversal in any POST parameter before processing
    $rawParams = ['productID', 'newPrice', 'effectiveFrom', 'effectiveTo'];
    foreach ($rawParams as $key) {
        if (isset($_POST[$key]) && contains_path_traversal($_POST[$key])) {
            $_SESSION['sweetAlertConfig'] = "<script>Swal.fire({icon: 'error', title: 'Invalid Input', text: 'Invalid request.', confirmButtonText: 'OK'});</script>";
            header('Location: products.php');
            exit();
        }
    }
    $productId = validate_id_safe($_POST['productID'] ?? null);
    $newPrice = validate_float_safe($_POST['newPrice'] ?? null);
    $effectiveFrom = validate_date_ymd($_POST['effectiveFrom'] ?? null);
    $effectiveTo = isset($_POST['effectiveTo']) && $_POST['effectiveTo'] !== ''
        ? validate_date_ymd($_POST['effectiveTo'])
        : null;

    if ($productId && $newPrice !== null && $effectiveFrom !== null) {
        $con = new database();
        $result = $con->updateProductPriceWithHistory($productId, $newPrice, $effectiveFrom, $effectiveTo);
        if ($result === true) {
            $_SESSION['sweetAlertConfig'] = "<script>Swal.fire({icon: 'success', title: 'Price Updated', text: 'Product price and history updated successfully!', confirmButtonText: 'OK'});</script>";
        } else {
            $_SESSION['sweetAlertConfig'] = "<script>Swal.fire({icon: 'error', title: 'Update Failed', text: " . json_encode($result, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ", confirmButtonText: 'OK'});</script>";
        }
    } else {
        $_SESSION['sweetAlertConfig'] = "<script>Swal.fire({icon: 'error', title: 'Invalid Input', text: 'Please fill in all required fields.', confirmButtonText: 'OK'});</script>";
    }
    header('Location: products.php');
    exit();
} else {
    header('Location: products.php');
    exit();
} 