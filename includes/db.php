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
    try {
        $con = $this->opencon();
        $con->beginTransaction();

        // Fetch the old product details before updating
        $stmt = $con->prepare("SELECT Prod_Price, Prod_Stock FROM products WHERE ProductID = ?");
        $stmt->execute([$id]);
        $oldProduct = $stmt->fetch(PDO::FETCH_ASSOC);
        $oldPrice = $oldProduct ? $oldProduct['Prod_Price'] : null;
        $oldStock = $oldProduct ? $oldProduct['Prod_Stock'] : null;

        // Update the product
        $query = $con->prepare("UPDATE products SET Prod_Name = ?, Prod_Cat = ?, Prod_Desc = ?, Prod_Price = ? , Prod_Stock = ? WHERE ProductID = ? ");
        $query->execute([$name, $category, $description, $price, $stock, $id]);

        // If price changed, add to pricing history
        if ($oldPrice !== null && $oldPrice != $price) {
            $changeDate = date('Y-m-d');
            $effectiveFrom = $changeDate;
            $stmt = $con->prepare("INSERT INTO pricing_history 
                (ProductID, PH_OldPrice, PH_NewPrice, PH_ChangeDate, PH_Effective_from) 
                VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id, $oldPrice, $price, $changeDate, $effectiveFrom]);
        }

        // If stock changed, add to inventory history
        if ($oldStock !== null && $oldStock != $stock) {
            $qtyChange = $stock - $oldStock;
            $changeDate = date('Y-m-d H:i:s');
            $stmt = $con->prepare("INSERT INTO inventory_history 
                (ProductID, IH_QtyChange, IH_NewStckLvl, IH_ChangeDate) 
                VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $qtyChange, $stock, $changeDate]);
        }

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

