-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 15, 2025 at 09:47 AM
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
(1, 1, 'Cris', 'Hernandez', '+639952604071', 'None', 0.00),
(2, 2, 'Carlo', 'Hernandez', '+639952604071', 'None', 0.00),
(3, 3, 'CC', 'Henandez', '+639952604071', 'Gold', 15.00),
(4, 4, 'Ian Isaac', 'Terrenal', '+639911776613', 'None', 0.00),
(5, 5, 'aian', 'aian', '+639913961872', 'None', 0.00),
(6, 6, 'Ian', 'Ian', '+639913961872', 'None', 0.00),
(7, 7, 'JAJAJ', 'JAJAJ', '+631232312343', 'None', 0.00),
(8, 8, 'Test', 'Customer', '+639919919911', 'Gold', 15.00),
(9, 9, 'Test', 'Account', '+632123123123', 'None', 0.00);

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
(1, 1, 'Low Stock', 10, '2025-06-12 15:08:22'),
(2, 4, 'Low Stock', 5, '2025-06-13 11:25:00'),
(3, 3, 'Low Stock', 30, '2025-06-14 17:15:18'),
(4, 4, 'Low Stock', 30, '2025-06-14 17:15:18'),
(5, 15, 'Low Stock', 30, '2025-06-14 17:15:18'),
(6, 3, 'Low Stock', 30, '2025-06-14 17:16:05'),
(7, 4, 'Low Stock', 30, '2025-06-14 17:16:05'),
(8, 15, 'Low Stock', 30, '2025-06-14 17:16:05'),
(9, 3, 'Low Stock', 30, '2025-06-14 18:39:16'),
(10, 4, 'Low Stock', 30, '2025-06-14 18:39:16'),
(11, 15, 'Low Stock', 30, '2025-06-14 18:39:16');

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
(1, 1, 20, 30, '2025-06-12 09:08:32'),
(2, 1, 1, 31, '2025-06-13 15:47:52'),
(3, 1, 1, 32, '2025-06-13 15:48:33'),
(4, 1, 20, 52, '2025-06-13 15:48:48'),
(5, 2, 12, 29, '2025-06-13 15:49:54'),
(6, 2, 1, 30, '2025-06-13 16:06:02'),
(7, 4, 1, 2, '2025-06-13 16:08:31'),
(8, 4, 23, 25, '2025-06-13 10:09:54'),
(9, 1, 2, 54, '2025-06-13 16:12:45'),
(10, 2, 20, 50, '2025-06-13 16:59:42'),
(11, 1, 1, 55, '2025-06-13 17:01:02'),
(12, 1, 52, 107, '2025-06-13 11:02:36'),
(13, 2, 1, 51, '2025-06-13 17:08:48'),
(14, 4, 2, 27, '2025-06-13 17:25:47'),
(15, 3, 1, 21, '2025-06-13 17:35:44'),
(16, 1, 20, 127, '2025-06-13 22:56:21'),
(17, 1, 20, 147, '2025-06-13 23:18:40'),
(18, 1, 5, 152, '2025-06-14 05:38:35'),
(19, 2, 20, 71, '2025-06-14 06:21:03'),
(20, 1, 5, 157, '2025-06-14 06:24:36'),
(21, 1, 2, 159, '2025-06-14 06:38:10'),
(22, 3, 21, 42, '2025-06-14 08:47:33'),
(23, 2, -1, 70, '2025-06-14 16:20:27'),
(24, 2, -1, 69, '2025-06-14 16:23:56'),
(25, 1, -1, 158, '2025-06-14 16:23:56'),
(26, 2, -1, 68, '2025-06-14 16:46:11'),
(27, 1, -1, 157, '2025-06-14 17:02:37'),
(28, 3, -20, 22, '2025-06-14 17:03:25'),
(29, 1, -20, 137, '2025-06-14 17:04:22'),
(30, 3, -1, 21, '2025-06-14 17:31:55'),
(31, 1, -1, 136, '2025-06-14 17:48:11'),
(32, 1, -1, 135, '2025-06-14 17:48:47'),
(33, 2, -2, 66, '2025-06-14 18:09:48'),
(34, 1, -2, 133, '2025-06-14 18:09:48'),
(35, 1, -1, 134, '2025-06-14 18:09:48'),
(36, 5, -2, 98, '2025-06-14 18:09:48'),
(37, 3, -1, 20, '2025-06-14 18:09:48'),
(38, 1, -1, 133, '2025-06-14 18:11:58'),
(39, 2, -1, 65, '2025-06-14 18:26:44'),
(40, 1, -1, 132, '2025-06-14 18:27:09'),
(41, 1, -1, 131, '2025-06-14 18:27:44'),
(42, 1, -1, 130, '2025-06-14 18:28:06'),
(43, 1, -1, 129, '2025-06-14 18:40:24'),
(44, 2, -1, 64, '2025-06-15 05:00:48'),
(45, 1, -1, 128, '2025-06-15 05:18:41'),
(46, 2, -1, 63, '2025-06-15 05:19:22'),
(47, 1, -1, 127, '2025-06-15 05:20:17'),
(48, 1, -1, 126, '2025-06-15 05:23:44'),
(49, 1, -1, 125, '2025-06-15 05:24:37'),
(50, 1, -1, 124, '2025-06-15 05:27:51'),
(51, 3, -1, 19, '2025-06-15 05:28:30'),
(52, 1, -1, 123, '2025-06-15 05:33:23'),
(53, 1, -2, 121, '2025-06-15 05:36:32'),
(54, 1, -1, 120, '2025-06-15 05:48:52'),
(55, 1, -1, 119, '2025-06-15 05:49:09'),
(56, 2, -1, 62, '2025-06-15 05:49:30'),
(57, 1, -1, 118, '2025-06-15 05:51:39'),
(58, 1, -1, 117, '2025-06-15 05:52:05'),
(59, 2, -1, 61, '2025-06-15 05:54:08'),
(60, 2, -1, 60, '2025-06-15 05:54:23'),
(61, 2, -1, 59, '2025-06-15 05:56:39'),
(62, 2, -1, 58, '2025-06-15 05:57:02'),
(63, 1, -1, 116, '2025-06-15 05:57:29'),
(64, 2, -1, 57, '2025-06-15 06:00:06'),
(65, 2, -1, 56, '2025-06-15 06:00:33'),
(66, 2, -1, 55, '2025-06-15 06:02:41'),
(67, 2, -1, 54, '2025-06-15 06:02:59'),
(68, 1, -1, 115, '2025-06-15 06:03:25'),
(69, 1, -1, 114, '2025-06-15 06:03:41'),
(70, 1, -1, 113, '2025-06-15 06:14:06'),
(71, 1, -1, 112, '2025-06-15 06:14:27'),
(72, 1, -1, 111, '2025-06-15 06:14:59'),
(73, 1, -1, 110, '2025-06-15 06:16:51'),
(74, 1, -1, 109, '2025-06-15 06:22:58'),
(75, 2, -1, 53, '2025-06-15 06:25:10'),
(76, 1, -1, 108, '2025-06-15 06:26:55'),
(77, 1, -1, 107, '2025-06-15 06:31:31'),
(78, 2, -1, 52, '2025-06-15 06:34:59'),
(79, 1, -1, 106, '2025-06-15 06:38:47'),
(80, 2, -1, 51, '2025-06-15 06:39:39'),
(81, 1, -1, 105, '2025-06-15 06:54:16'),
(82, 2, -1, 50, '2025-06-15 07:45:53'),
(83, 2, -1, 49, '2025-06-15 07:46:49');

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
(1, 1, 0, 'None', '2025-06-12 11:38:10'),
(2, 2, 0, 'None', '2025-06-12 11:39:25'),
(3, 3, 50000, 'Gold', '2025-06-12 11:40:36'),
(4, 4, 0, 'None', '2025-06-13 11:07:57'),
(5, 5, 0, 'None', '2025-06-13 12:49:08'),
(6, 6, 0, 'None', '2025-06-13 14:40:41'),
(7, 7, 0, 'None', '2025-06-13 18:11:21'),
(8, 8, 19407, 'Gold', '2025-06-15 07:46:49'),
(9, 9, 0, 'None', '2025-06-14 18:21:39');

