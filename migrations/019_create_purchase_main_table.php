<?php

require_once 'Migration.php';

class CreatePurchaseMainTable extends Migration {
    
    public function up() {
        $this->createTable('purchase_main', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`po_number` VARCHAR(50) NOT NULL',
            '`jci_number` VARCHAR(50) NOT NULL',
            '`sell_order_number` VARCHAR(50) NOT NULL',
            '`bom_number` VARCHAR(50) NOT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS purchase_main");
        echo "✅ Table 'purchase_main' dropped\n";
    }
}
