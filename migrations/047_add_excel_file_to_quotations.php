<?php
/**
 * Migration: Add excel_file column to quotations table
 * This allows storing the original Excel file used to create the quotation
 */

try {
    // Add excel_file column to quotations table
    $sql = "ALTER TABLE quotations ADD COLUMN excel_file VARCHAR(255) NULL AFTER terms_of_delivery";
    $conn->exec($sql);
    
    echo "✅ Added excel_file column to quotations table<br>\n";
    
} catch (PDOException $e) {
    // Check if column already exists
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "ℹ️ excel_file column already exists in quotations table<br>\n";
    } else {
        throw $e;
    }
}
?>