<?php
class database{

    function opencon(): PDO{
        return new PDO(
            dsn: 'mysql:host=localhost;
            dbname=agri_db',
            username: 'root',
            password: '');
    }

    function addProduct($productName, $category, $description, $price, $stock) {

        $con = $this->opencon();
        
        try {
            $con->beginTransaction();

            $stmt = $con->prepare("INSERT INTO products (Prod_Name, Prod_Cat, Prod_Desc, Prod_Price, Prod_Stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$productName, $category, $description, $price, $stock]);
            
            $productID = $con->lastInsertId();

            $con->commit();

            return $productID;

        } catch (PDOException $e) {

            $con->rollback();
            return false;

        }

    }

    function viewProducts() {

        $con = $this->opencon();
        return $con->query("SELECT * FROM products")->fetchAll();

    }


    
       function updateProduct($name, $category, $description, $price, $stock, $id){
    try    {
        $con = $this->opencon();
        $con->beginTransaction();
        $query = $con->prepare("UPDATE products SET Prod_Name = ?, Prod_Cat = ?, Prod_Desc = ?, Prod_Price = ? , Prod_Stock = ? WHERE ProductID = ? ");
        $query->execute([$name, $category, $description, $price, $stock, $id]);
        $con->commit();
        return true;

    } catch (PDOException $e) {
       
         $con->rollBack();
        return false; 
    }
    }

    function deleteProduct($id) {
    try {
        $con = $this->opencon();
        $con->beginTransaction();
        $stmt = $con->prepare("DELETE FROM products WHERE ProductID = ?");
        $result = $stmt->execute([$id]);
        $con->commit();
        return $result;
    } catch (PDOException $e) {
        if (isset($con)) $con->rollBack();
        return false;
    }
}

function addCustomer($customerName, $contactInfo, $discountRate) {
 
        $con = $this->opencon();
       
        try {
            $con->beginTransaction();
 
            $stmt = $con->prepare("INSERT INTO customers (Cust_Name, Cust_CoInfo, Cust_DiscRate) VALUES (?, ?, ?)");
            $stmt->execute([$customerName, $contactInfo, $discountRate]);
           
            $custID = $con->lastInsertId();
 
            $con->commit();
 
            return $custID;
 
        } catch (PDOException $e) {
 
            $con->rollback();
            return false;
 
        }
 
    }
 
    function viewCustomers() {
 
        $con = $this->opencon();
        return $con->query("SELECT * FROM customers")->fetchAll();
 
    }

    function viewPromotions() {
    $con = $this->opencon();
    $stmt = $con->prepare("SELECT * FROM promotions ORDER BY PromotionID");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    function addPromotion($code, $desc, $amount, $type, $start, $end, $limit, $isActive) {
    $con = $this->opencon();
    try {
        $con->beginTransaction();
        $stmt = $con->prepare("INSERT INTO promotions 
            (Prom_Code, Promo_Description, Promo_DiscAmnt, Promo_DiscountType, Promo_StartDate, Promo_EndDate, UsageLimit, Promo_IsActive) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$code, $desc, $amount, $type, $start, $end, $limit, $isActive]);
        $promotionID = $con->lastInsertId();
        $con->commit();
        return $promotionID;
    } catch (PDOException $e) {
        if (isset($con)) $con->rollBack();
        return false;
    }
}

function updatePromotionDetails($code, $desc, $amount, $type, $start, $end, $limit, $promotionId){
    try    {
        $con = $this->opencon();
        $con->beginTransaction();
        $query = $con->prepare("UPDATE promotions SET Prom_Code = ?, Promo_Description = ?, Promo_DiscAmnt =?, Promo_DiscountType = ?, Promo_StartDate = ? , Promo_EndDate = ?, UsageLimit = ? WHERE PromotionID = ? ");
        $query->execute([$code, $desc, $amount, $type, $start, $end, $limit, $promotionId]);
        $con->commit();
        return true;
 
    } catch (PDOException $e) {
       
         $con->rollBack();
        return false;
    }
    }

 function addSupplier($Sup_Name, $Sup_CoInfo, $Sup_PayTerm, $Sup_DeSched) {
    $con = $this->opencon();
    try {
        $con->beginTransaction();
        $stmt = $con->prepare("INSERT INTO suppliers (Sup_Name, Sup_CoInfo, Sup_PayTerm, Sup_DeSched) VALUES (?, ?, ?, ?)");
        $stmt->execute([$Sup_Name, $Sup_CoInfo, $Sup_PayTerm, $Sup_DeSched]);
        $supplierID = $con->lastInsertId();
        $con->commit();
        return $supplierID;
    } catch (PDOException $e) {
        $con->rollback();
        // Uncomment for debugging:
        // die("DB Error: " . $e->getMessage());
        return false;
    }
}

function viewSuppliers() {
    $con = $this->opencon();
    return $con->query("SELECT * FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);
}

function updateSupplier( $Sup_Name, $Sup_CoInfo, $Sup_PayTerm, $Sup_DeSched, $SupplierID) {
    $con = $this->opencon();
    try {
        $con->beginTransaction();
        $stmt = $con->prepare("UPDATE suppliers SET Sup_Name = ?, Sup_CoInfo = ?, Sup_PayTerm = ?, Sup_DeSched = ? WHERE SupplierID = ?");
        $updated = $stmt->execute([$Sup_Name, $Sup_CoInfo, $Sup_PayTerm, $Sup_DeSched, $SupplierID]);
        $con->commit();
        return $updated;
    } catch (PDOException $e) {
        $con->rollBack();
        return false;
    }
}

function deleteSupplier($id) {
    try {
        $con = $this->opencon();
        $con->beginTransaction();
        $stmt = $con->prepare("DELETE FROM suppliers WHERE SupplierID = ?");
        $result = $stmt->execute([$id]);
        $con->commit();
        return $result;
    } catch (PDOException $e) {
        if (isset($con)) $con->rollBack();
        return false;
    }
}

function getProductById($id) {
    $con = $this->opencon();
    $stmt = $con->prepare("SELECT * FROM products WHERE ProductID = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
 

    function registerUser($username, $password, $role, $photo = null) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $con->prepare("INSERT INTO USER_ACCOUNTS (User_Name, User_Password, User_Role, User_Photo) VALUES (?, ?, ?, ?)");
            $stmt->execute([$username, $hashedPassword, $role, $photo]);
            
            $userID = $con->lastInsertId();
            $con->commit();
            return $userID;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            return false;
        }
    }

    function loginUser($username, $password) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT * FROM USER_ACCOUNTS WHERE User_Name = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['User_Password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    function getUserById($userID) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT * FROM USER_ACCOUNTS WHERE UserID = ?");
            $stmt->execute([$userID]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    function updateUserProfile($userID, $username, $photo = null) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            if ($photo) {
                $stmt = $con->prepare("UPDATE USER_ACCOUNTS SET User_Name = ?, User_Photo = ? WHERE UserID = ?");
                $stmt->execute([$username, $photo, $userID]);
            } else {
                $stmt = $con->prepare("UPDATE USER_ACCOUNTS SET User_Name = ? WHERE UserID = ?");
                $stmt->execute([$username, $userID]);
            }
            
            $con->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            return false;
        }
    }

    function updateUserPassword($userID, $newPassword) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $con->prepare("UPDATE USER_ACCOUNTS SET User_Password = ? WHERE UserID = ?");
            $stmt->execute([$hashedPassword, $userID]);
            
            $con->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            return false;
        }
    }

    function checkUsernameExists($username) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT COUNT(*) FROM USER_ACCOUNTS WHERE User_Name = ?");
            $stmt->execute([$username]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

        
    function viewLoyaltyProgram() {
        $con = $this->opencon();
        return $con->query("SELECT * FROM loyalty_program")->fetchAll();
    }

}
?>