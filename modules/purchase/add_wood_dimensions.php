<?php
// Add Wood dimensions columns to purchase_items table
session_start();
include_once __DIR__ . '/../../config/config.php';

global $conn;

try {
    // Check if columns already exist
    $stmt = $conn->query("SHOW COLUMNS FROM purchase_items LIKE 'length_ft'");
    if ($stmt->rowCount() === 0) {
        // Add Wood dimension columns
        $conn->exec("
            ALTER TABLE purchase_items 
            ADD COLUMN length_ft DECIMAL(10,2) DEFAULT NULL AFTER builty_image,
            ADD COLUMN width_ft DECIMAL(10,2) DEFAULT NULL AFTER length_ft,
            ADD COLUMN thickness_inch DECIMAL(10,2) DEFAULT NULL AFTER width_ft
        ");
        echo "Wood dimension columns added successfully!";
    } else {
        echo "Wood dimension columns already exist.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>