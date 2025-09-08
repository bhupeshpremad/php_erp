-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 04, 2025 at 02:16 PM
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
-- Database: `crm_purewood`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_otps`
--

CREATE TABLE `admin_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`, `department`, `status`, `approved_by`, `created_at`, `updated_at`, `profile_picture`) VALUES
(1, 'Communication Admin', 'communication@purewood.in', '$2y$10$6Qt4O7J0RHFKQ.2B2KLfZOO.p5t.qMR87VJkdDoxTB9i7/AOSDkaS', '', 'approved', NULL, '2025-09-03 12:44:15', '2025-09-03 12:50:29', 'admin_profile_1_1756903829.png'),
(2, 'accounts', 'accounts@purewood.in', '$2y$10$RKyAe6GGauHZ0hOO6cejSuMBCZwKapU7aU80ggJ8YeB5jWtcqEpQO', 'accounts', 'approved', NULL, '2025-09-03 13:22:49', '2025-09-04 07:55:49', 'admin_profile_2_1756966310.png');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_factory`
--

INSERT INTO `bom_factory` (`id`, `bom_main_id`, `total_amount`, `factory_percentage`, `factory_cost`, `updated_total`) VALUES
(7, 1, 1637.78, 15.00, 245.67, 1883.45),
(14, 2, 11804.44, 15.00, 1770.67, 13575.11);

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
  `total` decimal(10,2) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_glow`
--

INSERT INTO `bom_glow` (`id`, `bom_main_id`, `glowtype`, `quantity`, `price`, `total`, `supplier_name`) VALUES
(1, 1, 'Favicole', 1.000, 100.00, 100.00, NULL),
(2, 2, 'glue', 10.000, 10.00, 100.00, NULL);

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
  `totalprice` decimal(10,2) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_hardware`
--

INSERT INTO `bom_hardware` (`id`, `bom_main_id`, `itemname`, `quantity`, `price`, `totalprice`, `supplier_name`) VALUES
(1, 1, 'harware', 1, 10.00, 10.00, NULL),
(2, 2, 'hardware', 10, 1.00, 10.00, NULL);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_labour`
--

INSERT INTO `bom_labour` (`id`, `bom_main_id`, `itemname`, `quantity`, `price`, `totalprice`) VALUES
(1, 1, 'Labour ', 5, 100.00, 500.00),
(2, 2, 'Labour', 10, 100.00, 1000.00);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_main`
--