function addCustomer($customerName, $contactInfo, $discountRate, $enrollLoyalty = false) {
    $con = $this->opencon();
    try {
        $con->beginTransaction();
 
        $stmt = $con->prepare("INSERT INTO customers (Cust_Name, Cust_CoInfo, Cust_DiscRate) VALUES (?, ?, ?)");
        $stmt->execute([$customerName, $contactInfo, $discountRate]);
        $custID = $con->lastInsertId();
 
        // If enroll loyalty is checked, add to loyalty_program
        if ($enrollLoyalty) {
            $tier = 'None'; // Default tier
            $points = 0;      // Default points
            $now = date('Y-m-d H:i:s');
            $stmt2 = $con->prepare("INSERT INTO loyalty_program (CustomerID, LP_PtsBalance, LP_MbspTier, LP_LastUpdt) VALUES (?, ?, ?, ?)");
            $stmt2->execute([$custID, $points, $tier, $now]);
        }
 
        $con->commit();
        return $custID;
    } catch (PDOException $e) {
        $con->rollBack();
        return false;
    }
}
 
    
function viewCustomers() {
    $con = $this->opencon();
    $sql = "SELECT c.*, l.LP_PtsBalance 
            FROM customers c
            LEFT JOIN loyalty_program l ON c.CustomerID = l.CustomerID";
    return $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
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
    $stmt = $con->prepare("
        SELECT l.LoyaltyID, l.CustomerID, c.Cust_Name, l.LP_PtsBalance, l.LP_LastUpdt
        FROM loyalty_program l
        JOIN customers c ON l.CustomerID = c.CustomerID
        ORDER BY l.LoyaltyID
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateMemberTier($customerId) {
    $con = $this->opencon();
    // Get current points
    $stmt = $con->prepare("SELECT LP_PtsBalance FROM loyalty_program WHERE CustomerID = ?");
    $stmt->execute([$customerId]);
    $points = (int)$stmt->fetchColumn();

    // Determine tier
    if ($points >= 15000) {
        $tier = 'Gold';
    } elseif ($points >= 10000) {
        $tier = 'Silver';
    } elseif ($points >= 5000) {
        $tier = 'Bronze';
    } else {
        $tier = 'None';
    }

    // Update tier in DB
    $stmt = $con->prepare("UPDATE loyalty_program SET LP_MbspTier = ? WHERE CustomerID = ?");
    $stmt->execute([$tier, $customerId]);

    // Update loyalty status in customers table
    $stmt = $con->prepare("UPDATE customers SET Cust_LoStat = ? WHERE CustomerID = ?");
    $stmt->execute([$tier, $customerId]);
}

    // Pricing History Functions
    function addPricingHistory($productId, $oldPrice, $newPrice, $changeDate, $effectiveFrom, $effectiveTo = null) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            $stmt = $con->prepare("INSERT INTO pricing_history 
                (ProductID, PH_OldPrice, PH_NewPrice, PH_ChangeDate, PH_Effective_from, PH_Effective_to) 
                VALUES (?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $productId, 
                $oldPrice, 
                $newPrice, 
                $changeDate, 
                $effectiveFrom, 
                $effectiveTo
            ]);
            
            $historyId = $con->lastInsertId();
            $con->commit();
            return $historyId;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            return false;
        }
    }

    function viewPricingHistory($productId = null) {
        try {
            $con = $this->opencon();
            $sql = "SELECT ph.*, p.Prod_Name 
                    FROM pricing_history ph 
                    JOIN products p ON ph.ProductID = p.ProductID";
            
            if ($productId) {
                $sql .= " WHERE ph.ProductID = ?";
                $stmt = $con->prepare($sql);
                $stmt->execute([$productId]);
            } else {
                $stmt = $con->prepare($sql);
                $stmt->execute();
            }
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    function getPricingHistoryStats() {
        try {
            $con = $this->opencon();
            $stats = [];
            
            // Total Records
            $stmt = $con->query("SELECT COUNT(*) FROM pricing_history");
            $stats['totalRecords'] = $stmt->fetchColumn();
            
            // Current Prices (where effective_from <= current date and (effective_to is null or effective_to >= current date))
            $stmt = $con->query("SELECT COUNT(*) FROM pricing_history 
                WHERE PH_Effective_from <= CURDATE() 
                AND (PH_Effective_to IS NULL OR PH_Effective_to >= CURDATE())");
            $stats['currentPrices'] = $stmt->fetchColumn();
            
            // Upcoming Changes (where effective_from > current date)
            $stmt = $con->query("SELECT COUNT(*) FROM pricing_history 
                WHERE PH_Effective_from > CURDATE()");
            $stats['upcomingChanges'] = $stmt->fetchColumn();
            
            // Expired Prices (where effective_to < current date)
            $stmt = $con->query("SELECT COUNT(*) FROM pricing_history 
                WHERE PH_Effective_to < CURDATE()");
            $stats['expiredPrices'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function updatePricingHistory($historyId, $oldPrice, $newPrice, $changeDate, $effectiveFrom, $effectiveTo = null) {
    $con = $this->opencon();
    $sql = "UPDATE pricing_history 
            SET PH_Effective_from = :effectiveFrom, PH_Effective_to = :effectiveTo
            WHERE HistoryID = :historyId";
    $stmt = $con->prepare($sql);
    $stmt->bindParam(':effectiveFrom', $effectiveFrom);
    $stmt->bindParam(':effectiveTo', $effectiveTo);
    $stmt->bindParam(':historyId', $historyId);
    return $stmt->execute();
}

    function deletePricingHistory($historyId) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            $stmt = $con->prepare("DELETE FROM pricing_history WHERE HistoryID = ?");
            $result = $stmt->execute([$historyId]);
            
            $con->commit();
            return $result;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            return false;
        }
    }

    function viewInventoryAlerts() {
    $con = $this->opencon();
    $stmt = $con->prepare("SELECT * FROM inventory_alerts ORDER BY AlertID");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function viewInventoryHistory() {
    $con = $this->opencon();
    $stmt = $con->prepare("
        SELECT ih.IHID, ih.ProductID, p.Prod_Name, ih.IH_QtyChange, ih.IH_NewStckLvl, ih.IH_ChangeDate
        FROM inventory_history ih
        JOIN products p ON ih.ProductID = p.ProductID
        ORDER BY ih.IH_ChangeDate DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPurchaseOrders() {
    $con = $this->opencon();
    $stmt = $con->prepare("
        SELECT po.Pur_OrderID, po.SupplierID, s.Sup_Name, po.PO_Order_Date, po.PO_Order_Stat
        FROM purchase_orders po
        JOIN suppliers s ON po.SupplierID = s.SupplierID
        ORDER BY po.Pur_OrderID DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

}
?>