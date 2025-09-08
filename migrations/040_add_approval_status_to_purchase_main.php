<?php

require_once 'Migration.php';

class AddApprovalStatusToPurchaseMain extends Migration {
    
    public function up() {
        $sql = "ALTER TABLE `purchase_main` ADD COLUMN `approval_status` VARCHAR(50) DEFAULT 'pending' AFTER `sell_order_number`;";
        $this->conn->exec($sql);
        echo "✅ Column 'approval_status' added to 'purchase_main' table.\n";
    }
    
    public function down() {
        $sql = "ALTER TABLE `purchase_main` DROP COLUMN `approval_status`;";
        $this->conn->exec($sql);
        echo "✅ Column 'approval_status' dropped from 'purchase_main' table.\n";
    }
}


