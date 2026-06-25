-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 25, 2026 at 08:53 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ims_db`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `current_stock`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `current_stock`;
CREATE TABLE IF NOT EXISTS `current_stock` (
`available_stock` int
,`buying_price` decimal(10,2)
,`id` int
,`product_code` varchar(50)
,`product_name` varchar(150)
,`selling_price` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `godowns`
--

DROP TABLE IF EXISTS `godowns`;
CREATE TABLE IF NOT EXISTS `godowns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `location` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `godowns`
--

INSERT INTO `godowns` (`id`, `name`, `location`, `created_at`) VALUES
(1, 'Main Store', 'Kampala', '2026-06-20 16:33:07');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_name` varchar(150) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `type_id` int DEFAULT NULL,
  `unit_id` int DEFAULT NULL,
  `buying_price` decimal(10,2) DEFAULT '0.00',
  `selling_price` decimal(10,2) DEFAULT '0.00',
  `quantity` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_code` (`product_code`),
  KEY `type_id` (`type_id`),
  KEY `unit_id` (`unit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `product_name`, `product_code`, `type_id`, `unit_id`, `buying_price`, `selling_price`, `quantity`, `created_at`) VALUES
(3, 'Beans', '222', 3, 3, 15000.00, 20000.00, 8, '2026-06-23 14:21:04'),
(6, 'television', '999', 2, 1, 500000.00, 800000.00, 20, '2026-06-25 08:30:12'),
(5, 'Laptop', '000', 2, 1, 800.00, 1000.00, 4, '2026-06-23 19:27:44');

-- --------------------------------------------------------

--
-- Table structure for table `product_types`
--

DROP TABLE IF EXISTS `product_types`;
CREATE TABLE IF NOT EXISTS `product_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `product_types`
--

INSERT INTO `product_types` (`id`, `name`, `created_at`) VALUES
(1, 'General', '2026-06-20 16:33:07'),
(2, 'Electronics', '2026-06-20 16:33:07'),
(3, 'Food', '2026-06-20 16:33:07');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int DEFAULT NULL,
  `godown_id` int DEFAULT NULL,
  `invoice_no` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('pending','approved','delivered') DEFAULT 'pending',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `supplier_id` (`supplier_id`),
  KEY `godown_id` (`godown_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`id`, `supplier_id`, `godown_id`, `invoice_no`, `purchase_date`, `total_amount`, `status`, `created_by`, `created_at`) VALUES
(1, 2, 1, '1452', '2026-06-08', 750000.00, 'delivered', 1, '2026-06-23 14:23:04'),
(2, 3, 1, '444', '2026-06-27', 40000.00, 'delivered', 1, '2026-06-23 19:30:24');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

DROP TABLE IF EXISTS `purchase_items`;
CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 3, 5, 150000.00),
(2, 2, 5, 2, 20000.00);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_returns`
--

DROP TABLE IF EXISTS `purchase_returns`;
CREATE TABLE IF NOT EXISTS `purchase_returns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `purchase_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `reason` text,
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE IF NOT EXISTS `sales` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(150) DEFAULT NULL,
  `godown_id` int DEFAULT NULL,
  `sale_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('pending','approved','completed') DEFAULT 'pending',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `godown_id` (`godown_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `customer_name`, `godown_id`, `sale_date`, `total_amount`, `status`, `created_by`, `created_at`) VALUES
(1, 'Nambassa Rebecca', 1, '2026-06-02', 40000.00, 'completed', 1, '2026-06-23 14:23:54'),
(2, 'Jeremiah', 1, '2026-06-03', 40000.00, 'completed', 1, '2026-06-23 19:31:38');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 3, 2, 20000.00),
(2, 2, 5, 2, 20000.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_returns`
--

DROP TABLE IF EXISTS `sale_returns`;
CREATE TABLE IF NOT EXISTS `sale_returns` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sale_id` int DEFAULT NULL,
  `product_id` int DEFAULT NULL,
  `quantity` int DEFAULT NULL,
  `reason` text,
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff_activity`
--

DROP TABLE IF EXISTS `staff_activity`;
CREATE TABLE IF NOT EXISTS `staff_activity` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `activity_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `staff_activity`
--

INSERT INTO `staff_activity` (`id`, `user_id`, `action`, `activity_time`) VALUES
(1, 1, 'Added product: Beans', '2026-06-22 08:19:58'),
(2, 1, 'Added product: Rice', '2026-06-22 08:53:37'),
(3, 1, 'Deleted product: Rice', '2026-06-23 14:19:09'),
(4, 1, 'Deleted product: Beans', '2026-06-23 14:19:13'),
(5, 1, 'Added product: Beans', '2026-06-23 14:21:04'),
(6, 1, 'Added supplier Roland Hiestand', '2026-06-23 14:21:40'),
(7, 1, 'Added supplier lauryn Hiestand', '2026-06-23 14:22:12'),
(8, 1, 'Created purchase ID 1', '2026-06-23 14:23:04'),
(9, 1, 'Created sale: 1', '2026-06-23 14:23:54'),
(10, 1, 'Added product: Beans', '2026-06-23 14:31:14'),
(11, 1, 'Created user Ageno', '2026-06-23 14:33:55'),
(12, 1, 'Created user Laura', '2026-06-23 14:34:43'),
(13, 1, 'Added product: Laptop', '2026-06-23 19:27:44'),
(14, 1, 'Deleted product: Beans', '2026-06-23 19:27:51'),
(15, 1, 'Added supplier Nankinja Joseline', '2026-06-23 19:29:20'),
(16, 1, 'Deleted supplier Roland Hiestand', '2026-06-23 19:29:26'),
(17, 1, 'Created purchase ID 2', '2026-06-23 19:30:24'),
(18, 1, 'Created sale: 2', '2026-06-23 19:31:38'),
(19, 1, 'Created user Tristan', '2026-06-23 19:33:09'),
(20, 1, 'Deleted user Tristan', '2026-06-23 19:33:21'),
(21, 1, 'Added product: television', '2026-06-25 08:30:12');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `phone`, `email`, `address`, `created_at`) VALUES
(2, 'lauryn Hiestand', '0780463337', 'atukundalauryn@gmail.com', 'Namugongo', '2026-06-23 14:22:12'),
(3, 'Nankinja Joseline', '0702444698', 'joseline@gmail.com', 'Kireka', '2026-06-23 19:29:20');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
CREATE TABLE IF NOT EXISTS `units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`) VALUES
(1, 'Piece'),
(2, 'Box'),
(3, 'Kg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','manager','staff') DEFAULT 'staff',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `role`, `status`, `created_at`) VALUES
(1, 'System Administrator', 'admin', 'admin@ims.com', '$2y$10$zFdMHoqVJuObYvtseyf9x./jaN/uOfrvNHqY6iDFeOHwkNCnKSZ5a', 'admin', 'active', '2026-06-20 16:33:06'),
(2, 'Bekisa Paulyne', 'Ageno', 'bekisa@gmail.com', '$2y$10$bTlld3y63MPW.eHmf9P2E.kOBhcWccYSWlY1BTcrJPZC3T96IkWdW', 'staff', 'active', '2026-06-23 14:33:55'),
(3, 'Atkunda Lauryn', 'Laura', 'atukundalauryn@gmail.com', '$2y$10$BsXROwKNwcUnq7wcFhsTtOAO6ssnqHBGS7auJwSwd2oWXuY29NmzW', 'manager', 'active', '2026-06-23 14:34:43');

-- --------------------------------------------------------

--
-- Structure for view `current_stock`
--
DROP TABLE IF EXISTS `current_stock`;

DROP VIEW IF EXISTS `current_stock`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `current_stock`  AS SELECT `p`.`id` AS `id`, `p`.`product_name` AS `product_name`, `p`.`product_code` AS `product_code`, `p`.`quantity` AS `available_stock`, `p`.`buying_price` AS `buying_price`, `p`.`selling_price` AS `selling_price` FROM `products` AS `p` ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
