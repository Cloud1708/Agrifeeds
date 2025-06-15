-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 10:17 PM
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
  `UserID` int(11) DEFAULT NULL,
  `Cust_FN` varchar(255) NOT NULL,
  `Cust_LN` varchar(255) NOT NULL,
  `Cust_CoInfo` varchar(255) DEFAULT NULL,
  `Cust_LoStat` varchar(100) DEFAULT NULL,
  `Cust_DiscRate` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`CustomerID`, `UserID`, `Cust_FN`, `Cust_LN`, `Cust_CoInfo`, `Cust_LoStat`, `Cust_DiscRate`) VALUES
(1, 3, 'Carlo', 'Hernandez', '+639952604071', 'None', 0.00),
(2, 4, 'Cris uwu', 'hers', '+639952604071', 'None', 0.00);

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
(22, 5, 'Low Stock', 10, '2025-06-15 18:48:07'),
(23, 6, 'Low Stock', 15, '2025-06-15 18:48:07'),
(24, 7, 'Low Stock', 5, '2025-06-15 18:48:07'),
(25, 8, 'Low Stock', 15, '2025-06-15 18:48:07');

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
(1, 1, -1, 49, '2025-06-15 18:47:44'),
(2, 2, -1, 29, '2025-06-15 19:59:51'),
(3, 1, -1, 48, '2025-06-15 20:03:49');

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
(1, 1, 555, 'None', '2025-06-15 18:47:44'),
(2, 2, 1170, 'None', '2025-06-15 20:03:49');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_settings`
--

CREATE TABLE `loyalty_settings` (
  `LSID` int(11) NOT NULL,
  `bronze` int(11) NOT NULL,
  `silver` int(11) NOT NULL,
  `gold` int(11) NOT NULL,
  `min_purchase` int(11) NOT NULL DEFAULT 0,
  `points_per_peso` float NOT NULL DEFAULT 1,
  `points_expire_after` int(11) DEFAULT 12
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loyalty_settings`
--

INSERT INTO `loyalty_settings` (`LSID`, `bronze`, `silver`, `gold`, `min_purchase`, `points_per_peso`, `points_expire_after`) VALUES
(1, 6000, 10000, 15000, 1000, 0.5, 3);

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
(1, 1, 555, NULL, '2025-06-15 18:47:44', 'Points earned from order #1'),
(2, 2, 670, NULL, '2025-06-15 19:59:51', 'Points earned from order #2'),
(3, 2, 500, NULL, '2025-06-15 20:03:49', 'Points earned from order #3');

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
(1, 1, 1111.00, '2025-06-15 18:47:44', 'cash'),
(2, 2, 1340.00, '2025-06-15 19:59:51', 'cash'),
(3, 3, 1000.00, '2025-06-15 20:03:49', 'card');

-- --------------------------------------------------------

--
-- Table structure for table `pricing_history`
--

CREATE TABLE `pricing_history` (
  `HistoryID` int(11) NOT NULL,
  `ProductID` int(11) DEFAULT NULL,
  `PH_OldPrice` decimal(10,2) DEFAULT NULL,
  `PH_NewPrice` decimal(10,2) DEFAULT NULL,
  `PH_ChangeDate` date NOT NULL,
  `PH_Effective_from` date NOT NULL,
  `PH_Effective_to` date DEFAULT NULL,
  `PH_Created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pricing_history`
--

INSERT INTO `pricing_history` (`HistoryID`, `ProductID`, `PH_OldPrice`, `PH_NewPrice`, `PH_ChangeDate`, `PH_Effective_from`, `PH_Effective_to`, `PH_Created_at`) VALUES
(1, 1, 1111.00, 1000.00, '2025-06-16', '2025-06-16', NULL, '2025-06-15 19:17:52'),
(2, 2, 1340.00, 1300.00, '2025-06-16', '2025-06-17', NULL, '2025-06-15 19:36:46');

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
  `Prod_Image` varchar(255) DEFAULT NULL,
  `UserID` int(11) DEFAULT NULL,
  `Prod_Created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `Prod_Updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `discontinued` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `Prod_Name`, `Prod_Cat`, `Prod_Desc`, `Prod_Price`, `Prod_Stock`, `Prod_Image`, `UserID`, `Prod_Created_at`, `Prod_Updated_at`, `discontinued`) VALUES
