<?php

require_once 'Migration.php';

class CreateJciItemsTable extends Migration {
    
    public function up() {
        $this->createTable('jci_items', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`jci_id` INT(11) NOT NULL',
            '`job_card_number` VARCHAR(255) DEFAULT NULL',
            '`po_product_id` INT(11) DEFAULT NULL',
            '`product_name` VARCHAR(255) DEFAULT NULL',
            '`item_code` VARCHAR(100) DEFAULT NULL',
            '`original_po_quantity` DECIMAL(10,2) DEFAULT NULL',
            '`labour_cost` DECIMAL(10,2) NOT NULL',
            '`quantity` INT(11) NOT NULL',
            '`total_amount` DECIMAL(10,2) NOT NULL',
            '`delivery_date` DATE NOT NULL',
            '`job_card_date` DATE DEFAULT NULL',
            '`job_card_type` ENUM("Contracture", "In-House") DEFAULT NULL',
            '`contracture_name` VARCHAR(255) DEFAULT NULL',
            'FOREIGN KEY (`jci_id`) REFERENCES `jci_main` (`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS jci_items");
        echo "✅ Table 'jci_items' dropped\n";
    }
}
