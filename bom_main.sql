-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 08, 2025 at 05:08 AM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u404997496_crm_purewood`
--

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
(22, 'BOM-2025-0022', '22', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 05:09:38', '2025-09-05 05:22:12', NULL, NULL, NULL, 67223.82, 1),
(23, 'BOM-2025-0023', '23', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 05:39:13', '2025-09-05 05:45:46', NULL, NULL, NULL, 50869.24, 1),
(24, 'BOM-2025-0024', '24', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 05:53:46', '2025-09-05 06:04:45', NULL, NULL, NULL, 52893.08, 1),
(25, 'BOM-2025-0025', '25', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 06:16:05', '2025-09-05 06:23:08', NULL, NULL, NULL, 51772.85, 1),
(26, 'BOM-2025-0026', '26', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 06:41:44', '2025-09-05 06:50:00', NULL, NULL, NULL, 53035.10, 1),
(27, 'BOM-2025-0027', '27', '7 Seas', 'Js Chouhan', '2025-09-06', '2025-09-06', '2025-09-06 05:13:24', '2025-09-06 06:01:35', NULL, NULL, NULL, 2736.42, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bom_main`
--
ALTER TABLE `bom_main`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bom_number` (`bom_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bom_main`
--
ALTER TABLE `bom_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