(1, 'Integra 2000', 'feed', 'pakain sa chicken', 1000.00, 48, 'uploads/product_images/684f10f13c991.png', 1, '2025-06-15 18:29:05', '2025-06-15 20:03:49', NULL),
(2, 'Top Breed adult 20kls', 'feed', 'Pakain sa dog', 1340.00, 29, 'uploads/product_images/684f121f3c72f.jpg', 2, '2025-06-15 18:34:07', '2025-06-15 19:59:51', NULL),
(3, 'Top Breed puppy 20kls ', 'feed', 'pakain sa puppy', 1610.00, 30, 'uploads/product_images/684f126f730f7.jpg', 2, '2025-06-15 18:35:27', '2025-06-15 18:35:27', NULL),
(4, 'pedigree adult beef 20kls', 'feed', 'pakain sa aso beef edition', 2477.00, 35, 'uploads/product_images/684f12fd183a2.jpg', 2, '2025-06-15 18:37:49', '2025-06-15 18:37:49', NULL),
(5, 'special cat food 7kls', 'feed', 'pakain sa cat', 922.00, 10, 'uploads/product_images/684f134f452ad.jpg', 2, '2025-06-15 18:39:11', '2025-06-15 18:39:11', NULL),
(6, 'vitamin pro powder 20x20grams', 'supplements', 'pangpalakas', 350.00, 15, 'uploads/product_images/684f1390bdf0f.jpg', 2, '2025-06-15 18:40:16', '2025-06-15 18:40:16', NULL),
(7, 'sulpar qr 50x5gms scahet', 'supplements', 'vitamins', 1336.00, 5, 'uploads/product_images/684f13d28e4e4.jpg', 2, '2025-06-15 18:41:22', '2025-06-15 18:41:22', NULL),
(8, 'tepox 48x5g', 'supplements', 'vitamins pro max', 1280.00, 15, 'uploads/product_images/684f142007afc.png', 2, '2025-06-15 18:42:40', '2025-06-15 18:42:40', NULL),
(9, 'cock box', 'equipment', 'lagayan ng cock', 75.00, 30, 'uploads/product_images/684f14b3b17be.jpg', 2, '2025-06-15 18:45:07', '2025-06-15 18:45:07', NULL),
(10, 'Scratch pen', 'equipment', 'kulungan ng manok sa labas', 285.00, 30, 'uploads/product_images/684f1509e9b42.jpg', 2, '2025-06-15 18:46:33', '2025-06-15 18:46:33', NULL);

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
  `Promo_IsActive` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`PromotionID`, `Prom_Code`, `Promo_Description`, `Promo_DiscAmnt`, `Promo_DiscountType`, `Promo_StartDate`, `Promo_EndDate`, `UsageLimit`, `Promo_IsActive`) VALUES
(1, 'NEWYEAR', 'bagong taon', 12.00, 'Percentage', '2025-06-15 19:41:00', '2025-06-16 19:41:00', 10, 1),
(2, 'Code123', 'testing', 50.00, 'Fixed', '2025-06-16 19:42:00', '2025-06-17 19:42:00', 20, 1);

-- --------------------------------------------------------

--
-- Table structure for table `promo_usage`
--

CREATE TABLE `promo_usage` (
  `UsageID` int(11) NOT NULL,
  `PromotionID` int(11) NOT NULL,
  `UserID` int(11) NOT NULL,
  `UsedAt` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `Pur_OrderID` int(11) NOT NULL,
  `PO_Order_Date` datetime DEFAULT NULL,
  `SupplierID` int(11) DEFAULT NULL,
  `PO_Order_Stat` varchar(100) DEFAULT NULL,
  `PO_Total_Amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`Pur_OrderID`, `PO_Order_Date`, `SupplierID`, `PO_Order_Stat`, `PO_Total_Amount`) VALUES
(1, '2025-06-16 00:00:00', 1, 'Waiting', 5000.00),
(2, '2025-06-16 00:00:00', 1, 'Waiting', 8850.00),
(3, '2025-06-16 00:00:00', 1, 'Waiting', 375.00),
(4, '2025-06-16 04:13:00', 1, 'Waiting', 1000.00);

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
(4, 1, 1, 5, 1000.00),
(5, 2, 3, 3, 1610.00),
(6, 2, 2, 3, 1340.00),
(7, 3, 9, 5, 75.00),
(8, 4, 1, 1, 1000.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `SaleID` int(11) NOT NULL,
  `Sale_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Sale_Per` varchar(100) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Sale_Status` varchar(20) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`SaleID`, `Sale_Date`, `Sale_Per`, `CustomerID`, `Sale_Status`) VALUES
