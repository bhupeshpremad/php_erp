<?php
require_once 'config/config.php';

// Quick fix for duplicate purchase data
global $conn;

try {
    // Clear duplicate invoice/builty data for JCI-2025-0004
    $stmt = $conn->prepare("
        UPDATE purchase_items 
        SET invoice_number = '', invoice_image = '', builty_number = '', builty_image = '' 
        WHERE purchase_main_id = 5 
        AND id NOT IN (
            SELECT min_id FROM (
                SELECT MIN(id) as min_id 
                FROM purchase_items 
                WHERE purchase_main_id = 5 
                AND invoice_number != '' 
                GROUP BY invoice_number
            ) as subquery
        )
        AND invoice_number != ''
    ");
    
    $result = $stmt->execute();
    
    if ($result) {
        echo "✅ Duplicate invoice/builty data cleared successfully!";
        echo "<br>Now check: https://crm.purewood.in/modules/purchase/add.php";
        echo "<br>Select JCI-2025-0004 and verify the fix.";
    } else {
        echo "❌ Failed to clear duplicate data";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>