-- --------------------------------------------------------

--
-- Table structure for table `loyalty_settings`
--

CREATE TABLE `loyalty_settings` (
  `id` int(11) NOT NULL,
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

INSERT INTO `loyalty_settings` (`id`, `bronze`, `silver`, `gold`, `min_purchase`, `points_per_peso`, `points_expire_after`) VALUES
(1, 5000, 10000, 15000, 5, 2, 12);

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
(1, 8, 600, NULL, '2025-06-15 05:23:44', 'Points earned from order #58'),
(2, 8, 600, NULL, '2025-06-15 05:24:15', 'Points earned from order #59'),
(3, 8, 600, NULL, '2025-06-15 05:25:13', 'Points earned from order #60'),
(4, 8, 600, NULL, '2025-06-15 05:27:51', 'Points earned from order #60'),
(5, 8, 246, NULL, '2025-06-15 05:28:30', 'Points earned from order #61'),
(6, 8, 570, NULL, '2025-06-15 05:33:23', 'Points earned from order #62'),
(7, 8, 1140, NULL, '2025-06-15 05:36:32', 'Points earned from order #63'),
(8, 8, 570, NULL, '2025-06-15 05:48:52', 'Points earned from order #64'),
(9, 8, 570, NULL, '2025-06-15 05:49:09', 'Points earned from order #65'),
(10, 8, 233, NULL, '2025-06-15 05:49:30', 'Points earned from order #66'),
(11, 8, 570, NULL, '2025-06-15 05:51:39', 'Points earned from order #67'),
(12, 8, 570, NULL, '2025-06-15 05:52:05', 'Points earned from order #68'),
(13, 8, 233, NULL, '2025-06-15 05:54:08', 'Points earned from order #70'),
(14, 8, 233, NULL, '2025-06-15 05:54:23', 'Points earned from order #71'),
(15, 8, 233, NULL, '2025-06-15 05:56:39', 'Points earned from order #73'),
(16, 8, 233, NULL, '2025-06-15 05:57:02', 'Points earned from order #74'),
(17, 8, 570, NULL, '2025-06-15 05:57:29', 'Points earned from order #75'),
(18, 8, 233, NULL, '2025-06-15 06:00:07', 'Points earned from order #76'),
(19, 8, 233, NULL, '2025-06-15 06:00:33', 'Points earned from order #77'),
(20, 8, 233, NULL, '2025-06-15 06:02:41', 'Points earned from order #79'),
(21, 8, 233, NULL, '2025-06-15 06:02:59', 'Points earned from order #80'),
(22, 8, 570, NULL, '2025-06-15 06:03:25', 'Points earned from order #81'),
(23, 8, 570, NULL, '2025-06-15 06:03:41', 'Points earned from order #82'),
(24, 8, 540, NULL, '2025-06-15 06:14:06', 'Points earned from order #84'),
(25, 8, 540, NULL, '2025-06-15 06:14:27', 'Points earned from order #85'),
(26, 8, 540, NULL, '2025-06-15 06:14:59', 'Points earned from order #86'),
(27, 8, 510, NULL, '2025-06-15 06:16:51', 'Points earned from order #87'),
(28, 8, 510, NULL, '2025-06-15 06:22:58', 'Points earned from order #88'),
(29, 8, 209, NULL, '2025-06-15 06:25:10', 'Points earned from order #89'),
(30, 8, 510, NULL, '2025-06-15 06:26:55', 'Points earned from order #90'),
(31, 8, 510, NULL, '2025-06-15 06:31:31', 'Points earned from order #93'),
(32, 8, 209, NULL, '2025-06-15 06:34:59', 'Points earned from order #96'),
(33, 8, 510, NULL, '2025-06-15 06:38:47', 'Points earned from order #97'),
(34, 8, 209, NULL, '2025-06-15 06:39:39', 'Points earned from order #98'),
(35, 8, 510, NULL, '2025-06-15 06:54:16', 'Points earned from order #99'),
(36, 8, 209, NULL, '2025-06-15 07:45:53', 'Points earned from order #100'),
(37, 8, 209, NULL, '2025-06-15 07:46:49', 'Points earned from order #101');

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
(1, 2, 5, 150.60, '2025-06-14 04:33:53'),
(2, 3, 5, 128.01, '2025-06-14 04:42:00'),
(3, 4, 6, 122.40, '2025-06-14 04:44:33'),
(4, 5, 2, 21.34, '2025-06-14 04:46:55'),
(5, 6, 5, 2.40, '2025-06-14 05:54:36'),
(6, 7, 2, 0.60, '2025-06-14 06:39:33'),
(7, 8, 2, 36.00, '2025-06-14 06:50:29'),
(8, 9, 6, 14.76, '2025-06-14 07:07:49'),
(9, 10, 2, 2.46, '2025-06-14 07:08:28'),
(10, 11, 7, 1.23, '2025-06-14 07:10:08'),
(11, 12, 6, 14.76, '2025-06-14 07:11:36'),
(12, 13, 6, 3.60, '2025-06-14 07:12:26'),
(13, 14, 2, 0.60, '2025-06-14 07:24:52'),
(14, 15, 2, 2.46, '2025-06-14 07:34:16'),
(15, 16, 8, 61.50, '2025-06-14 08:44:29'),
(16, 17, 8, 61.50, '2025-06-14 09:15:52'),
(17, 18, 8, 61.50, '2025-06-14 09:16:32'),
(18, 19, 8, 15.00, '2025-06-14 09:25:53'),
(19, 20, 8, 61.50, '2025-06-14 09:30:46'),
(20, 21, 8, 61.50, '2025-06-14 09:31:37'),
(21, 22, 8, 76.50, '2025-06-14 10:21:15'),
(22, 23, 2, 0.60, '2025-06-14 10:23:14'),
(23, 25, 9, 3.60, '2025-06-14 10:38:59'),
(24, 27, 9, 14.76, '2025-06-14 16:20:27'),
(25, 49, 10, 30.00, '2025-06-14 18:22:43'),
(26, 51, 10, 12.30, '2025-06-14 18:24:28');

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
(1, 1, 123.00, '2025-06-13 18:13:18', 'cash'),
(2, 2, 1255.00, '2025-06-14 04:38:05', 'cash'),
(3, 1, 123.00, '2025-06-14 04:38:06', 'cash'),
(4, 3, 1255.00, '2025-06-14 04:42:35', 'cash'),
(5, 4, 1200.00, '2025-06-14 04:44:45', 'cash'),
(6, 5, 1255.00, '2025-06-14 04:48:47', 'cash'),
(7, 6, 20.00, '2025-06-14 05:54:51', 'cash'),
(8, 7, 30.00, '2025-06-14 06:39:43', 'cash'),
(9, 8, 1800.00, '2025-06-14 07:13:03', 'cash'),
(10, 9, 123.00, '2025-06-14 07:13:08', 'cash'),
(11, 10, 123.00, '2025-06-14 07:13:19', 'cash'),
(12, 11, 123.00, '2025-06-14 07:33:08', 'cash'),
(13, 12, 123.00, '2025-06-14 07:33:52', 'cash'),
(14, 15, 120.54, '2025-06-14 07:34:16', 'card'),
(15, 13, 30.00, '2025-06-14 07:35:01', 'cash'),
(16, 14, 30.00, '2025-06-14 07:35:43', 'cash'),
(17, 16, 123.00, '2025-06-14 08:45:37', 'cash'),
(18, 18, 61.50, '2025-06-14 09:16:32', 'card'),
(19, 19, 30.00, '2025-06-14 09:26:25', 'cash'),
(20, 21, 61.50, '2025-06-14 09:32:20', 'cash'),
(21, 20, 61.50, '2025-06-14 09:32:37', 'cash'),
(22, 25, 26.40, '2025-06-14 10:38:59', 'card'),
(23, 26, 123.00, '2025-06-14 12:10:26', 'cash'),
(24, 24, 30.00, '2025-06-14 12:10:30', 'cash'),
(25, 23, 29.40, '2025-06-14 12:10:34', 'cash'),
(26, 22, 76.50, '2025-06-14 12:10:39', 'cash'),
(27, 17, 123.00, '2025-06-14 12:10:45', 'cash'),
(28, 27, 231.24, '2025-06-14 16:38:44', 'cash'),
(29, 28, 306.00, '2025-06-14 16:39:35', 'cash'),
(30, 29, 246.00, '2025-06-14 16:46:31', 'cash'),
(31, 30, 60.00, '2025-06-14 17:04:01', 'cash'),
(32, 34, 1353.00, '2025-06-14 17:11:57', 'cash'),
(33, 35, 30.00, '2025-06-14 17:24:38', 'cash'),
(34, 36, 30.00, '2025-06-14 17:25:23', 'cash'),
(35, 37, 123.00, '2025-06-14 17:26:01', 'cash'),
(36, 39, 123.00, '2025-06-14 17:31:55', 'card'),
(37, 40, 30.00, '2025-06-14 17:35:37', 'cash'),
(38, 41, 30.00, '2025-06-14 17:38:37', 'cash'),
(39, 42, 30.00, '2025-06-14 17:44:23', 'cash'),
(40, 43, 30.00, '2025-06-14 17:44:52', 'cash'),
(41, 44, 30.00, '2025-06-14 17:45:24', 'cash'),
(42, 45, 30.00, '2025-06-14 17:48:11', 'cash'),
(43, 46, 30.00, '2025-06-14 17:48:47', 'cash'),
(44, 47, 3669.00, '2025-06-14 18:09:48', 'cash'),
(45, 48, 300.00, '2025-06-14 18:11:58', 'cash'),
(46, 51, 110.70, '2025-06-14 18:26:44', 'cash'),
(47, 50, 300.00, '2025-06-14 18:27:09', 'cash'),
(48, 49, 270.00, '2025-06-14 18:27:44', 'cash'),
(49, 52, 300.00, '2025-06-14 18:28:06', 'card'),
(50, 53, 300.00, '2025-06-14 18:40:24', 'cash'),
(51, 55, 300.00, '2025-06-15 05:18:41', 'cash'),
(52, 56, 123.00, '2025-06-15 05:19:22', 'cash'),
(53, 57, 300.00, '2025-06-15 05:20:17', 'cash'),
(54, 58, 300.00, '2025-06-15 05:23:44', 'card'),
(55, 59, 300.00, '2025-06-15 05:24:37', 'cash'),
(56, 60, 300.00, '2025-06-15 05:27:51', 'cash'),
(57, 61, 123.00, '2025-06-15 05:28:30', 'cash'),
(58, 62, 285.00, '2025-06-15 05:33:23', 'cash'),
(59, 63, 570.00, '2025-06-15 05:36:32', 'cash'),
(60, 64, 285.00, '2025-06-15 05:48:52', 'card'),
(61, 65, 285.00, '2025-06-15 05:49:09', 'card'),
(62, 66, 116.85, '2025-06-15 05:49:30', 'card'),
(63, 67, 285.00, '2025-06-15 05:51:39', 'card'),
(64, 68, 285.00, '2025-06-15 05:52:05', 'card'),
(65, 70, 116.85, '2025-06-15 05:54:08', 'card'),
(66, 71, 116.85, '2025-06-15 05:54:23', 'card'),
(67, 72, 0.00, '2025-06-15 05:54:27', 'card'),
(68, 73, 116.85, '2025-06-15 05:56:39', 'card'),
(69, 74, 116.85, '2025-06-15 05:57:02', 'card'),
(70, 75, 285.00, '2025-06-15 05:57:29', 'card'),
(71, 76, 116.85, '2025-06-15 06:00:06', 'card'),
(72, 77, 116.85, '2025-06-15 06:00:33', 'card'),
(73, 79, 116.85, '2025-06-15 06:02:41', 'card'),
(74, 80, 116.85, '2025-06-15 06:02:59', 'card'),
(75, 81, 285.00, '2025-06-15 06:03:25', 'card'),
(76, 82, 285.00, '2025-06-15 06:03:41', 'card'),
(77, 84, 270.00, '2025-06-15 06:14:06', 'card'),
(78, 85, 270.00, '2025-06-15 06:14:27', 'card'),
(79, 86, 270.00, '2025-06-15 06:14:59', 'card'),
(80, 87, 255.00, '2025-06-15 06:16:51', 'cash'),
(81, 88, 255.00, '2025-06-15 06:22:58', 'card'),
(82, 89, 104.55, '2025-06-15 06:25:10', 'card'),
(83, 90, 255.00, '2025-06-15 06:26:55', 'card'),
(84, 93, 255.00, '2025-06-15 06:31:31', 'card'),
(85, 96, 104.55, '2025-06-15 06:34:59', 'card'),
(86, 97, 255.00, '2025-06-15 06:38:47', 'card'),
(87, 98, 104.55, '2025-06-15 06:39:39', 'card'),
(88, 99, 255.00, '2025-06-15 06:54:16', 'card'),
(89, 100, 104.55, '2025-06-15 07:45:53', 'cash'),
(90, 95, NULL, '2025-06-15 07:45:59', 'cash'),
(91, 101, 104.55, '2025-06-15 07:46:49', 'cash');

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
(1, 1, 123.00, 120.00, '2025-06-11 16:00:00', '2025-06-12', '2025-06-14', '2025-06-12 15:08:51'),
(2, 1, 120.00, 125.00, '2025-06-12 16:00:00', '2025-06-13', NULL, '2025-06-13 17:02:55'),
(3, 1, 125.00, 1255.00, '2025-06-12 16:00:00', '2025-06-13', NULL, '2025-06-13 17:03:16'),
(4, 1, 1255.00, 20.00, '2025-06-13 16:00:00', '2025-06-14', NULL, '2025-06-14 05:18:05'),
(5, 1, 20.00, 25.00, '2025-06-13 16:00:00', '2025-06-14', NULL, '2025-06-14 06:17:58'),
(6, 1, 25.00, 30.00, '2025-06-13 16:00:00', '2025-06-14', NULL, '2025-06-14 06:38:36'),
(7, 1, 30.00, 300.00, '2025-06-13 16:00:00', '2025-06-14', NULL, '2025-06-14 17:54:35');

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
  `Prod_Updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`ProductID`, `Prod_Name`, `Prod_Cat`, `Prod_Desc`, `Prod_Price`, `Prod_Stock`, `Prod_Image`, `UserID`, `Prod_Created_at`, `Prod_Updated_at`) VALUES
(1, 'What if', 'equipment', 'asking lang', 300.00, 105, 'uploads/product_images/684dc302c6dac.jpeg', 3, '2025-06-12 14:13:58', '2025-06-15 06:54:16'),
(2, 'tayo pala', 'feed', 'naman eh', 123.00, 49, 'uploads/product_images/684db7f7a3c70.gif', 3, '2025-06-12 14:18:29', '2025-06-15 07:46:49'),
(3, '???', 'feed', ':(((', 123.00, 19, 'uploads/product_images/684db7ebd1bf3.png', 3, '2025-06-12 14:24:45', '2025-06-15 05:28:30'),
(4, 'Pala', 'feed', 'dasdass', 121.00, 27, 'uploads/product_images/684db7de4040b.jpeg', 3, '2025-06-13 07:17:13', '2025-06-14 18:45:15'),
(5, '?', 'feed', 'High-quality chicken feed with essential ', 1200.00, 98, 'uploads/product_images/684dc2e2c1411.jpg', 1, '2025-06-13 15:11:16', '2025-06-14 18:45:20'),
(6, 'Pig Grower Feed', 'Swine', 'Complete feed for growing pigs', 1500.00, 80, NULL, 1, '2025-06-13 15:11:16', '2025-06-13 15:11:16'),
(7, 'Cattle Feed Mix', 'Cattle', 'Balanced nutrition for cattle', 2000.00, 50, NULL, 1, '2025-06-13 15:11:16', '2025-06-13 15:11:16'),
(8, 'Fish Feed Pellets', 'Aquaculture', 'Floating pellets for fish', 800.00, 200, NULL, 1, '2025-06-13 15:11:16', '2025-06-13 15:11:16'),
(9, 'Organic Layer Feed', 'Poultry', 'Organic feed for egg-laying hens', 1800.00, 60, NULL, 1, '2025-06-13 15:11:16', '2025-06-13 15:11:16'),
(10, 'Premium Chicken Feed', 'Poultry', 'High-quality chicken feed with essential nutrients', 1200.00, 100, NULL, 1, '2025-06-13 15:11:26', '2025-06-13 15:11:26'),
(11, 'Pig Grower Feed', 'Swine', 'Complete feed for growing pigs', 1500.00, 80, NULL, 1, '2025-06-13 15:11:26', '2025-06-13 15:11:26'),
(12, 'Cattle Feed Mix', 'Cattle', 'Balanced nutrition for cattle', 2000.00, 50, NULL, 1, '2025-06-13 15:11:26', '2025-06-13 15:11:26'),
(13, 'Fish Feed Pellets', 'Aquaculture', 'Floating pellets for fish', 800.00, 200, NULL, 1, '2025-06-13 15:11:26', '2025-06-13 15:11:26'),
(14, 'Organic Layer Feed', 'Poultry', 'Organic feed for egg-laying hens', 1800.00, 60, NULL, 1, '2025-06-13 15:11:26', '2025-06-13 15:11:26'),
(15, 'Aian', 'supplements', 'Aian', 20.00, 20, NULL, 6, '2025-06-14 04:58:37', '2025-06-14 04:58:37');

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
(1, 1, 6, 'Updated product: category from \'supplements\' to \'equipment\'', '2025-06-14 05:34:33'),
(2, 5, 6, 'Updated product: category from \'Poultry\' to \'feed\', description', '2025-06-14 05:38:05'),
(3, 1, 6, 'Updated product: stock from 147 to 152', '2025-06-14 05:38:35'),
(4, 1, 6, 'Updated product: name from \'Integra 200\' to \'Aian\'', '2025-06-14 05:53:21'),
(5, 1, 6, 'Updated product: price from ₱20.00 to ₱25.00', '2025-06-14 06:17:58'),
(6, 2, 6, 'Updated product: stock from 51 to 71', '2025-06-14 06:21:03'),
(7, 1, 6, 'Updated product: stock from 152 to 157', '2025-06-14 06:24:36'),
(8, 1, 6, 'Updated product: stock from 157 to 159', '2025-06-14 06:38:10'),
(9, 1, 6, 'Updated product: price from ₱25.00 to ₱30.00', '2025-06-14 06:38:36'),
(10, 1, 6, 'Updated product: price from ₱30.00 to ₱300.00', '2025-06-14 17:54:35'),
(11, 5, 6, 'Updated product: image', '2025-06-14 17:56:33'),
(12, 4, 6, 'Updated product: image', '2025-06-14 17:56:46'),
(13, 3, 6, 'Updated product: image', '2025-06-14 17:56:59'),
(14, 2, 6, 'Updated product: image', '2025-06-14 17:57:11'),
(15, 1, 6, 'Updated product: image', '2025-06-14 17:57:24'),
(16, 1, 6, 'Updated product: image', '2025-06-14 18:10:57'),
(17, 5, 6, 'Updated product: image', '2025-06-14 18:43:46'),
(18, 1, 6, 'Updated product: image', '2025-06-14 18:44:18'),
(19, 1, 6, 'Updated product: name from \'Aian\' to \'What\'', '2025-06-14 18:44:30'),
(20, 2, 6, 'Updated product: name from \'Integra 30002\' to \'If\'', '2025-06-14 18:45:03'),
(21, 3, 6, 'Updated product: name from \'Integra 4000\' to \'Tayo\'', '2025-06-14 18:45:09'),
(22, 4, 6, 'Updated product: name from \'sdsad\' to \'Pala\'', '2025-06-14 18:45:15'),
(23, 5, 6, 'Updated product: name from \'Premium Chicken Feed\' to \'?\'', '2025-06-14 18:45:20'),
(24, 1, 6, 'Updated product: name from \'What\' to \'What if\'', '2025-06-14 18:46:25'),
(25, 2, 6, 'Updated product: name from \'If\' to \'tayo\'', '2025-06-14 18:46:30'),
(26, 2, 6, 'Updated product: name from \'tayo\' to \'tayo pala?\'', '2025-06-14 18:46:35'),
(27, 2, 6, 'Updated product: name from \'tayo pala?\' to \'tayo pala\'', '2025-06-14 18:46:40'),
(28, 3, 6, 'Updated product: name from \'Tayo\' to \'???\'', '2025-06-14 18:46:45'),
(29, 1, 6, 'Updated product: description', '2025-06-14 18:46:55'),
(30, 2, 6, 'Updated product: description', '2025-06-14 18:47:02'),
(31, 3, 6, 'Updated product: description', '2025-06-14 18:47:10');

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
(1, 'BIRTHDAYSEX', 'may handaan', 50.00, 'Percentage', '2025-06-12 14:42:00', '2025-06-13 14:42:00', 1, NULL, 0),
(2, 'dsad', 'sdasda', 2.00, 'Percentage', '2025-06-11 14:52:00', '2025-06-15 14:43:00', 1, NULL, 0),
(3, 'abcd', 'sdsadad', 15.00, 'Percentage', '2025-06-12 14:43:00', '2025-06-14 02:44:00', 12, NULL, 0),
(5, 'uwus', 'sdasd', 12.00, 'Percentage', '2025-06-13 06:34:00', '2025-06-14 06:34:00', 2, NULL, 0),
(6, 'qwerty', 'sdasdd', 12.00, 'Percentage', '2025-06-12 06:40:00', '2025-06-14 06:40:00', 2, NULL, 0),
(7, 'althea', 'sada', 1.00, 'Percentage', '2025-06-13 06:45:00', '2025-06-14 06:45:00', 5, NULL, 0),
(8, 'cris', 'hernandez', 50.00, 'Percentage', '2025-06-12 07:05:00', '2025-06-19 07:05:00', 2, NULL, 0),
(9, 'Test', 'Test', 12.00, 'Percentage', '2025-06-14 10:38:00', '2025-06-19 10:38:00', 1, NULL, 0),
(10, 'TEST', 'TEST', 10.00, 'Percentage', '2025-06-14 18:21:00', '2025-06-17 18:21:00', 2, NULL, 0);

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

--
-- Dumping data for table `promo_usage`
--

INSERT INTO `promo_usage` (`UsageID`, `PromotionID`, `UserID`, `UsedAt`) VALUES
(1, 5, 5, '2025-06-14 12:33:53'),
(2, 5, 3, '2025-06-14 12:42:00'),
(3, 6, 3, '2025-06-14 12:44:33'),
(4, 2, 3, '2025-06-14 12:46:55'),
(5, 5, 5, '2025-06-14 13:54:36'),
(6, 2, 5, '2025-06-14 14:39:33'),
(7, 2, 5, '2025-06-14 14:50:29'),
(8, 6, 5, '2025-06-14 15:07:49'),
(9, 2, 5, '2025-06-14 15:08:28'),
(10, 7, 5, '2025-06-14 15:10:08'),
(11, 6, 5, '2025-06-14 15:11:36'),
(12, 6, 5, '2025-06-14 15:12:26'),
(13, 2, 5, '2025-06-14 15:24:52'),
(14, 2, 5, '2025-06-14 15:34:16'),
(15, 8, 5, '2025-06-14 16:44:29'),
(16, 8, 5, '2025-06-14 17:15:52'),
(17, 8, 5, '2025-06-14 17:16:32'),
(18, 8, 5, '2025-06-14 17:25:53'),
(19, 8, 5, '2025-06-14 17:30:46'),
(20, 8, 5, '2025-06-14 17:31:37'),
(21, 8, 8, '2025-06-14 18:21:15'),
(22, 2, 8, '2025-06-14 18:23:14'),
(23, 9, 8, '2025-06-14 18:38:59'),
(24, 9, 5, '2025-06-15 00:20:27'),
(25, 10, 8, '2025-06-15 02:22:43'),
(26, 10, 9, '2025-06-15 02:24:28');

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
(1, '2025-06-12 16:00:00', 1, 'Delivered', 1476.00),
(2, '2025-06-12 16:00:00', 2, 'Delivered', 2400.00),
(3, '2025-06-12 16:00:00', 1, 'Delivered', 120.00),
(4, '2025-06-12 16:00:00', 1, 'Delivered', 120.00),
(5, '2025-06-12 16:00:00', 1, 'Delivered', 123.00),
(6, '2025-06-12 16:00:00', 4, 'Delivered', 121.00),
(7, '2025-06-12 16:00:00', 3, 'Delivered', 240.00),
(8, '2025-06-12 16:00:00', 1, 'Delivered', 2460.00),
(9, '2025-06-12 16:00:00', 1, 'Delivered', 120.00),
(10, '2025-06-12 16:00:00', 2, 'Delivered', 123.00),
(11, '2025-06-12 16:00:00', 1, 'Delivered', 242.00),
(12, '2025-06-12 16:00:00', 1, 'Delivered', 123.00),
(13, '2025-06-13 16:00:00', 2, 'Delivered', 2583.00);

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
(1, 1, 2, 12, 123.00),
(2, 2, 1, 20, 120.00),
(3, 3, 1, 1, 120.00),
(4, 4, 1, 1, 120.00),
(5, 5, 2, 1, 123.00),
(6, 6, 4, 1, 121.00),
(7, 7, 1, 2, 120.00),
(8, 8, 2, 20, 123.00),
(9, 9, 1, 1, 120.00),
(10, 10, 2, 1, 123.00),
(11, 11, 4, 2, 121.00),
(12, 12, 3, 1, 123.00),
(13, 13, 3, 21, 123.00);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `SaleID` int(11) NOT NULL,
  `Sale_Date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Sale_Method` varchar(100) DEFAULT NULL,
  `Sale_Per` varchar(100) DEFAULT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Sale_Status` varchar(20) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`SaleID`, `Sale_Date`, `Sale_Method`, `Sale_Per`, `CustomerID`, `Sale_Status`) VALUES
