<?php
// Quick database setup for purchase module
session_start();
include_once __DIR__ . '/../../config/config.php';

echo "<h3>Purchase Module Database Setup</h3>";

global $conn;

try {
    // Check if created_by column exists
    $stmt = $conn->query("SHOW COLUMNS FROM purchase_main LIKE 'created_by'");
    if ($stmt->rowCount() == 0) {
        echo "<p>Adding created_by column...</p>";
        $conn->exec("ALTER TABLE purchase_main ADD COLUMN created_by INT(11) NULL AFTER bom_number");
        $conn->exec("UPDATE purchase_main SET created_by = 1 WHERE created_by IS NULL");
        echo "<p style='color: green;'>✓ created_by column added</p>";
    } else {
        echo "<p style='color: blue;'>✓ created_by column exists</p>";
    }
    
    // Check jci_items table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'jci_items'");
    if ($stmt->rowCount() == 0) {
        echo "<p style='color: orange;'>⚠ jci_items table missing - creating basic structure</p>";
        $conn->exec("CREATE TABLE IF NOT EXISTS jci_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            jci_number VARCHAR(50),
            job_card_number VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        echo "<p style='color: green;'>✓ jci_items table created</p>";
    } else {
        echo "<p style='color: blue;'>✓ jci_items table exists</p>";
    }
    
    echo "<h4 style='color: green;'>Setup Complete!</h4>";
    echo "<p><a href='index.php'>Go to Purchase Module</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>