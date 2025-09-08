<?php

require_once 'Migration.php';

class AddItemApprovalStatusToPurchaseItems extends Migration {
    
    public function up() {
        $sql = "ALTER TABLE `purchase_items` ADD COLUMN `item_approval_status` VARCHAR(50) DEFAULT 'pending' AFTER `builty_image`;";
        $this->conn->exec($sql);
        echo "✅ Column 'item_approval_status' added to 'purchase_items' table.\n";
    }
    
    public function down() {
        $sql = "ALTER TABLE `purchase_items` DROP COLUMN `item_approval_status`;";
        $this->conn->exec($sql);
        echo "✅ Column 'item_approval_status' dropped from 'purchase_items' table.\n";
    }
}