INSERT INTO `bom_main` (`id`, `bom_number`, `costing_sheet_number`, `client_name`, `prepared_by`, `order_date`, `delivery_date`, `created_at`, `updated_at`, `labour_cost`, `factory_cost`, `margin`, `grand_total_amount`, `jci_assigned`) VALUES
(1, 'BOM-2025-0001', '001', 'User', 'self', '2025-09-03', '2025-09-03', '2025-09-03 12:57:02', '2025-09-03 13:01:35', NULL, NULL, NULL, 2037.78, 1),
(2, 'BOM-2025-0002', '002', 'Test 2', 'test 2', '2025-09-04', '2025-09-04', '2025-09-04 07:52:43', '2025-09-04 07:55:30', NULL, NULL, NULL, 12204.44, 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_margin`
--

INSERT INTO `bom_margin` (`id`, `bom_main_id`, `total_amount`, `margin_percentage`, `margin_cost`, `updated_total`) VALUES
(7, 1, 1837.78, 15.00, 200.00, 2037.78),
(14, 2, 12004.44, 15.00, 200.00, 12204.44);

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
  `total` decimal(10,2) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_plynydf`
--

INSERT INTO `bom_plynydf` (`id`, `bom_main_id`, `quantity`, `width`, `length`, `price`, `total`, `supplier_name`) VALUES
(1, 1, 1, 10.00, 10.00, 10.00, 1000.00, NULL),
(2, 2, 10, 10.00, 10.00, 10.00, 10000.00, NULL);

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
  `total` decimal(10,2) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_wood`
--

INSERT INTO `bom_wood` (`id`, `bom_main_id`, `woodtype`, `length_ft`, `width_ft`, `thickness_inch`, `quantity`, `price`, `cft`, `total`, `supplier_name`) VALUES
(1, 1, 'Mango', 10.00, 0.17, 2.00, 10, 10.00, 2.78, 27.78, NULL),
(2, 2, 'Babool', 10.00, 0.83, 10.00, 10, 10.00, 69.44, 694.44, NULL);

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
-- Table structure for table `buyer_otps`
--

CREATE TABLE `buyer_otps` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `buyer_quotations`
--

CREATE TABLE `buyer_quotations` (
  `id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jci_items`
--

INSERT INTO `jci_items` (`id`, `jci_id`, `job_card_number`, `po_product_id`, `product_name`, `item_code`, `original_po_quantity`, `labour_cost`, `quantity`, `total_amount`, `delivery_date`, `job_card_date`, `job_card_type`, `contracture_name`) VALUES
(1, 1, 'JOB-2025-0001-1', 1, 'Product 01', 'PRP-001', 10.00, 100.00, 10, 1000.00, '2025-09-23', '2025-09-03', 'Contracture', 'Contracture '),
(2, 2, 'JOB-2025-0002-1', 2, 'Product 02', 'PRP-002', 10.00, 200.00, 10, 2000.00, '2025-09-18', '2025-09-01', 'Contracture', 'test 2');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jci_main`
--

INSERT INTO `jci_main` (`id`, `jci_number`, `po_id`, `bom_id`, `jci_type`, `created_by`, `jci_date`, `created_at`, `updated_at`, `sell_order_number`, `purchase_created`, `payment_completed`) VALUES
(1, 'JCI-2025-0001', 1, 1, 'Contracture', 'Self', '2025-09-03', '2025-09-03 13:01:35', '2025-09-03 13:02:19', '\r\n                                    SALE-2025-00', 1, 0),
(2, 'JCI-2025-0002', 2, 2, 'Contracture', 'test 2', '2025-09-04', '2025-09-04 07:55:30', '2025-09-04 07:56:47', '\r\n                                    SALE-2025-00', 1, 0);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leads`
--

INSERT INTO `leads` (`id`, `lead_number`, `entry_date`, `lead_source`, `company_name`, `contact_name`, `contact_phone`, `contact_email`, `country`, `state`, `city`, `created_status`, `approve`, `status`, `created_at`, `updated_at`) VALUES
(1, 'LEAD-2025-0001', '2025-09-03', 'Online', 'Test', 'Test', '9828000001', 'test@gmail.com', 'India', 'Rajasthan', 'Jaipur', 'new', 1, 'active', '2025-09-03 12:53:09', '2025-09-03 12:53:13'),
(2, 'LEAD-2025-0002', '2025-09-05', 'online', 'test 2', 'test 2', 'test 2', 'test2@gmail.com', 'India', 'Rajasthan', 'Jaipur', 'new', 1, 'active', '2025-09-04 06:12:26', '2025-09-04 06:12:43');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `executed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`, `executed_at`) VALUES
(1, '045_alter_payments_payment_number_to_null', 1, '2025-09-03 12:42:19'),
(2, '001_create_admin_users_table', 2, '2025-09-03 12:44:13'),
(3, '002_create_suppliers_table', 2, '2025-09-03 12:44:14'),
(4, '002_fix_admin_department_enum', 2, '2025-09-03 12:44:14'),
(5, '003_create_leads_table', 2, '2025-09-03 12:44:14'),
(6, '003_normalize_admin_departments', 2, '2025-09-03 12:44:14'),
(7, '004_create_quotations_table', 2, '2025-09-03 12:44:14'),
(8, '005_create_quotation_products_table', 2, '2025-09-03 12:44:14'),
(9, '006_create_customers_table', 2, '2025-09-03 12:44:14'),
(10, '007_create_pi_table', 2, '2025-09-03 12:44:14'),
(11, '008_create_purchase_details_table', 2, '2025-09-03 12:44:14'),
(12, '009_create_po_details_table', 2, '2025-09-03 12:44:14'),
(13, '010_create_so_details_table', 2, '2025-09-03 12:44:14'),
(14, '011_create_bom_details_table', 2, '2025-09-03 12:44:14'),
(15, '012_create_jci_details_table', 2, '2025-09-03 12:44:14'),
(16, '013_create_payments_table', 2, '2025-09-03 12:44:14'),
(17, '014_add_lock_system', 2, '2025-09-03 12:44:14'),
(18, '015_create_admin_otps_table', 2, '2025-09-03 12:44:14'),
(19, '015_create_notifications_table', 2, '2025-09-03 12:44:14'),
(20, '016_create_buyer_otps_table', 2, '2025-09-03 12:44:14'),
(21, '016_create_buyers_table', 2, '2025-09-03 12:44:14'),
(22, '017_create_supplier_quotations_table', 2, '2025-09-03 12:44:14'),
(23, '018_add_description_finish_to_quotation_products', 2, '2025-09-03 12:44:15'),
(24, '019_create_purchase_main_table', 2, '2025-09-03 12:44:15'),
(25, '020_create_purchase_items_table', 2, '2025-09-03 12:44:15'),
(26, '021_create_jci_main_table', 2, '2025-09-03 12:44:15'),
(27, '022_create_jci_items_table', 2, '2025-09-03 12:44:15'),
(28, '023_create_po_main_table', 2, '2025-09-03 12:44:15'),
(29, '024_create_po_items_table', 2, '2025-09-03 12:44:15'),
(30, '025_create_sell_order_table', 2, '2025-09-03 12:44:15'),
(31, '026_create_bom_main_table', 2, '2025-09-03 12:44:15'),
(32, '026_create_payment_details_table', 2, '2025-09-03 12:44:15'),
(33, '027_create_bom_wood_table', 2, '2025-09-03 12:44:15'),
(34, '028_create_bom_glow_table', 2, '2025-09-03 12:44:15'),
(35, '029_create_bom_plynydf_table', 2, '2025-09-03 12:44:15'),
(36, '030_create_bom_hardware_table', 2, '2025-09-03 12:44:15'),
(37, '031_create_bom_labour_table', 2, '2025-09-03 12:44:15'),
(38, '032_create_bom_factory_table', 2, '2025-09-03 12:44:15'),
(39, '033_create_bom_margin_table', 2, '2025-09-03 12:44:15'),
(40, '034_create_communication_admin_user', 2, '2025-09-03 12:44:15'),
(41, '035_add_profile_picture_to_admin_users', 2, '2025-09-03 12:44:15'),
(42, '036_create_buyer_quotations_table', 2, '2025-09-03 12:44:15'),
(43, '037_add_bom_id_fk_to_jci_main', 2, '2025-09-03 12:44:15'),
(44, '038_add_fk_jci_po_to_jci_main', 2, '2025-09-03 12:44:15'),
(45, '039_add_supplier_name_to_bom_tables', 2, '2025-09-03 12:44:16'),
(46, '040_add_approval_status_to_purchase_main', 2, '2025-09-03 12:44:16'),
(47, '041_add_item_approval_status_to_purchase_items', 2, '2025-09-03 12:44:16'),
(48, '042_add_jci_number_to_payments_table', 2, '2025-09-03 12:44:16'),
(49, '043_add_po_number_and_sell_order_number_to_payments_table', 2, '2025-09-03 12:44:16'),
(50, '044_alter_payment_details_cheque_number_to_null', 2, '2025-09-03 12:44:16');

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
(1, 'superadmin', NULL, 'Quotations locked', 'Quotations ID #1 has been locked', 'warning', 'quotations', 1, 1, '2025-09-03 12:55:15', '2025-09-04 07:15:58'),
(2, 'superadmin', NULL, 'Quotation Locked', 'Quotation \'QUOTE-2025-00001\' locked', 'info', 'quotation', 1, 1, '2025-09-03 12:55:15', '2025-09-04 07:15:58'),
(3, 'superadmin', NULL, 'New PI', 'PI \'PI-2025-0001\' created', 'info', 'pi', 1, 1, '2025-09-03 12:55:15', '2025-09-04 07:15:58'),
(4, 'superadmin', NULL, 'Quotations unlocked', 'Quotations ID #1 has been unlocked', 'info', 'quotations', 1, 1, '2025-09-03 12:55:23', '2025-09-04 07:15:58'),
(5, 'superadmin', NULL, 'Quotation Unlocked', 'Quotation \'QUOTE-2025-00001\' unlocked', 'info', 'quotation', 1, 1, '2025-09-03 12:55:23', '2025-09-04 07:15:58'),
(6, 'superadmin', NULL, 'Quotations locked', 'Quotations ID #1 has been locked', 'warning', 'quotations', 1, 1, '2025-09-03 12:55:27', '2025-09-04 07:15:58'),
(7, 'superadmin', NULL, 'Quotation Locked', 'Quotation \'QUOTE-2025-00001\' locked', 'info', 'quotation', 1, 1, '2025-09-03 12:55:27', '2025-09-04 07:15:58'),
(8, 'superadmin', NULL, 'PO Locked', 'PO \'PO-001\' locked', 'info', 'po', 1, 1, '2025-09-03 12:58:40', '2025-09-04 07:15:58'),
(9, 'superadmin', NULL, 'New Admin Registration', 'Admin \'accounts\' registered for accounts', 'info', 'admin', 2, 1, '2025-09-03 13:22:49', '2025-09-04 07:15:58'),
(10, 'superadmin', NULL, 'PO Unlocked', 'PO \'PO-001\' unlocked', 'info', 'po', 1, 1, '2025-09-03 14:37:39', '2025-09-04 07:15:58'),
(11, 'superadmin', NULL, 'PO Locked', 'PO \'PO-001\' locked', 'info', 'po', 1, 1, '2025-09-03 14:38:03', '2025-09-04 07:15:58'),
(12, 'superadmin', NULL, 'Quotations locked', 'Quotations ID #2 has been locked', 'warning', 'quotations', 2, 1, '2025-09-04 06:50:45', '2025-09-04 07:15:58'),
(13, 'superadmin', NULL, 'Quotation Locked', 'Quotation \'QUOTE-2025-00002\' locked', 'info', 'quotation', 2, 1, '2025-09-04 06:50:45', '2025-09-04 07:15:58'),
(14, 'superadmin', NULL, 'New PI', 'PI \'PI-2025-0002\' created', 'info', 'pi', 2, 1, '2025-09-04 06:50:45', '2025-09-04 07:15:58');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `jci_number` varchar(255) DEFAULT NULL,
  `po_number` varchar(255) DEFAULT NULL,
  `sell_order_number` varchar(255) DEFAULT NULL,
  `payment_number` varchar(50) NOT NULL,
  `payment_type` enum('received','made') NOT NULL,
  `party_name` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` enum('cash','cheque','bank_transfer','online') DEFAULT 'cash',
  `payment_date` date NOT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `jci_number`, `po_number`, `sell_order_number`, `payment_number`, `payment_type`, `party_name`, `amount`, `payment_method`, `payment_date`, `reference_number`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'JCI-2025-0001', 'PO-001', 'SALE-2025-0001', '', 'received', '', 0.00, 'cash', '0000-00-00', NULL, NULL, 'pending', '2025-09-03 13:26:59', '2025-09-03 13:26:59'),
