<?php

    date_default_timezone_set('Asia/Manila');
    
class database{



    function opencon(): PDO{
        $pdo = new PDO(
            dsn: 'mysql:host=mysql.hostinger.com;
            dbname=u689218423_agrifeeds',
            username: 'u689218423_agrifeeds',
            password: '@Agrifeeds12345'
        );
        // Ensure SQL errors throw exceptions so issues are visible in logs
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $pdo;
    }

    function addProduct($productName, $category, $description, $price, $stock, $imagePath = null) {
        $con = $this->opencon();
       
        try {
            if (!isset($_SESSION['user_id'])) {
                error_log("User not logged in when adding product");
                return ['success' => false, 'message' => 'User not logged in'];
            }

            $con->beginTransaction();
 
            // Debug the image path
            error_log("Image path being saved: " . ($imagePath ?? 'null'));
 
            $stmt = $con->prepare("INSERT INTO products (Prod_Name, Prod_Cat, Prod_Desc, Prod_Price, Prod_Stock, Prod_Image, UserID, Prod_Created_at, Prod_Updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            
            if (!$stmt) {
                error_log("Failed to prepare statement: " . implode(", ", $con->errorInfo()));
                throw new PDOException(implode(", ", $con->errorInfo()));
            }

            $params = [$productName, $category, $description, $price, $stock, $imagePath, $_SESSION['user_id']];
            error_log("SQL Parameters: " . print_r($params, true));

            $result = $stmt->execute($params);
           
            if (!$result) {
                error_log("Failed to execute statement: " . implode(", ", $stmt->errorInfo()));
                throw new PDOException(implode(", ", $stmt->errorInfo()));
            }

            $productID = $con->lastInsertId();
            error_log("Product added successfully with ID: " . $productID);
 
            $con->commit();
            return ['success' => true, 'message' => 'Product added successfully'];
 
        } catch (PDOException $e) {
            if (isset($con)) $con->rollback();
            error_log("Error adding product: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    function viewProducts($showDiscontinued = false) {
        $con = $this->opencon();
        $sql = "SELECT * FROM products WHERE discontinued = 0 OR discontinued IS NULL";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getDiscontinuedProducts() {
        $con = $this->opencon();
        $sql = "SELECT * FROM products WHERE discontinued = 1";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function updateProduct($name, $category, $description, $price, $stock, $id, $imagePath = null) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // Fetch the old product details before updating
            $stmt = $con->prepare("SELECT Prod_Name, Prod_Cat, Prod_Desc, Prod_Price, Prod_Stock, Prod_Image FROM products WHERE ProductID = ?");
            $stmt->execute([$id]);
            $oldProduct = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Track changes
            $changes = [];
            if ($oldProduct['Prod_Name'] !== $name) {
                $changes[] = "name from '{$oldProduct['Prod_Name']}' to '{$name}'";
            }
            if ($oldProduct['Prod_Cat'] !== $category) {
                $changes[] = "category from '{$oldProduct['Prod_Cat']}' to '{$category}'";
            }
            if ($oldProduct['Prod_Desc'] !== $description) {
                $changes[] = "description";
            }
            if ($oldProduct['Prod_Price'] != $price) {
                $changes[] = "price from ₱" . number_format($oldProduct['Prod_Price'], 2) . " to ₱" . number_format($price, 2);
            }
            if ($oldProduct['Prod_Stock'] != $stock) {
                $changes[] = "stock from {$oldProduct['Prod_Stock']} to {$stock}";
            }
            if ($imagePath !== null && $oldProduct['Prod_Image'] !== $imagePath) {
                $changes[] = "image";
            }

            // Update the product
            $sql = "UPDATE products SET Prod_Name = ?, Prod_Cat = ?, Prod_Desc = ?, Prod_Price = ?, Prod_Stock = ?, Prod_Updated_at = NOW()";
            $params = [$name, $category, $description, $price, $stock];
            
            // Add image path to update if provided
            if ($imagePath !== null) {
                $sql .= ", Prod_Image = ?";
                $params[] = $imagePath;
            }
            
            $sql .= " WHERE ProductID = ?";
            $params[] = $id;
            
            $query = $con->prepare($sql);
            $query->execute($params);

            // If price changed, add to pricing history
            if ($oldProduct['Prod_Price'] != $price) {
                $changeDate = date('Y-m-d');
                $effectiveFrom = $changeDate;
                $stmt = $con->prepare("INSERT INTO pricing_history 
                    (ProductID, PH_OldPrice, PH_NewPrice, PH_ChangeDate, PH_Effective_from) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id, $oldProduct['Prod_Price'], $price, $changeDate, $effectiveFrom]);
            }

            // If stock changed, add to inventory history
            if ($oldProduct['Prod_Stock'] != $stock) {
                $qtyChange = $stock - $oldProduct['Prod_Stock'];
                $stmt = $con->prepare("INSERT INTO inventory_history 
                    (ProductID, IH_QtyChange, IH_NewStckLvl, IH_ChangeDate) 
                    VALUES (?, ?, ?, NOW())");
                $stmt->execute([$id, $qtyChange, $stock]);
            }

            // Log the changes in product_access_log
            if (!empty($changes)) {
                $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
                
                // Log each change separately for better tracking
                foreach ($changes as $change) {
                    $stmt = $con->prepare("INSERT INTO product_access_log (ProductID, UserID, Pal_Action, Pal_TimeStamp) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$id, $userId, "Updated product: " . $change]);
                }
            }

            $con->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollback();
            error_log("Error updating product: " . $e->getMessage());
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

    function addCustomer($firstName, $lastName, $contactInfo, $discountRate, $enrollLoyalty = false) {
        $con = $this->opencon();
        try {
            $con->beginTransaction();
 
            $stmt = $con->prepare("INSERT INTO customers (Cust_FN, Cust_LN, Cust_CoInfo, Cust_DiscRate) VALUES (?, ?, ?, ?)");
            $stmt->execute([$firstName, $lastName, $contactInfo, $discountRate]);
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
 

    function registerUser($username, $password, $role, $photo = null, $firstName = null, $lastName = null, $contactInfo = null) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert into USER_ACCOUNTS
            $stmt = $con->prepare("INSERT INTO user_accounts (User_Name, User_Password, User_Role, User_Photo, User_CreatedAt) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$username, $hashedPassword, $role, $photo]);
            
            $userID = $con->lastInsertId();

            // Insert into Customers
            $stmt = $con->prepare("INSERT INTO customers (UserID, Cust_FN, Cust_LN, Cust_CoInfo, Cust_LoStat, Cust_DiscRate) VALUES (?, ?, ?, ?, 'None', '0.00')");
            $stmt->execute([$userID, $firstName, $lastName, $contactInfo]);
            
            $customerID = $con->lastInsertId();

            // Insert into Loyalty_Program
            $stmt = $con->prepare("INSERT INTO loyalty_program (CustomerID, LP_PtsBalance, LP_MbspTier, LP_LastUpdt) VALUES (?, 0, 'None', NOW())");
            $stmt->execute([$customerID]);

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
            $stmt = $con->prepare("SELECT * FROM user_accounts WHERE User_Name = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            error_log("Login attempt for username: " . $username);
            error_log("User found: " . ($user ? 'yes' : 'no'));
            
            if ($user) {
                error_log("User role: " . $user['User_Role']);
                error_log("Password verification: " . (password_verify($password, $user['User_Password']) ? 'success' : 'failed'));
            }
            
            if ($user && password_verify($password, $user['User_Password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    function getUserById($userID) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT * FROM user_accounts WHERE UserID = ?");
            $stmt->execute([$userID]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("getUserById - UserID: " . $userID . ", Result: " . print_r($user, true));
            return $user;
        } catch (PDOException $e) {
            error_log("getUserById error: " . $e->getMessage());
            return false;
        }
    }

    function updateUserProfile($userID, $username, $photo = null) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            if ($photo) {
                $stmt = $con->prepare("UPDATE user_accounts SET User_Name = ?, User_Photo = ? WHERE UserID = ?");
                $stmt->execute([$username, $photo, $userID]);
            } else {
                $stmt = $con->prepare("UPDATE user_accounts SET User_Name = ? WHERE UserID = ?");
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
            $stmt = $con->prepare("UPDATE user_accounts SET User_Password = ? WHERE UserID = ?");
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
            $stmt = $con->prepare("SELECT COUNT(*) FROM user_accounts WHERE User_Name = ?");
            $stmt->execute([$username]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
        
    function viewLoyaltyProgram() {
        $con = $this->opencon();
        $stmt = $con->prepare("
            SELECT l.LoyaltyID, l.CustomerID, c.Cust_FN, c.Cust_LN, l.LP_PtsBalance, l.LP_LastUpdt
            FROM loyalty_program l
            JOIN customers c ON l.CustomerID = c.CustomerID
            ORDER BY l.LoyaltyID
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateMemberTier($customerId) {
        $con = $this->opencon();

        // Get current points
        $stmt = $con->prepare("SELECT LP_PtsBalance FROM loyalty_program WHERE CustomerID = ?");
        $stmt->execute([$customerId]);
        $points = (int)$stmt->fetchColumn();

        // Fetch tier thresholds from settings (no fixed defaults)
        $settings = $con->query("SELECT bronze, silver, gold FROM loyalty_settings WHERE LSID = 1")->fetch(PDO::FETCH_ASSOC);

        if (!$settings) {
            // Optionally, handle error if settings are missing
            return;
        }

        $bronze = (int)$settings['bronze'];
        $silver = (int)$settings['silver'];
        $gold   = (int)$settings['gold'];

        // Determine tier and discount rate (with correct range logic)
        if ($points >= $gold) {
            $tier = 'Gold';
            $discount = 15;
        } elseif ($points >= $silver) {
            $tier = 'Silver';
            $discount = 10;
        } elseif ($points >= $bronze) {
            $tier = 'Bronze';
            $discount = 5;
        } else {
            $tier = 'None';
            $discount = 0;
        }

        // Update tier in DB
        $stmt = $con->prepare("UPDATE loyalty_program SET LP_MbspTier = ? WHERE CustomerID = ?");
        $stmt->execute([$tier, $customerId]);

        // Update loyalty status and discount rate in customers table
        $stmt = $con->prepare("UPDATE customers SET Cust_LoStat = ?, Cust_DiscRate = ? WHERE CustomerID = ?");
        $stmt->execute([$tier, $discount, $customerId]);
    }

    // Pricing History Functions
    function addPricingHistory($productId, $oldPrice, $newPrice, $changeDate, $effectiveFrom, $effectiveTo = null) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            // Get the current user ID from session
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            $stmt = $con->prepare("INSERT INTO pricing_history 
                (ProductID, PH_OldPrice, PH_NewPrice, PH_ChangeDate, PH_Effective_from, PH_Effective_to, UserID) 
                VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            $stmt->execute([
                $productId, 
                $oldPrice, 
                $newPrice, 
                $changeDate, 
                $effectiveFrom, 
                $effectiveTo,
                $userId
            ]);
            
            $historyId = $con->lastInsertId();
            $con->commit();
            return $historyId;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
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

    function viewInventoryHistory() {
        $con = $this->opencon();
        $stmt = $con->prepare("
            SELECT ih.*, p.Prod_Name 
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
            SELECT 
                po.*,
                s.Sup_Name,
                s.Sup_CoInfo,
                s.Sup_PayTerm,
                s.Sup_DeSched,
                GROUP_CONCAT(
                    CONCAT(
                        p.Prod_Name, ' (',
                        poi.Pur_OIQuantity, ' x ₱',
                        poi.Pur_OIPrice, ')'
                    ) SEPARATOR ', '
                ) as items_list,
                GROUP_CONCAT(
                    CONCAT(
                        p.Prod_Name, ':', poi.Pur_OIPrice
                    ) SEPARATOR ','
                ) as item_prices,
                COUNT(poi.Pur_OrderItemID) as total_items,
                SUM(poi.Pur_OIQuantity * poi.Pur_OIPrice) as total_amount
            FROM purchase_orders po
            JOIN suppliers s ON po.SupplierID = s.SupplierID
            LEFT JOIN purchase_order_item poi ON po.Pur_OrderID = poi.Pur_OrderID
            LEFT JOIN products p ON poi.ProductID = p.ProductID
            GROUP BY po.Pur_OrderID
            ORDER BY po.Pur_OrderID DESC
        ");
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add current stock levels and inventory history for each order
        foreach ($orders as &$order) {
            // Parse item prices
            $itemPrices = [];
            if ($order['item_prices']) {
                foreach (explode(',', $order['item_prices']) as $price) {
                    list($name, $price) = explode(':', $price);
                    $itemPrices[$name] = $price;
                }
            }
            $order['item_prices'] = $itemPrices;

            // Get current stock levels
            $currentStock = [];
            if ($order['items_list']) {
                $items = explode(', ', $order['items_list']);
                foreach ($items as $item) {
                    $itemName = explode(' (', $item)[0];
                    $stmt = $con->prepare("SELECT Prod_Stock FROM products WHERE Prod_Name = ?");
                    $stmt->execute([$itemName]);
                    $currentStock[$itemName] = $stmt->fetchColumn() ?: 0;
                }
            }
            $order['current_stock'] = $currentStock;

            // Get inventory history
            $inventoryHistory = [];
            if ($order['items_list']) {
                $items = explode(', ', $order['items_list']);
                foreach ($items as $item) {
                    $itemName = explode(' (', $item)[0];
                    $stmt = $con->prepare("
                        SELECT 
                            p.Prod_Name as product_name,
                            ih.IH_QtyChange as quantity_change,
                            ih.IH_NewStckLvl as new_stock_level,
                            ih.IH_ChangeDate as change_date
                        FROM inventory_history ih
                        JOIN products p ON ih.ProductID = p.ProductID
                        WHERE p.Prod_Name = ?
                        ORDER BY ih.IH_ChangeDate DESC
                        LIMIT 5
                    ");
                    $stmt->execute([$itemName]);
                    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    if ($history) {
                        // Add description based on quantity change
                        foreach ($history as &$entry) {
                            $entry['description'] = $entry['quantity_change'] > 0 
                                ? "Stock added from Purchase Order #" . $order['Pur_OrderID']
                                : "Stock reduced from sales";
                        }
                        $inventoryHistory = array_merge($inventoryHistory, $history);
                    }
                }
            }
            $order['inventory_history'] = $inventoryHistory;
        }

        return $orders;
    }

    function viewInventoryAlerts() {
        try {
            $sql = "SELECT 
                    p.Prod_Name,
                    p.Prod_Stock as CurrentStock,
                    CASE 
                        WHEN p.Prod_Stock <= 10 THEN 10
                        WHEN p.Prod_Stock <= 20 THEN 20
                        ELSE 30
                    END as Threshold
                    FROM Products p
                    WHERE p.Prod_Stock <= 30
                    ORDER BY p.Prod_Stock ASC";
            $stmt = $this->opencon()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in viewInventoryAlerts: " . $e->getMessage());
            return [];
        }
    }
 
   
    public function getAllProducts() {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT * FROM products WHERE discontinued = 0 OR discontinued IS NULL");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    public function updateProductStock($productId, $newStock) {
        $con = $this->opencon();
        $stmt = $con->prepare("UPDATE products SET Prod_Stock = ? WHERE ProductID = ?");
        return $stmt->execute([$newStock, $productId]);
    }

    function updateCustomer($id, $firstName, $lastName, $contactInfo, $discountRate, $enrollLoyalty) {
        $con = $this->opencon();
        try {
            $con->beginTransaction();
 
            // Update customer details
            $stmt = $con->prepare("UPDATE customers SET Cust_FN = ?, Cust_LN = ?, Cust_CoInfo = ?, Cust_DiscRate = ? WHERE CustomerID = ?");
            $stmt->execute([$firstName, $lastName, $contactInfo, $discountRate, $id]);
 
            // Handle loyalty program enrollment
            $stmt = $con->prepare("SELECT COUNT(*) FROM loyalty_program WHERE CustomerID = ?");
            $stmt->execute([$id]);
            $isEnrolled = $stmt->fetchColumn() > 0;
 
            if ($enrollLoyalty && !$isEnrolled) {
                // Add to loyalty program
                $tier = 'None';
                $points = 0;
                $now = date('Y-m-d H:i:s');
                $stmt = $con->prepare("INSERT INTO loyalty_program (CustomerID, LP_PtsBalance, LP_MbspTier, LP_LastUpdt) VALUES (?, ?, ?, ?)");
                $stmt->execute([$id, $points, $tier, $now]);
            } elseif (!$enrollLoyalty && $isEnrolled) {
                // Remove from loyalty program
                $stmt = $con->prepare("DELETE FROM loyalty_program WHERE CustomerID = ?");
                $stmt->execute([$id]);
            }
 
            $con->commit();
            return true;
        } catch (PDOException $e) {
            $con->rollBack();
            return false;
        }
    }

    public function addPurchaseOrder($supplierId, $orderDate, $paymentTerms, $notes, $shippingCost, $totalAmount, $items = []) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // Debug log
            error_log("Adding purchase order with data: " . print_r([
                'supplierId' => $supplierId,
                'orderDate' => $orderDate,
                'paymentTerms' => $paymentTerms,
                'notes' => $notes,
                'shippingCost' => $shippingCost,
                'totalAmount' => $totalAmount,
                'items' => $items
            ], true));

            // Insert into purchase_orders
            $stmt = $con->prepare("INSERT INTO purchase_orders 
                (SupplierID, PO_Order_Date, PO_Order_Stat, PO_Total_Amount, PO_Shipping_Cost, PO_Payment_Terms, PO_Notes)
                VALUES (?, ?, 'Waiting', ?, ?, ?, ?)");
            
            $stmt->execute([
                $supplierId,
                $orderDate,
                $totalAmount,
                $shippingCost ?? 0,
                $paymentTerms,
                $notes
            ]);

            $poId = $con->lastInsertId();

            // Insert items
            foreach ($items as $item) {
                $stmt = $con->prepare("INSERT INTO purchase_order_item 
                    (Pur_OrderID, ProductID, Pur_OIQuantity, Pur_OIPrice)
                    VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $poId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            $con->commit();
            return ['success' => true, 'po_id' => $poId];
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            error_log("Error adding purchase order: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to add purchase order: ' . $e->getMessage()];
        }
    }

    public function getCustomerById($customerId) {
        $conn = $this->opencon();
        $stmt = $conn->prepare("
            SELECT c.*, l.LP_PtsBalance, l.LP_MbspTier, l.LP_LastUpdt
            FROM customers c
            LEFT JOIN loyalty_program l ON c.CustomerID = l.CustomerID
            WHERE c.CustomerID = ?
        ");
        $stmt->execute([$customerId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getCustomerCount() {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT COUNT(*) as total FROM customers");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    public function getProductsInStock() {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT COUNT(*) as total FROM products WHERE Prod_Stock > 0");
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['total'] : 0;
    }

    // new methods



    function getUserInfo($userID) {
        $con = $this->opencon();
        $stmt = $con->prepare("
            SELECT * FROM user_accounts 
            WHERE UserID = ?
        ");
        $stmt->execute([$userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function getCustomerInfo($userID) {
        $con = $this->opencon();
        $stmt = $con->prepare("
            SELECT 
                c.*,
                COALESCE(l.LP_PtsBalance, 0) as LP_PtsBalance,
                COALESCE(l.LP_MbspTier, 'None') as Cust_LoStat,
                COALESCE(c.Cust_DiscRate, '0.00') as Cust_DiscRate
            FROM customers c
            LEFT JOIN loyalty_program l ON c.CustomerID = l.CustomerID
            WHERE c.UserID = ?
        ");
        $stmt->execute([$userID]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function getUserOrders($userID, $limit = null, $offset = null) {
        $con = $this->opencon();
        $sql = "
            SELECT 
                s.SaleID AS OrderID,
                s.Sale_Date AS Order_Date,
                s.Sale_Status as Order_Status,
                (SELECT COUNT(*) FROM sale_item WHERE SaleID = s.SaleID) as item_count,
                (SELECT SUM(SI_Quantity * SI_Price) FROM sale_item WHERE SaleID = s.SaleID) as Order_Total,
                p.Prom_Code as PromotionName
            FROM sales s
            JOIN customers c ON s.CustomerID = c.CustomerID
            LEFT JOIN order_promotions op ON s.SaleID = op.SaleID
            LEFT JOIN promotions p ON op.PromotionID = p.PromotionID
            WHERE c.UserID = ?
            ORDER BY s.Sale_Date DESC";

        if ($limit !== null) {
            $sql .= " LIMIT ?";
            if ($offset !== null) {
                $sql .= " OFFSET ?";
            }
        }

        $stmt = $con->prepare($sql);
        
        if ($limit !== null) {
            if ($offset !== null) {
                $stmt->bindValue(1, $userID, PDO::PARAM_INT);
                $stmt->bindValue(2, $limit, PDO::PARAM_INT);
                $stmt->bindValue(3, $offset, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(1, $userID, PDO::PARAM_INT);
                $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            }
        } else {
            $stmt->bindValue(1, $userID, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getOrderDetails($saleID) {
        $con = $this->opencon();
        $stmt = $con->prepare("
            SELECT 
                s.SaleID as OrderID,
                s.Sale_Date as Order_Date,
                s.Sale_Status as Order_Status,
                COALESCE(ph.PT_PayMethod, 'cash') as Payment_Method,
                si.SI_Quantity as Quantity,
                si.SI_Price as Price,
                p.Prod_Name as product_name,
                CONCAT(c.Cust_FN, ' ', c.Cust_LN) as customer_name,
                c.Cust_DiscRate,
                COALESCE(op.OrderP_DiscntApplied, 0) as discount_applied,
                (si.SI_Quantity * si.SI_Price) as Subtotal,
                (SELECT SUM(SI_Quantity * SI_Price) FROM sale_item WHERE SaleID = s.SaleID) as Order_Total,
                prom.Prom_Code as PromotionName,
                CASE 
                    WHEN COALESCE(ph.PT_PayMethod, 'cash') = 'cash' THEN 'Cash'
                    WHEN COALESCE(ph.PT_PayMethod, 'cash') = 'card' THEN 'Card'
                    ELSE 'Cash'
                END as Payment_Method_Display
            FROM sales s
            JOIN sale_item si ON s.SaleID = si.SaleID
            JOIN products p ON si.ProductID = p.ProductID
            JOIN customers c ON s.CustomerID = c.CustomerID
            LEFT JOIN order_promotions op ON s.SaleID = op.SaleID
            LEFT JOIN promotions prom ON op.PromotionID = prom.PromotionID
            LEFT JOIN payment_history ph ON s.SaleID = ph.SaleID
            WHERE s.SaleID = ?
        ");
        $stmt->execute([$saleID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    //


    function getAllUsers($offset = null, $limit = null) {
        try {
            $con = $this->opencon();
            $sql = "SELECT * FROM user_accounts ORDER BY UserID";
            
            if ($offset !== null && $limit !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $con->prepare($sql);
                $stmt->bindValue(1, $limit, PDO::PARAM_INT);
                $stmt->bindValue(2, $offset, PDO::PARAM_INT);
            } else {
                $stmt = $con->prepare($sql);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("getAllUsers - Number of users fetched: " . count($results));
            error_log("getAllUsers - Offset: " . $offset . ", Limit: " . $limit);
            return $results;
        } catch (PDOException $e) {
            error_log("getAllUsers error: " . $e->getMessage());
            return false;
        }
    }

    function getTotalUsers() {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT COUNT(*) as total FROM user_accounts");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $count = (int)$result['total'];
            error_log("getTotalUsers - Raw count from database: " . $count);
            return $count;
        } catch (PDOException $e) {
            error_log("getTotalUsers error: " . $e->getMessage());
            return 0;
        }
    }

    function updateUserRole($userID, $newRole) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();
            
            $stmt = $con->prepare("UPDATE user_accounts SET User_Role = ? WHERE UserID = ?");
            $result = $stmt->execute([$newRole, $userID]);
            
            if ($result) {
                error_log("Successfully updated role for user $userID to $newRole");
            } else {
                error_log("Failed to update role for user $userID");
            }
            
            $con->commit();
            return $result;
        } catch (PDOException $e) {
            error_log("updateUserRole error: " . $e->getMessage());
            if (isset($con)) $con->rollBack();
            return false;
        }
    }

    function addAuditLog($userId, $action, $details) {
        try {
            $con = $this->opencon();
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            
            $stmt = $con->prepare("INSERT INTO audit_logs (user_id, action, details, ip_address, timestamp) 
                                  VALUES (?, ?, ?, ?, NOW())");
            $result = $stmt->execute([$userId, $action, $details, $ipAddress]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error adding audit log: " . $e->getMessage());
            return false;
        }
    }

    function getAuditLogs($start = 0, $length = 25, $search = '', $orderColumn = 'timestamp', $orderDir = 'DESC') {
        try {
            $con = $this->opencon();
            
            $query = "SELECT a.*, u.User_Name 
                     FROM audit_logs a 
                     LEFT JOIN user_accounts u ON a.user_id = u.UserID";
            
            if (!empty($search)) {
                $query .= " WHERE a.action LIKE :search 
                           OR a.details LIKE :search 
                           OR u.User_Name LIKE :search 
                           OR a.ip_address LIKE :search";
            }
            
            $query .= " ORDER BY $orderColumn $orderDir LIMIT :start, :length";
            
            $stmt = $con->prepare($query);
            
            if (!empty($search)) {
                $searchParam = "%$search%";
                $stmt->bindParam(':search', $searchParam);
            }
            
            $stmt->bindParam(':start', $start, PDO::PARAM_INT);
            $stmt->bindParam(':length', $length, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting audit logs: " . $e->getMessage());
            return false;
        }
    }

    function getTotalAuditLogs($search = '') {
        try {
            $con = $this->opencon();
            
            $query = "SELECT COUNT(*) FROM audit_logs a 
                     LEFT JOIN user_accounts u ON a.user_id = u.UserID";
            
            if (!empty($search)) {
                $query .= " WHERE a.action LIKE :search 
                           OR a.details LIKE :search 
                           OR u.User_Name LIKE :search 
                           OR a.ip_address LIKE :search";
            }
            
            $stmt = $con->prepare($query);
            
            if (!empty($search)) {
                $searchParam = "%$search%";
                $stmt->bindParam(':search', $searchParam);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error getting total audit logs: " . $e->getMessage());
            return 0;
        }
    }

    function getAvailablePromos($userID = null) {
        $con = $this->opencon();
        $today = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM promotions 
            WHERE Promo_IsActive = 1 
              AND DATE(Promo_StartDate) <= CURDATE() 
              AND DATE(Promo_EndDate) >= CURDATE()";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        $promos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Filter by usage limit
        $filtered = [];
        foreach ($promos as $promo) {
            if (empty($promo['UsageLimit']) || $promo['UsageLimit'] == 0) {
                $filtered[] = $promo; // Unlimited usage
                continue;
            }
            $used = $this->getPromoUsageCount($promo['PromotionID']);
            if ($used < $promo['UsageLimit']) {
                $filtered[] = $promo;
            }
        }
        return $filtered;
    }

     function getPromoByCode($code) {
        $con = $this->opencon();
        $sql = "SELECT * FROM promotions 
            WHERE Promo_IsActive = 1 
              AND DATE(Promo_StartDate) <= CURDATE() 
              AND DATE(Promo_EndDate) >= CURDATE()";
        $stmt = $con->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getPromoUsageCount($promotionId) {
        $con = $this->opencon();
        $stmt = $con->prepare("SELECT COUNT(*) FROM promo_usage WHERE PromotionID = ?");
        $stmt->execute([$promotionId]);
        return (int)$stmt->fetchColumn();
    }

    function logPromoUsage($promotionId, $userId) {
        $con = $this->opencon();
        $stmt = $con->prepare("INSERT INTO promo_usage (PromotionID, UserID) VALUES (?, ?)");
        $stmt->execute([$promotionId, $userId]);
    }

    public function addInventoryHistory($productId, $qtyChange, $newStockLevel) {
        try {
            $con = $this->opencon();
            // Get the current user ID from session
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
            
            $stmt = $con->prepare("INSERT INTO inventory_history 
                (ProductID, IH_QtyChange, IH_NewStckLvl, IH_ChangeDate, UserID) 
                VALUES (?, ?, ?, NOW(), ?)");
            return $stmt->execute([$productId, $qtyChange, $newStockLevel, $userId]);
        } catch (PDOException $e) {
            error_log("Error adding inventory history: " . $e->getMessage());
            return false;
        }
    }

    public function createSale($customerId, $paymentMethod, $cartItems, $promoCode = '', $promoDiscount = 0) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // Insert into Sales table (match your columns)
            $stmt = $con->prepare("INSERT INTO Sales (CustomerID, Sale_Date, Sale_Per) VALUES (?, NOW(), ?)");
            $stmt->execute([$customerId, $paymentMethod]);
            $saleId = $con->lastInsertId();

            // Insert each item into Sale_Item table
            $itemStmt = $con->prepare("INSERT INTO Sale_Item (SaleID, ProductID, SI_Quantity, SI_Price) VALUES (?, ?, ?, ?)");
            foreach ($cartItems as $item) {
                $itemStmt->execute([$saleId, $item['id'], $item['quantity'], $item['price']]);
            }

            $con->commit();
            return $saleId;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            error_log("createSale error: " . $e->getMessage());
            return false;
        }
    }

    public function updatePurchaseOrderStatus($poId, $newStatus) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // Update purchase order status
            $stmt = $con->prepare("UPDATE purchase_orders SET PO_Order_Stat = ? WHERE Pur_OrderID = ?");
            $stmt->execute([$newStatus, $poId]);

            // If status is 'Received', update stock levels
            if ($newStatus === 'Received') {
                // Get purchase order items
                $stmt = $con->prepare("
                    SELECT ProductID, Pur_OIQuantity 
                    FROM purchase_order_item 
                    WHERE Pur_OrderID = ?
                ");
                $stmt->execute([$poId]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Update stock for each item
                foreach ($items as $item) {
                    // Get current stock
                    $stmt = $con->prepare("SELECT Prod_Stock FROM products WHERE ProductID = ?");
                    $stmt->execute([$item['ProductID']]);
                    $currentStock = $stmt->fetchColumn();

                    // Calculate new stock level
                    $newStock = $currentStock + $item['Pur_OIQuantity'];

                    // Update stock
                    $stmt = $con->prepare("UPDATE products SET Prod_Stock = ? WHERE ProductID = ?");
                    $stmt->execute([$newStock, $item['ProductID']]);

                    // Add to inventory history
                    $stmt = $con->prepare("
                        INSERT INTO inventory_history 
                        (ProductID, IH_QtyChange, IH_NewStckLvl, IH_ChangeDate) 
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $item['ProductID'],
                        $item['Pur_OIQuantity'],
                        $newStock
                    ]);
                }

                // Log the action
                if (isset($_SESSION['user_id'])) {
                    $this->addAuditLog(
                        $_SESSION['user_id'],
                        'RECEIVE_PURCHASE_ORDER',
                        "Received purchase order #$poId and updated stock levels"
                    );
                }
            }

            $con->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            return false;
        }
    }

    public function getPurchaseOrderItems($poId) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT * FROM purchase_order_item WHERE Pur_OrderID = ?");
            $stmt->execute([$poId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getPurchaseOrderItems: " . $e->getMessage());
            throw new Exception("Failed to get purchase order items: " . $e->getMessage());
        }
    }

    public function getProductStock($productId) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT Prod_Stock FROM products WHERE ProductID = ?");
            $stmt->execute([$productId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['Prod_Stock'] : 0;
        } catch (PDOException $e) {
            error_log("Error in getProductStock: " . $e->getMessage());
            throw new Exception("Failed to get product stock: " . $e->getMessage());
        }
    }

    public function logInventoryChange($productId, $quantityChange, $newStockLevel, $description) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("INSERT INTO inventory_history 
                (ProductID, IH_QtyChange, IH_NewStckLvl, IH_ChangeDate) 
                VALUES (?, ?, ?, NOW())");
            $result = $stmt->execute([$productId, $quantityChange, $newStockLevel]);
            if (!$result) {
                throw new PDOException("Failed to log inventory change");
            }
            return true;
        } catch (PDOException $e) {
            error_log("Error in logInventoryChange: " . $e->getMessage());
            throw new Exception("Failed to log inventory change: " . $e->getMessage());
        }
    }

    public function savePurchaseOrder($data) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // Debug log
            error_log("Saving purchase order with data: " . print_r($data, true));

            // Insert into purchase_orders
            $stmt = $con->prepare("INSERT INTO purchase_orders 
                (SupplierID, PO_Order_Date, PO_Order_Stat, PO_Total_Amount)
                VALUES (?, ?, 'Waiting', ?)");
            
            $stmt->execute([
                $data['supplier_id'],
                $data['order_date'],
                $data['total_amount']
            ]);

            $poId = $con->lastInsertId();

            // Insert items
            foreach ($data['items'] as $item) {
                $stmt = $con->prepare("INSERT INTO purchase_order_item 
                    (Pur_OrderID, ProductID, Pur_OIQuantity, Pur_OIPrice)
                    VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $poId,
                    $item['product_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            $con->commit();
            return ['success' => true, 'po_id' => $poId];
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            error_log("Error saving purchase order: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to save purchase order: ' . $e->getMessage()];
        }
    }

    public function markPurchaseOrderDelivered($poId) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // Get purchase order items
            $items = $this->getPurchaseOrderItems($poId);
            
            // Update stock for each item
            foreach ($items as $item) {
                $productId = $item['ProductID'];
                $quantity = $item['Pur_OIQuantity'];
                
                // Get current stock
                $currentStock = $this->getProductStock($productId);
                $newStock = $currentStock + $quantity;
                
                // Update stock
                $this->updateProductStock($productId, $newStock);
                
                // Log inventory change
                $this->logInventoryChange(
                    $productId,
                    $quantity,
                    $newStock,
                    "Stock added from Purchase Order #$poId"
                );
            }

            // Update purchase order status
            $this->updatePurchaseOrderStatus($poId, 'Delivered');

            $con->commit();
            return [
                'success' => true,
                'message' => 'Purchase order has been marked as delivered and stock has been updated successfully.'
            ];

        } catch (Exception $e) {
            if (isset($con)) {
                $con->rollBack();
            }
            error_log("Error in markPurchaseOrderDelivered: " . $e->getMessage());
            throw new Exception("Failed to mark purchase order as delivered: " . $e->getMessage());
        }
    }


    public function viewSales() {
        $conn = $this->opencon();
        $stmt = $conn->prepare("
            SELECT 
                s.SaleID, 
                s.Sale_Date, 
                s.Sale_Per, 
                s.CustomerID, 
                CONCAT(c.Cust_FN, ' ', c.Cust_LN) AS CustomerName, 
                p.Prom_Code AS PromotionName,
                s.Sale_Status
            FROM sales s
            LEFT JOIN user_accounts a ON s.Sale_Per = a.UserID
            LEFT JOIN customers c ON s.CustomerID = c.CustomerID
            LEFT JOIN order_promotions op ON s.SaleID = op.SaleID
            LEFT JOIN promotions p ON op.PromotionID = p.PromotionID
            ORDER BY s.SaleID DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function getProductAccessLogs($productId = null) {
        try {
            $con = $this->opencon();
        $sql = "SELECT pal.*, u.User_Name 
            FROM product_access_log pal 
            LEFT JOIN user_accounts u ON pal.UserID = u.UserID";
            
            if ($productId) {
                $sql .= " WHERE pal.ProductID = ?";
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

    function getUserPromoUsageCount($promotionId, $userId) {
        $conn = $this->opencon();
        $stmt = $conn->prepare("SELECT COUNT(*) FROM promo_usage WHERE PromotionID = ? AND UserID = ?");
        $stmt->execute([$promotionId, $userId]);
        return $stmt->fetchColumn();
    }

    function getAllPricingHistory() {
        $conn = $this->opencon();
        $stmt = $conn->prepare("
            SELECT ph.*, p.Prod_Name 
            FROM pricing_history ph
            JOIN products p ON ph.ProductID = p.ProductID
            ORDER BY ph.PH_ChangeDate DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function saveLoyaltySettings($bronze, $silver, $gold, $minPurchase, $pointsPerPeso, $pointsExpireAfter) {
        $stmt = $this->opencon()->prepare("UPDATE loyalty_settings SET bronze=?, silver=?, gold=?, min_purchase=?, points_per_peso=?, points_expire_after=? WHERE LSID=1");
        $stmt->execute([$bronze, $silver, $gold, $minPurchase, $pointsPerPeso, $pointsExpireAfter]);
    }
 
 
    public function resetExpiredPoints($pointsExpireAfter) {
        $pdo = $this->opencon();
        $expireDate = date('Y-m-d H:i:s', strtotime("-$pointsExpireAfter months"));
 
        // Use LP_LastUpdt instead of last_earned
        $stmt = $pdo->prepare("SELECT CustomerID FROM loyalty_program WHERE LP_LastUpdt <= ?");
        $stmt->execute([$expireDate]);
        $expiredMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
        foreach ($expiredMembers as $member) {
            $update = $pdo->prepare("UPDATE loyalty_program SET LP_PtsBalance = 0 WHERE CustomerID = ?");
            $update->execute([$member['CustomerID']]);
        }
    }

    public function getTotalSales() {
        try {
        $sql = "SELECT COALESCE(SUM(SI.SI_Price * SI.SI_Quantity), 0) as total_sales 
            FROM sales S 
            JOIN sale_item SI ON S.SaleID = SI.SaleID 
                    WHERE S.Sale_Date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    AND S.Sale_Status = 'Completed'";
            $stmt = $this->opencon()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_sales'];
        } catch (PDOException $e) {
            error_log("Error in getTotalSales: " . $e->getMessage());
            return 0;
        }
    }

    public function getTotalOrders($userID) {
        try {
        $sql = "SELECT COUNT(*) as total_orders 
            FROM sales s
            JOIN customers c ON s.CustomerID = c.CustomerID
                    WHERE c.UserID = ?";
            $stmt = $this->opencon()->prepare($sql);
            $stmt->execute([$userID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_orders'];
        } catch (PDOException $e) {
            error_log("Error in getTotalOrders: " . $e->getMessage());
            return 0;
        }
    }

    public function getSalesData() {
        try {
        $sql = "SELECT DATE(S.Sale_Date) as date, 
            SUM(SI.SI_Price * SI.SI_Quantity) as total_sales 
            FROM sales S 
            JOIN sale_item SI ON S.SaleID = SI.SaleID 
                    WHERE S.Sale_Date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    AND S.Sale_Status = 'Completed'
                    GROUP BY DATE(S.Sale_Date) 
                    ORDER BY date";
            $stmt = $this->opencon()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            foreach ($results as $row) {
                $labels[] = date('M d', strtotime($row['date']));
                $data[] = $row['total_sales'];
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (PDOException $e) {
            error_log("Error in getSalesData: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    public function getTopProducts() {
        try {
        $sql = "SELECT 
                    P.Prod_Name,
                    SUM(SI.SI_Quantity) as total_quantity,
                    SUM(SI.SI_Price * SI.SI_Quantity) as total_sales
            FROM products P
            JOIN sale_item SI ON P.ProductID = SI.ProductID
            JOIN sales S ON SI.SaleID = S.SaleID
                    WHERE S.Sale_Date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
                    AND S.Sale_Status = 'Completed'
                    GROUP BY P.ProductID, P.Prod_Name
                    ORDER BY total_sales DESC
                    LIMIT 5";
            $stmt = $this->opencon()->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $labels = [];
            $data = [];
            foreach ($results as $row) {
                $labels[] = $row['Prod_Name'];
                $data[] = $row['total_sales'];
            }

            return [
                'labels' => $labels,
                'data' => $data
            ];
        } catch (PDOException $e) {
            error_log("Error in getTopProducts: " . $e->getMessage());
            return ['labels' => [], 'data' => []];
        }
    }

    public function getRecentOrders($limit = 5) {
        try {
        $sql = "SELECT S.SaleID, 
                    CONCAT(C.Cust_FN, ' ', C.Cust_LN) as CustomerName,
                    SUM(SI.SI_Price * SI.SI_Quantity) as TotalAmount,
            S.Sale_Status as Status
            FROM sales S 
            JOIN customers C ON S.CustomerID = C.CustomerID 
            JOIN sale_item SI ON S.SaleID = SI.SaleID 
            GROUP BY S.SaleID, C.Cust_FN, C.Cust_LN, S.Sale_Status 
                    ORDER BY S.Sale_Date DESC 
                    LIMIT :limit";
            $stmt = $this->opencon()->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getRecentOrders: " . $e->getMessage());
            return [];
        }
    }

    function getUserRecentOrders($userID, $limit = 5) {
        $con = $this->opencon();
        $stmt = $con->prepare("
            SELECT 
                s.SaleID,
                s.Sale_Date as Order_Date,
                s.Sale_Status as Order_Status,
                (SELECT COUNT(*) FROM sale_item WHERE SaleID = s.SaleID) as item_count,
                (SELECT SUM(SI_Quantity * SI_Price) FROM sale_item WHERE SaleID = s.SaleID) as Order_Total
            FROM sales s
            JOIN customers c ON s.CustomerID = c.CustomerID
            WHERE c.UserID = ?
            ORDER BY s.Sale_Date DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userID, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserTotalOrders($userID) {
        try {
        $sql = "SELECT COUNT(*) as total_orders 
            FROM sales s
            JOIN customers c ON s.CustomerID = c.CustomerID
                    WHERE c.UserID = ?";
            $stmt = $this->opencon()->prepare($sql);
            $stmt->execute([$userID]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_orders'];
        } catch (PDOException $e) {
            error_log("Error in getUserTotalOrders: " . $e->getMessage());
            return 0;
        }
    }

// ... existing code ...

    public function getTotalSystemOrders() {
        try {
        $sql = "SELECT COUNT(*) as total_orders 
            FROM sales 
                    WHERE Sale_Status = 'Completed'";
            $stmt = $this->opencon()->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_orders'];
        } catch (PDOException $e) {
            error_log("Error in getTotalSystemOrders: " . $e->getMessage());
            return 0;
        }
    }

    // ... existing code ...

    function discontinueProduct($id) {
        try {
            $con = $this->opencon();
            $sql = "UPDATE products SET discontinued = 1 WHERE ProductID = ?";
            $stmt = $con->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                // Log the action
                $this->addAuditLog($_SESSION['user_id'], 'Product Discontinued', "Product ID: $id");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error discontinuing product: " . $e->getMessage());
            return false;
        }
    }

    function restoreProduct($id) {
        try {
            $con = $this->opencon();
            $sql = "UPDATE products SET discontinued = 0 WHERE ProductID = ?";
            $stmt = $con->prepare($sql);
            $result = $stmt->execute([$id]);
            
            if ($result) {
                // Log the action
                $this->addAuditLog($_SESSION['user_id'], 'Product Restored', "Product ID: $id");
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error restoring product: " . $e->getMessage());
            return false;
        }
    }

    function getTotalProducts($search = '', $category = '', $stock = '') {
        $con = $this->opencon();
        $sql = "SELECT COUNT(*) as total FROM products WHERE (discontinued = 0 OR discontinued IS NULL)";
        $params = [];
        
        if ($search) {
            $sql .= " AND (Prod_Name LIKE ? OR Prod_Desc LIKE ? OR Prod_Cat LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if ($category && $category !== 'all') {
            $sql .= " AND Prod_Cat = ?";
            $params[] = $category;
        }
        
        if ($stock && $stock !== 'all') {
            if ($stock === 'in_stock') {
                $sql .= " AND Prod_Stock > 10";
            } elseif ($stock === 'low_stock') {
                $sql .= " AND Prod_Stock > 0 AND Prod_Stock <= 10";
            } elseif ($stock === 'out_of_stock') {
                $sql .= " AND Prod_Stock = 0";
            }
        }
        
        $stmt = $con->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
    
    function getPaginatedProducts($page = 1, $perPage = 10, $search = '', $category = '', $stock = '') {
        $con = $this->opencon();
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT * FROM products WHERE (discontinued = 0 OR discontinued IS NULL)";
        $params = [];
        
        if ($search) {
            $sql .= " AND (Prod_Name LIKE ? OR Prod_Desc LIKE ? OR Prod_Cat LIKE ?)";
            $searchParam = "%$search%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
        }
        
        if ($category && $category !== 'all') {
            $sql .= " AND Prod_Cat = ?";
            $params[] = $category;
        }
        
        if ($stock && $stock !== 'all') {
            if ($stock === 'in_stock') {
                $sql .= " AND Prod_Stock > 10";
            } elseif ($stock === 'low_stock') {
                $sql .= " AND Prod_Stock > 0 AND Prod_Stock <= 10";
            } elseif ($stock === 'out_of_stock') {
                $sql .= " AND Prod_Stock = 0";
            }
        }
        
        // Convert to integers to ensure proper SQL syntax
        $perPage = (int)$perPage;
        $offset = (int)$offset;
        
        $sql .= " LIMIT $perPage OFFSET $offset";
        
        $stmt = $con->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Update product price with pricing history logic.
     *
     * @param int $productId
     * @param float $newPrice
     * @param string $effectiveFrom (Y-m-d)
     * @param string|null $effectiveTo (Y-m-d or null)
     * @return bool|string Returns true on success, or error message on failure
     */
    public function updateProductPriceWithHistory($productId, $newPrice, $effectiveFrom, $effectiveTo = null) {
        try {
            $con = $this->opencon();
            $con->beginTransaction();

            // 1. Find the current active price history record
            $stmt = $con->prepare("SELECT * FROM pricing_history WHERE ProductID = ? AND (PH_Effective_to IS NULL OR PH_Effective_to >= ?) ORDER BY PH_Effective_from DESC LIMIT 1");
            $stmt->execute([$productId, $effectiveFrom]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            // 2. If found, set its PH_Effective_to to the day before the new effective_from
            if ($current) {
                $oldEffectiveTo = date('Y-m-d', strtotime($effectiveFrom . ' -1 day'));
                $updateStmt = $con->prepare("UPDATE pricing_history SET PH_Effective_to = ? WHERE HistoryID = ?");
                $updateStmt->execute([$oldEffectiveTo, $current['HistoryID']]);
            }

            // 3. Insert new pricing history record
            $changeDate = date('Y-m-d');
            // Find the price in effect on the new effectiveFrom date
            $stmt = $con->prepare("
                SELECT PH_NewPrice FROM pricing_history
                WHERE ProductID = ?
                  AND PH_Effective_from <= ?
                  AND (PH_Effective_to IS NULL OR PH_Effective_to >= ?)
                ORDER BY PH_Effective_from DESC, HistoryID DESC
                LIMIT 1
            ");
            $stmt->execute([$productId, $effectiveFrom, $effectiveFrom]);
            $inEffect = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($inEffect) {
                $oldPrice = $inEffect['PH_NewPrice'];
            } else {
                $prod = $con->prepare("SELECT Prod_Price FROM products WHERE ProductID = ?");
                $prod->execute([$productId]);
                $prodRow = $prod->fetch(PDO::FETCH_ASSOC);
                $oldPrice = $prodRow ? $prodRow['Prod_Price'] : null;
            }

            // Get the current user ID for Product_Access_Log
            $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

            // Insert into pricing_history (without UserID)
            $stmt = $con->prepare("INSERT INTO pricing_history (ProductID, PH_OldPrice, PH_NewPrice, PH_ChangeDate, PH_Effective_from, PH_Effective_to) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $productId,
                $oldPrice,
                $newPrice,
                $changeDate,
                $effectiveFrom,
                $effectiveTo
            ]);

            // Log the price change in Product_Access_Log (with UserID)
            $stmt = $con->prepare("INSERT INTO product_access_log (ProductID, UserID, Pal_Action, Pal_TimeStamp) VALUES (?, ?, ?, NOW())");
            $stmt->execute([
                $productId,
                $userId,
                "Updated product: price from " . number_format($oldPrice, 2) . " to " . number_format($newPrice, 2)
            ]);

            // 4. Update the products table with the new price ONLY if the effective_from is today or earlier
            if (strtotime($effectiveFrom) <= strtotime(date('Y-m-d'))) {
                $updateProd = $con->prepare("UPDATE products SET Prod_Price = ?, Prod_Updated_at = NOW() WHERE ProductID = ?");
                $updateProd->execute([$newPrice, $productId]);
            }

            $con->commit();
            return true;
        } catch (PDOException $e) {
            if (isset($con)) $con->rollBack();
            return $e->getMessage();
        }
    }

    public function updateLoyaltyMember($loyaltyId, $points, $tier) {
        $pdo = $this->opencon();
        $stmt = $pdo->prepare("UPDATE loyalty_program SET LP_PtsBalance = ?, LP_MbspTier = ?, LP_LastUpdt = NOW() WHERE LoyaltyID = ?");
        return $stmt->execute([$points, $tier, $loyaltyId]);
    }

    public function getPointsHistoryByCustomer($customerID) {
        $pdo = $this->opencon();
        // Get LoyaltyID for this customer
        $stmt = $pdo->prepare("SELECT LoyaltyID FROM loyalty_program WHERE CustomerID = ?");
        $stmt->execute([$customerID]);
        $loyalty = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$loyalty) return [];

        $loyaltyID = $loyalty['LoyaltyID'];

        $stmt = $pdo->prepare("
            SELECT 
                LoTranHID,
                LoTranH_TransDate AS Date,
                LoTranH_TransDesc AS Transaction,
                LoTranH_PtsEarned AS PointsEarned,
                LoTranH_PtsRedeemed AS PointsRedeemed
            FROM Loyalty_Transaction_History
            WHERE LoyaltyID = ?
            ORDER BY LoTranH_TransDate DESC
        ");
        $stmt->execute([$loyaltyID]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInventoryHistory($productId) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("
                SELECT ih.*, u.User_Name 
                FROM inventory_history ih
                LEFT JOIN user_accounts u ON ih.UserID = u.UserID
                WHERE ih.ProductID = ?
                ORDER BY ih.IH_ChangeDate DESC
            ");
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting inventory history: " . $e->getMessage());
            return false;
        }
    }

    public function getPricingHistory($productId) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("
                SELECT ph.*, u.User_Name 
                FROM pricing_history ph
                LEFT JOIN user_accounts u ON ph.UserID = u.UserID
                WHERE ph.ProductID = ?
                ORDER BY ph.PH_ChangeDate DESC
            ");
            $stmt->execute([$productId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting pricing history: " . $e->getMessage());
            return false;
        }
    }
}

// Handle direct method calls from JavaScript
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_CONTENT_TYPE']) && strpos($_SERVER['HTTP_CONTENT_TYPE'], 'application/json') !== false) {
    // Prevent any output before JSON response
    ob_clean();
    
    // Set JSON header
    header('Content-Type: application/json');
    
    try {
        // Get JSON data from request body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        // Log the incoming request
        error_log("Incoming request data: " . print_r($data, true));

        if (!$data || !isset($data['method'])) {
            throw new Exception('Invalid request data: Missing method name or invalid JSON');
        }

        $con = new database();
        $method = $data['method'];
        $params = isset($data['data']) ? $data['data'] : [];

        // Log the method and parameters
        error_log("Calling method: " . $method);
        error_log("Parameters: " . print_r($params, true));

        // Check if method exists
        if (!method_exists($con, $method)) {
            throw new Exception("Method '$method' does not exist");
        }

        // Call the method
        $result = call_user_func_array([$con, $method], [$params]);

        // Log the result
        error_log("Method result: " . print_r($result, true));

        // Ensure result is an array
        if (!is_array($result)) {
            $result = ['success' => true, 'data' => $result];
        }

        // Return the result
        echo json_encode($result);

    } catch (Exception $e) {
        error_log("Error in db.php: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage(),
            'error_details' => $e->getMessage()
        ]);
    }
    exit;
}
?>
