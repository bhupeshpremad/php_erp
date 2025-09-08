<?php

require_once 'Migration.php';

class CreatePurchaseDetailsTable extends Migration {
    
    public function up() {
        $this->createTable('purchase', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`purchase_number` VARCHAR(50) NOT NULL',
            '`supplier_name` VARCHAR(255) NOT NULL',
            '`supplier_email` VARCHAR(255) DEFAULT NULL',
            '`supplier_phone` VARCHAR(20) DEFAULT NULL',
            '`purchase_date` DATE NOT NULL',
            '`total_amount` DECIMAL(15,2) DEFAULT 0.00',
            '`status` ENUM("pending", "approved", "received", "cancelled") DEFAULT "pending"',
            '`notes` TEXT DEFAULT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('purchase', 'idx_purchase_number', ['purchase_number']);
        $this->createIndex('purchase', 'idx_supplier_name', ['supplier_name']);
        $this->createIndex('purchase', 'idx_purchase_date', ['purchase_date']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS purchase");
        echo "✅ Table 'purchase' dropped\n";
    }
}
