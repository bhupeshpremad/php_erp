<?php
require_once 'config/config.php';

try {
    // Add excel_file column to quotations table
    $sql = "ALTER TABLE quotations ADD COLUMN excel_file VARCHAR(255) DEFAULT NULL";
    $conn->exec($sql);
    echo "✅ Excel file column added successfully!";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✅ Excel file column already exists!";
    } else {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>