(1, '2025-06-15 18:47:36', '2', 1, 'Completed'),
(2, '2025-06-15 19:59:43', '2', 2, 'Completed'),
(3, '2025-06-15 20:03:49', NULL, 2, 'Completed');

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
(1, 1, 1, 1, 1111.00),
(2, 2, 2, 1, 1340.00),
(3, 3, 1, 1, 1000.00);

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
(1, 'ABC Company', '2321313', 'Net 15', 'Monthly');

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
(1, 'admin', '$2y$10$OTzcXoSGiLRqZpi0ewwpcu.9Tz3DFzqQNjc65yt0pf2fkDEVS7Mte', 1, '2025-06-15 18:16:17', 'uploads/profile_photos/684f0df10fe41.jpg'),
(2, 'superadmin', '$2y$10$nRahBQrl2Pe2yIO94ZDwFe9pQOXHklgv4KVvk1jJKkzJlTW5MHzWG', 3, '2025-06-15 18:18:32', 'uploads/profile_photos/684f0e78a1acf.jpg'),
(3, 'user', '$2y$10$kBOtQYFU52cuWYrYY50EjOLv.9BlAkZ5oxG0BBr7oOktBkCfn5.Vq', 2, '2025-06-15 18:19:49', 'uploads/profile_photos/684f0ec4f3852.jpg'),
(4, 'user1', '$2y$10$0VEk3k0/i9/uuroJm6QtfOwPVhDkuq/xmH6RnkfoGDIo6/wF1zeyu', 2, '2025-06-15 19:59:22', 'uploads/profile_photos/684f261a3ff43.jpg');

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
  ADD PRIMARY KEY (`CustomerID`),
  ADD KEY `fk_customers_user_accounts` (`UserID`);

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
-- Indexes for table `loyalty_settings`
--
ALTER TABLE `loyalty_settings`
  ADD PRIMARY KEY (`LSID`);

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
-- Indexes for table `promo_usage`
--
ALTER TABLE `promo_usage`
  ADD PRIMARY KEY (`UsageID`),
  ADD KEY `PromotionID` (`PromotionID`);

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
  MODIFY `CusDiscountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  MODIFY `AlertID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `IHID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `loyalty_program`
--
ALTER TABLE `loyalty_program`
  MODIFY `LoyaltyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `loyalty_transaction_history`
--
ALTER TABLE `loyalty_transaction_history`
  MODIFY `LoTranHID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_promotions`
--
ALTER TABLE `order_promotions`
  MODIFY `OrderPromotionID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `PaytoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pricing_history`
--
ALTER TABLE `pricing_history`
  MODIFY `HistoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_access_log`
--
ALTER TABLE `product_access_log`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `PromotionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `promo_usage`
--
ALTER TABLE `promo_usage`
  MODIFY `UsageID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `Pur_OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_order_item`
--
ALTER TABLE `purchase_order_item`
  MODIFY `Pur_OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `SaleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sale_item`
--
ALTER TABLE `sale_item`
  MODIFY `SaleItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `SupplierID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `UnitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customers_user_accounts` FOREIGN KEY (`UserID`) REFERENCES `user_accounts` (`UserID`) ON UPDATE CASCADE;

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
-- Constraints for table `promo_usage`
--
ALTER TABLE `promo_usage`
  ADD CONSTRAINT `promo_usage_ibfk_1` FOREIGN KEY (`PromotionID`) REFERENCES `promotions` (`PromotionID`);

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
