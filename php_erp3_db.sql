-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 08, 2025 at 06:54 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `php_erp3_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` enum('sales','accounts','operation','production') DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`, `department`, `status`, `approved_by`, `created_at`, `updated_at`) VALUES
(1, 'JITENDRA SINGH', 'accounts@thepurewood.com', '$2y$10$zAr4QhesM.k5DjjwTNgX2.UONI4g7O8lY48tjQYhBbQ9n0wbYpuKq', 'accounts', 'approved', NULL, '2025-08-13 11:09:35', '2025-08-14 09:22:55'),
(2, 'accounts', 'accounts@purewood.in', '$2y$10$vmomsGU1JnjMidxmCWDpcuN2OLatz1IKcCwdl7b9NcumnPve5AeiC', 'accounts', 'approved', NULL, '2025-09-05 12:29:46', '2025-09-05 12:29:53');

-- --------------------------------------------------------

--
-- Table structure for table `bom`
--

CREATE TABLE `bom` (
  `id` int(11) NOT NULL,
  `bom_number` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `product_code` varchar(100) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `material_cost` decimal(15,2) DEFAULT 0.00,
  `labor_cost` decimal(15,2) DEFAULT 0.00,
  `overhead_cost` decimal(15,2) DEFAULT 0.00,
  `total_cost` decimal(15,2) GENERATED ALWAYS AS (`material_cost` + `labor_cost` + `overhead_cost`) STORED,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bom_factory`
--

