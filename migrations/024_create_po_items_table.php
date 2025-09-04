<?php

require_once 'Migration.php';

class CreatePoItemsTable extends Migration {
    
    public function up() {
        $this->createTable('po_items', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`po_id` INT(11) NOT NULL',
            '`product_code` VARCHAR(100) NOT NULL',
            '`product_name` VARCHAR(255) NOT NULL',
            '`quantity` DECIMAL(10,2) NOT NULL',
            '`unit` VARCHAR(50) NOT NULL',
            '`price` DECIMAL(10,2) NOT NULL',
            '`total_amount` DECIMAL(10,2) NOT NULL',
            '`product_image` VARCHAR(255) DEFAULT NULL',
            '`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS po_items");
        echo "✅ Table 'po_items' dropped\n";
    }
}
