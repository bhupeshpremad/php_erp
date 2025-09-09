<?php
require_once __DIR__ . '/../../config/config.php';

// Fix duplicate purchase data for JOB-2025-0004-1
// This script will clean up duplicate invoice/builty entries

global $conn;

try {
    echo "<h3>Fixing Duplicate Purchase Data for JOB-2025-0004-1</h3>\n";
    
    // Get purchase_main_id for JCI-2025-0004
    $stmt = $conn->prepare("SELECT id FROM purchase_main WHERE jci_number = ?");
    $stmt->execute(['JCI-2025-0004']);
    $purchase_main = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$purchase_main) {
        echo "No purchase record found for JCI-2025-0004\n";
        exit;
    }
    
    $purchase_main_id = $purchase_main['id'];
    echo "Found purchase_main_id: $purchase_main_id\n";
    
    // Get all purchase items for this JCI
    $stmt = $conn->prepare("SELECT * FROM purchase_items WHERE purchase_main_id = ? ORDER BY id");
    $stmt->execute([$purchase_main_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Found " . count($items) . " purchase items\n";
    
    // Group items by invoice number
    $invoice_groups = [];
    foreach ($items as $item) {
        $invoice = $item['invoice_number'] ?? '';
        if ($invoice !== '') {
            if (!isset($invoice_groups[$invoice])) {
                $invoice_groups[$invoice] = [];
            }
            $invoice_groups[$invoice][] = $item;
        }
    }
    
    echo "\nInvoice groups found:\n";
    foreach ($invoice_groups as $invoice => $group_items) {
        echo "Invoice: $invoice - " . count($group_items) . " items\n";
        
        if (count($group_items) > 1) {
            echo "  -> Duplicate invoice found! Items:\n";
            foreach ($group_items as $item) {
                echo "    ID: {$item['id']}, Product: {$item['product_name']}, Qty: {$item['assigned_quantity']}\n";
            }
            
            // Keep only the first item with invoice/builty data, clear others
            $keep_item = $group_items[0];
            echo "  -> Keeping invoice data for item ID: {$keep_item['id']}\n";
            
            for ($i = 1; $i < count($group_items); $i++) {
                $clear_item = $group_items[$i];
                echo "  -> Clearing duplicate invoice data for item ID: {$clear_item['id']}\n";
                
                // Clear invoice and builty data for duplicate items
                $update_stmt = $conn->prepare("UPDATE purchase_items SET 
                    invoice_number = '', 
                    invoice_image = '', 
                    builty_number = '', 
                    builty_image = '' 
                    WHERE id = ?");
                $update_stmt->execute([$clear_item['id']]);
            }
        }
    }
    
    echo "\n✅ Duplicate purchase data cleanup completed!\n";
    echo "Please refresh the Job Card page to see the changes.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>