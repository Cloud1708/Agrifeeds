<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    if (updateProduct($id, $name, $category, $description, $price, $stock)) {
        // Success - redirect back to products page with success message
        header('Location: products.php?success=1');
        exit();
    } else {
        // Error - redirect back to products page with error message
        header('Location: products.php?error=1');
        exit();
    }
} else {
    // If not POST request, redirect to products page
    header('Location: products.php');
    exit();
}
?> 