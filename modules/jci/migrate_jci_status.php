<?php
include_once __DIR__ . '/../../config/config.php';

try {
    global $conn;
    
    echo "Starting JCI status migration...\n";
    
    // Add columns if they don't exist
    $conn->exec("ALTER TABLE sell_order ADD COLUMN IF NOT EXISTS jci_created TINYINT(1) DEFAULT 0");
    $conn->exec("ALTER TABLE bom_main ADD COLUMN IF NOT EXISTS jci_assigned TINYINT(1) DEFAULT 0");
    $conn->exec("ALTER TABLE po_main ADD COLUMN IF NOT EXISTS jci_assigned TINYINT(1) DEFAULT 0");
    
    echo "Columns added successfully.\n";
    
    // Update existing records
    $stmt = $conn->prepare("UPDATE sell_order so SET jci_created = 1 WHERE EXISTS (SELECT 1 FROM jci_main j WHERE j.sell_order_number = so.sell_order_number)");
    $stmt->execute();
    echo "Updated sell_order jci_created status.\n";
    
    $stmt = $conn->prepare("UPDATE bom_main b SET jci_assigned = 1 WHERE EXISTS (SELECT 1 FROM jci_main j WHERE j.bom_id = b.id)");
    $stmt->execute();
    echo "Updated bom_main jci_assigned status.\n";
    
    $stmt = $conn->prepare("UPDATE po_main p SET jci_assigned = 1 WHERE EXISTS (SELECT 1 FROM jci_main j WHERE j.po_id = p.id)");
    $stmt->execute();
    echo "Updated po_main jci_assigned status.\n";
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>