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
    $productId = isset($_POST['productID']) ? intval($_POST['productID']) : 0;
    $newPrice = isset($_POST['newPrice']) ? floatval($_POST['newPrice']) : null;
    $effectiveFrom = isset($_POST['effectiveFrom']) ? $_POST['effectiveFrom'] : null;
    $effectiveTo = isset($_POST['effectiveTo']) && $_POST['effectiveTo'] !== '' ? $_POST['effectiveTo'] : null;

    if ($productId && $newPrice !== null && $effectiveFrom) {
        $con = new database();
        $result = $con->updateProductPriceWithHistory($productId, $newPrice, $effectiveFrom, $effectiveTo);
        if ($result === true) {
            $_SESSION['sweetAlertConfig'] = "<script>Swal.fire({icon: 'success', title: 'Price Updated', text: 'Product price and history updated successfully!', confirmButtonText: 'OK'});</script>";
        } else {
            $_SESSION['sweetAlertConfig'] = "<script>Swal.fire({icon: 'error', title: 'Update Failed', text: '" . addslashes($result) . "', confirmButtonText: 'OK'});</script>";
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