(1, '2025-06-13 18:13:18', 'cash', NULL, 5, 'Completed'),
(2, '2025-06-14 04:33:53', NULL, NULL, 5, 'Completed'),
(3, '2025-06-14 04:42:00', NULL, NULL, 3, 'Completed'),
(4, '2025-06-14 04:44:33', NULL, NULL, 3, 'Completed'),
(5, '2025-06-14 04:46:55', NULL, NULL, 3, 'Completed'),
(6, '2025-06-14 05:54:36', NULL, NULL, 5, 'Completed'),
(7, '2025-06-14 06:39:33', NULL, NULL, 5, 'Completed'),
(8, '2025-06-14 06:50:29', NULL, NULL, 5, 'Completed'),
(9, '2025-06-14 07:07:49', NULL, NULL, 5, 'Completed'),
(10, '2025-06-14 07:08:28', NULL, NULL, 5, 'Completed'),
(11, '2025-06-14 07:10:08', NULL, '6', 5, 'Completed'),
(12, '2025-06-14 07:11:36', NULL, '6', 5, 'Completed'),
(13, '2025-06-14 07:12:26', NULL, '6', 5, 'Completed'),
(14, '2025-06-14 07:24:52', NULL, '6', 5, 'Completed'),
(15, '2025-06-14 07:34:16', NULL, NULL, 5, 'Completed'),
(16, '2025-06-14 08:44:29', NULL, '6', 5, 'Completed'),
(17, '2025-06-14 09:15:52', NULL, '6', 5, 'Completed'),
(18, '2025-06-14 09:16:32', NULL, NULL, 5, 'Completed'),
(19, '2025-06-14 09:25:53', NULL, '6', 5, 'Completed'),
(20, '2025-06-14 09:30:46', NULL, '6', 5, 'Completed'),
(21, '2025-06-14 09:31:37', NULL, '6', 5, 'Completed'),
(22, '2025-06-14 10:21:15', NULL, '6', 8, 'Completed'),
(23, '2025-06-14 10:23:14', NULL, '6', 8, 'Completed'),
(24, '2025-06-14 10:23:50', NULL, '6', 8, 'Completed'),
(25, '2025-06-14 10:38:59', NULL, NULL, 8, 'Completed'),
(26, '2025-06-14 12:09:55', NULL, '6', 8, 'Completed'),
(27, '2025-06-14 16:20:27', NULL, '6', 5, 'Completed'),
(28, '2025-06-14 16:23:56', NULL, '6', 5, 'Completed'),
(29, '2025-06-14 16:46:11', NULL, '6', 8, 'Completed'),
(30, '2025-06-14 17:02:37', NULL, '6', 8, 'Completed'),
(31, '2025-06-14 17:03:25', NULL, NULL, 8, 'Pending'),
(32, '2025-06-14 17:04:22', NULL, NULL, 8, 'Pending'),
(33, '2025-06-14 17:09:18', NULL, NULL, 8, 'Pending'),
(34, '2025-06-14 17:11:25', NULL, '6', 8, 'Completed'),
(35, '2025-06-14 17:24:04', NULL, '6', 8, 'Completed'),
(36, '2025-06-14 17:24:59', NULL, '6', 8, 'Completed'),
(37, '2025-06-14 17:25:51', NULL, '6', 8, 'Completed'),
(38, '2025-06-14 17:28:27', NULL, 'cash', 8, 'Pending'),
(39, '2025-06-14 17:31:55', NULL, NULL, 8, 'Completed'),
(40, '2025-06-14 17:35:19', NULL, '6', 8, 'Completed'),
(41, '2025-06-14 17:35:53', NULL, '6', 8, 'Completed'),
(42, '2025-06-14 17:41:04', NULL, '6', 8, 'Completed'),
(43, '2025-06-14 17:44:41', NULL, '6', 8, 'Completed'),
(44, '2025-06-14 17:45:11', NULL, '6', 8, 'Completed'),
(45, '2025-06-14 17:48:03', NULL, '6', 8, 'Completed'),
(46, '2025-06-14 17:48:40', NULL, '6', 8, 'Completed'),
(47, '2025-06-14 18:09:30', NULL, '6', 8, 'Completed'),
(48, '2025-06-14 18:11:28', NULL, '6', 8, 'Completed'),
(49, '2025-06-14 18:22:43', NULL, '9', 8, 'Completed'),
(50, '2025-06-14 18:24:05', NULL, '9', 9, 'Completed'),
(51, '2025-06-14 18:24:28', NULL, '9', 9, 'Completed'),
(52, '2025-06-14 18:28:06', NULL, NULL, 8, 'Completed'),
(53, '2025-06-14 18:40:12', NULL, '6', 6, 'Completed'),
(54, '2025-06-15 05:00:48', NULL, NULL, 5, 'Pending'),
(55, '2025-06-15 05:03:53', NULL, '6', 5, 'Completed'),
(56, '2025-06-15 05:19:06', NULL, '6', 9, 'Completed'),
(57, '2025-06-15 05:20:07', NULL, '6', 8, 'Completed'),
(58, '2025-06-15 05:23:44', NULL, NULL, 8, 'Completed'),
(59, '2025-06-15 05:24:15', NULL, '6', 8, 'Completed'),
(60, '2025-06-15 05:25:13', NULL, '6', 8, 'Completed'),
(61, '2025-06-15 05:28:08', NULL, '6', 8, 'Completed'),
(62, '2025-06-15 05:32:59', NULL, '6', 8, 'Completed'),
(63, '2025-06-15 05:36:03', NULL, '6', 8, 'Completed'),
(64, '2025-06-15 05:48:52', NULL, NULL, 8, 'Completed'),
(65, '2025-06-15 05:49:09', NULL, NULL, 8, 'Completed'),
(66, '2025-06-15 05:49:30', NULL, NULL, 8, 'Completed'),
(67, '2025-06-15 05:51:39', NULL, NULL, 8, 'Completed'),
(68, '2025-06-15 05:52:05', NULL, NULL, 8, 'Completed'),
(69, '2025-06-15 05:53:51', NULL, NULL, 8, 'Pending'),
(70, '2025-06-15 05:54:08', NULL, NULL, 8, 'Completed'),
(71, '2025-06-15 05:54:23', NULL, NULL, 8, 'Completed'),
(72, '2025-06-15 05:54:27', NULL, NULL, 8, 'Completed'),
(73, '2025-06-15 05:56:39', NULL, NULL, 8, 'Completed'),
(74, '2025-06-15 05:57:02', NULL, NULL, 8, 'Completed'),
(75, '2025-06-15 05:57:29', NULL, NULL, 8, 'Completed'),
(76, '2025-06-15 06:00:06', NULL, NULL, 8, 'Completed'),
(77, '2025-06-15 06:00:33', NULL, NULL, 8, 'Completed'),
(78, '2025-06-15 06:02:25', NULL, NULL, 8, 'Pending'),
(79, '2025-06-15 06:02:41', NULL, NULL, 8, 'Completed'),
(80, '2025-06-15 06:02:59', NULL, NULL, 8, 'Completed'),
(81, '2025-06-15 06:03:25', NULL, NULL, 8, 'Completed'),
(82, '2025-06-15 06:03:41', NULL, NULL, 8, 'Completed'),
(83, '2025-06-15 06:13:48', NULL, NULL, 8, 'Pending'),
(84, '2025-06-15 06:14:06', NULL, NULL, 8, 'Completed'),
(85, '2025-06-15 06:14:27', NULL, NULL, 8, 'Completed'),
(86, '2025-06-15 06:14:59', NULL, NULL, 8, 'Completed'),
(87, '2025-06-15 06:16:33', NULL, '6', 8, 'Completed'),
(88, '2025-06-15 06:22:58', NULL, NULL, 8, 'Completed'),
(89, '2025-06-15 06:25:10', NULL, NULL, 8, 'Completed'),
(90, '2025-06-15 06:26:55', NULL, NULL, 8, 'Completed'),
(91, '2025-06-15 06:29:20', NULL, NULL, 8, 'Pending'),
(92, '2025-06-15 06:30:42', NULL, NULL, 8, 'Pending'),
(93, '2025-06-15 06:31:31', NULL, NULL, 8, 'Completed'),
(94, '2025-06-15 06:34:25', NULL, NULL, 8, 'Pending'),
(95, '2025-06-15 06:34:36', NULL, '6', 8, 'Completed'),
(96, '2025-06-15 06:34:59', NULL, NULL, 8, 'Completed'),
(97, '2025-06-15 06:38:47', NULL, NULL, 8, 'Completed'),
(98, '2025-06-15 06:39:39', NULL, NULL, 8, 'Completed'),
(99, '2025-06-15 06:54:16', NULL, NULL, 8, 'Completed'),
(100, '2025-06-15 06:58:01', NULL, '6', 8, 'Completed'),
(101, '2025-06-15 06:59:58', NULL, '6', 8, 'Completed');

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
(1, 1, 2, 1, 123.00),
(2, 2, 1, 1, 1255.00),
(3, 3, 1, 1, 1255.00),
(4, 4, 5, 1, 1200.00),
(5, 5, 1, 1, 1255.00),
(6, 6, 1, 1, 20.00),
(7, 7, 1, 1, 30.00),
(8, 8, 14, 1, 1800.00),
(9, 9, 2, 1, 123.00),
(10, 10, 2, 1, 123.00),
(11, 11, 2, 1, 123.00),
(12, 12, 2, 1, 123.00),
(13, 13, 1, 1, 30.00),
(14, 14, 1, 1, 30.00),
(15, 15, 2, 1, 123.00),
(16, 16, 2, 1, 123.00),
(17, 17, 2, 1, 123.00),
(18, 18, 2, 1, 123.00),
(19, 19, 1, 1, 30.00),
(20, 20, 2, 1, 61.50),
(21, 21, 2, 1, 61.50),
(22, 22, 1, 1, 15.00),
(23, 22, 2, 1, 61.50),
(24, 23, 1, 1, 29.40),
(25, 24, 1, 1, 30.00),
(26, 25, 1, 1, 26.40),
(27, 26, 2, 1, 123.00),
(28, 27, 2, 1, 108.24),
(29, 27, 2, 1, 123.00),
(30, 28, 2, 1, 123.00),
(31, 28, 1, 1, 30.00),
(32, 28, 2, 1, 123.00),
(33, 28, 1, 1, 30.00),
(34, 29, 2, 1, 123.00),
(35, 29, 2, 1, 123.00),
(36, 30, 1, 1, 30.00),
(37, 30, 1, 1, 30.00),
(38, 31, 3, 20, 123.00),
(39, 31, 3, 20, 123.00),
(40, 32, 1, 20, 30.00),
(41, 32, 1, 20, 30.00),
(42, 33, 1, 1, 30.00),
(43, 34, 2, 11, 123.00),
(44, 35, 1, 1, 30.00),
(45, 36, 1, 1, 30.00),
(46, 37, 3, 1, 123.00),
(47, 38, 1, 1, 30.00),
(48, 39, 3, 1, 123.00),
(49, 40, 1, 1, 30.00),
(50, 41, 1, 1, 30.00),
(51, 42, 1, 1, 30.00),
(52, 43, 1, 1, 30.00),
(53, 44, 1, 1, 30.00),
(54, 45, 1, 1, 30.00),
(55, 46, 1, 1, 30.00),
(56, 47, 2, 2, 123.00),
(57, 47, 1, 2, 300.00),
(58, 47, 1, 1, 300.00),
(59, 47, 5, 2, 1200.00),
(60, 47, 3, 1, 123.00),
(61, 48, 1, 1, 300.00),
(62, 49, 1, 1, 270.00),
(63, 50, 1, 1, 300.00),
(64, 51, 2, 1, 110.70),
(65, 52, 1, 1, 300.00),
(66, 53, 1, 1, 300.00),
(67, 54, 2, 1, 123.00),
(68, 54, 2, 1, 123.00),
(69, 55, 1, 1, 300.00),
(70, 56, 2, 1, 123.00),
(71, 57, 1, 1, 300.00),
(72, 58, 1, 1, 300.00),
(73, 59, 1, 1, 300.00),
(74, 60, 1, 1, 300.00),
(75, 61, 3, 1, 123.00),
(76, 62, 1, 1, 285.00),
(77, 63, 1, 2, 285.00),
(78, 64, 1, 1, 285.00),
(79, 65, 1, 1, 285.00),
(80, 66, 2, 1, 116.85),
(81, 67, 1, 1, 285.00),
(82, 68, 1, 1, 285.00),
(83, 69, 1, 1, 285.00),
(84, 70, 2, 1, 116.85),
(85, 71, 2, 1, 116.85),
(86, 73, 2, 1, 116.85),
(87, 74, 2, 1, 116.85),
(88, 75, 1, 1, 285.00),
(89, 76, 2, 1, 116.85),
(90, 77, 2, 1, 116.85),
(91, 78, 3, 1, 116.85),
(92, 79, 2, 1, 116.85),
(93, 80, 2, 1, 116.85),
(94, 81, 1, 1, 285.00),
(95, 82, 1, 1, 285.00),
(96, 83, 1, 1, 270.00),
(97, 84, 1, 1, 270.00),
(98, 85, 1, 1, 270.00),
(99, 86, 1, 1, 270.00),
(100, 87, 1, 1, 255.00),
(101, 88, 1, 1, 255.00),
(102, 89, 2, 1, 104.55),
(103, 90, 1, 1, 255.00),
(104, 91, 1, 1, 255.00),
(105, 92, 1, 1, 255.00),
(106, 93, 1, 1, 255.00),
(107, 94, 1, 1, 255.00),
(108, 96, 2, 1, 104.55),
(109, 97, 1, 1, 255.00),
(110, 98, 2, 1, 104.55),
(111, 99, 1, 1, 255.00),
(112, 100, 2, 1, 104.55),
(113, 101, 2, 1, 104.55);

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
(1, 'Test', 'Test', 'Immediate', 'Monthly'),
(2, 'AgriSupply Co.', '09123456789', 'Net30', 'Every Monday'),
(3, 'FarmTech Solutions', '09234567890', 'Net15', 'Every Wednesday'),
(4, 'AgriTech Philippines', '09345678901', 'Immediate', 'Every Friday'),
(5, 'FarmFresh Suppliers', '09456789012', 'Net60', 'Every Tuesday'),
(6, 'AgriSupply Co.', '09123456789', 'Net30', 'Every Monday'),
(7, 'FarmTech Solutions', '09234567890', 'Net15', 'Every Wednesday'),
(8, 'AgriTech Philippines', '09345678901', 'Immediate', 'Every Friday'),
(9, 'FarmFresh Suppliers', '09456789012', 'Net60', 'Every Tuesday');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_products`
--

CREATE TABLE `supplier_products` (
  `SupProID` int(11) NOT NULL,
  `SupplierID` int(11) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `SupProd_Price` decimal(10,2) NOT NULL,
  `SupProd_LeadTimeDays` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_products`
