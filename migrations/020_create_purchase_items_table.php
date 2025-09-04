<?php

require_once 'Migration.php';

class CreatePurchaseItemsTable extends Migration {
    
    public function up() {
        $this->createTable('purchase_items', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`purchase_main_id` INT(11) NOT NULL',
            '`supplier_name` VARCHAR(100) NOT NULL',
            '`product_type` VARCHAR(100) NOT NULL',
            '`product_name` VARCHAR(100) NOT NULL',
            '`job_card_number` VARCHAR(50) NOT NULL',
            '`assigned_quantity` DECIMAL(10,3) NOT NULL DEFAULT 0.000',
            '`price` DECIMAL(10,2) NOT NULL',
            '`total` DECIMAL(10,2) NOT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            '`date` DATE DEFAULT NULL',
            '`invoice_number` VARCHAR(100) DEFAULT NULL',
            '`amount` DECIMAL(10,2) DEFAULT NULL',
            '`invoice_image` VARCHAR(255) DEFAULT NULL',
            '`builty_number` VARCHAR(100) DEFAULT NULL',
            '`builty_image` VARCHAR(255) DEFAULT NULL',
            'FOREIGN KEY (`purchase_main_id`) REFERENCES `purchase_main`(`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS purchase_items");
        echo "✅ Table 'purchase_items' dropped\n";
    }
}
