-- Communication Admin Tables Migration
-- Run this SQL to create supplier quotation system

-- Supplier quotations table (separate from main quotations)
CREATE TABLE IF NOT EXISTS `supplier_quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `quotation_date` date NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `delivery_term` text DEFAULT NULL,
  `terms_of_delivery` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','converted_to_pi') DEFAULT 'pending',
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_number` (`quotation_number`),
  KEY `supplier_id` (`supplier_id`),
  FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Supplier quotation products table
CREATE TABLE IF NOT EXISTS `supplier_quotation_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `assembly` varchar(255) DEFAULT NULL,
  `item_w` decimal(10,2) DEFAULT NULL,
  `item_d` decimal(10,2) DEFAULT NULL,
  `item_h` decimal(10,2) DEFAULT NULL,
  `box_w` decimal(10,2) DEFAULT NULL,
  `box_d` decimal(10,2) DEFAULT NULL,
  `box_h` decimal(10,2) DEFAULT NULL,
  `cbm` decimal(10,4) DEFAULT NULL,
  `wood_type` varchar(255) DEFAULT NULL,
  `no_of_packet` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price_usd` decimal(10,2) NOT NULL,
  `comments` text DEFAULT NULL,
  `product_image_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  FOREIGN KEY (`quotation_id`) REFERENCES `supplier_quotations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Supplier PI table
CREATE TABLE IF NOT EXISTS `supplier_pi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_quotation_id` int(11) NOT NULL,
  `pi_number` varchar(50) NOT NULL,
  `pi_date` date NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pi_number` (`pi_number`),
  KEY `supplier_quotation_id` (`supplier_quotation_id`),
  FOREIGN KEY (`supplier_quotation_id`) REFERENCES `supplier_quotations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buyer quotations table
CREATE TABLE IF NOT EXISTS `buyer_quotations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buyer_id` int(11) NOT NULL,
  `quotation_number` varchar(50) NOT NULL,
  `quotation_date` date NOT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `supplier_email` varchar(255) DEFAULT NULL,
  `supplier_phone` varchar(20) DEFAULT NULL,
  `delivery_term` text DEFAULT NULL,
  `terms_of_delivery` text DEFAULT NULL,
  `status` enum('pending','approved','rejected','converted_to_pi') DEFAULT 'pending',
  `is_locked` tinyint(1) DEFAULT 0,
  `locked_by` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `quotation_number` (`quotation_number`),
  KEY `buyer_id` (`buyer_id`),
  FOREIGN KEY (`buyer_id`) REFERENCES `buyers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Buyer quotation products table
CREATE TABLE IF NOT EXISTS `buyer_quotation_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quotation_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_code` varchar(100) DEFAULT NULL,
  `assembly` varchar(255) DEFAULT NULL,
  `item_w` decimal(10,2) DEFAULT NULL,
  `item_d` decimal(10,2) DEFAULT NULL,
  `item_h` decimal(10,2) DEFAULT NULL,
  `box_w` decimal(10,2) DEFAULT NULL,
  `box_d` decimal(10,2) DEFAULT NULL,
  `box_h` decimal(10,2) DEFAULT NULL,
  `cbm` decimal(10,4) DEFAULT NULL,
  `wood_type` varchar(255) DEFAULT NULL,
  `no_of_packet` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price_usd` decimal(10,2) NOT NULL,
  `comments` text DEFAULT NULL,
  `product_image_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `quotation_id` (`quotation_id`),
  FOREIGN KEY (`quotation_id`) REFERENCES `buyer_quotations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;