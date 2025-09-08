<?php
include_once __DIR__ . '/../../config/config.php';

try {
    // Create bom_main table
    $conn->exec("CREATE TABLE IF NOT EXISTS `bom_main` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `bom_number` varchar(255) NOT NULL,
        `costing_sheet_number` varchar(255) DEFAULT NULL,
        `client_name` varchar(255) DEFAULT NULL,
        `prepared_by` varchar(255) DEFAULT NULL,
        `order_date` date DEFAULT NULL,
        `delivery_date` date DEFAULT NULL,
        `labour_cost` decimal(10,2) DEFAULT NULL,
        `factory_cost` decimal(10,2) DEFAULT NULL,
        `margin` decimal(10,2) DEFAULT NULL,
        `grand_total_amount` decimal(10,2) DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `bom_number` (`bom_number`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

    // Add columns to bom_main if they don't exist
    $columns = $conn->query("SHOW COLUMNS FROM `bom_main`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('order_date', $columns)) {
        $conn->exec("ALTER TABLE `bom_main` ADD COLUMN `order_date` DATE;");
    }
    if (!in_array('delivery_date', $columns)) {
// Create jci_main table if it doesn't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS `jci_main` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `po_id` int(11) DEFAULT NULL,
        `bom_id` int(11) DEFAULT NULL,
        `sell_order_number` varchar(255) DEFAULT NULL,
        `jci_number` varchar(255) NOT NULL,
        `created_by` varchar(255) DEFAULT NULL,
        `jci_date` date DEFAULT NULL,
        `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");

// Create jci_items table if it doesn't exist
    $conn->exec("CREATE TABLE IF NOT EXISTS `jci_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `jci_id` int(11) NOT NULL,
        `job_card_number` varchar(255) DEFAULT NULL,
        `po_product_id` int(11) DEFAULT NULL,
        `product_name` varchar(255) DEFAULT NULL,
        `item_code` varchar(255) DEFAULT NULL,
        `original_po_quantity` decimal(10,2) DEFAULT NULL,
        `quantity` decimal(10,2) DEFAULT NULL,
        `labour_cost` decimal(10,2) DEFAULT NULL,
        `total_amount` decimal(10,2) DEFAULT NULL,
        `delivery_date` date DEFAULT NULL,
        `job_card_date` date DEFAULT NULL,
        `contracture_name` varchar(255) DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
// Add job_card_type to jci_items if it doesn't exist
    $columns = $conn->query("SHOW COLUMNS FROM `jci_items`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('job_card_type', $columns)) {
        $conn->exec("ALTER TABLE `jci_items` ADD COLUMN `job_card_type` VARCHAR(255) NULL DEFAULT NULL AFTER `job_card_date`;");
    }

    // Add job_card_number to jci_items if it doesn't exist
    $columns = $conn->query("SHOW COLUMNS FROM `jci_items`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('job_card_number', $columns)) {
        $conn->exec("ALTER TABLE `jci_items` ADD COLUMN `job_card_number` VARCHAR(255) NULL DEFAULT NULL AFTER `jci_id`;");
    }
    // Add bom_id to jci_main if it doesn't exist
    $columns = $conn->query("SHOW COLUMNS FROM `jci_main`")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('bom_id', $columns)) {
        $conn->exec("ALTER TABLE `jci_main` ADD COLUMN `bom_id` INT(11) NULL DEFAULT NULL AFTER `po_id`;");
    }
        $conn->exec("ALTER TABLE `bom_main` ADD COLUMN `delivery_date` DATE;");
    }

    // Create other bom tables
    $sql = file_get_contents(__DIR__ . '/sql_create_bom_tables.sql');
    $conn->exec($sql);

    echo "Migration completed successfully.";

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}