(6, 'JCI-2025-0001', 'PO-001', 'SALE-2025-0001', 'PAY-2025-8612-1756906376', 'made', 'JCI: JCI-2025-0001', 0.00, 'cash', '2025-09-03', NULL, NULL, 'pending', '2025-09-03 13:32:56', '2025-09-03 13:32:56');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_details`
--

INSERT INTO `payment_details` (`id`, `payment_id`, `jc_number`, `payment_type`, `cheque_number`, `pd_acc_number`, `ptm_amount`, `gst_percent`, `gst_amount`, `total_with_gst`, `payment_invoice_date`, `payment_date`, `payment_category`, `amount`, `created_at`, `updated_at`) VALUES
(1, 1, 'JCI-2025-0001', 'Cheque', '200', '100', 1000.00, 10.00, 100.00, 1100.00, '2025-09-01', '2025-09-03', 'Job Card', 1000.00, '2025-09-03 13:26:59', '2025-09-03 13:26:59'),
(2, 6, '', 'RTGS', '111', '300', 10.00, 12.00, 1.20, 11.20, '2025-08-31', '2025-09-03', 'Supplier', 10.00, '2025-09-03 13:32:56', '2025-09-03 13:32:56');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pi`
--

INSERT INTO `pi` (`pi_id`, `quotation_id`, `quotation_number`, `pi_number`, `status`, `inspection`, `date_of_pi_raised`, `sample_approval_date`, `detailed_seller_address`, `created_at`, `updated_at`) VALUES
(1, 1, 'QUOTE-2025-00001', 'PI-2025-0001', 'Active', NULL, '2025-09-03', NULL, NULL, '2025-09-03 12:55:15', '2025-09-03 12:55:15'),
(2, 2, 'QUOTE-2025-00002', 'PI-2025-0002', 'Active', NULL, '2025-09-04', NULL, NULL, '2025-09-04 06:50:45', '2025-09-04 06:50:45');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `po_items`
--

