<?php
$host = 'localhost';
$db   = 'agri_db';
$user = 'root'; // default XAMPP user
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

function viewProducts() {
    global $pdo;
    $sql = "SELECT p.*, 
            ua.User_Name as created_by
            FROM products p 
            LEFT JOIN user_accounts ua ON p.UserID = ua.UserID";
    $result = $pdo->query($sql);
    $products = [];
    if ($result->rowCount() > 0) {
        while($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $products[] = $row;
        }
    }
    return $products;
}

function getProductHistory($productId) {
    global $pdo;
    $sql = "SELECT p.*, 
            ua.User_Name as created_by
            FROM products p 
            LEFT JOIN user_accounts ua ON p.UserID = ua.UserID 
            WHERE p.ProductID = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$productId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function updateProduct($id, $name, $category, $description, $price, $stock) {
    global $pdo;
    try {
        $sql = "UPDATE products SET 
                Prod_Name = :name,
                Prod_Cat = :category,
                Prod_Desc = :description,
                Prod_Price = :price,
                Prod_Stock = :stock,
                Prod_Updated_at = CURRENT_TIMESTAMP
                WHERE ProductID = :id";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':category' => $category,
            ':description' => $description,
            ':price' => $price,
            ':stock' => $stock
        ]);
        
        return true;
    } catch (PDOException $e) {
        error_log("Error updating product: " . $e->getMessage());
        return false;
    }
}

function addProduct($name, $category, $description, $price, $stock) {
    global $pdo;
    try {
        $sql = "INSERT INTO products (Prod_Name, Prod_Cat, Prod_Desc, Prod_Price, Prod_Stock, UserID) 
                VALUES (:name, :category, :description, :price, :stock, :userid)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':userid', $_SESSION['UserID']);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error adding product: " . $e->getMessage());
        return false;
    }
}

function loginUser($username, $password) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM user_accounts WHERE User_Name = :username LIMIT 1');
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && $password === $user['User_Password']) {
        return $user;
    }
    return false;
}
?> 