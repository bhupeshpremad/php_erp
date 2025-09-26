<?php

require_once 'Migration.php';

class AddMultiSupplierSupport extends Migration {
    
    public function up() {
        // Add supplier_sequence to track multiple suppliers for same product
        $sql1 = "ALTER TABLE `purchase_items` ADD COLUMN `supplier_sequence` INT DEFAULT 1 AFTER `supplier_name`;";
        $this->conn->exec($sql1);
        echo "✅ Column 'supplier_sequence' added to 'purchase_items' table.\n";
        
        // Add remaining_quantity to track what's left to purchase
        $sql2 = "ALTER TABLE `purchase_items` ADD COLUMN `remaining_quantity` DECIMAL(10,3) DEFAULT 0.000 AFTER `assigned_quantity`;";
        $this->conn->exec($sql2);
        echo "✅ Column 'remaining_quantity' added to 'purchase_items' table.\n";
        
        // Add composite index for better performance on multi-supplier queries
        $sql3 = "ALTER TABLE `purchase_items` ADD INDEX `idx_multi_supplier` (`purchase_main_id`, `job_card_number`, `product_name`, `supplier_sequence`);";
        $this->conn->exec($sql3);
        echo "✅ Index 'idx_multi_supplier' added to 'purchase_items' table.\n";
        
        // Update existing records to have supplier_sequence = 1
        $sql4 = "UPDATE `purchase_items` SET `supplier_sequence` = 1 WHERE `supplier_sequence` IS NULL;";
        $this->conn->exec($sql4);
        echo "✅ Updated existing records with supplier_sequence = 1.\n";
    }
    
    public function down() {
        $sql1 = "ALTER TABLE `purchase_items` DROP INDEX `idx_multi_supplier`;";
        $this->conn->exec($sql1);
        echo "✅ Index 'idx_multi_supplier' dropped from 'purchase_items' table.\n";
        
        $sql2 = "ALTER TABLE `purchase_items` DROP COLUMN `remaining_quantity`;";
        $this->conn->exec($sql2);
        echo "✅ Column 'remaining_quantity' dropped from 'purchase_items' table.\n";
        
        $sql3 = "ALTER TABLE `purchase_items` DROP COLUMN `supplier_sequence`;";
        $this->conn->exec($sql3);
        echo "✅ Column 'supplier_sequence' dropped from 'purchase_items' table.\n";
    }
}
?>