INSERT INTO `po_items` (`id`, `po_id`, `product_code`, `product_name`, `quantity`, `unit`, `price`, `total_amount`, `product_image`, `created_at`, `updated_at`) VALUES
(1, 1, 'PRP-001', 'Product 01', 10.00, '', 100.00, 1000.00, '68b83b7505391.png', '2025-09-03 18:28:29', '2025-09-03 18:28:29'),
(2, 2, 'PRP-002', 'Product 02', 10.00, '', 100.00, 1000.00, '68b945a46073e.png', '2025-09-04 13:24:12', '2025-09-04 13:24:12');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sell_order_number` varchar(50) DEFAULT NULL,
  `jci_number` varchar(50) DEFAULT NULL,
  `jci_assigned` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `po_main`
--

INSERT INTO `po_main` (`id`, `po_number`, `client_name`, `prepared_by`, `order_date`, `delivery_date`, `sell_order_id`, `status`, `is_locked`, `created_at`, `updated_at`, `sell_order_number`, `jci_number`, `jci_assigned`) VALUES
(1, 'PO-001', 'test', 'test Prepared', '2025-09-03', '2025-09-23', 2, 'Approved', 0, '2025-09-03 12:58:29', '2025-09-03 14:42:53', 'SALE-2025-0002', 'JCI-2025-0001', 1),
(2, 'PO-002', 'test 2', 'test Prepared2', '2025-09-04', '2025-09-24', 3, 'Approved', 0, '2025-09-04 07:54:12', '2025-09-04 07:55:30', 'SALE-2025-0003', '', 1);

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
  `builty_image` varchar(255) DEFAULT NULL,
  `item_approval_status` enum('pending','approved','rejected') DEFAULT 'pending',
  `item_name` varchar(255) NOT NULL DEFAULT 'Sample Item',
  `quantity` decimal(10,2) NOT NULL DEFAULT 1.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`id`, `purchase_main_id`, `supplier_name`, `product_type`, `product_name`, `job_card_number`, `assigned_quantity`, `price`, `total`, `created_at`, `updated_at`, `date`, `invoice_number`, `amount`, `invoice_image`, `builty_number`, `builty_image`, `item_approval_status`, `item_name`, `quantity`) VALUES
(1, 1, 'Supplier 1', 'Glow', 'Favicole', 'JOB-2025-0001-1', 1.000, 100.00, 100.00, '2025-09-03 13:02:19', '2025-09-03 13:25:29', NULL, '111', 100.00, 'invoice_68b83c5b152cd.png', '222', 'builty_68b83c5b158c5.png', 'pending', 'Sample Item', 1.00),
(2, 1, 'Supplier 1', 'Hardware', 'harware', 'JOB-2025-0001-1', 1.000, 10.00, 10.00, '2025-09-03 13:20:39', '2025-09-03 13:26:02', NULL, '111', 10.00, 'invoice_68b840a74d6ce.png', '222', 'builty_68b840a74dbdc.png', 'pending', 'Sample Item', 1.00),
(3, 1, 'Supplier 2', 'Plynydf', 'Plynydf', 'JOB-2025-0001-1', 1.000, 10.00, 10.00, '2025-09-03 13:21:12', '2025-09-03 13:26:23', NULL, '222', 10.00, 'invoice_68b840c831c41.png', '333', 'builty_68b840c832192.png', 'pending', 'Sample Item', 1.00),
(4, 1, 'Supplier 2', 'Wood', 'Mango', 'JOB-2025-0001-1', 10.000, 10.00, 100.00, '2025-09-03 13:21:35', '2025-09-04 11:32:31', NULL, '222', 100.00, 'invoice_68b840df09d71.png', '333', 'builty_68b840df0a2cc.png', 'pending', 'Sample Item', 10.00),
(5, 2, 'Sopplie 1', 'Glow', 'glue', 'JOB-2025-0002-1', 10.000, 10.00, 100.00, '2025-09-04 07:56:47', '2025-09-04 11:32:31', NULL, '100', 100.00, 'invoice_68b9463fb129e.png', '101', 'builty_68b9463fb194e.png', 'pending', 'Sample Item', 10.00);

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
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_main`
--

