-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2025 at 12:24 PM
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
(3, 'jane', 'janepass', 3, '2025-06-03 07:06:50', 'jane.jpg'),
(4, 'aiannnnn', '$2y$10$eYvVDQ84QJZXVR1D84Tzh.LFs7R.LMIFbqblmqfVq2EdoOqyZOHHW', 2, '2025-06-11 17:57:14', NULL),
(5, 'aian21', '$2y$10$3e.bJWFDbHp7ERIvES5xJeqaIYO6OGFMe0hZKh0vworm6zbHoInZG', 2, '2025-06-11 17:59:02', NULL),
(6, 'cris', '$2y$10$/ocJ0ad2XbIvYmywh131tOfyZCxUWWuRFDC7kvpiyfd4h2lbGRNly', 2, '2025-06-12 05:07:43', 'uploads/profile_photos/684a609f00b07.jpg'),
(7, 'criscarlo', '$2y$10$.IxTARv0mjBWyhyCE.OBX.6FJzg3PPyoI1OFsqTjt5MOxs/XxXU7K', 2, '2025-06-12 05:09:06', 'uploads/profile_photos/684a60f2b54d0.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `user_accounts`
--
ALTER TABLE `user_accounts`
  ADD PRIMARY KEY (`UserID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `user_accounts`
--
ALTER TABLE `user_accounts`
  MODIFY `UserID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