--

INSERT INTO `supplier_products` (`SupProID`, `SupplierID`, `ProductID`, `SupProd_Price`, `SupProd_LeadTimeDays`) VALUES
(1, 1, 2, 1400.00, 3),
(2, 2, 3, 1900.00, 5),
(3, 2, 4, 750.00, 2),
(4, 3, 5, 1700.00, 4),
(5, 4, 1, 1150.00, 2),
(6, 4, 3, 1950.00, 3);

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
(1, 'superadmin', '$2y$10$/jTpukKxgaIrXxm8SLWM2uyHJexRLNyEHAIobQcIf0ib8mJbB8G/u', 3, '2025-06-12 11:38:10', 'uploads/profile_photos/684abc2224da5.jpg'),
(2, 'admin', '$2y$10$NzR1JAIQAhDrGWLB0m3nNepqqnHDeYxh5cvPM8aEyWs/dX9/j0fiy', 1, '2025-06-12 11:39:25', 'uploads/profile/profile_684d76b283c3b.jpeg'),
(3, 'user', '$2y$10$l5e2TuOgwc59AGMTikvrD.3rdf5OZBO327d0AgIlJf0zj59W2WF.S', 2, '2025-06-12 11:40:36', 'uploads/profile_photos/684abcb43f70d.png'),
(4, 'aian21', '$2y$10$V.3C2xfewHFkO.NcJVcyNuqvFNZOEBqqnMf5WWX.FjpSqsp0miWAy', 2, '2025-06-13 11:07:57', NULL),
(5, 'aian', '$2y$10$fgyJUTZkpYxyF3jVsq5A3uxlj5mCYTo5N/KNWaGBlk79VEjyxQmIa', 2, '2025-06-13 12:49:08', NULL),
(6, 'aian2123', '$2y$10$1G/AEtWLW047EGQlX9fU3eX82IElk9SvLVPwEhzOcQDDeQy2SXRpO', 3, '2025-06-13 14:40:41', 'uploads/profile/profile_684d722020515.png'),
(7, 'jajaja', '$2y$10$cYmH21ZXBwWmn8drWJCPPevLK/rP9CT1Q6RxyBEiZUTeTHCkCPVRS', 1, '2025-06-13 18:11:21', NULL),
(8, 'test', '$2y$10$EaXSvnb6J2gQjTcwiJ.Y0utykkXxiRcGIovKIUonEzk1JhZpq5NAe', 2, '2025-06-14 10:15:23', NULL),
(9, 'test2', '$2y$10$0yDNBE6qjFBMFd55ar5iNe8kfbCc3OtTjo3.oK8QiSI0jk4/sfnm.', 2, '2025-06-14 18:21:39', NULL);

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
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD PRIMARY KEY (`SupProID`),
  ADD KEY `SupplierID` (`SupplierID`),
  ADD KEY `ProductID` (`ProductID`);

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
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customer_discount_rate`
--
ALTER TABLE `customer_discount_rate`
  MODIFY `CusDiscountID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_alerts`
--
ALTER TABLE `inventory_alerts`
  MODIFY `AlertID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `IHID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `loyalty_program`
--
ALTER TABLE `loyalty_program`
  MODIFY `LoyaltyID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `loyalty_transaction_history`
