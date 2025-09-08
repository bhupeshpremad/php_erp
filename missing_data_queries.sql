-- Missing Data from Live Server (php_erp2) to be added to php_erp3
-- Run these queries on php_erp3_db database

-- BOM: Missing 6 records (22-27)
INSERT INTO `bom_main` (`id`, `bom_number`, `costing_sheet_number`, `client_name`, `prepared_by`, `order_date`, `delivery_date`, `created_at`, `updated_at`, `labour_cost`, `factory_cost`, `margin`, `grand_total_amount`, `jci_assigned`) VALUES
(22, 'BOM-2025-0022', '22', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 05:09:38', '2025-09-05 05:22:12', NULL, NULL, NULL, 67223.82, 1),
(23, 'BOM-2025-0023', '23', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 05:39:13', '2025-09-05 05:45:46', NULL, NULL, NULL, 50869.24, 1),
(24, 'BOM-2025-0024', '24', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 05:53:46', '2025-09-05 06:04:45', NULL, NULL, NULL, 52893.08, 1),
(25, 'BOM-2025-0025', '25', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 06:16:05', '2025-09-05 06:23:08', NULL, NULL, NULL, 51772.85, 1),
(26, 'BOM-2025-0026', '26', 'ANSHI DOOR', 'M.S', '2025-09-05', '2025-09-05', '2025-09-05 06:41:44', '2025-09-05 06:50:00', NULL, NULL, NULL, 53035.10, 1),
(27, 'BOM-2025-0027', '27', '7 Seas', 'Js Chouhan', '2025-09-06', '2025-09-06', '2025-09-06 05:13:24', '2025-09-06 06:01:35', NULL, NULL, NULL, 2736.42, 1);

-- BOM Wood data for new BOMs
INSERT INTO `bom_wood` (`id`, `bom_main_id`, `woodtype`, `length_ft`, `width_ft`, `thickness_inch`, `quantity`, `price`, `cft`, `total`) VALUES
(199, 22, 'Other', 11.00, 1.25, 2.00, 2, 1850.00, 4.58, 8479.17),
(200, 22, 'Other', 4.00, 1.25, 2.00, 1, 1850.00, 0.83, 1541.67),
(201, 22, 'Other', 11.50, 0.42, 1.00, 4, 1850.00, 1.60, 2954.86),
(202, 22, 'Other', 4.75, 0.42, 1.00, 2, 1850.00, 0.33, 610.24),
(203, 22, 'Other', 11.00, 0.50, 2.00, 2, 2350.00, 1.83, 4308.33),
(204, 22, 'Other', 3.75, 1.67, 2.00, 1, 2350.00, 1.04, 2447.92),
(205, 22, 'Other', 2.50, 3.67, 1.00, 1, 2350.00, 0.76, 1795.14),
(206, 22, 'Other', 7.25, 3.67, 1.00, 1, 2350.00, 2.22, 5205.90),
(207, 22, 'Other', 7.25, 0.33, 1.50, 2, 2500.00, 0.60, 1510.42),
(208, 22, 'Other', 3.25, 0.33, 1.50, 4, 2350.00, 0.54, 1272.92),
(209, 22, 'Other', 2.50, 0.33, 1.50, 2, 2350.00, 0.21, 489.58),
(210, 23, 'Other', 4.00, 1.17, 2.00, 1, 1850.00, 0.78, 1438.89),
(211, 23, 'Other', 8.25, 1.17, 2.00, 2, 1850.00, 3.21, 5935.42),
(212, 23, 'Other', 4.75, 0.42, 1.00, 2, 1850.00, 0.33, 610.24),
(213, 23, 'Other', 8.50, 0.42, 1.00, 4, 1850.00, 1.18, 2184.03),
(214, 23, 'Other', 8.25, 0.42, 2.00, 2, 2350.00, 1.15, 2692.71),
(215, 23, 'Other', 4.00, 1.58, 2.00, 1, 2350.00, 1.06, 2480.56),
(216, 23, 'Other', 3.25, 0.33, 1.50, 4, 2350.00, 0.54, 1272.92),
(217, 23, 'Other', 2.50, 0.33, 1.50, 2, 2350.00, 0.21, 489.58),
(218, 23, 'Other', 4.50, 0.33, 1.50, 2, 2500.00, 0.38, 937.50),
(219, 23, 'Other', 4.50, 3.67, 1.00, 1, 2350.00, 1.38, 3231.25),
(220, 23, 'Other', 2.50, 3.67, 1.00, 1, 2350.00, 0.76, 1795.14),
(232, 24, 'Other', 8.25, 1.25, 2.00, 2, 1850.00, 3.44, 6359.38),
(233, 24, 'Other', 4.50, 1.25, 2.00, 1, 1850.00, 0.94, 1734.38),
(234, 24, 'Other', 8.50, 0.42, 1.00, 4, 1850.00, 1.18, 2184.03),
(235, 24, 'Other', 5.00, 0.42, 1.00, 2, 1850.00, 0.35, 642.36),
(236, 24, 'Other', 8.25, 0.42, 2.00, 2, 2350.00, 1.15, 2692.71),
(237, 24, 'Other', 4.25, 1.58, 2.00, 1, 2350.00, 1.12, 2635.59),
(238, 24, 'Other', 3.25, 0.33, 1.50, 4, 2350.00, 0.54, 1272.92),
(239, 24, 'Other', 2.50, 0.33, 1.50, 2, 2350.00, 0.21, 489.58),
(240, 24, 'Other', 4.50, 0.33, 1.50, 2, 2500.00, 0.38, 937.50),
(241, 24, 'Other', 4.50, 3.75, 1.00, 1, 2350.00, 1.41, 3304.69),
(242, 24, 'Other', 2.50, 3.75, 1.00, 1, 2350.00, 0.78, 1835.94),
(243, 25, 'Other', 8.25, 1.25, 2.00, 2, 1850.00, 3.44, 6359.38),
(244, 25, 'Other', 4.25, 1.25, 2.00, 1, 1850.00, 0.89, 1638.02),
(245, 25, 'Other', 8.50, 0.42, 1.00, 4, 1850.00, 1.18, 2184.03),
(246, 25, 'Other', 5.00, 0.42, 1.00, 2, 1850.00, 0.35, 642.36),
(247, 25, 'Other', 8.00, 0.42, 2.00, 2, 2350.00, 1.11, 2611.11),
(248, 25, 'Other', 4.00, 1.58, 2.00, 1, 2350.00, 1.06, 2480.56),
(249, 25, 'Other', 3.25, 0.33, 1.50, 4, 2350.00, 0.54, 1272.92),
(250, 25, 'Other', 2.50, 0.33, 1.50, 2, 2350.00, 0.21, 489.58),
(251, 25, 'Other', 4.50, 0.33, 1.50, 2, 2500.00, 0.38, 937.50),
(252, 25, 'Other', 4.50, 3.67, 1.00, 1, 2350.00, 1.38, 3231.25),
(253, 25, 'Other', 2.50, 3.67, 1.00, 1, 2350.00, 0.76, 1795.14),
(265, 26, 'Other', 8.25, 1.25, 2.00, 2, 1850.00, 3.44, 6359.38),
(266, 26, 'Other', 4.50, 1.25, 2.00, 1, 1850.00, 0.94, 1734.38),
(267, 26, 'Other', 8.50, 0.42, 1.00, 4, 1850.00, 1.18, 2184.03),
(268, 26, 'Other', 5.00, 0.42, 1.00, 2, 1850.00, 0.35, 642.36),
(269, 26, 'Other', 8.25, 0.42, 2.00, 2, 2350.00, 1.15, 2692.71),
(270, 26, 'Other', 4.25, 1.58, 2.00, 1, 2350.00, 1.12, 2635.59),
(271, 26, 'Other', 3.25, 0.33, 1.50, 4, 2350.00, 0.54, 1272.92),
(272, 26, 'Other', 2.50, 0.33, 1.50, 2, 2350.00, 0.21, 489.58),
(273, 26, 'Other', 4.50, 0.33, 1.50, 2, 2350.00, 0.38, 881.25),
(274, 26, 'Other', 4.50, 3.75, 1.00, 1, 2350.00, 1.41, 3304.69),
(275, 26, 'Other', 2.50, 3.75, 1.00, 1, 2350.00, 0.78, 1835.94),
(276, 27, 'Mango', 2.75, 0.42, 2.00, 2, 690.00, 0.38, 263.54),
(277, 27, 'Mango', 2.00, 0.42, 2.00, 4, 690.00, 0.56, 383.33),
(278, 27, 'Mango', 2.25, 0.33, 2.00, 2, 690.00, 0.25, 172.50),
(279, 27, 'Mango', 1.50, 0.25, 2.00, 10, 690.00, 0.63, 431.25);

-- BOM Glow data for new BOMs
INSERT INTO `bom_glow` (`id`, `bom_main_id`, `glowtype`, `quantity`, `price`, `total`) VALUES
(33, 22, 'HARDWARE', 7.500, 160.00, 1200.00),
(34, 23, 'HARDWARE', 7.200, 160.00, 1152.00),
(35, 24, 'HARDWARE', 6.300, 160.00, 1008.00),
(36, 25, 'HARDWARE', 6.500, 160.00, 1040.00),
(37, 26, 'HARDWARE', 6.700, 160.00, 1072.00),
(38, 27, 'GLUE', 0.130, 160.00, 20.80);

-- BOM Hardware data for new BOMs
INSERT INTO `bom_hardware` (`id`, `bom_main_id`, `itemname`, `quantity`, `price`, `totalprice`) VALUES
(36, 22, 'HARDDARE', 1, 1862.00, 1862.00),
(37, 23, 'HARDWARE', 1, 1155.00, 1155.00),
(38, 24, 'HARDWAE', 1, 1401.00, 1401.00),
(39, 25, 'HARDWARE', 1, 1324.00, 1324.00),
(40, 26, 'HARDWARE', 1, 1331.00, 1331.00),
(41, 27, 'HARDWARE', 1, 100.00, 100.00);

-- BOM Labour data for new BOMs
INSERT INTO `bom_labour` (`id`, `bom_main_id`, `itemname`, `quantity`, `price`, `totalprice`) VALUES
(60, 22, 'LABOUR', 1, 8025.00, 8025.00),
(61, 22, 'POLISH & PACKING', 1, 9128.00, 9128.00),
(62, 23, 'LABOUR', 1, 5930.00, 5930.00),
(63, 23, 'POLISH & PACKING', 1, 7159.00, 7159.00),
(66, 24, 'LABOUR', 1, 6277.00, 6277.00),
(67, 24, 'POLISH & PACKING', 1, 7220.00, 7220.00),
(70, 25, 'LABOUR', 1, 5970.00, 5970.00),
(71, 25, 'POLISH & PACKING', 1, 7172.00, 7172.00),
(74, 26, 'LABOUR', 1, 6277.00, 6277.00),
(75, 26, 'POLISH & PACKNG', 1, 7389.00, 7389.00),
(76, 27, 'RAJU DAN JI', 1, 600.00, 600.00),
(77, 27, 'CNC', 1, 100.00, 100.00);

-- BOM Factory data for new BOMs
INSERT INTO `bom_factory` (`id`, `bom_main_id`, `total_amount`, `factory_percentage`, `factory_cost`, `updated_total`) VALUES
(229, 22, 50831.15, 15.00, 7624.67, 58455.82),
(235, 23, 38464.24, 15.00, 5769.64, 44233.88),
(243, 24, 39995.08, 15.00, 5999.26, 45994.34),
(250, 25, 39147.85, 15.00, 5872.18, 45020.03),
(268, 26, 40101.83, 15.00, 6015.27, 46117.10),
(274, 27, 2071.42, 15.00, 310.71, 2382.13);

-- BOM Margin data for new BOMs
INSERT INTO `bom_margin` (`id`, `bom_main_id`, `total_amount`, `margin_percentage`, `margin_cost`, `updated_total`) VALUES
(229, 22, 58456.15, 15.00, 8768.00, 67224.15),
(235, 23, 44234.23, 15.00, 6635.00, 50869.23),
(243, 24, 45994.06, 15.00, 6899.00, 52893.06),
(250, 25, 45019.84, 15.00, 6753.00, 51772.84),
(268, 26, 46116.81, 15.00, 6918.00, 53034.81),
(274, 27, 2279.43, 15.00, 457.00, 2736.43);

-- PO: Missing 2 records (19-20)
INSERT INTO `po_main` (`id`, `po_number`, `client_name`, `prepared_by`, `order_date`, `delivery_date`, `sell_order_id`, `status`, `is_locked`, `locked_by`, `locked_at`, `created_at`, `updated_at`, `sell_order_number`, `jci_number`, `jci_assigned`) VALUES
(19, 'PO04513', 'Ambiance Home', 'Mahaveer Singh', '2025-09-05', '2025-09-26', 17, 'Approved', 0, NULL, NULL, '2025-09-05 04:24:17', '2025-09-05 04:31:45', 'SALE-2025-0017', '', 0),
(20, 'VERBAL BY HUKAM SINGH', '7 SEAS', 'JS CHOUHAN', '2025-09-06', '2025-09-08', 18, 'Approved', 0, NULL, NULL, '2025-09-06 05:17:48', '2025-09-06 06:01:35', 'SALE-2025-0018', '', 1);

-- PO Items for new POs
INSERT INTO `po_items` (`id`, `po_id`, `product_code`, `product_name`, `quantity`, `unit`, `price`, `total_amount`, `product_image`, `created_at`, `updated_at`) VALUES
(123, 19, 'ADC-490', 'AHFLDT71MT230_Dining table Mayfield 230-Base', 10.00, '', 1900.00, 19000.00, '', '2025-09-05 04:30:21', '2025-09-05 04:30:21'),
(124, 19, 'AHFLCN30-B', 'Console Table Wooden Base', 80.00, '', 1850.00, 148000.00, '', '2025-09-05 04:30:21', '2025-09-05 04:30:21'),
(125, 19, 'ADC-526', 'AHFL-RD140MB_Fluting Round Table Wooden Base', 15.00, '', 3100.00, 46500.00, '', '2025-09-05 04:30:21', '2025-09-05 04:30:21'),
(126, 20, 'NA', 'ARM CHAIR (SAMPLE)', 1.00, '', 2750.00, 2750.00, '', '2025-09-06 05:17:48', '2025-09-06 05:17:48');

-- End of missing data queries
-- Total Records Added:
-- BOM Main: 6 records (22-27)
-- BOM Wood: 61 records
-- BOM Glow: 6 records
-- BOM Hardware: 6 records
-- BOM Labour: 12 records
-- BOM Factory: 6 records
-- BOM Margin: 6 records
-- PO Main: 2 records (19-20)
-- PO Items: 4 records'', '2025-09-06 05:17:48', '2025-09-06 05:17:48');

-- SO: Missing 2 records (17-18)
INSERT INTO `sell_order` (`id`, `sell_order_number`, `po_id`, `created_at`, `updated_at`, `jci_created`) VALUES
(17, 'SALE-2025-0017', 19, '2025-09-05 04:31:45', '2025-09-05 04:31:45', 0),
(18, 'SALE-2025-0018', 20, '2025-09-06 06:00:41', '2025-09-06 06:01:35', 1);

-- JCI: Missing 7 records (19-25)
INSERT INTO `jci_main` (`id`, `jci_number`, `po_id`, `bom_id`, `jci_type`, `created_by`, `jci_date`, `created_at`, `updated_at`, `sell_order_number`, `purchase_created`, `payment_completed`) VALUES
(19, 'JCI-2025-0019', 18, 21, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 04:56:56', '2025-09-05 04:56:56', '\r\n                                    SALE-2025-00', 0, 0),
(20, 'JCI-2025-0020', 18, 22, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 05:22:12', '2025-09-05 05:22:12', '\r\n                                    SALE-2025-00', 0, 0),
(21, 'JCI-2025-0021', 18, 23, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 05:45:46', '2025-09-05 05:45:46', '\r\n                                    SALE-2025-00', 0, 0),
(22, 'JCI-2025-0022', 18, 24, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 06:04:45', '2025-09-05 06:04:45', '\r\n                                    SALE-2025-00', 0, 0),
(23, 'JCI-2025-0023', 18, 25, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 06:23:08', '2025-09-05 06:23:08', '\r\n                                    SALE-2025-00', 0, 0),
(24, 'JCI-2025-0024', 18, 26, 'Contracture', 'M.S', '2025-09-05', '2025-09-05 06:50:00', '2025-09-05 06:50:00', '\r\n                                    SALE-2025-00', 0, 0),
(25, 'JCI-2025-0025', 20, 27, 'Contracture', 'JS CHOUHAN', '2025-09-06', '2025-09-06 06:01:35', '2025-09-06 06:01:35', '\r\n                                    SALE-2025-00', 0, 0);

-- JCI Items for new JCIs
INSERT INTO `jci_items` (`id`, `jci_id`, `job_card_number`, `po_product_id`, `product_name`, `item_code`, `original_po_quantity`, `labour_cost`, `quantity`, `total_amount`, `delivery_date`, `job_card_date`, `job_card_type`, `contracture_name`) VALUES
(23, 19, 'JOB-2025-0019-1', 103, 'Bathroom-1-D4 (38 MM THICK)', '3', 1.00, 8286.00, 1, 8286.00, '2025-09-26', '2025-09-05', 'In-House', NULL),
(24, 20, 'JOB-2025-0020-1', 104, 'Bedroom-2-D5 (38 MM THICK)', '4', 1.00, 8025.00, 1, 8025.00, '2025-09-26', '2025-09-05', 'In-House', NULL),
(25, 21, 'JOB-2025-0021-1', 105, 'Bathroom-2-D6 (38 MM THICK)', '5', 1.00, 5930.00, 1, 5930.00, '2025-09-26', '2025-09-05', 'In-House', NULL),
(26, 22, 'JOB-2025-0022-1', 103, 'Bathroom-1-D4 (38 MM THICK)', '3', 1.00, 6277.00, 1, 6277.00, '2025-09-26', '2025-09-05', 'In-House', NULL),
(27, 23, 'JOB-2025-0023-1', 102, 'Bedroom-1-D3 (38 MM THICK)', '2', 1.00, 5970.00, 1, 5970.00, '2025-09-26', '2025-09-05', 'In-House', NULL),
(28, 24, 'JOB-2025-0024-1', 101, 'Kitchen-D2 (38 MM THICK)', '1', 1.00, 6277.00, 1, 6277.00, '2025-09-26', '2025-09-05', 'In-House', NULL),
(29, 25, 'JOB-2025-0025-1', 126, 'ARM CHAIR (SAMPLE)', 'NA', 1.00, 600.00, 1, 600.00, '2025-09-08', '2025-09-06', 'In-House', NULL);

-- Update BOM-2025-0021 grand_total_amount (small correction)
UPDATE `bom_main` SET `grand_total_amount` = 68543.07 WHERE `id` = 21;

-- Add missing columns to payments table for better functionality
ALTER TABLE `payments` ADD COLUMN `payment_number` VARCHAR(50) NULL AFTER `id`;
ALTER TABLE `payments` ADD COLUMN `payment_type` VARCHAR(20) NULL AFTER `sell_order_number`;
ALTER TABLE `payments` ADD COLUMN `party_name` VARCHAR(255) NULL AFTER `payment_type`;
ALTER TABLE `payments` ADD COLUMN `amount` DECIMAL(15,2) DEFAULT 0.00 AFTER `party_name`;
ALTER TABLE `payments` ADD COLUMN `payment_date` DATE NULL AFTER `amount`;
ALTER TABLE `payments` ADD COLUMN `status` VARCHAR(20) DEFAULT 'pending' AFTER `payment_date`;