CREATE TABLE `bom_factory` (
  `id` int(11) NOT NULL,
  `bom_main_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `factory_percentage` decimal(5,2) DEFAULT 15.00,
  `factory_cost` decimal(10,2) DEFAULT NULL,
  `updated_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bom_factory`
--

INSERT INTO `bom_factory` (`id`, `bom_main_id`, `total_amount`, `factory_percentage`, `factory_cost`, `updated_total`) VALUES
(7, 1, 2566.67, 15.00, 385.00, 2951.67),
(33, 2, 49490.10, 15.00, 7423.52, 56913.62),
(57, 4, 88193.15, 15.00, 13228.97, 101422.12),
(61, 3, 156716.95, 15.00, 23507.54, 180224.49),
(67, 5, 101765.63, 15.00, 15264.84, 117030.47),
(85, 6, 15562.50, 15.00, 2334.38, 17896.88),
(99, 7, 73070.50, 15.00, 10960.58, 84031.08),
(105, 8, 48215.00, 15.00, 7232.25, 55447.25),
(110, 9, 2798.42, 15.00, 419.76, 3218.18),
(117, 10, 32850.00, 15.00, 4927.50, 37777.50),
(124, 11, 34656.25, 15.00, 5198.44, 39854.69),
(132, 12, 93125.00, 15.00, 13968.75, 107093.75),
(139, 13, 117031.25, 15.00, 17554.69, 134585.94),
(148, 14, 118474.74, 15.00, 17771.21, 136245.95),
(154, 15, 272976.39, 15.00, 40946.46, 313922.85),
(163, 16, 43993.42, 15.00, 6599.01, 50592.43),
(172, 17, 37480.53, 15.00, 5622.08, 43102.61),
(186, 18, 79632.58, 15.00, 11944.89, 91577.47),
(199, 19, 27528.56, 15.00, 4129.28, 31657.84),
(210, 20, 37983.77, 15.00, 5697.57, 43681.34),
(218, 21, 51827.73, 15.00, 7774.16, 59601.89);

-- --------------------------------------------------------

--
-- Table structure for table `bom_glow`
--

CREATE TABLE `bom_glow` (
  `id` int(11) NOT NULL,
  `bom_main_id` int(11) NOT NULL,
  `glowtype` varchar(255) DEFAULT NULL,
  `quantity` decimal(10,3) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bom_glow`
--

INSERT INTO `bom_glow` (`id`, `bom_main_id`, `glowtype`, `quantity`, `price`, `total`) VALUES
(1, 1, 'glue', 0.500, 100.00, 50.00),
(4, 2, 'GLUE', 10.000, 250.00, 2500.00),
(6, 3, 'glow', 24.000, 160.00, 3840.00),
(8, 4, 'GLUE', 18.800, 50.00, 940.00),
(10, 5, 'GLUE', 25.000, 50.00, 1250.00),
(12, 6, 'GLUE', 2.000, 160.00, 320.00),
(13, 7, 'GLUE', 7.800, 160.00, 1248.00),
(14, 8, 'GLUE', 5.000, 160.00, 800.00),
(15, 9, 'fevicol', 1.300, 160.00, 208.00),
(16, 10, 'GLUE', 4.000, 160.00, 640.00),
(17, 11, 'GLUE', 2.500, 160.00, 400.00),
(18, 12, 'GLUE `', 10.000, 160.00, 1600.00),
(19, 13, 'GLUE', 5.000, 160.00, 800.00),
(21, 14, 'GLUE', 20.000, 160.00, 3200.00),
(22, 15, 'GLUE', 40.000, 160.00, 6400.00),
(23, 16, 'GLUE', 10.000, 160.00, 1600.00),
(25, 17, 'GLUE', 6.200, 160.00, 992.00),
(26, 18, 'HARDWARE', 12.500, 160.00, 2000.00),
(28, 19, 'HARDWARE', 5.000, 160.00, 800.00),
(30, 20, 'HARDWARE', 6.800, 160.00, 1088.00),
(31, 21, 'HARDWARE', 8.100, 160.00, 1296.00),
(32, 21, '', 0.000, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `bom_hardware`
--

CREATE TABLE `bom_hardware` (
  `id` int(11) NOT NULL,
  `bom_main_id` int(11) NOT NULL,
  `itemname` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `totalprice` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bom_hardware`
--

INSERT INTO `bom_hardware` (`id`, `bom_main_id`, `itemname`, `quantity`, `price`, `totalprice`) VALUES
(1, 1, 'hardware', 1, 100.00, 100.00),
(4, 2, 'HARDWARE', 30, 175.00, 5250.00),
(6, 3, 'Hardware', 75, 110.00, 8250.00),
(7, 4, 'HARWARE', 1, 4700.00, 4700.00),
(8, 5, 'HARDWARE', 25, 1350.00, 33750.00),
(13, 6, '5*80 GRANDER PAPER', 20, 17.50, 350.00),
(14, 6, 'BOND', 10, 13.00, 130.00),
(15, 7, 'HARDWARE', 1, 3750.00, 3750.00),
(16, 8, 'HARDWARE', 1, 2000.00, 2000.00),
(17, 10, 'HARDWARE', 1, 980.00, 980.00),
(18, 11, 'HARDWARE', 1, 600.00, 600.00),
(19, 12, 'HARDWARE', 1, 3400.00, 3400.00),
(20, 13, 'HARDWARE', 1, 3700.00, 3700.00),
(22, 14, 'HARDWARE', 15, 336.67, 5050.00),
(23, 14, 'CNC WORK', 15, 150.00, 2250.00),
(24, 15, 'HARDWARE', 40, 320.00, 12800.00),
(25, 15, 'CNC WORK`', 40, 150.00, 6000.00),
(27, 16, 'HARDWARE', 1, 1933.00, 1933.00),
(28, 17, 'HARDWARE', 1, 1337.00, 1337.00),
(32, 18, 'HARDWARE', 1, 3298.00, 3298.00),
(33, 19, 'HARDWARE', 1, 876.00, 876.00),
(34, 20, 'HARDWARE', 1, 1196.00, 1196.00),
(35, 21, 'HARDWARE', 1, 1830.00, 1830.00);

-- --------------------------------------------------------

--
-- Table structure for table `bom_labour`
--

CREATE TABLE `bom_labour` (
  `id` int(11) NOT NULL,
  `bom_main_id` int(11) NOT NULL,
  `itemname` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `totalprice` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bom_labour`
--

INSERT INTO `bom_labour` (`id`, `bom_main_id`, `itemname`, `quantity`, `price`, `totalprice`) VALUES
(1, 1, 'test labour ', 10, 100.00, 1000.00),
(6, 2, 'LAXMAN JI ', 10, 700.00, 7000.00),
(10, 4, 'LAXMAN JI', 47, 550.00, 25850.00),
(11, 3, 'RAJU DAN JI', 75, 410.00, 30750.00),
(12, 5, 'RAJU DAN JI', 25, 700.00, 17500.00),
(14, 6, 'RAJU TEST', 10, 550.00, 5500.00),
(16, 7, 'RAJU DAN', 25, 700.00, 17500.00),
(17, 8, 'RAJU DAN JI', 20, 812.00, 16240.00),
(18, 9, 'raju daan ', 1, 100.00, 100.00),
(19, 9, 'raju daan', 2, 100.00, 200.00),
(21, 10, 'LAXMAN JI', 18, 700.00, 12600.00),
(22, 11, 'LAXMAN JI', 20, 1250.00, 25000.00),
(23, 12, 'LAXMAN JI', 50, 700.00, 35000.00),
(24, 13, 'LAXMAN JI', 50, 850.00, 42500.00),
(25, 14, 'COMPANY WORKERS', 15, 1100.00, 16500.00),
(26, 15, 'COMPANY WORKER', 40, 950.00, 38000.00),
(27, 16, 'RAJU DAN JI', 1, 5200.00, 5200.00),
(29, 17, 'COMPANY ', 1, 5200.00, 5200.00),
(30, 17, 'POLISH CHARGE ', 1, 6657.00, 6657.00),
(40, 18, 'LABOURE', 1, 10890.00, 10890.00),
(41, 18, 'DOOR CHARGES (CNC)', 1, 6000.00, 6000.00),
(42, 18, 'POLISH WORK', 1, 10455.00, 10455.00),
(47, 19, 'LABOUR ', 1, 3640.00, 3640.00),
(48, 19, 'POLISH & PACKING', 1, 5455.00, 5455.00),
(54, 20, 'LABOUR', 1, 5776.00, 5776.00),
(55, 20, 'POLIsH & PACKING', 1, 7084.00, 7084.00),
(56, 21, 'LABOUR', 1, 8286.00, 8286.00),
(57, 21, 'POLISH & PACKING', 1, 9154.00, 9154.00);

-- --------------------------------------------------------

--
-- Table structure for table `bom_main`
--

CREATE TABLE `bom_main` (
  `id` int(11) NOT NULL,
  `bom_number` varchar(50) NOT NULL,
  `costing_sheet_number` varchar(255) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `prepared_by` varchar(255) NOT NULL,
  `order_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `labour_cost` decimal(10,2) DEFAULT NULL,
  `factory_cost` decimal(10,2) DEFAULT NULL,
  `margin` decimal(10,2) DEFAULT NULL,
  `grand_total_amount` decimal(10,2) DEFAULT NULL,
  `jci_assigned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bom_main`
--

INSERT INTO `bom_main` (`id`, `bom_number`, `costing_sheet_number`, `client_name`, `prepared_by`, `order_date`, `delivery_date`, `created_at`, `updated_at`, `labour_cost`, `factory_cost`, `margin`, `grand_total_amount`, `jci_assigned`) VALUES
(1, 'BOM-2025-0001', 'test 01', 'test 01', 'test', '2025-07-11', '2025-07-11', '2025-07-11 06:51:36', '2025-07-11 06:55:30', NULL, NULL, NULL, 2966.67, 1),
(2, 'BOM-2025-0002', 'DUMMY', 'APL', 'JS CHOUHAN', '2025-07-11', '2025-07-11', '2025-07-11 10:52:41', '2025-07-12 03:25:13', NULL, NULL, NULL, 63490.10, 1),
(3, 'BOM-2025-0003', '1', '7 Seas II', 'Js Chouhan', '2025-07-17', '2025-07-17', '2025-07-17 09:18:14', '2025-07-26 11:36:03', NULL, NULL, NULL, 210337.50, 1),
(4, 'BOM-2025-0004', '2', '7 Seas II', 'JS CHOUHAN', '2025-07-21', '2025-07-21', '2025-07-21 10:06:42', '2025-07-26 11:20:36', NULL, NULL, NULL, 121612.11, 1),
(5, 'BOM-2025-0005', '3', 'APL', 'JS CHOUHAN', '2025-07-24', '2025-07-24', '2025-07-30 10:10:48', '2025-07-30 10:15:12', NULL, NULL, NULL, 134585.04, 0),
(6, 'BOM-2025-0006', '6', 'TEST PD', 'JS CHOUHANQ', '2025-07-30', '2025-07-30', '2025-07-30 10:32:54', '2025-07-30 10:52:56', NULL, NULL, NULL, 20466.88, 1),
(7, 'BOM-2025-0007', '7', 'APL', 'JS CHOUHAN', '2025-07-31', '2025-07-31', '2025-07-31 09:06:22', '2025-07-31 10:49:25', NULL, NULL, NULL, 94042.12, 1),
(8, 'BOM-2025-0008', '8', 'APL', 'JS CHOUHAN', '2025-07-31', '2025-07-31', '2025-07-31 11:32:52', '2025-07-31 11:40:12', NULL, NULL, NULL, 58800.70, 1),
(9, 'BOM-2025-0009', '9', 'APL', 'JS CHOUHAN', '2025-08-07', '2025-08-07', '2025-08-07 11:10:08', '2025-08-07 11:16:26', NULL, NULL, NULL, 3538.42, 1),
(10, 'BOM-2025-0010', '10', 'APL', 'JS CHOUHAN', '2025-08-11', '2025-08-11', '2025-08-11 12:03:17', '2025-08-12 05:07:47', NULL, NULL, NULL, 50548.50, 1),
(11, 'BOM-2025-0011', '11', 'APL', 'JS CHOUHAN', '2025-08-12', '2025-08-12', '2025-08-12 05:37:50', '2025-08-12 05:44:24', NULL, NULL, NULL, 47942.44, 1),
(12, 'BOM-2025-0012', '12', '7 SEAS II', 'JS CHOUHAN', '2025-08-12', '2025-08-12', '2025-08-12 06:05:28', '2025-08-12 06:22:40', NULL, NULL, NULL, 130000.00, 1),
(13, 'BOM-2025-0013', '13', '7 Seas II', 'JS CHOUHAN', '2025-08-12', '2025-08-12', '2025-08-12 12:27:26', '2025-08-12 12:34:35', NULL, NULL, NULL, 155000.00, 1),
(14, 'BOM-2025-0014', '14', 'Shree Bhikshu Art Exim', 'JS CHOUHAN', '2025-08-18', '2025-08-18', '2025-08-18 07:04:28', '2025-08-18 09:04:14', NULL, NULL, NULL, 141761.95, 1),
(15, 'BOM-2025-0015', '15', 'SHREE BHIKSU ART EXIM', 'JS CHOUHAN', '2025-08-18', '2025-08-18', '2025-08-18 08:59:57', '2025-08-18 09:06:01', NULL, NULL, NULL, 310000.39, 1),
(16, 'BOM-2025-0016', '16', 'ANSHI COYMBTOR', 'JS CHOUHAN', '2025-08-23', '2025-08-23', '2025-08-23 09:06:45', '2025-08-23 09:15:28', NULL, NULL, NULL, 57610.21, 0),
(17, 'BOM-2025-0017', '17', 'ANSHI COIMBTOR', 'MAHAVEER SINGH', '2025-09-03', '2025-09-03', '2025-09-03 10:14:55', '2025-09-03 10:24:45', NULL, NULL, NULL, 47879.61, 1),
(18, 'BOM-2025-0018', '18', 'ANSHI', 'MAHAVEER SINGH', '2025-09-03', '2025-09-03', '2025-09-03 10:30:54', '2025-09-03 11:05:01', NULL, NULL, NULL, 103509.71, 1),
(19, 'BOM-2025-0019', '19', 'ANSHI DOOD', 'MAHAVEER SINGH', '2025-09-03', '2025-09-03', '2025-09-03 11:15:51', '2025-09-04 04:16:46', NULL, NULL, NULL, 36406.84, 1),
(20, 'BOM-2025-0020', '20', 'ANSHI DOOD', 'M.s', '2025-09-04', '2025-09-04', '2025-09-04 11:37:13', '2025-09-04 11:53:47', NULL, NULL, NULL, 50233.34, 1),
(21, 'BOM-2025-0021', '21', 'ANSHI DOORS', 'M.A', '2025-09-04', '2025-09-04', '2025-09-04 12:06:48', '2025-09-04 12:19:48', NULL, NULL, NULL, 68541.89, 0);

-- --------------------------------------------------------

--
-- Table structure for table `bom_margin`
--

CREATE TABLE `bom_margin` (
  `id` int(11) NOT NULL,
  `bom_main_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `margin_percentage` decimal(5,2) DEFAULT 15.00,
  `margin_cost` decimal(10,2) DEFAULT NULL,
  `updated_total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bom_margin`
--

INSERT INTO `bom_margin` (`id`, `bom_main_id`, `total_amount`, `margin_percentage`, `margin_cost`, `updated_total`) VALUES
(7, 1, 2766.67, 15.00, 200.00, 2966.67),
(33, 2, 53490.10, 15.00, 10000.00, 63490.10),
(57, 4, 101422.12, 15.00, 15213.32, 116635.44),
(61, 3, 179886.99, 15.00, 30113.01, 210000.00),
(67, 5, 117030.47, 15.00, 17554.57, 134585.04),
(85, 6, 17122.50, 15.00, 2570.00, 19692.50),
(99, 7, 83739.23, 15.00, 10011.04, 93750.27),
(105, 8, 55567.25, 15.00, 3233.45, 58800.70),
(110, 9, 3078.42, 15.00, 460.00, 3538.42),
(117, 10, 37629.00, 15.00, 12771.00, 50400.00),
(124, 11, 39912.25, 15.00, 8087.75, 48000.00),
(132, 12, 109343.00, 15.00, 20657.00, 130000.00),
(139, 13, 134531.25, 15.00, 20468.75, 155000.00),
(148, 14, 127234.74, 15.00, 5516.00, 132750.74),
(154, 15, 298696.39, 15.00, 11304.00, 310000.39),
(163, 16, 50592.43, 15.00, 7588.86, 58181.29),
(172, 17, 43136.20, 15.00, 4777.00, 47913.20),
(186, 18, 91577.47, 15.00, 13736.62, 105314.09),
(199, 19, 31657.55, 15.00, 4749.00, 36406.55),
(210, 20, 43681.76, 15.00, 6552.00, 50233.76),
(218, 21, 59601.72, 15.00, 8940.00, 68541.72);

-- --------------------------------------------------------

--
-- Table structure for table `bom_plynydf`
--

CREATE TABLE `bom_plynydf` (
  `id` int(11) NOT NULL,
  `bom_main_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `width` decimal(10,2) DEFAULT NULL,
  `length` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bom_plynydf`
--

INSERT INTO `bom_plynydf` (`id`, `bom_main_id`, `quantity`, `width`, `length`, `price`, `total`) VALUES
(1, 1, 1, 10.00, 10.00, 10.00, 1000.00),
(2, 2, 2, 10.00, 10.00, 10.00, 2000.00),
(5, 6, 10, 1.50, 2.00, 42.00, 1260.00),
(6, 10, 1, 1.00, 1.00, 3780.00, 3780.00),
(8, 12, 1, 1.00, 1.00, 9000.00, 9000.00),
(9, 13, 50, 1.00, 1.00, 450.00, 22500.00),
(10, 14, 1, 1.00, 1.00, 2250.00, 2250.00);

-- --------------------------------------------------------

--
-- Table structure for table `bom_wood`
--

CREATE TABLE `bom_wood` (
  `id` int(11) NOT NULL,
  `bom_main_id` int(11) NOT NULL,
  `woodtype` varchar(255) DEFAULT NULL,
  `length_ft` decimal(10,2) DEFAULT NULL,
  `width_ft` decimal(10,2) DEFAULT NULL,
  `thickness_inch` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `cft` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `bom_wood`
--

INSERT INTO `bom_wood` (`id`, `bom_main_id`, `woodtype`, `length_ft`, `width_ft`, `thickness_inch`, `quantity`, `price`, `cft`, `total`) VALUES
(1, 1, 'Mango', 10.00, 0.83, 10.00, 3, 20.00, 20.83, 416.67),
(16, 2, 'Mango', 4.50, 2.83, 2.00, 20, 690.00, 42.45, 29290.50),
(17, 2, 'Mango', 1.75, 0.33, 1.60, 80, 560.00, 6.16, 3449.60),
(40, 3, 'Mango', 3.00, 1.67, 1.50, 225, 650.00, 140.91, 91589.06),
(41, 3, 'Mango', 2.75, 0.25, 1.50, 300, 650.00, 25.78, 16757.81),
(42, 3, 'Mango', 2.75, 0.33, 1.50, 75, 650.00, 8.51, 5530.08),
(43, 4, 'Mango', 1.75, 1.58, 1.00, 94, 560.00, 21.66, 12129.13),
(44, 4, 'Mango', 1.75, 1.92, 1.00, 94, 560.00, 26.32, 14739.20),
(45, 4, 'Mango', 1.75, 0.42, 1.00, 188, 560.00, 11.52, 6448.40),
(46, 4, 'Mango', 1.50, 0.42, 1.00, 188, 560.00, 9.87, 5527.20),
(47, 4, 'Mango', 1.75, 0.13, 1.00, 94, 560.00, 1.78, 997.97),
(48, 4, 'Mango', 1.50, 1.25, 1.00, 141, 560.00, 22.03, 12337.50),
(49, 4, 'Mango', 1.50, 0.25, 1.00, 188, 560.00, 5.88, 3290.00),
(50, 4, '', 1.50, 0.25, 1.50, 47, 560.00, 2.20, 1233.75),
(54, 5, 'Mango', 2.75, 1.92, 1.00, 100, 570.00, 43.92, 25036.46),
(55, 5, 'Mango', 2.50, 0.33, 2.00, 200, 690.00, 27.78, 19166.67),
(56, 5, 'Mango', 1.50, 0.33, 1.50, 150, 540.00, 9.38, 5062.50),
(61, 6, 'Mango', 2.00, 0.33, 1.50, 50, 560.00, 4.13, 2310.00),
(62, 6, 'Mango', 2.50, 0.33, 1.50, 80, 690.00, 8.25, 5692.50),
(69, 7, 'Mango', 2.50, 1.92, 1.00, 100, 660.00, 40.00, 26400.00),
(70, 7, 'Mango', 2.50, 0.33, 2.00, 200, 690.00, 27.50, 18975.00),
(71, 7, 'Mango', 1.50, 0.33, 1.50, 150, 560.00, 9.28, 5197.50),
(72, 8, 'Mango', 2.25, 1.58, 1.00, 140, 560.00, 41.56, 23275.00),
(73, 8, 'Mango', 1.50, 2.00, 2.00, 20, 590.00, 10.00, 5900.00),
(74, 9, 'Mango', 4.75, 0.33, 2.00, 6, 690.00, 1.58, 1092.50),
(75, 9, 'Mango', 2.50, 0.42, 2.00, 10, 690.00, 1.74, 1197.92),
(76, 10, 'Mango', 2.50, 0.33, 1.00, 324, 660.00, 22.50, 14850.00),
(77, 11, 'Mango', 4.50, 0.42, 1.50, 20, 660.00, 4.69, 3093.75),
(78, 11, 'Mango', 3.00, 0.42, 1.50, 20, 660.00, 3.13, 2062.50),
(79, 11, 'Mango', 1.50, 0.83, 1.50, 40, 560.00, 6.25, 3500.00),
(80, 12, 'Mango', 1.75, 0.33, 1.50, 900, 560.00, 65.62, 36750.00),
(81, 12, 'Mango', 1.50, 0.50, 2.00, 100, 590.00, 12.50, 7375.00),
(82, 13, 'Mango', 2.50, 1.50, 1.00, 150, 660.00, 46.88, 30937.50),
(83, 13, 'Mango', 1.50, 0.75, 2.00, 150, 590.00, 28.13, 16593.75),
(84, 14, 'Babool', 8.50, 4.42, 1.50, 15, 850.00, 70.39, 59832.03),
(85, 14, 'Babool', 2.75, 2.92, 2.00, 30, 650.00, 40.10, 26067.71),
(86, 14, 'Babool', 1.75, 0.58, 2.00, 30, 600.00, 5.10, 3062.50),
(87, 14, 'Babool', 1.50, 0.25, 1.00, 15, 560.00, 0.47, 262.50),
(88, 15, 'Babool', 7.00, 4.42, 1.50, 40, 850.00, 154.58, 131395.83),
(89, 15, 'Babool', 2.75, 2.92, 2.00, 80, 650.00, 106.94, 69513.89),
(90, 15, 'Babool', 1.75, 0.58, 2.00, 80, 600.00, 13.61, 8166.67),
(91, 15, 'Babool', 1.50, 0.25, 1.00, 40, 560.00, 1.25, 700.00),
(97, 16, 'Other', 8.50, 5.00, 2.50, 1, 2500.00, 8.85, 22135.42),
(98, 16, 'Other', 8.50, 1.25, 2.00, 2, 2500.00, 3.54, 8854.17),
(99, 16, 'Other', 4.00, 1.25, 2.00, 1, 2500.00, 0.83, 2083.33),
(100, 16, 'Other', 4.00, 0.25, 2.00, 1, 2500.00, 0.17, 416.67),
(101, 16, 'Other', 8.50, 0.25, 2.00, 2, 2500.00, 0.71, 1770.83),
(102, 17, 'Other', 8.00, 1.17, 2.50, 2, 1850.00, 3.89, 7194.44),
(103, 17, 'Other', 3.75, 1.17, 2.50, 1, 1850.00, 0.91, 1686.20),
(104, 17, 'Other', 8.25, 0.42, 1.00, 4, 1850.00, 1.15, 2119.79),
(105, 17, 'Other', 4.50, 0.42, 1.00, 2, 1850.00, 0.31, 578.13),
(106, 17, 'Other', 8.00, 0.42, 2.00, 2, 2350.00, 1.11, 2611.11),
(107, 17, 'Other', 3.50, 1.58, 2.00, 1, 2350.00, 0.92, 2170.49),
(108, 17, 'Other', 2.50, 3.33, 1.00, 1, 2350.00, 0.69, 1631.94),
(109, 17, 'Other', 4.25, 3.33, 1.00, 1, 2350.00, 1.18, 2774.31),
(110, 17, 'Other', 2.50, 0.33, 1.50, 2, 2500.00, 0.21, 520.83),
(111, 17, 'Other', 3.00, 0.33, 1.50, 4, 2350.00, 0.50, 1175.00),
(112, 17, 'Other', 4.25, 0.33, 1.50, 2, 2350.00, 0.35, 832.29),
(114, 18, 'Other', 10.50, 8.33, 2.00, 1, 2350.00, 14.58, 34270.83),
(115, 18, 'Other', 10.50, 1.25, 2.00, 2, 1850.00, 4.38, 8093.75),
(116, 18, 'Other', 6.50, 1.25, 2.00, 1, 1850.00, 1.35, 2505.21),
(117, 18, 'Other', 10.50, 0.25, 2.00, 2, 1850.00, 0.88, 1618.75),
(118, 18, 'Other', 6.50, 0.25, 2.00, 1, 1850.00, 0.27, 501.04),
(123, 19, 'Other', 6.50, 1.17, 2.00, 2, 1850.00, 2.53, 4676.39),
(124, 19, 'Other', 3.25, 1.17, 2.00, 1, 1850.00, 0.63, 1169.10),
(125, 19, 'Other', 7.00, 0.42, 1.00, 4, 1850.00, 0.97, 1798.61),
(126, 19, 'Other', 3.75, 0.42, 1.00, 2, 1850.00, 0.26, 481.77),
(127, 19, 'Other', 6.50, 0.42, 2.00, 2, 2350.00, 0.90, 2121.53),
(128, 19, 'Other', 3.00, 1.58, 2.00, 1, 2350.00, 0.79, 1860.42),
(129, 19, 'Other', 2.25, 2.58, 1.00, 1, 2350.00, 0.48, 1138.28),
(130, 19, 'Other', 3.00, 2.58, 1.00, 1, 2350.00, 0.65, 1517.71),
(131, 19, 'Other', 2.25, 0.33, 1.50, 6, 2500.00, 0.56, 1406.25),
(132, 19, 'Other', 3.00, 0.33, 1.50, 2, 2350.00, 0.25, 587.50),
(133, 20, 'Other', 4.00, 1.17, 2.00, 1, 1850.00, 0.78, 1438.89),
(134, 20, 'Other', 8.25, 1.17, 2.00, 2, 1850.00, 3.21, 5935.42),
(135, 20, 'Other', 4.75, 0.42, 1.00, 2, 1850.00, 0.33, 610.24),
(136, 20, 'Other', 8.50, 0.42, 1.00, 4, 1850.00, 1.18, 2184.03),
(137, 20, 'Seesam', 8.25, 0.42, 2.00, 2, 2350.00, 1.15, 2692.71),
(138, 20, 'Other', 4.00, 1.58, 2.00, 1, 2350.00, 1.06, 2480.56),
(139, 20, 'Other', 3.25, 0.33, 1.50, 4, 2350.00, 0.54, 1272.92),
(140, 20, 'Other', 2.50, 0.33, 1.50, 2, 2350.00, 0.21, 489.58),
(141, 20, 'Other', 4.50, 0.33, 1.50, 2, 2500.00, 0.38, 937.50),
(142, 20, 'Other', 4.50, 3.50, 1.00, 1, 2350.00, 1.31, 3084.38),
(143, 20, 'Other', 2.50, 3.50, 1.00, 1, 2350.00, 0.73, 1713.54),
(155, 21, 'Other', 11.25, 1.25, 2.00, 2, 1850.00, 4.69, 8671.88),
(156, 21, 'Other', 4.00, 1.25, 2.00, 1, 1850.00, 0.83, 1541.67),
(157, 21, 'Other', 11.50, 0.42, 1.00, 4, 1850.00, 1.60, 2954.86),
(158, 21, 'Other', 4.75, 0.42, 1.00, 2, 1850.00, 0.33, 610.24),
(159, 21, 'Other', 11.75, 0.50, 2.00, 2, 2350.00, 1.96, 4602.08),
(160, 21, 'Other', 3.75, 1.67, 2.00, 1, 2350.00, 1.04, 2447.92),
(161, 21, 'Other', 2.50, 3.75, 1.00, 1, 2350.00, 0.78, 1835.94),
(162, 21, 'Other', 7.25, 3.75, 1.00, 1, 2350.00, 2.27, 5324.22),
(163, 21, 'Other', 7.25, 0.33, 1.50, 2, 2500.00, 0.60, 1510.42),
(164, 21, 'Other', 3.25, 0.33, 1.50, 4, 2350.00, 0.54, 1272.92),
(165, 21, 'Other', 2.50, 0.33, 1.50, 2, 2350.00, 0.21, 489.58);

-- --------------------------------------------------------

--
-- Table structure for table `buyers`
--

CREATE TABLE `buyers` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `contact_person_name` varchar(255) NOT NULL,
  `contact_person_email` varchar(255) NOT NULL,
  `contact_person_phone` varchar(20) NOT NULL,
  `company_address` text NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jci`
--

CREATE TABLE `jci` (
  `id` int(11) NOT NULL,
  `jci_number` varchar(50) NOT NULL,
  `job_title` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `estimated_cost` decimal(15,2) DEFAULT 0.00,
  `actual_cost` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jci_items`
--

CREATE TABLE `jci_items` (
  `id` int(11) NOT NULL,
  `jci_id` int(11) NOT NULL,
  `job_card_number` varchar(255) DEFAULT NULL,
  `po_product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `original_po_quantity` decimal(10,2) DEFAULT NULL,
  `labour_cost` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_date` date NOT NULL,
  `job_card_date` date DEFAULT NULL,
  `job_card_type` enum('Contracture','In-House') DEFAULT NULL,
  `contracture_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jci_items`
--

INSERT INTO `jci_items` (`id`, `jci_id`, `job_card_number`, `po_product_id`, `product_name`, `item_code`, `original_po_quantity`, `labour_cost`, `quantity`, `total_amount`, `delivery_date`, `job_card_date`, `job_card_type`, `contracture_name`) VALUES
(1, 1, 'JOB-2025-0001-1', 1, 'test pro', 'pro01', 10.00, 100.00, 10, 1000.00, '2025-07-01', '2025-07-16', 'Contracture', 'test con'),
(2, 2, 'JOB-2025-0002-1', 2, 'COFFEE TABLE 120X65X43CM', 'AHFL-CT180', 20.00, 700.00, 20, 14000.00, '2025-07-31', '2025-07-14', 'Contracture', 'LAXMAN JI'),
(5, 4, 'JOB-2025-0004-1', 4, 'C Side Table Raw Section', 'CBRN001', 47.00, 550.00, 47, 25850.00, '2025-07-21', '2025-08-10', 'Contracture', 'LAXMAN JI'),
(7, 3, 'JOB-2025-0003-1', 3, 'Burano Chair Mango Wood Frame Raw', 'NA', 75.00, 0.00, 75, 0.00, '2025-07-26', '2025-08-10', 'In-House', NULL),
(9, 5, 'JOB-2025-0005-1', 5, 'TEST PERFECT STUDY TABLE', 'TEST CODE-001', 10.00, 550.00, 10, 5500.00, '2025-08-20', '2025-07-30', 'Contracture', 'RAJU TEST'),
(10, 6, 'JOB-2025-0006-1', 7, '\" 124311 AHDU DT220MB Wooden  Base\"', 'ADC-410', 25.00, 700.00, 25, 17500.00, '2025-08-20', '2025-07-24', 'Contracture', 'RAJU DAN JI'),
(11, 7, 'JOB-2025-0007-1', 8, 'Moda Side Table', 'BS-MOD02N', 20.00, 812.00, 20, 16240.00, '2025-08-25', '2025-07-31', 'Contracture', 'RAJU DAN JI'),
(12, 8, 'JOB-2025-0008-1', 9, 'top and leg', 'dining table', 1.00, 300.00, 1, 300.00, '2025-08-07', '2025-08-07', 'Contracture', 'raju dan ji'),
(13, 9, 'JOB-2025-0009-1', 10, '\"BS-FLU-12-Fluting Round Table\"', 'BS-FLU12', 18.00, 700.00, 18, 12600.00, '2025-08-24', '2025-08-12', 'Contracture', 'LAXMAN JI'),
(14, 10, 'JOB-2025-0010-1', 12, 'puzzle coffee table top frame', 'AHPZ-CT140PW-T', 20.00, 1250.00, 20, 25000.00, '2025-08-18', '2025-08-12', 'Contracture', 'LAXMAN JI'),
(15, 11, 'JOB-2025-0011-1', 13, 'BSLA-004 - Basilia End Tabl Raw Section 6\" leg (1SET 3LEGS)', 'BSLA-004', 50.00, 700.00, 50, 35000.00, '2025-08-26', '2025-08-12', 'Contracture', 'LAXMAN JI'),
(16, 12, 'JOB-2025-0012-1', 15, '\"Basilia Coffee Table Wooden Raw Section\"', 'BSLA004-173535A', 50.00, 850.00, 50, 42500.00, '2025-08-25', '2025-08-12', 'Contracture', 'LAXMAN JI'),
(17, 13, 'JOB-2025-0013-1', 17, 'Wdn Acacia Dining Table', '51177', 40.00, 950.00, 40, 38000.00, '2025-09-09', '2025-08-18', 'In-House', NULL),
(18, 14, 'JOB-2025-0014-1', 18, 'Wdn Acacia Dining Table', '51178', 15.00, 1100.00, 15, 16500.00, '2025-08-09', '2025-08-18', 'In-House', NULL),
(19, 15, 'JOB-2025-0015-1', 89, 'Terrace Door-D10 (38 MM THICK)', '9', 1.00, 5200.00, 1, 5200.00, '2025-09-26', '2025-09-03', 'In-House', NULL),
(20, 16, 'JOB-2025-0016-1', 100, 'Main Door-D1 (50 MM THICK) (Double Leaf)', '20', 1.00, 10890.00, 1, 10890.00, '2025-09-26', '2025-09-03', 'In-House', NULL),
(21, 17, 'JOB-2025-0017-1', 108, 'Common Bathroom-D9 (38 MM THICK)', '8', 1.00, 3640.00, 1, 3640.00, '2025-09-26', '2025-09-26', 'In-House', NULL),
(22, 18, 'JOB-2025-0018-1', 102, 'Bedroom-1-D3 (38 MM THICK)', '2', 1.00, 5776.00, 1, 5776.00, '2025-09-26', '2025-09-26', 'In-House', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jci_main`
--

CREATE TABLE `jci_main` (
  `id` int(11) NOT NULL,
  `jci_number` varchar(255) NOT NULL,
  `po_id` int(11) DEFAULT NULL,
  `bom_id` int(11) DEFAULT NULL,
  `jci_type` enum('Contracture','In-House') NOT NULL,
  `created_by` varchar(255) NOT NULL,
  `jci_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sell_order_number` varchar(50) DEFAULT NULL,
  `purchase_created` tinyint(1) DEFAULT 0,
  `payment_completed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jci_main`
--

INSERT INTO `jci_main` (`id`, `jci_number`, `po_id`, `bom_id`, `jci_type`, `created_by`, `jci_date`, `created_at`, `updated_at`, `sell_order_number`, `purchase_created`, `payment_completed`) VALUES
(1, 'JCI-2025-0001', 1, 1, 'Contracture', 'test 01', '2025-07-11', '2025-07-11 06:55:30', '2025-07-11 11:46:44', '\r\n                                    SALE-2025-00', 1, 1),
(2, 'JCI-2025-0002', 2, 2, 'Contracture', 'JS CHOUHAN', '2025-07-11', '2025-07-11 11:03:42', '2025-07-11 11:03:42', '\r\n                                    SALE-2025-00', 0, 0),
(3, 'JCI-2025-0003', 3, 3, 'Contracture', 'Js Chouhan', '2025-07-17', '2025-07-17 10:03:55', '2025-07-21 08:38:28', '\r\n                                    SALE-2025-00', 0, 0),
(4, 'JCI-2025-0004', 4, 4, 'Contracture', 'JS CHOUHAN', '2025-07-21', '2025-07-21 10:24:09', '2025-07-21 10:24:09', '\r\n                                    SALE-2025-00', 0, 0),
(5, 'JCI-2025-0005', 5, 6, 'Contracture', 'JS', '2025-07-30', '2025-07-30 10:46:08', '2025-07-31 06:50:35', '\r\n                                    SALE-2025-00', 1, 1),
(6, 'JCI-2025-0006', 6, 7, 'Contracture', 'JS CHOUHAN', '2025-07-24', '2025-07-31 09:18:37', '2025-07-31 11:16:55', '\r\n                                    SALE-2025-00', 1, 1),
(7, 'JCI-2025-0007', 7, 8, 'Contracture', 'JS CHOUHAN', '2025-07-31', '2025-07-31 11:40:12', '2025-09-01 10:52:12', '\r\n                                    SALE-2025-00', 1, 0),
(8, 'JCI-2025-0008', 8, 9, 'Contracture', 'js chouhan', '2025-08-07', '2025-08-07 11:16:26', '2025-08-07 11:16:26', '\r\n                                    SALE-2025-00', 0, 0),
(9, 'JCI-2025-0009', 9, 10, 'Contracture', 'JS CHOUHAN', '2025-08-12', '2025-08-12 05:07:47', '2025-08-12 05:07:47', '\r\n                                    SALE-2025-00', 0, 0),
(10, 'JCI-2025-0010', 10, 11, 'Contracture', 'JS CHOUHAN', '2025-08-12', '2025-08-12 05:44:24', '2025-08-12 05:44:24', '\r\n                                    SALE-2025-00', 0, 0),
(11, 'JCI-2025-0011', 11, 12, 'Contracture', 'JS CHOUHAN', '2025-08-12', '2025-08-12 06:22:40', '2025-08-12 06:22:40', '\r\n                                    SALE-2025-00', 0, 0),
(12, 'JCI-2025-0012', 12, 13, 'Contracture', 'JS CHOUHAN', '2025-08-12', '2025-08-12 12:34:35', '2025-08-12 12:34:35', '\r\n                                    SALE-2025-00', 0, 0),
(13, 'JCI-2025-0013', 13, 14, 'Contracture', 'JS CHOUHAN', '2025-08-18', '2025-08-18 09:04:14', '2025-08-18 09:04:14', '\r\n                                    SALE-2025-00', 0, 0),
(14, 'JCI-2025-0014', 13, 15, 'Contracture', 'JS CHOUHAN', '2025-08-18', '2025-08-18 09:06:01', '2025-08-18 09:06:01', '\r\n                                    SALE-2025-00', 0, 0),
(15, 'JCI-2025-0015', 18, 17, 'Contracture', 'MAHAVEER SINGH', '2025-09-03', '2025-09-03 10:24:45', '2025-09-03 10:24:45', '\r\n                                    SALE-2025-00', 0, 0),
(16, 'JCI-2025-0016', 18, 18, 'Contracture', 'MAHAVEER SINGH', '2025-09-03', '2025-09-03 11:05:01', '2025-09-03 11:05:01', '\r\n                                    SALE-2025-00', 0, 0),
(17, 'JCI-2025-0017', 18, 19, 'Contracture', 'Mahaveer Singh', '2025-09-04', '2025-09-04 04:16:46', '2025-09-04 04:16:46', '\r\n                                    SALE-2025-00', 0, 0),
(18, 'JCI-2025-0018', 18, 20, 'Contracture', 'M.A', '2025-09-04', '2025-09-04 11:53:47', '2025-09-04 11:53:47', '\r\n                                    SALE-2025-00', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `job_cards`
--

CREATE TABLE `job_cards` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `jc_number` varchar(255) NOT NULL,
  `jc_amt` decimal(15,2) NOT NULL,
  `jc_type` varchar(255) DEFAULT NULL,
  `contracture_name` varchar(255) DEFAULT NULL,
  `labour_cost` decimal(15,2) DEFAULT 0.00,
  `quantity` int(11) DEFAULT 0,
  `total_amount` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leads`
--

CREATE TABLE `leads` (
  `id` int(11) NOT NULL,
  `lead_number` varchar(50) NOT NULL,
  `entry_date` date NOT NULL,
  `lead_source` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `contact_email` varchar(255) NOT NULL,
  `country` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `created_status` varchar(50) DEFAULT 'new',
  `approve` tinyint(1) DEFAULT 0,
  `status` varchar(50) DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `lead_number`, `entry_date`, `lead_source`, `company_name`, `contact_name`, `contact_phone`, `contact_email`, `country`, `state`, `city`, `created_status`, `approve`, `status`, `created_at`, `updated_at`) VALUES
(1, 'LEAD-2025-0001', '2025-08-02', 'Ravi Sir', 'Gret Holland', 'Gret', '0000000000', 'Test@gmail.com', 'Netherlands', 'South Holland', 'Adegeest', 'new', 1, 'active', '2025-08-02 11:49:43', '2025-08-02 11:49:56'),
(2, 'LEAD-2025-0002', '2025-08-06', 'Ravi Sir', 'Gret Holland', 'Gret', '0000000000', 'Test@gmail.com', 'Netherlands', 'North Holland', 'Amsterdam', 'new', 0, 'active', '2025-08-06 06:01:40', '2025-08-06 06:01:40'),
(3, 'LEAD-2025-0003', '2025-08-06', 'Ravi Sir', 'Shawn USA', 'Mr.Shawn', '0000000000', 'Test@gmail.com', 'United States', 'Arizona', 'Apache County', 'new', 1, 'active', '2025-08-06 06:03:29', '2025-08-06 06:03:36'),
(4, 'LEAD-2025-0004', '2025-08-06', 'Ravi Sir', 'Shawn USA', 'Mr.Shawn', '0000000000', 'Test@gmail.com', 'United States', 'Arizona', 'Apache County', 'new', 1, 'active', '2025-08-06 06:22:47', '2025-08-06 06:22:58'),
(5, 'LEAD-2025-0005', '2025-08-06', 'Ravi Sir', 'Plein 5', 'Gret Van Verseveld', '0031 653428855', 'H.Vanverseveld@hotmail.com', 'Netherlands', 'Gelderland', 'Ingen', 'new', 1, 'active', '2025-08-06 06:33:48', '2025-08-06 06:33:57'),
(6, 'LEAD-2025-0006', '2025-08-12', 'Ravi Sir', 'HVL Design', 'Harriet vonLadiges', 'NA', 'H.Ladiges@h-v-l.de', 'Germany', 'Schleswig-Holstein', 'Lübeck', 'new', 1, 'active', '2025-08-12 06:02:39', '2025-08-12 06:02:48'),
(7, 'LEAD-2025-0007', '2025-08-09', 'Ravi Sir', 'Johannes Germany', 'Mr. Johaness', 'NA', 'Johannes@testmail.com', 'Germany', 'Berlin', 'Adlershof', 'new', 1, 'active', '2025-08-12 06:23:27', '2025-08-12 06:23:33'),
(8, 'LEAD-2025-0008', '2025-08-09', 'Ravi Sir', 'Purewood USA', 'Mr. Shawn', 'NA', 'modensifurniture@gmail.com', 'United States', 'Arizona', 'Arizona City', 'new', 1, 'active', '2025-08-12 11:54:58', '2025-08-12 11:55:06'),
(9, 'LEAD-2025-0008', '2025-08-09', 'Ravi Sir', 'Purewood USA', 'Mr. Shawn', 'NA', 'modensifurniture@gmail.com', 'United States', 'Arizona', 'Arizona City', 'new', 0, 'active', '2025-08-12 11:54:58', '2025-08-12 11:54:58'),
(10, 'LEAD-2025-0009', '2025-08-15', 'Ravi Sir', 'Avi Homes USA ', 'Mr. Avi', 'NA', 'Avi@testmail.com', 'United States', 'California', 'Acalanes Ridge', 'new', 1, 'active', '2025-08-15 11:27:24', '2025-08-15 11:27:34');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES
(1, '001_create_admin_users_table', 1, '2025-08-13 09:50:10'),
(2, '002_create_suppliers_table', 1, '2025-08-13 09:50:10'),
(3, '003_create_leads_table', 1, '2025-08-13 09:50:10'),
(4, '004_create_quotations_table', 1, '2025-08-13 09:50:10'),
(5, '005_create_quotation_products_table', 1, '2025-08-13 09:50:10'),
(6, '006_create_customers_table', 1, '2025-08-13 09:50:10'),
(7, '007_create_pi_table', 1, '2025-08-13 09:50:10'),
(8, '008_create_purchase_table', 1, '2025-08-13 09:50:11'),
(9, '009_create_po_table', 1, '2025-08-13 09:50:11'),
(10, '010_create_so_table', 1, '2025-08-13 09:50:11'),
(11, '011_create_bom_table', 1, '2025-08-13 09:50:11'),
(12, '012_create_jci_table', 1, '2025-08-13 09:50:11'),
(13, '013_create_payments_table', 1, '2025-08-13 09:50:11'),
(14, '014_add_lock_system', 1, '2025-08-13 09:50:11'),
(15, '015_create_notifications_table', 1, '2025-08-13 09:50:11'),
(16, '016_create_buyers_table', 1, '2025-08-13 09:50:11'),
(17, '017_create_supplier_quotations_table', 2, '2025-08-13 09:59:22'),
(18, '002_fix_admin_department_enum', 3, '2025-08-14 07:52:25'),
(19, '003_normalize_admin_departments', 3, '2025-08-14 07:52:25');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_type` enum('superadmin','salesadmin','accounts','operation','production') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `module` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_type`, `user_id`, `title`, `message`, `type`, `module`, `reference_id`, `is_read`, `created_at`, `read_at`) VALUES
(1, 'superadmin', NULL, 'New Admin Registration', 'Admin \'accounts\' registered for accounts', 'info', 'admin', 2, 1, '2025-09-05 12:29:46', '2025-09-05 12:46:44'),
(2, 'superadmin', 1, 'Admin Approved', 'accounts (accounts@purewood.in) has been approved for accounts department.', 'info', 'admin', NULL, 1, '2025-09-05 12:29:53', '2025-09-05 12:46:44');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `jci_number` varchar(50) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `sell_order_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `jci_number`, `po_number`, `sell_order_number`, `created_at`, `updated_at`) VALUES
(2, 'JCI-2025-0001', 'test 001', 'SALE-2025-0001', '2025-07-11 11:46:44', '2025-07-11 11:46:44'),
(3, 'JCI-2025-0005', 'TEST-PD001', 'SALE-2025-0005', '2025-07-31 06:50:35', '2025-07-31 06:50:35'),
(4, 'JCI-2025-0006', '3764', 'SALE-2025-0006', '2025-07-31 11:16:55', '2025-08-16 09:36:08');

-- --------------------------------------------------------

--
-- Table structure for table `payment_details`
--

CREATE TABLE `payment_details` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `jc_number` varchar(50) DEFAULT NULL,
  `payment_type` varchar(20) DEFAULT NULL,
  `cheque_number` varchar(50) DEFAULT NULL,
  `pd_acc_number` varchar(50) DEFAULT NULL,
  `ptm_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gst_percent` decimal(6,2) DEFAULT 0.00,
  `gst_amount` decimal(15,2) DEFAULT 0.00,
  `total_with_gst` decimal(15,2) DEFAULT 0.00,
  `payment_invoice_date` date DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_category` varchar(20) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_details`
--

INSERT INTO `payment_details` (`id`, `payment_id`, `jc_number`, `payment_type`, `cheque_number`, `pd_acc_number`, `ptm_amount`, `gst_percent`, `gst_amount`, `total_with_gst`, `payment_invoice_date`, `payment_date`, `payment_category`, `amount`, `created_at`, `updated_at`) VALUES
(8, 2, 'JCI-2025-0001', 'Cheque', '100', '1234', 1000.00, 0.00, 0.00, 0.00, '0000-00-00', '2025-07-01', 'Job Card', 1000.00, '2025-07-11 11:46:44', '2025-07-11 11:46:44'),
(9, 2, '', 'RTGS', '101', '1234', 1.50, 0.00, 0.00, 0.00, '2025-07-18', '2025-07-17', 'Supplier', 1.50, '2025-07-11 11:46:44', '2025-07-11 11:46:44'),
(10, 2, '', 'RTGS', '100', '124', 4.00, 0.00, 0.00, 0.00, '2025-07-15', '2025-07-08', 'Supplier', 4.00, '2025-07-11 11:46:44', '2025-07-11 11:46:44'),
(11, 3, '', 'Cheque', '000356', '7777', 320.00, 0.00, 0.00, 0.00, '2025-08-01', '2025-07-31', 'Supplier', 320.00, '2025-07-31 06:50:35', '2025-07-31 06:50:35'),
(12, 4, '', 'Cheque', '000405', '777705091719', 5464.80, 0.00, 0.00, 0.00, '2025-07-26', '2025-08-25', 'Supplier', 5464.80, '2025-07-31 11:16:55', '2025-07-31 11:16:55'),
(13, 4, '', 'Cheque', '1411/2025-26', '777705091719', 24883.00, 0.00, 0.00, 0.00, '2025-07-27', '2025-09-10', 'Supplier', 24883.00, '2025-08-12 11:15:06', '2025-08-12 11:15:06'),
(14, 4, 'JCI-2025-0006', 'RTGS', 'ONLINE', '777705091719', 17500.00, 0.00, 0.00, 17500.00, '0000-00-00', '2025-08-18', 'Job Card', 17500.00, '2025-08-16 09:35:48', '2025-08-16 09:35:48');

-- --------------------------------------------------------

--
-- Table structure for table `pi`
--

CREATE TABLE `pi` (
  `pi_id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `pi_number` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Generated',
  `inspection` text DEFAULT NULL,
  `date_of_pi_raised` date DEFAULT NULL,
  `sample_approval_date` date DEFAULT NULL,
  `detailed_seller_address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `po`
--

CREATE TABLE `po` (
  `id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `po_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','sent','approved','delivered','cancelled') DEFAULT 'draft',
  `terms_conditions` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_by` int(11) DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `po_items`
--

CREATE TABLE `po_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `product_code` varchar(100) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `product_image` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `po_items`
--

INSERT INTO `po_items` (`id`, `po_id`, `product_code`, `product_name`, `quantity`, `unit`, `price`, `total_amount`, `product_image`, `created_at`, `updated_at`) VALUES
(1, 1, 'pro01', 'test pro', 10.00, '', 100.00, 1000.00, '6870b517caa75.png', '2025-07-11 06:54:15', '2025-07-11 06:54:15'),
(2, 2, 'AHFL-CT180', 'COFFEE TABLE 120X65X43CM', 20.00, '', 4000.00, 80000.00, '6870eed0323c9.jpeg', '2025-07-11 11:00:32', '2025-07-11 11:00:32'),
(3, 3, 'NA', 'Burano Chair Mango Wood Frame Raw', 75.00, '', 2800.00, 210000.00, '', '2025-07-16 11:31:58', '2025-07-16 11:31:58'),
(4, 4, 'CBRN001', 'C Side Table Raw Section', 47.00, '', 2500.00, 117500.00, '', '2025-07-21 10:15:16', '2025-07-21 10:15:16'),
(5, 5, 'TEST CODE-001', 'TEST PERFECT STUDY TABLE', 10.00, '', 1980.00, 19800.00, '', '2025-07-30 10:40:45', '2025-07-30 10:40:45'),
(7, 6, 'ADC-410', '\" 124311 AHDU DT220MB Wooden  Base\"', 25.00, '', 3750.00, 93750.00, '', '2025-07-31 09:16:59', '2025-07-31 09:16:59'),
(8, 7, 'BS-MOD02N', 'Moda Side Table', 20.00, '', 2940.00, 58800.00, '', '2025-07-31 11:37:10', '2025-07-31 11:37:10'),
(9, 8, 'dining table', 'top and leg', 1.00, '', 3500.00, 3500.00, '', '2025-08-07 11:14:10', '2025-08-07 11:14:10'),
(10, 9, 'BS-FLU12', '\"BS-FLU-12-Fluting Round Table\"', 18.00, '', 2800.00, 50400.00, '', '2025-08-12 05:05:18', '2025-08-12 05:05:18'),
(12, 10, 'AHPZ-CT140PW-T', 'puzzle coffee table top frame', 20.00, '', 2400.00, 48000.00, '', '2025-08-12 05:42:54', '2025-08-12 05:42:54'),
(13, 11, 'BSLA-004', 'BSLA-004 - Basilia End Tabl Raw Section 6\" leg (1SET 3LEGS)', 50.00, '', 2600.00, 130000.00, '', '2025-08-12 06:19:12', '2025-08-12 06:19:12'),
(15, 12, 'BSLA004-173535A', '\"Basilia Coffee Table Wooden Raw Section\"', 50.00, '', 3100.00, 155000.00, '', '2025-08-12 12:33:23', '2025-08-12 12:33:23'),
(17, 13, '51177', 'Wdn Acacia Dining Table', 40.00, '', 7750.00, 310000.00, '', '2025-08-18 08:44:01', '2025-08-18 08:44:01'),
(18, 13, '51178', 'Wdn Acacia Dining Table', 15.00, '', 8850.00, 132750.00, '', '2025-08-18 08:44:01', '2025-08-18 08:44:01'),
(20, 14, 'DF', 'DOOR FRAME', 1.00, '', 28611.00, 28611.00, '', '2025-08-23 09:17:56', '2025-08-23 09:17:56'),
(22, 15, 'LH-HEN27', 'KEN012-BRN Kenzo Dining Table Small 60\" - Brown', 10.00, '', 5600.00, 56000.00, '', '2025-09-01 10:04:15', '2025-09-01 10:04:15'),
(23, 15, 'LH-TOM02', 'KEN012-NTR-KENZO DINING TABLE SMALL 60\" - NATURA', 10.00, '', 5260.00, 52600.00, '', '2025-09-01 10:04:15', '2025-09-01 10:04:15'),
(24, 15, 'LH-TOM26', 'KEN012-BLK Kenzo Dining Table Small 60\" Black', 10.00, '', 5260.00, 52600.00, '', '2025-09-01 10:04:15', '2025-09-01 10:04:15'),
(25, 15, 'LH-HEN36', 'KENZO DINING TABLE 71\"BROWN With New Legs', 10.00, '', 6600.00, 66000.00, '', '2025-09-01 10:04:15', '2025-09-01 10:04:15'),
(26, 15, 'LH-MID02', 'KEN011-BLK Kenzo Dining Table 71\" - Black', 10.00, '', 6500.00, 65000.00, '', '2025-09-01 10:04:15', '2025-09-01 10:04:15'),
(27, 16, 'LH-HEN27', 'KEN012-BRN Kenzo Dining Table Small 60\" - Brown', 15.00, '', 5600.00, 84000.00, '', '2025-09-01 11:36:02', '2025-09-01 11:36:02'),
(39, 17, 'LH-HEN27', 'KEN012-BRN Kenzo Dining Table Small 60\'\'-Brown', 10.00, '', 5600.00, 56000.00, '', '2025-09-01 12:19:57', '2025-09-01 12:19:57'),
(40, 17, 'LH-HEN36', 'KENZO DINING TABLE 71\'\'BROWN With New Legs', 10.00, '', 6600.00, 66000.00, '', '2025-09-01 12:19:57', '2025-09-01 12:19:57'),
(41, 17, 'ADC-433', 'LH-ANN-04Wooden Base', 25.00, '', 5500.00, 137500.00, '', '2025-09-01 12:19:57', '2025-09-01 12:19:57'),
(42, 17, 'LH-TOM02', 'KEN012-NTR-KENZO DINING TABLE SMALL 60\'\'-NATURAL', 10.00, '', 5260.00, 52600.00, '', '2025-09-01 12:19:57', '2025-09-01 12:19:57'),
(101, 18, '1', 'Kitchen-D2 (38 MM THICK)', 1.00, '', 57600.00, 57600.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(102, 18, '2', 'Bedroom-1-D3 (38 MM THICK)', 1.00, '', 57600.00, 57600.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(103, 18, '3', 'Bathroom-1-D4 (38 MM THICK)', 1.00, '', 57600.00, 57600.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(104, 18, '4', 'Bedroom-2-D5 (38 MM THICK)', 1.00, '', 57600.00, 57600.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(105, 18, '5', 'Bathroom-2-D6 (38 MM THICK)', 1.00, '', 77760.00, 77760.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(106, 18, '6', 'Bedroom-2-D7 (38 MM THICK)', 1.00, '', 77760.00, 77760.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(107, 18, '7', 'Bathroom-2-D8 (38 MM THICK)', 1.00, '', 57600.00, 57600.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(108, 18, '8', 'Common Bathroom-D9 (38 MM THICK)', 1.00, '', 33750.00, 33750.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(109, 18, '9', 'Terrace Door-D10 (38 MM THICK)', 1.00, '', 47880.00, 47880.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(110, 18, '10', 'Frame-D1', 1.00, '', 46800.00, 46800.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(111, 18, '11', 'Frame-D2', 1.00, '', 32400.00, 32400.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(112, 18, '12', 'Frame-D3', 1.00, '', 32400.00, 32400.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(113, 18, '13', 'Frame-D4', 1.00, '', 29520.00, 29520.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(114, 18, '14', 'Frame-D5', 1.00, '', 31320.00, 31320.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(115, 18, '15', 'Frame-D6', 1.00, '', 41472.00, 41472.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(116, 18, '16', 'Frame-D7', 1.00, '', 41472.00, 41472.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(117, 18, '17', 'Frame-D8', 1.00, '', 31320.00, 31320.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(118, 18, '18', 'Frame-D9', 1.00, '', 23157.00, 23157.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(119, 18, '19', 'Frame-D10', 1.00, '', 28611.00, 28611.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33'),
(120, 18, '20', 'Main Door-D1 (50 MM THICK) (Double Leaf)', 1.00, '', 180000.00, 180000.00, '', '2025-09-03 11:07:33', '2025-09-03 11:07:33');

-- --------------------------------------------------------

--
-- Table structure for table `po_main`
--

CREATE TABLE `po_main` (
  `id` int(11) NOT NULL,
  `po_number` varchar(100) NOT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `prepared_by` varchar(255) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `sell_order_id` int(11) DEFAULT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `is_locked` tinyint(1) NOT NULL DEFAULT 0,
  `locked_by` int(11) DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sell_order_number` varchar(50) DEFAULT NULL,
  `jci_number` varchar(50) DEFAULT NULL,
  `jci_assigned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `po_main`
--

INSERT INTO `po_main` (`id`, `po_number`, `client_name`, `prepared_by`, `order_date`, `delivery_date`, `sell_order_id`, `status`, `is_locked`, `locked_by`, `locked_at`, `created_at`, `updated_at`, `sell_order_number`, `jci_number`, `jci_assigned`) VALUES
(1, 'test 001', 'test po', 'test', '2025-07-08', '2025-07-28', 1, 'Locked', 1, NULL, NULL, '2025-07-11 06:54:15', '2025-07-11 06:55:30', 'SALE-2025-0001', '', 1),
(2, '546', 'APL', 'JS CHOUHAN', '2025-07-11', '2025-07-31', 2, 'Locked', 1, NULL, NULL, '2025-07-11 11:00:32', '2025-07-11 11:03:42', 'SALE-2025-0002', '', 1),
(3, '2128', '7 Seas II', 'Js chouhan', '2025-07-15', '2025-08-05', 3, 'Approved', 0, NULL, NULL, '2025-07-16 11:31:58', '2025-07-17 10:03:55', 'SALE-2025-0003', '', 1),
(4, '2145', '7 SEAS II', 'JS CHOUHAN', '2025-07-21', '2025-08-10', 4, 'Approved', 0, NULL, NULL, '2025-07-21 10:15:16', '2025-07-21 10:24:09', 'SALE-2025-0004', '', 1),
(5, 'TEST-PD001', 'TEST PD', 'JS', '2025-07-30', '2025-08-19', 5, 'Approved', 0, NULL, NULL, '2025-07-30 10:40:45', '2025-07-30 10:46:08', 'SALE-2025-0005', '', 1),
(6, '3764', 'APL', 'JS CHOUHAN', '2025-07-08', '2025-08-22', 6, 'Locked', 1, NULL, NULL, '2025-07-31 09:16:52', '2025-07-31 09:18:37', 'SALE-2025-0006', '', 1),
(7, '4244', 'APL', 'JS CHOUHAN', '2025-07-31', '2025-08-25', 7, 'Approved', 0, NULL, NULL, '2025-07-31 11:37:10', '2025-07-31 11:40:12', 'SALE-2025-0007', '', 1),
(8, 'verbal order by hukum singh ji 7 seas qc', '7 seas', 'jitendra singh', '2025-08-07', '2025-08-07', 8, 'Approved', 0, NULL, NULL, '2025-08-07 11:14:10', '2025-08-07 11:16:26', 'SALE-2025-0008', '', 1),
(9, '4134', 'APL', 'JS CHOUHAN', '2025-07-10', '2025-08-24', 9, 'Locked', 1, NULL, NULL, '2025-08-12 05:05:18', '2025-08-12 05:07:47', 'SALE-2025-0009', '', 1),
(10, 'VERBAL ORDER BY CHIRAG JI', 'APL', 'JS CHOUHAN', '2025-08-12', '2025-08-18', 10, 'Locked', 1, NULL, NULL, '2025-08-12 05:42:49', '2025-08-12 05:44:24', 'SALE-2025-0010', '', 1),
(11, '2190', '7 SEAS', 'JS CHOUHAN', '2025-08-12', '2025-08-24', 11, 'Locked', 1, NULL, NULL, '2025-08-12 06:19:12', '2025-08-18 08:44:39', 'SALE-2025-0011', '', 1),
(12, '2190', '7 SEAS', 'JS CHOUHAN', '2025-08-12', '2025-08-25', 12, 'Locked', 1, NULL, NULL, '2025-08-12 12:33:18', '2025-08-12 12:34:35', 'SALE-2025-0012', '', 1),
(13, 'PO/25-26/CP00122', 'BHIKSU', 'JS CHOUHAN', '2025-08-16', '2025-09-15', 13, 'Locked', 1, NULL, NULL, '2025-08-18 07:19:47', '2025-08-18 09:04:14', 'SALE-2025-0013', '', 1),
(14, 'VERBAL ORDER BY SUDARSHAN JI', 'ANSHI COYMBTOR', 'JS CHOUHAN', '2025-08-15', '2025-08-30', NULL, 'Pending', 0, NULL, NULL, '2025-08-23 09:17:52', '2025-08-23 09:17:56', NULL, NULL, 0),
(15, 'PO04060', 'APL', 'Mahaveer Singh', '2025-07-02', '2025-08-04', 14, 'Approved', 0, NULL, NULL, '2025-09-01 09:51:41', '2025-09-01 10:05:16', 'SALE-2025-0014', '', 0),
(16, 'PO04073', 'APL', 'Mahaveer Singh', '2025-07-02', '2025-08-16', NULL, 'Pending', 0, NULL, NULL, '2025-09-01 11:36:02', '2025-09-01 11:36:02', NULL, NULL, 0),
(17, '4312', 'APL', 'Mahaveer Singh', '2025-08-08', '2025-09-02', 15, 'Approved', 0, NULL, NULL, '2025-09-01 12:00:05', '2025-09-01 12:20:46', 'SALE-2025-0015', '', 0),
(18, '887', 'ANSHI DOORS', 'Mahaveer Singh', '2025-08-04', '2025-09-26', 16, 'Approved', 0, NULL, NULL, '2025-09-03 09:18:22', '2025-09-03 11:07:33', 'SALE-2025-0016', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE `purchase` (
  `id` int(11) NOT NULL,
  `purchase_number` varchar(50) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `supplier_email` varchar(255) DEFAULT NULL,
  `supplier_phone` varchar(20) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','approved','received','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `id` int(11) NOT NULL,
  `purchase_main_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `product_type` varchar(100) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `job_card_number` varchar(50) NOT NULL,
  `assigned_quantity` decimal(10,3) NOT NULL DEFAULT 0.000,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `date` date DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `invoice_image` varchar(255) DEFAULT NULL,
  `builty_number` varchar(100) DEFAULT NULL,
  `builty_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_main_id`, `supplier_name`, `product_type`, `product_name`, `job_card_number`, `assigned_quantity`, `price`, `total`, `created_at`, `updated_at`, `date`, `invoice_number`, `amount`, `invoice_image`, `builty_number`, `builty_image`) VALUES
(34, 2, 'ARPIT SALES', 'Glow', 'GLUE', 'JOB-2025-0005-1', 2.000, 160.00, 320.00, '2025-07-30 11:51:40', '2025-07-31 06:39:59', '2025-08-01', 'TEST', 260.00, 'invoice_688a085943ceb_WhatsApp Image 2025-07-28 at 6.36.34 PM.jpeg', 'NA', 'builty_688a085943ced_WhatsApp Image 2025-07-28 at 6.36.34 PM.jpeg'),
(35, 2, 'MARWAR SUPPLIERS', 'Hardware', '5*80 GRANDER PAPER', 'JOB-2025-0005-1', 20.000, 17.50, 350.00, '2025-07-30 11:51:40', '2025-07-31 06:40:49', NULL, NULL, NULL, NULL, NULL, NULL),
(36, 2, 'MARWAR SUPPLIERS', 'Hardware', 'BOND', 'JOB-2025-0005-1', 10.000, 13.00, 130.00, '2025-07-30 11:51:40', '2025-07-31 06:41:16', NULL, NULL, NULL, NULL, NULL, NULL),
(37, 2, 'MAHA AMBE', 'Plynydf', 'Plynydf', 'JOB-2025-0005-1', 10.000, 160.00, 1600.00, '2025-07-30 11:51:40', '2025-07-31 06:41:55', NULL, NULL, NULL, NULL, NULL, NULL),
(38, 2, 'MK TIMBER', 'Wood', 'Mango', 'JOB-2025-0005-1', 8.330, 590.00, 4914.00, '2025-07-30 11:51:40', '2025-07-31 06:44:30', NULL, NULL, NULL, NULL, NULL, NULL),
(46, 3, 'Ashapurna Enterprises', 'Wood', 'Mango', 'JOB-2025-0006-1', 8.280, 660.00, 5464.80, '2025-07-31 10:55:05', '2025-07-31 11:15:31', '2025-07-26', 'AE/2526/196', 80388.00, 'invoice_688b5053664c5_Ashapurna Enterprises 196.jpeg', '732', 'builty_688b5053664c7_WhatsApp Image 2025-07-31 at 4.43.25 PM.jpeg'),
(67, 1, 'test suppi2', 'Glow', 'glue', 'JOB-2025-0001-1', 0.300, 100.00, 30.00, '2025-08-11 07:03:11', '2025-08-11 07:03:11', NULL, NULL, NULL, NULL, NULL, NULL),
(68, 1, 'test suppi2', 'Hardware', 'hardware', 'JOB-2025-0001-1', 1.000, 100.00, 100.00, '2025-08-11 07:03:11', '2025-08-11 07:03:11', NULL, NULL, NULL, NULL, NULL, NULL),
(69, 1, 'test suppi1', 'Plynydf', 'Plynydf', 'JOB-2025-0001-1', 1.000, 10.00, 10.00, '2025-08-11 07:03:11', '2025-08-11 07:03:11', NULL, NULL, NULL, NULL, NULL, NULL),
(70, 1, 'test suppi1', 'Wood', 'Mango', 'JOB-2025-0001-1', 3.000, 20.00, 60.00, '2025-08-11 07:03:11', '2025-08-11 07:03:11', NULL, NULL, NULL, NULL, NULL, NULL),
(71, 3, 'JAI GURUDEV ENTERPRISES', 'Wood', 'Mango', 'JOB-2025-0006-1', 31.950, 660.00, 21087.00, '2025-08-12 10:35:36', '2025-08-12 11:00:59', '2025-07-27', '1411/2025-26', 37688.00, 'invoice_689b1eebe090f_WhatsApp Image 2025-08-12 at 4.28.08 PM.jpeg', '1411', 'builty_689b1eebe0912_WhatsApp Image 2025-08-12 at 4.28.08 PM (1).jpeg'),
(72, 3, 'Maruti Timber Art And Carft', 'Wood', 'Mango', 'JOB-2025-0006-1', 7.480, 690.00, 5161.20, '2025-08-15 07:08:06', '2025-08-15 08:02:22', '2025-08-02', '25-26/89', 193607.00, 'invoice_689ee98eb8907_WhatsApp_Image_2025-08-15_at_1.31.02_PM.jpeg', '1511', 'builty_689ee98eb8948_WhatsApp_Image_2025-08-15_at_1.31.03_PM.jpeg'),
(73, 3, 'Maruti Art And Craft', 'Wood', 'Mango', 'JOB-2025-0006-1', 6.380, 560.00, 3572.80, '2025-08-15 08:51:41', '2025-08-15 09:08:13', '2025-07-10', '25-26/79', 38815.00, 'invoice_689ef8fd7adf8_Capture.PNG', '79', 'builty_689ef8fd7ae22_Capture.PNG'),
(75, 3, 'MARWAR HARDWARE', 'Hardware', 'HARDWARE', 'JOB-2025-0006-1', 0.300, 3750.00, 1125.00, '2025-08-16 10:36:48', '2025-08-16 10:36:48', NULL, NULL, NULL, NULL, NULL, NULL),
(81, 4, 'Maruti Art & Craft', 'Wood', 'Mango', 'JOB-2025-0007-1', 54.668, 560.00, 30614.08, '2025-09-02 09:47:06', '2025-09-02 09:47:06', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_main`
--

CREATE TABLE `purchase_main` (
  `id` int(11) NOT NULL,
  `po_number` varchar(50) NOT NULL,
  `jci_number` varchar(50) NOT NULL,
  `sell_order_number` varchar(50) NOT NULL,
  `bom_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `approval_status` varchar(50) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_main`
--

INSERT INTO `purchase_main` (`id`, `po_number`, `jci_number`, `sell_order_number`, `bom_number`, `created_at`, `updated_at`, `approval_status`) VALUES
(1, 'test 001', 'JCI-2025-0001', 'SALE-2025-0001', 'BOM-2025-0001', '2025-07-11 06:57:23', '2025-08-11 07:03:11', 'pending'),
(2, 'TEST-PD001', 'JCI-2025-0005', '\n                                    SALE-2025-00', 'BOM-2025-0006', '2025-07-30 10:55:46', '2025-07-30 11:51:40', 'pending'),
(3, '3764', 'JCI-2025-0006', 'SALE-2025-0006', 'BOM-2025-0007', '2025-07-31 09:43:54', '2025-09-05 12:30:23', 'sent_for_approval'),
(4, '4244', 'JCI-2025-0007', 'SALE-2025-0007', 'BOM-2025-0008', '2025-09-01 10:52:12', '2025-09-02 09:47:06', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `quotation_date` date NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `delivery_term` varchar(255) DEFAULT NULL,
  `terms_of_delivery` varchar(255) DEFAULT NULL,
  `quotation_image` varchar(255) DEFAULT NULL,
  `approve` tinyint(1) NOT NULL DEFAULT 0,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_by` int(11) DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `lead_id`, `quotation_date`, `quotation_number`, `customer_name`, `customer_email`, `customer_phone`, `delivery_term`, `terms_of_delivery`, `quotation_image`, `approve`, `locked`, `created_at`, `updated_at`, `is_locked`, `locked_by`, `locked_at`, `supplier_id`) VALUES
(1, 1, '2025-08-02', 'QUOTE-2025-00001', 'Gret Holland', 'Test@gmail.com', '0000000000', '30/70', '85 Days', NULL, 0, 0, '2025-08-02 12:01:10', '2025-08-02 12:01:10', 0, NULL, NULL, NULL),
(2, 3, '2025-08-06', 'QUOTE-2025-00002', 'Shawn USA', 'Test@gmail.com', '0000000000', '30/70', '85 Days', NULL, 0, 0, '2025-08-06 06:19:54', '2025-08-06 06:19:54', 0, NULL, NULL, NULL),
(3, 4, '2025-08-06', 'QUOTE-2025-00003', 'Shawn USA', 'Test@gmail.com', '0000000000', '30/70', '85 Days', NULL, 0, 0, '2025-08-06 06:25:29', '2025-08-06 06:25:29', 0, NULL, NULL, NULL),
(4, 5, '2025-08-06', 'QUOTE-2025-00004', 'Plein 5', 'H.Vanverseveld@hotmail.com', '0031 653428855', '30/70', '85 Days', NULL, 0, 0, '2025-08-06 06:50:14', '2025-08-06 06:50:14', 0, NULL, NULL, NULL),
(5, 6, '2025-08-09', 'QUOTE-2025-00005', 'HVL Design', 'H.Ladiges@h-v-l.de', 'NA', '30/70', '85 Days', NULL, 0, 0, '2025-08-12 06:04:26', '2025-08-12 06:04:26', 0, NULL, NULL, NULL),
(6, 7, '2025-08-12', 'QUOTE-2025-00006', 'Johannes Germany', 'Johannes@testmail.com', 'NA', '30/70', '85 Days', NULL, 0, 0, '2025-08-12 06:42:31', '2025-08-12 06:42:31', 0, NULL, NULL, NULL),
(7, 8, '2025-08-12', 'QUOTE-2025-00007', 'Purewood USA', 'modensifurniture@gmail.com', 'NA', '30/70', '85 Days', NULL, 0, 0, '2025-08-12 11:58:09', '2025-08-12 11:58:09', 0, NULL, NULL, NULL),
(8, 10, '2025-08-15', 'QUOTE-2025-00008', 'Avi Homes USA ', 'Avi@testmail.com', 'NA', '30/70', '85 Days', NULL, 0, 0, '2025-08-15 11:28:29', '2025-08-15 11:28:29', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_products`
--

CREATE TABLE `quotation_products` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `assembly` varchar(255) DEFAULT NULL,
  `item_h` decimal(10,2) DEFAULT NULL,
  `item_w` decimal(10,2) DEFAULT NULL,
  `item_d` decimal(10,2) DEFAULT NULL,
  `box_h` decimal(10,2) DEFAULT NULL,
  `box_w` decimal(10,2) DEFAULT NULL,
  `box_d` decimal(10,2) DEFAULT NULL,
  `cbm` decimal(10,3) DEFAULT NULL,
  `wood_type` varchar(255) DEFAULT NULL,
  `no_of_packet` int(11) DEFAULT NULL,
  `iron_gauge` varchar(100) DEFAULT NULL,
  `mdf_finish` varchar(255) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price_usd` decimal(10,2) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `product_image_name` varchar(255) DEFAULT NULL,
  `total_price_usd` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_products`
--

INSERT INTO `quotation_products` (`id`, `quotation_id`, `item_name`, `item_code`, `description`, `assembly`, `item_h`, `item_w`, `item_d`, `box_h`, `box_w`, `box_d`, `cbm`, `wood_type`, `no_of_packet`, `iron_gauge`, `mdf_finish`, `quantity`, `price_usd`, `comments`, `created_at`, `updated_at`, `product_image_name`, `total_price_usd`) VALUES
(1, 1, 'Senegal Century Leather Sofa', 'SenegalSofa-1 Purewood', NULL, 'Fix', 96.00, 241.00, 68.00, 104.00, 249.00, 76.00, 1.968, 'Wood,Leather', 1, NULL, NULL, 10.00, 544.00, '', '2025-08-02 12:01:10', '2025-08-02 12:01:10', 'prod_1_1_1754136070.png', 5440.00),
(2, 1, 'Olifants Jordan Leather Sofa', 'OlifantsJordansofa-2 Purewood', NULL, 'KD', 85.00, 212.00, 71.00, 93.00, 220.00, 79.00, 1.616, 'Metal, Leather', 1, NULL, NULL, 10.00, 459.00, '', '2025-08-02 12:01:10', '2025-08-02 12:01:10', 'prod_1_2_1754136070.png', 4590.00),
(3, 1, 'Nene Leather Chesterfield Sofa', 'NeneChesterfieldsofa-3 Purewood', NULL, 'Fix', 93.00, 226.00, 76.00, 101.00, 234.00, 84.00, 1.985, 'Wood,Leather', 1, NULL, NULL, 10.00, 494.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_3_1754136070.png', 4940.00),
(4, 1, 'Niger Arm Chair', 'NigerarmChair-4 Purewood', NULL, 'Fix', 94.00, 65.00, 94.00, 100.00, 71.00, 100.00, 0.710, 'Metal,Leather', 1, NULL, NULL, 10.00, 140.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_4_1754136071.png', 1400.00),
(5, 1, 'Ural Joy Leather Chair', 'UralJoyChair-5 Purewood', NULL, 'Fix', 57.00, 58.00, 48.00, 63.00, 64.00, 54.00, 0.218, 'Wood,Leather', 1, NULL, NULL, 10.00, 110.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_5_1754136071.png', 1100.00),
(6, 1, 'Old World Dining Table Top', 'Oldworlddiningtabletop-6 Purewood', NULL, 'KD', 3.50, 208.00, 99.00, 12.00, 218.00, 109.00, 0.285, 'wood', 1, NULL, NULL, 10.00, 423.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_6_1754136071.png', 4230.00),
(7, 1, 'Old World Dining Table base', 'Oldworlddiningtablebase-7 Purewood', NULL, 'KD', 74.00, 185.00, 35.00, 76.00, 193.00, 40.00, 0.587, 'Iron', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_7_1754136071.png', 0.00),
(8, 1, 'Falcon Dining Table Top', 'FalconDiningtabletop-8 Purewood', NULL, 'KD', 3.50, 213.00, 101.00, 12.00, 221.00, 109.00, 0.289, 'wood', 1, NULL, NULL, 10.00, 450.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_8_1754136071.png', 4500.00),
(9, 1, 'Falcon Dining Table Base', 'FalconDiningtablebase-9 Purewood', NULL, 'KD', 74.00, 150.00, 90.00, 76.00, 160.00, 99.00, 1.204, 'Iron', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_9_1754136071.png', 0.00),
(10, 1, 'Turnstone Dining Table Top', 'Truestonediningtabletop-10 Purewood', NULL, 'KD', 2.50, 213.00, 101.00, 12.00, 221.00, 109.00, 0.289, 'wood', 1, NULL, NULL, 10.00, 402.50, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_10_1754136071.png', 4025.00),
(11, 1, 'Turnstone Dining Table base', 'Truestonediningtablebase-11 Purewood', NULL, 'KD', 74.00, 150.00, 80.00, 76.00, 160.00, 95.00, 1.155, 'Iron', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_11_1754136071.png', 0.00),
(12, 1, 'Hobby Dining Table top', 'HobbyDiningTabletop-12 Purewood', NULL, 'KD', 3.50, 101.00, 101.00, 12.00, 109.00, 109.00, 0.143, 'wood', 1, NULL, NULL, 10.00, 346.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_12_1754136071.png', 3460.00),
(13, 1, 'Hobby Dining Table base', 'HobbyDiningTablebase-13 Purewood', NULL, 'KD', 74.00, 50.00, 50.00, 76.00, 55.00, 55.00, 0.230, 'Iron', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_13_1754136071.png', 0.00),
(14, 1, 'Kookaburra Dining Table', 'KookaburraDiningTable-14 Purewood', NULL, 'KD', 76.00, 243.00, 204.00, 26.00, 251.00, 112.00, 0.731, 'Iron , Wood', 1, NULL, NULL, 10.00, 365.00, '', '2025-08-02 12:01:11', '2025-08-02 12:01:11', 'prod_1_14_1754136071.png', 3650.00),
(15, 2, 'Wooden Rattan Arm Chair-1', 'Ratanarmchair1 Purewood', NULL, 'fix', 84.00, 52.00, 52.00, 90.00, 58.00, 58.00, 0.303, 'Wood, Rattan, Upholstered', 1, NULL, NULL, 50.00, 65.50, '', '2025-08-06 06:19:54', '2025-08-06 06:19:54', 'prod_2_1_1754461194.png', 3275.00),
(16, 2, 'Walnut Rattan Chair', 'WalnutRattanchair Purewood', NULL, 'fix', 77.50, 57.00, 53.50, 83.50, 63.00, 59.50, 0.313, 'Wood, Rattan, leather', 1, NULL, NULL, 50.00, 64.50, '', '2025-08-06 06:19:54', '2025-08-06 06:19:54', 'prod_2_2_1754461194.png', 3225.00),
(17, 2, 'Wooden Rattan Arm Chair-2', 'WoodenRattanArm Chair2 Purewood', NULL, 'fix', 81.00, 61.00, 67.00, 87.00, 67.00, 73.00, 0.426, 'Wood, Rattan', 1, NULL, NULL, 50.00, 66.00, '', '2025-08-06 06:19:54', '2025-08-06 06:19:54', 'prod_2_3_1754461194.png', 3300.00),
(18, 2, 'Wood Dining Chair 1', 'Wooddiningchair1 Purewood', NULL, 'fix', 76.00, 48.00, 53.00, 82.00, 54.00, 59.00, 0.261, 'Wood', 1, NULL, NULL, 50.00, 58.50, '', '2025-08-06 06:19:54', '2025-08-06 06:19:54', 'prod_2_4_1754461194.png', 2925.00),
(19, 2, 'Wood Dining Chair 2', 'Wooddiningchair2 Purewood', NULL, 'fix', 79.00, 53.00, 41.00, 85.00, 59.00, 47.00, 0.236, 'Wood, Rattan', 1, NULL, NULL, 50.00, 58.50, '', '2025-08-06 06:19:54', '2025-08-06 06:19:54', 'prod_2_5_1754461194.png', 2925.00),
(20, 3, 'Bald Eagle chair', 'BaldEaglechair Purewood', NULL, 'fix', 99.00, 66.00, 66.00, 105.00, 72.00, 72.00, 0.544, 'Metal,Leather/Leatherette', 1, NULL, NULL, 20.00, 89.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_1_1754461529.png', 1780.00),
(21, 3, 'Snowy Owl chair', 'SnowyOwlchair Purewood', NULL, 'fix', 85.00, 60.00, 60.00, 91.00, 66.00, 66.00, 0.396, 'Wood,Fabric', 1, NULL, NULL, 20.00, 78.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_2_1754461529.png', 1560.00),
(22, 3, 'Bowerbird chair', 'Bowerbirdchair Purewood', NULL, 'fix', 96.50, 48.00, 48.00, 102.50, 54.00, 54.00, 0.299, 'Metal,Fabric', 1, NULL, NULL, 20.00, 93.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_3_1754461529.png', 1860.00),
(23, 3, 'Lyrebird chair', 'Lyrebirdchair Purewood', NULL, 'fix', 96.50, 53.00, 53.00, 102.50, 59.00, 59.00, 0.357, 'Metal,Fabric', 1, NULL, NULL, 20.00, 90.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_4_1754461529.png', 1800.00),
(24, 3, 'Macaw chair', 'Macawchair Purewood', NULL, 'fix', 91.50, 53.00, 53.00, 96.50, 59.00, 59.00, 0.336, 'Wood,Fabric', 1, NULL, NULL, 20.00, 79.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_5_1754461529.png', 1580.00),
(25, 3, 'Puffin chair', 'Puffinchair Purewood', NULL, 'fix', 76.00, 51.00, 46.00, 82.00, 57.00, 52.00, 0.243, 'Metal,Leather/Leatherette', 1, NULL, NULL, 20.00, 75.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_6_1754461529.png', 1500.00),
(26, 3, 'Finch chair', 'Finchchair Purewood', NULL, 'fix', 76.00, 56.00, 61.00, 82.00, 62.00, 67.00, 0.341, 'Wood,Fabric', 1, NULL, NULL, 20.00, 91.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_7_1754461529.png', 1820.00),
(27, 3, 'Wren chair', 'Wrenchair Purewood', NULL, 'fix', 76.00, 56.00, 61.00, 82.00, 62.00, 67.00, 0.341, 'Wood,Fabric', 1, NULL, NULL, 20.00, 91.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_8_1754461529.png', 1820.00),
(28, 3, 'Rosente chair', 'Rosentechair Purewood', NULL, 'fix', 76.00, 43.00, 40.50, 82.00, 49.00, 46.50, 0.187, 'Metal,Leather/Leatherette', 1, NULL, NULL, 20.00, 80.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_9_1754461529.png', 1600.00),
(29, 3, 'Mirraine chair', 'Mirrainechair Purewood', NULL, 'fix', 91.50, 51.00, 61.00, 96.50, 57.00, 67.00, 0.369, 'Metal,Leather/Leatherette', 1, NULL, NULL, 20.00, 80.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_10_1754461529.png', 1600.00),
(30, 3, 'Regallo dining chair', 'Regallo dining chair', NULL, 'fix', 93.00, 53.50, 66.00, 99.00, 59.50, 72.00, 0.424, 'Wood,Fabric', 1, NULL, NULL, 20.00, 91.00, '', '2025-08-06 06:25:29', '2025-08-06 06:25:29', 'prod_3_11_1754461529.png', 1820.00),
(31, 4, 'Senegal Century Leather Sofa', 'SenegalSofa-1 Purewood', NULL, 'Fix', 96.00, 241.00, 68.00, 104.00, 249.00, 76.00, 1.968, 'Wood,Leather', 1, NULL, NULL, 10.00, 579.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_1_1754463014.png', 5790.00),
(32, 4, 'Olifants Jordan Leather Sofa', 'OlifantsJordansofa-2 Purewood', NULL, 'KD', 85.00, 212.00, 71.00, 93.00, 220.00, 79.00, 1.616, 'Metal, Leather', 1, NULL, NULL, 10.00, 489.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_2_1754463014.png', 4890.00),
(33, 4, 'Nene Leather Chesterfield Sofa', 'NeneChesterfieldsofa-3 Purewood', NULL, 'Fix', 93.00, 226.00, 76.00, 101.00, 234.00, 84.00, 1.985, 'Wood,Leather', 1, NULL, NULL, 10.00, 526.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_3_1754463014.png', 5260.00),
(34, 4, 'Niger Arm Chair', 'NigerarmChair-4 Purewood', NULL, 'Fix', 94.00, 65.00, 94.00, 100.00, 71.00, 100.00, 0.710, 'Metal,Leather', 1, NULL, NULL, 10.00, 149.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_4_1754463014.png', 1490.00),
(35, 4, 'Ural Joy Leather Chair', 'UralJoyChair-5 Purewood', NULL, 'Fix', 57.00, 58.00, 48.00, 63.00, 64.00, 54.00, 0.218, 'Wood,Leather', 1, NULL, NULL, 10.00, 117.50, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_5_1754463014.png', 1175.00),
(36, 4, 'Old World Dining Table Top', 'Oldworlddiningtabletop-6 Purewood', NULL, 'KD', 3.50, 208.00, 99.00, 12.00, 218.00, 109.00, 0.285, 'wood', 1, NULL, NULL, 10.00, 450.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_6_1754463014.png', 4500.00),
(37, 4, 'Old World Dining Table base', 'Oldworlddiningtablebase-7 Purewood', NULL, 'KD', 74.00, 185.00, 35.00, 76.00, 193.00, 40.00, 0.587, 'Iron', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_7_1754463014.png', 0.00),
(38, 4, 'Falcon Dining Table Top', 'FalconDiningtabletop-8 Purewood', NULL, 'KD', 3.50, 213.00, 101.00, 12.00, 221.00, 109.00, 0.289, 'wood', 1, NULL, NULL, 10.00, 479.50, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_8_1754463014.png', 4795.00),
(39, 4, 'Falcon Dining Table Base', 'FalconDiningtablebase-9 Purewood', NULL, 'KD', 74.00, 150.00, 90.00, 76.00, 160.00, 99.00, 1.204, 'Iron', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_9_1754463014.png', 0.00),
(40, 4, 'Turnstone Dining Table Top', 'Truestonediningtabletop-10 Purewood', NULL, 'KD', 2.50, 213.00, 101.00, 12.00, 221.00, 109.00, 0.289, 'wood', 1, NULL, NULL, 10.00, 429.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_10_1754463014.png', 4290.00),
(41, 4, 'Turnstone Dining Table base', 'Truestonediningtablebase-11 Purewood', NULL, 'KD', 74.00, 150.00, 80.00, 76.00, 160.00, 95.00, 1.155, 'Iron', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_11_1754463014.png', 0.00),
(42, 4, 'Hobby Dining Table top', 'HobbyDiningTabletop-12 Purewood', NULL, 'KD', 3.50, 101.00, 101.00, 12.00, 109.00, 109.00, 0.143, 'wood', 1, NULL, NULL, 10.00, 369.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_12_1754463014.png', 3690.00),
(43, 4, 'Hobby Dining Table base', 'HobbyDiningTablebase-13 Purewood', NULL, 'KD', 74.00, 50.00, 50.00, 76.00, 55.00, 55.00, 0.230, 'Iron', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_13_1754463014.png', 0.00),
(44, 4, 'Kookaburra Dining Table', 'KookaburraDiningTable-14 Purewood', NULL, 'KD', 76.00, 243.00, 204.00, 26.00, 251.00, 112.00, 0.731, 'Iron , Wood', 1, NULL, NULL, 10.00, 389.00, '', '2025-08-06 06:50:14', '2025-08-06 06:50:14', 'prod_4_14_1754463014.png', 3890.00),
(45, 5, 'Chair-1', 'Chair1 Purewood', NULL, 'Fix', 85.00, 48.00, 42.00, 91.00, 54.00, 48.00, 0.236, 'Wood & Cane/rope', 1, NULL, NULL, 50.00, 108.00, '', '2025-08-12 06:04:26', '2025-08-12 06:04:26', 'prod_5_1_1754978666.png', 5400.00),
(46, 5, 'Chair-2', 'Chair2 Purewood', NULL, 'Fix', 95.00, 55.00, 45.00, 101.00, 61.00, 51.00, 0.314, 'Wood & Cane/rope', 1, NULL, NULL, 50.00, 114.50, '', '2025-08-12 06:04:26', '2025-08-12 06:04:26', 'prod_5_2_1754978666.jpg', 5725.00),
(47, 5, 'chair-3', 'Chair3 Purewood', NULL, 'Fix', 95.00, 55.00, 45.00, 101.00, 61.00, 51.00, 0.314, 'Wood & Cane', 1, NULL, NULL, 50.00, 107.00, '', '2025-08-12 06:04:26', '2025-08-12 06:04:26', 'prod_5_3_1754978666.jpg', 5350.00),
(48, 5, 'Chair-4', 'Chair4 Purewood', NULL, 'Fix', 87.00, 53.00, 56.00, 93.00, 59.00, 62.00, 0.340, 'Wood & Cane/rope', 1, NULL, NULL, 50.00, 112.50, '', '2025-08-12 06:04:26', '2025-08-12 06:04:26', 'prod_5_4_1754978666.jpg', 5625.00),
(49, 6, 'Side Board', 'Sideborad Purewood', NULL, 'Fix', 84.00, 173.00, 52.00, 90.00, 179.00, 58.00, 0.934, 'mango', 1, NULL, NULL, 20.00, 293.50, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_1_1754980951.jpg', 5870.00),
(50, 6, 'Dining Table Top-1', 'DiningtableTop1 Purewood', NULL, 'KD', 2.50, 186.00, 106.00, 12.00, 186.00, 106.00, 0.237, 'mango', 1, NULL, NULL, 20.00, 214.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_2_1754980951.jpg', 4280.00),
(51, 6, 'Dining TableLeg-1', 'DiningtableLeg1 Purewood', NULL, 'KD', 72.00, 50.00, 30.00, 78.00, 56.00, 36.00, 0.157, 'mango', 1, NULL, NULL, 20.00, 0.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_3_1754980951.jpg', 0.00),
(52, 6, 'Chair-1', 'Chair1 Purewood', NULL, 'Fix', 71.00, 53.00, 48.00, 77.00, 59.00, 54.00, 0.245, 'mango', 1, NULL, NULL, 20.00, 101.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_4_1754980951.png', 2020.00),
(53, 6, 'Night Stand', 'Nightstand Purewood', NULL, 'Fix', 45.00, 50.00, 35.00, 51.00, 56.00, 41.00, 0.117, 'mango', 1, NULL, NULL, 20.00, 85.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_5_1754980951.jpg', 1700.00),
(54, 6, 'Dining tableTop-2', 'DiningTableTop2 Purewood', NULL, 'KD', 2.50, 180.00, 95.00, 12.00, 186.00, 101.00, 0.225, 'mango', 1, NULL, NULL, 20.00, 252.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_6_1754980951.jpg', 5040.00),
(55, 6, 'Dining Table Leg-2', 'DiningTableLeg2 Purewood', NULL, 'KD', 73.00, 60.00, 15.00, 79.00, 66.00, 22.00, 0.115, 'mango', 1, NULL, NULL, 20.00, 0.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_7_1754980951.jpg', 0.00),
(56, 6, 'Dining tableTop-3', 'DiningTable3 Purewood', NULL, 'KD', 2.50, 152.00, 152.00, 12.00, 148.00, 23.00, 0.041, 'mango', 1, NULL, NULL, 20.00, 216.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_8_1754980951.jpg', 4320.00),
(57, 6, 'Dining Table Leg-3', 'DiningTableLeg3 Purewood', NULL, 'KD', 68.00, 50.00, 25.00, 74.00, 56.00, 31.00, 0.129, 'mango', 1, NULL, NULL, 20.00, 0.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_9_1754980951.jpg', 0.00),
(58, 6, 'Dining TableTop-4', 'DiningTable4 Purewood', NULL, 'KD', 3.50, 200.00, 100.00, 12.00, 206.00, 206.00, 0.509, 'mango', 1, NULL, NULL, 20.00, 312.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_10_1754980951.jpg', 6240.00),
(59, 6, 'Dinig Table Leg-4', 'DiningTableLeg4 Purewood', NULL, 'KD', 72.00, 80.00, 80.00, 80.00, 88.00, 88.00, 0.620, 'mango', 1, NULL, NULL, 20.00, 0.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_11_1754980951.jpg', 0.00),
(60, 6, 'Chair -2', 'Chair2 Purewood', NULL, 'Fix', 76.00, 53.00, 56.00, 82.00, 59.00, 62.00, 0.300, 'mango', 1, NULL, NULL, 20.00, 77.00, '', '2025-08-12 06:42:31', '2025-08-12 06:42:31', 'prod_6_12_1754980951.jpg', 1540.00),
(61, 7, 'ORCHID DAY BED', 'OrchhidDayBed Purewood', NULL, 'Fix', 71.00, 203.00, 94.00, 81.00, 213.00, 104.00, 1.794, 'Mango Wood', 1, NULL, NULL, 10.00, 280.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_1_1754999889.png', 2800.00),
(62, 7, 'ORCHID KING BED  Headboard', 'OrchidKingBedHeadboard Purewood', NULL, 'KD', 122.00, 194.00, 216.00, 132.00, 226.00, 20.00, 0.597, 'Mango Wood', 1, NULL, NULL, 10.00, 470.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_2_1754999889.png', 4700.00),
(63, 7, 'ORCHID KING BED Siderails & foot Board', 'OrchidKingBedSiderail&Footboard Purewood', NULL, 'KD', 122.00, 194.00, 216.00, 55.00, 200.00, 50.00, 0.550, 'Mango Wood', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_3_1754999889.png', 0.00),
(64, 7, 'ORCHID ROUND SIDE TABLE', 'OrchidRoundSidetable Purewood', NULL, 'Fix', 46.00, 41.00, 41.00, 52.00, 49.00, 49.00, 0.125, 'Mango Wood', 1, NULL, NULL, 15.00, 55.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_4_1754999889.png', 825.00),
(65, 7, 'ORCHID ROUND DINING TABLE Top', 'OrchidRoundDinigTableTop Purewood', NULL, 'KD', 3.00, 119.00, 119.00, 12.00, 129.00, 129.00, 0.200, 'Mango Wood', 1, NULL, NULL, 15.00, 220.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_5_1754999889.png', 3300.00),
(66, 7, 'Orchid Round Dining Table Leg', 'OrchidRoundDinigTableLeg Purewood', NULL, 'KD', 73.00, 50.00, 50.00, 79.00, 58.00, 58.00, 0.266, 'Mango Wood', 1, NULL, NULL, 15.00, 0.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_6_1754999889.png', 0.00),
(67, 7, 'ORCHID OVAL DINING TABLE Top', 'OrchidOvalDiningTableTop Purewood', NULL, 'KD', 3.00, 203.00, 101.00, 12.00, 213.00, 110.00, 0.281, 'Mango Wood', 1, NULL, NULL, 15.00, 314.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_7_1754999889.png', 4710.00),
(68, 7, 'ORCHID OVAL DINING TABLE Leg', 'OrchidOvalDiningTableLeg Purewood', NULL, 'KD', 73.00, 50.00, 20.00, 80.00, 58.00, 28.00, 0.130, 'Mango Wood', 1, NULL, NULL, 15.00, 0.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_8_1754999889.png', 0.00),
(69, 7, 'ORCHID OPEN NIGHT STAND', 'OrchidOpenNightstnad Purewood', NULL, 'Fix', 61.00, 81.00, 51.00, 67.00, 87.00, 57.00, 0.332, 'Mango Wood', 1, NULL, NULL, 20.00, 110.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_9_1754999889.png', 2200.00),
(70, 7, 'ORCHID CONSOLE TABLE Top', 'OrchidConsoleTableTop Purewood', NULL, 'KD', 3.00, 119.00, 41.00, 12.00, 129.00, 49.00, 0.076, 'Mango Wood', 1, NULL, NULL, 10.00, 142.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_10_1754999889.png', 1420.00),
(71, 7, 'ORCHID CONSOLE TABLE Leg', 'OrchidConsoleTableLeg Purewood', NULL, 'KD', 73.00, 50.00, 20.00, 80.00, 58.00, 28.00, 0.130, 'Mango Wood', 1, NULL, NULL, 10.00, 0.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_11_1754999889.png', 0.00),
(72, 7, 'ORCHID COFFEE TABLE', 'OrchidCoffeeTable Purewood', NULL, 'Fix', 41.00, 102.00, 51.00, 49.00, 112.00, 59.00, 0.324, 'Mango Wood', 1, NULL, NULL, 10.00, 94.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_12_1754999889.png', 940.00),
(73, 7, 'ORCHID 6 DRAWER DRESSER', 'Orchid6DrawerDresser Purewood', NULL, 'Fix', 84.00, 152.50, 51.00, 94.00, 162.50, 61.00, 0.932, 'Mango Wood', 1, NULL, NULL, 10.00, 300.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_13_1754999889.png', 3000.00),
(74, 7, 'ORCHID 5 DRAWER DRESSER', 'Orchid5DrawerDresser Purewood', NULL, 'Fix', 114.00, 92.00, 41.00, 120.00, 100.00, 47.00, 0.564, 'Mango Wood', 1, NULL, NULL, 10.00, 240.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_14_1754999889.png', 2400.00),
(75, 7, 'ORCHID 2 DOOR 3 DRAWER SIDEBOARD', 'Orchid2Door3DrawerDresserSideboard Purewood', NULL, 'Fix', 81.00, 183.00, 51.00, 89.00, 193.00, 59.00, 1.013, 'Mango Wood', 1, NULL, NULL, 10.00, 281.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_15_1754999889.png', 2810.00),
(76, 7, 'ORCHID 2 DOOR 1 DRAWER LOW MEDIA CONSOLE', 'Orchid2Door1DrawerMediaConsole Purewood', NULL, 'Fix', 46.00, 183.00, 41.00, 52.00, 193.00, 49.00, 0.492, 'Mango Wood', 1, NULL, NULL, 10.00, 184.00, '', '2025-08-12 11:58:09', '2025-08-12 11:58:09', 'prod_7_16_1754999889.png', 1840.00),
(77, 8, 'Sofa Chair', 'Sofa Purewood', NULL, 'Fix', 79.00, 77.50, 75.00, 85.00, 73.50, 81.00, 0.506, 'Oak Wood & fabric', 1, NULL, NULL, 2.00, 220.00, '', '2025-08-15 11:28:29', '2025-08-15 11:28:29', 'prod_8_1_1755257309.png', 440.00);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_status`
--

CREATE TABLE `quotation_status` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `status_text` varchar(255) NOT NULL,
  `status_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sell_order`
--

CREATE TABLE `sell_order` (
  `id` int(11) NOT NULL,
  `sell_order_number` varchar(50) NOT NULL,
  `po_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `jci_created` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sell_order`
--

INSERT INTO `sell_order` (`id`, `sell_order_number`, `po_id`, `created_at`, `updated_at`, `jci_created`) VALUES
(1, 'SALE-2025-0001', 1, '2025-07-11 06:54:39', '2025-07-11 06:55:30', 1),
(2, 'SALE-2025-0002', 2, '2025-07-11 11:01:13', '2025-07-11 11:03:42', 1),
(3, 'SALE-2025-0003', 3, '2025-07-16 11:32:13', '2025-07-17 10:03:55', 1),
(4, 'SALE-2025-0004', 4, '2025-07-21 10:16:18', '2025-07-21 10:24:09', 1),
(5, 'SALE-2025-0005', 5, '2025-07-30 10:44:19', '2025-07-30 10:46:08', 1),
(6, 'SALE-2025-0006', 6, '2025-07-31 09:17:12', '2025-07-31 09:18:37', 1),
(7, 'SALE-2025-0007', 7, '2025-07-31 11:37:22', '2025-07-31 11:40:12', 1),
(8, 'SALE-2025-0008', 8, '2025-08-07 11:14:25', '2025-08-07 11:16:26', 1),
(9, 'SALE-2025-0009', 9, '2025-08-12 05:05:29', '2025-08-12 05:07:47', 1),
(10, 'SALE-2025-0010', 10, '2025-08-12 05:43:02', '2025-08-12 05:44:24', 1),
(11, 'SALE-2025-0011', 11, '2025-08-12 06:20:03', '2025-08-12 06:22:40', 1),
(12, 'SALE-2025-0012', 12, '2025-08-12 12:33:36', '2025-08-12 12:34:35', 1),
(13, 'SALE-2025-0013', 13, '2025-08-18 08:42:58', '2025-08-18 09:04:14', 1),
(14, 'SALE-2025-0014', 15, '2025-09-01 10:05:16', '2025-09-01 10:05:16', 0),
(15, 'SALE-2025-0015', 17, '2025-09-01 12:20:46', '2025-09-01 12:20:46', 0),
(16, 'SALE-2025-0016', 18, '2025-09-03 09:27:57', '2025-09-03 10:24:45', 1);

-- --------------------------------------------------------

--
-- Table structure for table `so`
--

CREATE TABLE `so` (
  `id` int(11) NOT NULL,
  `so_number` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `so_date` date NOT NULL,
  `delivery_date` date DEFAULT NULL,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('draft','confirmed','processing','shipped','delivered','cancelled') DEFAULT 'draft',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE `status` (
  `id` int(11) NOT NULL,
  `lead_id` int(11) NOT NULL,
  `status_text` varchar(255) NOT NULL,
  `status_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `invoice_number` varchar(255) NOT NULL,
  `invoice_amount` decimal(15,2) NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `company_address` text NOT NULL,
  `country` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `zip_code` varchar(20) NOT NULL,
  `gstin` varchar(15) NOT NULL,
  `contact_person_name` varchar(255) NOT NULL,
  `contact_person_phone` varchar(20) NOT NULL,
  `contact_person_email` varchar(255) NOT NULL,
  `contract_signed` enum('yes','no') DEFAULT 'no',
  `password` varchar(255) NOT NULL,
  `verification_token` varchar(100) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `company_name`, `payment_id`, `supplier_name`, `invoice_number`, `invoice_amount`, `invoice_date`, `company_address`, `country`, `state`, `city`, `zip_code`, `gstin`, `contact_person_name`, `contact_person_phone`, `contact_person_email`, `contract_signed`, `password`, `verification_token`, `email_verified`, `status`, `created_at`, `updated_at`) VALUES
(2, 'test', NULL, '', '', 0.00, NULL, 'test', 'IN', 'RJ', 'Jaipur', '302021', '09AAACH7409R1ZZ', 'test', '9898989898', 'gouttambhupesh@gmail.com', 'yes', '$2y$10$9R04UpQl6KTv6yv9qoeRc.VwL6aur1.4zF3z4xNxBr8uRuwwChTrm', '61e00bd5a6289de23042cbea0982a5430fd47fd73008a05941d0680a7e7016ab', 0, 'pending', '2025-08-14 09:23:07', '2025-08-14 09:23:07');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers_old`
--

CREATE TABLE `suppliers_old` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_items`
--

CREATE TABLE `supplier_items` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_quantity` int(11) NOT NULL,
  `item_price` decimal(15,2) NOT NULL,
  `item_amount` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_otps`
--

CREATE TABLE `supplier_otps` (
  `id` int(11) NOT NULL,
  `supplier_email` varchar(255) NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_quotations`
--

CREATE TABLE `supplier_quotations` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `rfq_reference` varchar(100) NOT NULL,
  `quotation_number` varchar(100) NOT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'INR',
  `validity_days` int(11) DEFAULT 30,
  `delivery_time` varchar(100) DEFAULT NULL,
  `payment_terms` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_department` (`department`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `bom`
--
ALTER TABLE `bom`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bom_number` (`bom_number`),
  ADD KEY `idx_bom_number` (`bom_number`),
  ADD KEY `idx_product_name` (`product_name`),
  ADD KEY `idx_product_code` (`product_code`);

--
-- Indexes for table `bom_factory`
--
ALTER TABLE `bom_factory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_main_id` (`bom_main_id`);

--
-- Indexes for table `bom_glow`
--
ALTER TABLE `bom_glow`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_main_id` (`bom_main_id`);

--
-- Indexes for table `bom_hardware`
--
ALTER TABLE `bom_hardware`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_main_id` (`bom_main_id`);

--
-- Indexes for table `bom_labour`
--
ALTER TABLE `bom_labour`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_main_id` (`bom_main_id`);

--
-- Indexes for table `bom_main`
--
ALTER TABLE `bom_main`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bom_number` (`bom_number`);

--
-- Indexes for table `bom_margin`
--
ALTER TABLE `bom_margin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_main_id` (`bom_main_id`);

--
-- Indexes for table `bom_plynydf`
--
ALTER TABLE `bom_plynydf`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_main_id` (`bom_main_id`);

--
-- Indexes for table `bom_wood`
--
ALTER TABLE `bom_wood`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bom_main_id` (`bom_main_id`);

--
-- Indexes for table `buyers`
--
ALTER TABLE `buyers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `contact_person_email` (`contact_person_email`),
  ADD KEY `idx_email` (`contact_person_email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_company` (`company_name`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_customer_email` (`customer_email`),
  ADD KEY `idx_customer_name` (`customer_name`),
  ADD KEY `idx_company_name` (`company_name`);

--
-- Indexes for table `jci`
--
ALTER TABLE `jci`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jci_number` (`jci_number`),
  ADD KEY `idx_jci_number` (`jci_number`),
  ADD KEY `idx_customer_name` (`customer_name`),
  ADD KEY `idx_start_date` (`start_date`);

--
-- Indexes for table `jci_items`
--
ALTER TABLE `jci_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jci_id` (`jci_id`);

--
-- Indexes for table `jci_main`
--
ALTER TABLE `jci_main`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jci_number` (`jci_number`),
  ADD KEY `fk_jci_po` (`po_id`),
  ADD KEY `idx_jci_sell_order_number` (`sell_order_number`);

--
-- Indexes for table `job_cards`
--
ALTER TABLE `job_cards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `leads`
--
ALTER TABLE `leads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_lead_number` (`lead_number`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_type` (`user_type`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_module` (`module`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payment_details_payment_id` (`payment_id`);

--
-- Indexes for table `pi`
--
ALTER TABLE `pi`
  ADD PRIMARY KEY (`pi_id`),
  ADD UNIQUE KEY `pi_number` (`pi_number`),
  ADD UNIQUE KEY `unique_pi_number` (`pi_number`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `idx_pi_number` (`pi_number`),
  ADD KEY `idx_quotation_id` (`quotation_id`);

--
-- Indexes for table `po`
--
ALTER TABLE `po`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `idx_po_number` (`po_number`),
  ADD KEY `idx_supplier_name` (`supplier_name`),
  ADD KEY `idx_po_date` (`po_date`);

--
-- Indexes for table `po_items`
--
ALTER TABLE `po_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `po_main`
--
ALTER TABLE `po_main`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_po_sell_order_number` (`sell_order_number`);

--
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_number` (`purchase_number`),
  ADD KEY `idx_purchase_number` (`purchase_number`),
  ADD KEY `idx_supplier_name` (`supplier_name`),
  ADD KEY `idx_purchase_date` (`purchase_date`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_main_id` (`purchase_main_id`);

--
-- Indexes for table `purchase_main`
--
ALTER TABLE `purchase_main`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_id` (`lead_id`),
  ADD KEY `idx_quotation_number` (`quotation_number`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_quotation_date` (`quotation_date`);

--
-- Indexes for table `quotation_products`
--
ALTER TABLE `quotation_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `idx_quotation_id` (`quotation_id`),
  ADD KEY `idx_item_code` (`item_code`);

--
-- Indexes for table `quotation_status`
--
ALTER TABLE `quotation_status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`);

--
-- Indexes for table `sell_order`
--
ALTER TABLE `sell_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sell_order_number` (`sell_order_number`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `so`
--
ALTER TABLE `so`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `so_number` (`so_number`),
  ADD KEY `idx_so_number` (`so_number`),
  ADD KEY `idx_customer_name` (`customer_name`),
  ADD KEY `idx_so_date` (`so_date`);

--
-- Indexes for table `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lead_id` (`lead_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `idx_suppliers_status` (`status`),
  ADD KEY `idx_suppliers_company` (`company_name`),
  ADD KEY `idx_suppliers_email` (`contact_person_email`);

--
-- Indexes for table `suppliers_old`
--
ALTER TABLE `suppliers_old`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_otps`
--
ALTER TABLE `supplier_otps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_quotations`
--
ALTER TABLE `supplier_quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rfq_reference` (`rfq_reference`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bom`
--
ALTER TABLE `bom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bom_factory`
--
ALTER TABLE `bom_factory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `bom_glow`
--
ALTER TABLE `bom_glow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `bom_hardware`
--
ALTER TABLE `bom_hardware`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `bom_labour`
--
ALTER TABLE `bom_labour`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `bom_main`
--
ALTER TABLE `bom_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `bom_margin`
--
ALTER TABLE `bom_margin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=219;

--
-- AUTO_INCREMENT for table `bom_plynydf`
--
ALTER TABLE `bom_plynydf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `bom_wood`
--
ALTER TABLE `bom_wood`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=166;

--
-- AUTO_INCREMENT for table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jci`
--
ALTER TABLE `jci`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jci_items`
--
ALTER TABLE `jci_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `jci_main`
--
ALTER TABLE `jci_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `job_cards`
--
ALTER TABLE `job_cards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pi`
--
ALTER TABLE `pi`
  MODIFY `pi_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `po`
--
ALTER TABLE `po`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `po_items`
--
ALTER TABLE `po_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=121;

--
-- AUTO_INCREMENT for table `po_main`
--
ALTER TABLE `po_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `purchase_main`
--
ALTER TABLE `purchase_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `quotation_products`
--
ALTER TABLE `quotation_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `quotation_status`
--
ALTER TABLE `quotation_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sell_order`
--
ALTER TABLE `sell_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `so`
--
ALTER TABLE `so`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `status`
--
ALTER TABLE `status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers_old`
--
ALTER TABLE `suppliers_old`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_items`
--
ALTER TABLE `supplier_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_otps`
--
ALTER TABLE `supplier_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_quotations`
--
ALTER TABLE `supplier_quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jci_items`
--
ALTER TABLE `jci_items`
  ADD CONSTRAINT `jci_items_ibfk_1` FOREIGN KEY (`jci_id`) REFERENCES `jci_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jci_main`
--
ALTER TABLE `jci_main`
  ADD CONSTRAINT `fk_jci_po` FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_cards`
--
ALTER TABLE `job_cards`
  ADD CONSTRAINT `job_cards_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `fk_payment_details_payment_id` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pi`
--
ALTER TABLE `pi`
  ADD CONSTRAINT `pi_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `po_items`
--
ALTER TABLE `po_items`
  ADD CONSTRAINT `po_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_main_id`) REFERENCES `purchase_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`);

--
-- Constraints for table `quotation_products`
--
ALTER TABLE `quotation_products`
  ADD CONSTRAINT `quotation_products_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`);

--
-- Constraints for table `quotation_status`
--
ALTER TABLE `quotation_status`
  ADD CONSTRAINT `quotation_status_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sell_order`
--
ALTER TABLE `sell_order`
  ADD CONSTRAINT `sell_order_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`);

--
-- Constraints for table `status`
--
ALTER TABLE `status`
  ADD CONSTRAINT `status_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_items`
--
ALTER TABLE `supplier_items`
  ADD CONSTRAINT `supplier_items_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