--
ALTER TABLE `loyalty_transaction_history`
  MODIFY `LoTranHID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `order_promotions`
--
ALTER TABLE `order_promotions`
  MODIFY `OrderPromotionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `payment_history`
--
ALTER TABLE `payment_history`
  MODIFY `PaytoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `pricing_history`
--
ALTER TABLE `pricing_history`
  MODIFY `HistoryID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `product_access_log`
--
ALTER TABLE `product_access_log`
  MODIFY `LogID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `PromotionID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `promo_usage`
--
ALTER TABLE `promo_usage`
  MODIFY `UsageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `Pur_OrderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `purchase_order_item`
--
ALTER TABLE `purchase_order_item`
  MODIFY `Pur_OrderItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `SaleID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=102;

--
-- AUTO_INCREMENT for table `sale_item`
--
ALTER TABLE `sale_item`
  MODIFY `SaleItemID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `SupplierID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `supplier_products`
--
ALTER TABLE `supplier_products`
  MODIFY `SupProID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `UnitID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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

--
-- Constraints for table `supplier_products`
--
ALTER TABLE `supplier_products`
  ADD CONSTRAINT `supplier_products_ibfk_1` FOREIGN KEY (`SupplierID`) REFERENCES `suppliers` (`SupplierID`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_products_ibfk_2` FOREIGN KEY (`ProductID`) REFERENCES `products` (`ProductID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
