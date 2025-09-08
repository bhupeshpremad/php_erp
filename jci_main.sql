-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Sep 08, 2025 at 05:10 AM
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
(20, 'JCI-2025-0020', 18, 22, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 05:22:12', '2025-09-05 05:22:12', '\r\n                                    SALE-2025-00', 0, 0),
(21, 'JCI-2025-0021', 18, 23, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 05:45:46', '2025-09-05 05:45:46', '\r\n                                    SALE-2025-00', 0, 0),
(22, 'JCI-2025-0022', 18, 24, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 06:04:45', '2025-09-05 06:04:45', '\r\n                                    SALE-2025-00', 0, 0),
(23, 'JCI-2025-0023', 18, 25, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 06:23:08', '2025-09-05 06:23:08', '\r\n                                    SALE-2025-00', 0, 0),
(24, 'JCI-2025-0024', 18, 26, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 06:50:00', '2025-09-05 06:50:00', '\r\n                                    SALE-2025-00', 0, 0),
(25, 'JCI-2025-0025', 20, 27, 'Contracture', 'JS CHOUHAN', '2025-09-06', '2025-09-06 06:01:35', '2025-09-06 06:01:35', '\r\n                                    SALE-2025-00', 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `jci_main`
--
ALTER TABLE `jci_main`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jci_number` (`jci_number`),
  ADD KEY `fk_jci_po` (`po_id`),
  ADD KEY `idx_jci_sell_order_number` (`sell_order_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `jci_main`
--
ALTER TABLE `jci_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jci_main`
--
ALTER TABLE `jci_main`
  ADD CONSTRAINT `fk_jci_po` FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