INSERT INTO `purchase_main` (`id`, `po_number`, `jci_number`, `sell_order_number`, `bom_number`, `created_at`, `updated_at`, `approval_status`) VALUES
(1, 'PO-001', 'JCI-2025-0001', 'SALE-2025-0001', 'BOM-2025-0001', '2025-09-03 13:02:19', '2025-09-03 13:23:23', 'pending'),
(2, 'PO-002', 'JCI-2025-0002', 'SALE-2025-0003', 'BOM-2025-0002', '2025-09-04 07:56:47', '2025-09-04 07:57:09', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `lead_id` int(11) NOT NULL,
  `quotation_date` date NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `customer_phone` varchar(50) NOT NULL,
  `delivery_term` varchar(255) DEFAULT NULL,
  `terms_of_delivery` varchar(255) DEFAULT NULL,
  `excel_file` varchar(255) DEFAULT NULL,
  `quotation_image` varchar(255) DEFAULT NULL,
  `approve` tinyint(1) NOT NULL DEFAULT 0,
  `locked` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_by` int(11) DEFAULT NULL,
  `locked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `supplier_id`, `lead_id`, `quotation_date`, `quotation_number`, `customer_name`, `customer_email`, `customer_phone`, `delivery_term`, `terms_of_delivery`, `excel_file`, `quotation_image`, `approve`, `locked`, `created_at`, `updated_at`, `is_locked`, `locked_by`, `locked_at`) VALUES
