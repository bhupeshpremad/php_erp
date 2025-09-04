<?php

require_once 'Migration.php';

class CreateQuotationProductsTable extends Migration {
    
    public function up() {
        $this->createTable('quotation_products', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`quotation_id` INT(11) NOT NULL',
            '`item_name` VARCHAR(255) NOT NULL',
            '`item_code` VARCHAR(100) DEFAULT NULL',
            '`description` TEXT DEFAULT NULL',
            '`assembly` VARCHAR(255) DEFAULT NULL',
            '`item_h` DECIMAL(10,2) DEFAULT NULL',
            '`item_w` DECIMAL(10,2) DEFAULT NULL',
            '`item_d` DECIMAL(10,2) DEFAULT NULL',
            '`box_h` DECIMAL(10,2) DEFAULT NULL',
            '`box_w` DECIMAL(10,2) DEFAULT NULL',
            '`box_d` DECIMAL(10,2) DEFAULT NULL',
            '`cbm` DECIMAL(10,3) DEFAULT NULL',
            '`wood_type` VARCHAR(255) DEFAULT NULL',
            '`no_of_packet` INT(11) DEFAULT NULL',
            '`finish` VARCHAR(255) DEFAULT NULL',
            '`iron_gauge` VARCHAR(100) DEFAULT NULL',
            '`mdf_finish` VARCHAR(255) DEFAULT NULL',
            '`quantity` DECIMAL(10,2) NOT NULL',
            '`price_usd` DECIMAL(10,2) NOT NULL',
            '`comments` TEXT DEFAULT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            '`product_image_name` VARCHAR(255) DEFAULT NULL',
            '`total_price_usd` DECIMAL(10,2) NOT NULL DEFAULT 0.00',
            'FOREIGN KEY (`quotation_id`) REFERENCES `quotations`(`id`)'
        ]);
        
        $this->createIndex('quotation_products', 'idx_quotation_id', ['quotation_id']);
        $this->createIndex('quotation_products', 'idx_item_code', ['item_code']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS quotation_products");
        echo "✅ Table 'quotation_products' dropped\n";
    }
}