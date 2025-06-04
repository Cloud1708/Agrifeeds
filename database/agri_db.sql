-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 03, 2025 at 09:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `agri_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `CategoryID` int(11) NOT NULL,
  `CategoryName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`CategoryID`, `CategoryName`) VALUES
(1, 'Poultry'),
(2, 'Swine'),
(3, 'Cattle'),
(4, 'Supplements'),
(5, 'Equipment');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `Cust_Name` varchar(255) NOT NULL,
  `Cust_CoInfo` varchar(255) DEFAULT NULL,
  `Cust_LoStat` varchar(100) DEFAULT NULL,
  `Cust_DiscRate` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`CustomerID`, `Cust_Name`, `Cust_CoInfo`, `Cust_LoStat`, `Cust_DiscRate`) VALUES
(1, 'Juan Dela Cruz', 'Farm Owner', 'Active', 5.00),
(2, 'Maria Santos', 'Retailer', 'Active', 3.00);

-- --------------------------------------------------------

--
-- Table structure for table `customer_discount_rate`
--

CREATE TABLE `customer_discount_rate` (
  `CusDiscountID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `CDR_DiscountRate` decimal(5,2) DEFAULT NULL,
  `CDR_EffectiveDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `CDR_ExpirationDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_discount_rate`
--

INSERT INTO `customer_discount_rate` (`CusDiscountID`, `CustomerID`, `CDR_DiscountRate`, `CDR_EffectiveDate`, `CDR_ExpirationDate`) VALUES
(1, 1, 5.00, '2025-06-03 07:06:52', '2025-06-03 07:06:52'),
(2, 2, 3.00, '2025-06-03 07:06:52', '2025-06-03 07:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_alerts`
--

CREATE TABLE `inventory_alerts` (
  `AlertID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `IA_AlertType` varchar(100) DEFAULT NULL,
  `IA_Threshold` int(11) DEFAULT NULL,
  `IA_AlertDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_alerts`
--

INSERT INTO `inventory_alerts` (`AlertID`, `ProductID`, `IA_AlertType`, `IA_Threshold`, `IA_AlertDate`) VALUES
(1, 1, 'Low Stock', 10, '2025-06-03 07:06:52'),
(2, 2, 'Out of Stock', 0, '2025-06-03 07:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_history`
--

CREATE TABLE `inventory_history` (
  `IHID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `IH_QtyChange` int(11) DEFAULT NULL,
  `IH_NewStckLvl` int(11) DEFAULT NULL,
  `IH_ChangeDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_history`
--

INSERT INTO `inventory_history` (`IHID`, `ProductID`, `IH_QtyChange`, `IH_NewStckLvl`, `IH_ChangeDate`) VALUES
(1, 1, -10, 90, '2025-06-03 07:06:52'),
(2, 2, 20, 100, '2025-06-03 07:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_program`
--

CREATE TABLE `loyalty_program` (
  `LoyaltyID` int(11) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `LP_PtsBalance` int(11) DEFAULT NULL,
  `LP_MbspTier` varchar(100) DEFAULT NULL,
  `LP_LastUpdt` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_program`
--

INSERT INTO `loyalty_program` (`LoyaltyID`, `CustomerID`, `LP_PtsBalance`, `LP_MbspTier`, `LP_LastUpdt`) VALUES
(1, 1, 3200, 'Gold', '2025-06-03 07:06:52'),
(2, 2, 1100, 'Silver', '2025-06-03 07:06:52');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_transaction_history`
--

CREATE TABLE `loyalty_transaction_history` (
  `LoTranHID` int(11) NOT NULL,
  `LoyaltyID` int(11) DEFAULT NULL,
  `LoTranH_PtsEarned` int(11) DEFAULT NULL,
  `LoTranH_PtsRedeemed` int(11) DEFAULT NULL,
  `LoTranH_TransDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `LoTranH_TransDesc` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_transaction_history`
--

INSERT INTO `loyalty_transaction_history` (`LoTranHID`, `LoyaltyID`, `LoTranH_PtsEarned`, `LoTranH_PtsRedeemed`, `LoTranH_TransDate`, `LoTranH_TransDesc`) VALUES
(1, 1, 200, 0, '2025-06-03 07:06:52', 'Initial points'),
(2, 2, 0, 100, '2025-06-03 07:06:52', 'Redeemed for discount');

-- --------------------------------------------------------

--
-- Table structure for table `order_promotions`
--

CREATE TABLE `order_promotions` (
  `OrderPromotionID` int(11) NOT NULL,
  `SaleID` int(11) DEFAULT NULL,
  `PromotionID` int(11) DEFAULT NULL,
  `OrderP_DiscntApplied` decimal(10,2) DEFAULT NULL,
  `OrderP_AppliedDate` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_promotions`
--

INSERT INTO `order_promotions` (`OrderPromotionID`, `SaleID`, `PromotionID`, `OrderP_DiscntApplied`, `OrderP_AppliedDate`) VALUES
(1, 1, 1, 10.00, '2025-06-03 07:06:53'),
(2, 2, 2, 20.00, '2025-06-03 07:06:53');

-- --------------------------------------------------------

--
-- Table structure for table `payment_history`
--

CREATE TABLE `payment_history` (
  `PaytoryID` int(11) NOT NULL,
  `SaleID` int(11) DEFAULT NULL,
  `PT_PayAmount` decimal(10,2) DEFAULT NULL,
  `PT_PayDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `PT_PayMethod` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_history`
--

INSERT INTO `payment_history` (`PaytoryID`, `SaleID`, `PT_PayAmount`, `PT_PayDate`, `PT_PayMethod`) VALUES
(1, 1, 100.00, '2025-06-03 07:06:51', 'Cash'),
(2, 2, 320.00, '2025-06-03 07:06:51', 'Card');

-- --------------------------------------------------------

--
-- Table structure for table `pricing_history`
--

CREATE TABLE `pricing_history` (
  `HistoryID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `PH_OldPrice` decimal(10,2) DEFAULT NULL,
  `PH_NewPrice` decimal(10,2) DEFAULT NULL,
  `PH_ChangeDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `PH_Effective_from` date NOT NULL,
  `PH_Effective_to` date DEFAULT NULL,
  `PH_Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pricing_history`
--

INSERT INTO `pricing_history` (`HistoryID`, `ProductID`, `PH_OldPrice`, `PH_NewPrice`, `PH_ChangeDate`, `PH_Effective_from`, `PH_Effective_to`, `PH_Created_at`) VALUES
(1, 1, 45.00, 50.00, '2025-06-03 07:06:50', '2024-06-01', NULL, '2025-06-03 07:06:50'),
(2, 2, 40.00, 45.00, '2025-06-03 07:06:50', '2024-06-01', NULL, '2025-06-03 07:06:50');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `Prod_Name` varchar(255) NOT NULL,
  `Prod_Cat` varchar(100) DEFAULT NULL,
  `Prod_Desc` text DEFAULT NULL,
  `Prod_Price` decimal(10,2) DEFAULT NULL,
  `Prod_Stock` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Prod_Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Prod_Updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `Prod_Name`, `Prod_Cat`, `Prod_Desc`, `Prod_Price`, `Prod_Stock`, `UserID`, `Prod_Created_at`, `Prod_Updated_at`) VALUES
(1, 'B-MEG Chicken Feed', 'Poultry', 'High quality chicken feed', 50.00, 100, 1, '2025-06-03 07:06:50', '2025-06-03 07:06:50'),
(2, 'Purina Pig Feed', 'Swine', 'Premium pig feed', 45.00, 80, 2, '2025-06-03 07:06:50', '2025-06-03 07:06:50'),
(3, 'Nutri-Mix Cattle Feed', 'Cattle', 'Balanced cattle feed', 65.00, 120, 1, '2025-06-03 07:06:50', '2025-06-03 07:06:50');

-- --------------------------------------------------------

--
-- Table structure for table `product_access_log`
--

CREATE TABLE `product_access_log` (
  `LogID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Pal_Action` varchar(100) DEFAULT NULL,
  `Pal_TimeStamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_access_log`
--

INSERT INTO `product_access_log` (`LogID`, `ProductID`, `UserID`, `Pal_Action`, `Pal_TimeStamp`) VALUES
(1, 1, 1, 'view', '2025-06-03 07:06:53'),
(2, 2, 2, 'edit', '2025-06-03 07:06:53');

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `PromotionID` int(11) NOT NULL,
  `Prom_Code` varchar(100) NOT NULL,
  `Promo_Description` text DEFAULT NULL,
  `Promo_DiscAmnt` decimal(10,2) DEFAULT NULL,
  `Promo_DiscountType` varchar(50) DEFAULT NULL,
  `Promo_StartDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `Promo_EndDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `UsageLimit` int(11) DEFAULT NULL,
  `Promo_MaxDiscAmnt` decimal(10,2) DEFAULT NULL,
  `Promo_IsActive` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`PromotionID`, `Prom_Code`, `Promo_Description`, `Promo_DiscAmnt`, `Promo_DiscountType`, `Promo_StartDate`, `Promo_EndDate`, `UsageLimit`, `Promo_MaxDiscAmnt`, `Promo_IsActive`) VALUES
(1, 'SUMMER10', '10% off summer promo', 10.00, 'percentage', '2025-06-03 07:06:52', '2025-06-03 07:06:52', 100, 50.00, 1),
(2, 'BUNDLE20', 'Bundle deal for pig and cattle feed', 20.00, 'fixed', '2025-06-03 07:06:52', '2025-06-03 07:06:52', 50, 20.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `Pur_OrderID` int(11) NOT NULL,
  `PO_Order_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `SupplierID` int(11) DEFAULT NULL,
  `PO_Order_Stat` varchar(100) DEFAULT NULL,
  `PO_Total_Amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`Pur_OrderID`, `PO_Order_Date`, `SupplierID`, `PO_Order_Stat`, `PO_Total_Amount`) VALUES
(1, '2025-06-03 07:06:51', 1, 'Pending', 1200.00),
(2, '2025-06-03 07:06:51', 2, 'Completed', 800.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_item`
--

CREATE TABLE `purchase_order_item` (
  `Pur_OrderItemID` int(11) NOT NULL,
  `Pur_OrderID` int(11) DEFAULT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `Pur_OIQuantity` int(11) DEFAULT NULL,
  `Pur_OIPrice` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_item`
--

INSERT INTO `purchase_order_item` (`Pur_OrderItemID`, `Pur_OrderID`, `ProductID`, `Pur_OIQuantity`, `Pur_OIPrice`) VALUES
(1, 1, 1, 10, 50.00),
(2, 1, 2, 5, 45.00),
(3, 2, 3, 8, 65.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `SaleID` int(11) NOT NULL,
  `Sale_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Sale_Method` varchar(100) DEFAULT NULL,
  `Sale_Per` varchar(100) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`SaleID`, `Sale_Date`, `Sale_Method`, `Sale_Per`, `CustomerID`) VALUES
(1, '2025-06-03 07:06:51', 'Cash', 'John', 1),
(2, '2025-06-03 07:06:51', 'Card', 'Jane', 2);

-- --------------------------------------------------------

--
-- Table structure for table `sale_item`
--

CREATE TABLE `sale_item` (
  `SaleItemID` int(11) NOT NULL,
  `SaleID` int(11) DEFAULT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `SI_Quantity` int(11) DEFAULT NULL,
  `SI_Price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_item`
--

INSERT INTO `sale_item` (`SaleItemID`, `SaleID`, `ProductID`, `SI_Quantity`, `SI_Price`) VALUES
(1, 1, 1, 2, 50.00),
(2, 1, 2, 1, 45.00),
(3, 2, 3, 3, 65.00);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `SupplierID` int(11) NOT NULL,
  `Sup_Name` varchar(255) NOT NULL,
  `Sup_CoInfo` varchar(255) DEFAULT NULL,
  `Sup_PayTerm` varchar(100) DEFAULT NULL,
  `Sup_DeSched` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`SupplierID`, `Sup_Name`, `Sup_CoInfo`, `Sup_PayTerm`, `Sup_DeSched`) VALUES
(1, 'AgriSupplier Inc.', 'Bulk Supplier', 'Net 30', 'Weekly'),
(2, 'FarmGoods Co.', 'Local Supplier', 'Immediate', 'Monthly');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `UnitID` int(11) NOT NULL,
  `UnitName` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`UnitID`, `UnitName`) VALUES
(1, 'kg'),
(2, 'pcs'),
(3, 'liters'),
(4, 'bags');

-- --------------------------------------------------------

--
-- Table structure for table `user_accounts`
--

CREATE TABLE `user_accounts` (
  `UserID` int(11) NOT NULL,
  `User_Name` varchar(255) NOT NULL,
  `User_Password` varchar(255) NOT NULL,
  `User_Role` int(11) DEFAULT NULL,
  `User_CreatedAt` timestamp NOT NULL DEFAULT current_timestamp(),
  `User_Photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_accounts`
--

INSERT INTO `user_accounts` (`UserID`, `User_Name`, `User_Password`, `User_Role`, `User_CreatedAt`, `User_Photo`) VALUES
(1, 'admin', 'adminpass', 1, '2025-06-03 07:06:50', 'admin.jpg'),
(2, 'john', 'johnpass', 2, '2025-06-03 07:06:50', 'john.jpg'),
(3, 'jane', 'janepass', 3, '2025-06-03 07:06:50', 'jane.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`CategoryID`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `customer_discount_rate`
--
ALTER TABLE `customer_discount_rate`
  ADD PRIMARY KEY (`CusDiscountID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD PRIMARY KEY (`AlertID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`IHID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `loyalty_program`
--
ALTER TABLE `loyalty_program`
  ADD PRIMARY KEY (`LoyaltyID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `loyalty_transaction_history`
--
ALTER TABLE `loyalty_transaction_history`
  ADD PRIMARY KEY (`LoTranHID`),
  ADD KEY `LoyaltyID` (`LoyaltyID`);

--
-- Indexes for table `order_promotions`
--
ALTER TABLE `order_promotions`
  ADD PRIMARY KEY (`OrderPromotionID`),
  ADD KEY `SaleID` (`SaleID`),
  ADD KEY `PromotionID` (`PromotionID`);

--
-- Indexes for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD PRIMARY KEY (`PaytoryID`),
  ADD KEY `SaleID` (`SaleID`);

--
-- Indexes for table `pricing_history`
--
ALTER TABLE `pricing_history`
  ADD PRIMARY KEY (`HistoryID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `product_access_log`
--
ALTER TABLE `product_access_log`
  ADD PRIMARY KEY (`LogID`),
  ADD KEY `ProductID` (`ProductID`),
  ADD KEY `UserID` (`UserID`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`PromotionID`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`Pur_OrderID`),
  ADD KEY `SupplierID` (`SupplierID`);

--
-- Indexes for table `purchase_order_item`
--
ALTER TABLE `purchase_order_item`
  ADD PRIMARY KEY (`Pur_OrderItemID`),
  ADD KEY `Pur_OrderID` (`Pur_OrderID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`SaleID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `sale_item`
--
ALTER TABLE `sale_item`
  ADD PRIMARY KEY (`SaleItemID`),
  ADD KEY `SaleID` (`SaleID`),
  ADD KEY `ProductID` (`ProductID`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`SupplierID`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`UnitID`);

--
-- Indexes for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `CategoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_discount_rate`
--
ALTER TABLE `customer_discount_rate`
  MODIFY `CusDiscountID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  MODIFY `AlertID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `IHID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loyalty_program`
--
ALTER TABLE `loyalty_program`
  MODIFY `LoyaltyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loyalty_transaction_history`
--
ALTER TABLE `loyalty_transaction_history`
  MODIFY `LoTranHID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_promotions`
--
ALTER TABLE `order_promotions`
  MODIFY `OrderPromotionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `PaytoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pricing_history`
--
ALTER TABLE `pricing_history`
  MODIFY `HistoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_access_log`
--
ALTER TABLE `product_access_log`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `PromotionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `Pur_OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_order_item`
--
ALTER TABLE `purchase_order_item`
  MODIFY `Pur_OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `SaleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sale_item`
--
ALTER TABLE `sale_item`
  MODIFY `SaleItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `SupplierID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `UnitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_discount_rate`
--
ALTER TABLE `customer_discount_rate`
  ADD CONSTRAINT `customer_discount_rate_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`);

--
-- Constraints for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  ADD CONSTRAINT `inventory_alerts_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`);

--
-- Constraints for table `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD CONSTRAINT `inventory_history_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`);

--
-- Constraints for table `loyalty_program`
--
ALTER TABLE `loyalty_program`
  ADD CONSTRAINT `loyalty_program_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`);

--
-- Constraints for table `loyalty_transaction_history`
--
ALTER TABLE `loyalty_transaction_history`
  ADD CONSTRAINT `loyalty_transaction_history_ibfk_1` FOREIGN KEY (`LoyaltyID`) REFERENCES `loyalty_program` (`LoyaltyID`);

--
-- Constraints for table `order_promotions`
--
ALTER TABLE `order_promotions`
  ADD CONSTRAINT `order_promotions_ibfk_1` FOREIGN KEY (`SaleID`) REFERENCES `sales` (`SaleID`),
  ADD CONSTRAINT `order_promotions_ibfk_2` FOREIGN KEY (`PromotionID`) REFERENCES `promotions` (`PromotionID`);

--
-- Constraints for table `payment_history`
--
ALTER TABLE `payment_history`
  ADD CONSTRAINT `payment_history_ibfk_1` FOREIGN KEY (`SaleID`) REFERENCES `sales` (`SaleID`);

--
-- Constraints for table `pricing_history`
--
ALTER TABLE `pricing_history`
  ADD CONSTRAINT `pricing_history_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`UserID`) REFERENCES `user_accounts` (`UserID`);

--
-- Constraints for table `product_access_log`
--
ALTER TABLE `product_access_log`
  ADD CONSTRAINT `product_access_log_ibfk_1` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`),
  ADD CONSTRAINT `product_access_log_ibfk_2` FOREIGN KEY (`UserID`) REFERENCES `user_accounts` (`UserID`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`);

--
-- Constraints for table `purchase_order_item`
--
ALTER TABLE `purchase_order_item`
  ADD CONSTRAINT `purchase_order_item_ibfk_1` FOREIGN KEY (`Pur_OrderID`) REFERENCES `purchase_orders` (`Pur_OrderID`),
  ADD CONSTRAINT `purchase_order_item_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`CustomerID`) REFERENCES `customers` (`CustomerID`);

--
-- Constraints for table `sale_item`
--
ALTER TABLE `sale_item`
  ADD CONSTRAINT `sale_item_ibfk_1` FOREIGN KEY (`SaleID`) REFERENCES `sales` (`SaleID`),
  ADD CONSTRAINT `sale_item_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