(1, NULL, 1, '2025-09-03', 'QUOTE-2025-00001', 'Test', 'test@gmail.com', '9828000001', '89', '89', NULL, NULL, 1, 0, '2025-09-03 12:53:52', '2025-09-03 12:55:27', 1, 1, '2025-09-03 12:55:27'),
(2, NULL, 2, '2025-09-04', 'QUOTE-2025-00002', 'test 2', 'test2@gmail.com', 'test 2', '89', '89', 'quotation_1756968527.xlsx', NULL, 1, 0, '2025-09-04 06:48:47', '2025-09-04 06:50:45', 1, 1, '2025-09-04 06:50:45');

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
  `finish` varchar(255) DEFAULT NULL,
  `iron_gauge` varchar(100) DEFAULT NULL,
  `mdf_finish` varchar(255) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price_usd` decimal(10,2) NOT NULL,
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `product_image_name` varchar(255) DEFAULT NULL,
  `total_price_usd` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `quotation_products`
--

INSERT INTO `quotation_products` (`id`, `quotation_id`, `item_name`, `item_code`, `description`, `assembly`, `item_h`, `item_w`, `item_d`, `box_h`, `box_w`, `box_d`, `cbm`, `wood_type`, `no_of_packet`, `finish`, `iron_gauge`, `mdf_finish`, `quantity`, `price_usd`, `comments`, `created_at`, `updated_at`, `product_image_name`, `total_price_usd`) VALUES
(1, 1, 'Product A', 'P-001', NULL, 'Yes', 19.00, 10.00, 17.00, 7.00, 21.00, 26.00, 0.004, 'Oak', 1, NULL, NULL, NULL, 6.00, 18.00, 'Handle with care', '2025-09-03 12:53:52', '2025-09-03 12:53:52', 'prod_1_1_1756904032.png', 108.00),
(2, 1, 'Product B', 'P-002', NULL, 'No', 20.00, 11.00, 18.00, 8.00, 22.00, 27.00, 0.005, 'Marble', 2, NULL, NULL, NULL, 7.00, 19.00, 'Fragile', '2025-09-03 12:53:52', '2025-09-03 12:53:52', 'prod_1_2_1756904032.png', 133.00),
(5, 2, 'Sample Chair', 'CH001', NULL, 'Assembled', 85.00, 45.00, 50.00, 90.00, 50.00, 55.00, 0.248, 'Teak Wood', 1, NULL, NULL, NULL, 10.00, 150.00, 'High quality chair', '2025-09-04 06:50:22', '2025-09-04 06:50:22', 'prod_2_1_1756968622.png', 1500.00),
(6, 2, 'Sample Table', 'TB001', NULL, 'KD', 75.00, 120.00, 80.00, 80.00, 125.00, 85.00, 0.850, 'Oak Wood', 1, NULL, NULL, NULL, 5.00, 300.00, 'Dining table', '2025-09-04 06:50:22', '2025-09-04 06:50:22', 'prod_2_2_1756968622.png', 1500.00);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_status`
--

CREATE TABLE `quotation_status` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) NOT NULL,
  `status_date` date NOT NULL,
  `status_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_status`
--

INSERT INTO `quotation_status` (`id`, `quotation_id`, `status_date`, `status_text`, `created_at`) VALUES
(1, 2, '2025-09-03', 'de', '2025-09-04 06:51:17'),
(2, 2, '2025-09-15', 'dasdas', '2025-09-04 06:51:27');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sell_order`
--

INSERT INTO `sell_order` (`id`, `sell_order_number`, `po_id`, `created_at`, `updated_at`, `jci_created`) VALUES
(1, 'SALE-2025-0001', 1, '2025-09-03 18:28:37', '2025-09-03 18:31:35', 1),
(2, 'SALE-2025-0002', 1, '2025-09-03 20:12:53', '2025-09-03 20:12:53', 0),
(3, 'SALE-2025-0003', 2, '2025-09-04 13:24:21', '2025-09-04 13:25:30', 1);

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
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
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
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_otps`
--
ALTER TABLE `admin_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_otp_idx` (`email`,`otp`),
  ADD KEY `expires_at_idx` (`expires_at`);

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
-- Indexes for table `buyer_otps`
--
ALTER TABLE `buyer_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `email_otp_idx` (`email`,`otp`),
  ADD KEY `expires_at_idx` (`expires_at`);

--
-- Indexes for table `buyer_quotations`
--
ALTER TABLE `buyer_quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_buyer_id` (`buyer_id`),
  ADD KEY `idx_status_buyer_quotations` (`status`),
  ADD KEY `idx_rfq_reference_buyer_quotations` (`rfq_reference`);

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
  ADD KEY `idx_jci_sell_order_number` (`sell_order_number`),
  ADD KEY `fk_jci_bom` (`bom_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payment_number` (`payment_number`),
  ADD KEY `idx_payment_number` (`payment_number`),
  ADD KEY `idx_party_name` (`party_name`),
  ADD KEY `idx_payment_date` (`payment_date`),
  ADD KEY `idx_payment_type` (`payment_type`);

--
-- Indexes for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`);

--
-- Indexes for table `pi`
--
ALTER TABLE `pi`
  ADD PRIMARY KEY (`pi_id`),
  ADD UNIQUE KEY `pi_number` (`pi_number`),
  ADD KEY `idx_pi_number` (`pi_number`),
  ADD KEY `idx_quotation_id` (`quotation_id`);

--
-- Indexes for table `po`
--
ALTER TABLE `po`
  ADD PRIMARY KEY (`id`),
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
  ADD KEY `idx_quotation_number` (`quotation_number`),
  ADD KEY `idx_lead_id` (`lead_id`),
  ADD KEY `idx_supplier_id` (`supplier_id`),
  ADD KEY `idx_quotation_date` (`quotation_date`);

--
-- Indexes for table `quotation_products`
--
ALTER TABLE `quotation_products`
  ADD PRIMARY KEY (`id`),
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
  ADD KEY `idx_so_number` (`so_number`),
  ADD KEY `idx_customer_name` (`customer_name`),
  ADD KEY `idx_so_date` (`so_date`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gstin` (`gstin`),
  ADD UNIQUE KEY `contact_person_email` (`contact_person_email`),
  ADD KEY `idx_email` (`contact_person_email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_company` (`company_name`);

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
-- AUTO_INCREMENT for table `admin_otps`
--
ALTER TABLE `admin_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `bom_glow`
--
ALTER TABLE `bom_glow`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bom_hardware`
--
ALTER TABLE `bom_hardware`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bom_labour`
--
ALTER TABLE `bom_labour`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bom_main`
--
ALTER TABLE `bom_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bom_margin`
--
ALTER TABLE `bom_margin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `bom_plynydf`
--
ALTER TABLE `bom_plynydf`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bom_wood`
--
ALTER TABLE `bom_wood`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `buyers`
--
ALTER TABLE `buyers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `buyer_otps`
--
ALTER TABLE `buyer_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `buyer_quotations`
--
ALTER TABLE `buyer_quotations`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jci_main`
--
ALTER TABLE `jci_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leads`
--
ALTER TABLE `leads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payment_details`
--
ALTER TABLE `payment_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pi`
--
ALTER TABLE `pi`
  MODIFY `pi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `po`
--
ALTER TABLE `po`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `po_items`
--
ALTER TABLE `po_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `po_main`
--
ALTER TABLE `po_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase_main`
--
ALTER TABLE `purchase_main`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `quotation_products`
--
ALTER TABLE `quotation_products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `quotation_status`
--
ALTER TABLE `quotation_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sell_order`
--
ALTER TABLE `sell_order`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `so`
--
ALTER TABLE `so`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
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
-- Constraints for table `bom_factory`
--
ALTER TABLE `bom_factory`
  ADD CONSTRAINT `bom_factory_ibfk_1` FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_glow`
--
ALTER TABLE `bom_glow`
  ADD CONSTRAINT `bom_glow_ibfk_1` FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_hardware`
--
ALTER TABLE `bom_hardware`
  ADD CONSTRAINT `bom_hardware_ibfk_1` FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_labour`
--
ALTER TABLE `bom_labour`
  ADD CONSTRAINT `bom_labour_ibfk_1` FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_margin`
--
ALTER TABLE `bom_margin`
  ADD CONSTRAINT `bom_margin_ibfk_1` FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_plynydf`
--
ALTER TABLE `bom_plynydf`
  ADD CONSTRAINT `bom_plynydf_ibfk_1` FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_wood`
--
ALTER TABLE `bom_wood`
  ADD CONSTRAINT `bom_wood_ibfk_1` FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `buyer_quotations`
--
ALTER TABLE `buyer_quotations`
  ADD CONSTRAINT `buyer_quotations_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `buyers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jci_items`
--
ALTER TABLE `jci_items`
  ADD CONSTRAINT `jci_items_ibfk_1` FOREIGN KEY (`jci_id`) REFERENCES `jci_main` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jci_main`
--
ALTER TABLE `jci_main`
  ADD CONSTRAINT `fk_jci_bom` FOREIGN KEY (`bom_id`) REFERENCES `bom_main` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_jci_po` FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payment_details`
--
ALTER TABLE `payment_details`
  ADD CONSTRAINT `payment_details_ibfk_1` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE;

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
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`),
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `quotation_products`
--
ALTER TABLE `quotation_products`
  ADD CONSTRAINT `quotation_products_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`);

--
-- Constraints for table `quotation_status`
--
ALTER TABLE `quotation_status`
  ADD CONSTRAINT `quotation_status_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`);

--
-- Constraints for table `sell_order`
--
ALTER TABLE `sell_order`
  ADD CONSTRAINT `sell_order_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
