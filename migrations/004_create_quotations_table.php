<?php

require_once 'Migration.php';

class CreateQuotationsTable extends Migration {
    
    public function up() {
        $this->createTable('quotations', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`supplier_id` INT(11) DEFAULT NULL',
            '`lead_id` INT(11) NOT NULL',
            '`quotation_date` DATE NOT NULL',
            '`quotation_number` VARCHAR(50) NOT NULL',
            '`customer_name` VARCHAR(255) NOT NULL',
            '`customer_email` VARCHAR(255) NOT NULL',
            '`customer_phone` VARCHAR(50) NOT NULL',
            '`delivery_term` VARCHAR(255) DEFAULT NULL',
            '`terms_of_delivery` VARCHAR(255) DEFAULT NULL',
            '`quotation_image` VARCHAR(255) DEFAULT NULL',
            '`approve` TINYINT(1) NOT NULL DEFAULT 0',
            '`locked` TINYINT(1) NOT NULL DEFAULT 0',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            '`is_locked` TINYINT(1) DEFAULT 0',
            '`locked_by` INT(11) DEFAULT NULL',
            '`locked_at` TIMESTAMP NULL DEFAULT NULL',
            'FOREIGN KEY (`lead_id`) REFERENCES `leads`(`id`)',
            'FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`) ON DELETE SET NULL'
        ]);
        
        $this->createIndex('quotations', 'idx_quotation_number', ['quotation_number']);
        $this->createIndex('quotations', 'idx_lead_id', ['lead_id']);
        $this->createIndex('quotations', 'idx_supplier_id', ['supplier_id']);
        $this->createIndex('quotations', 'idx_quotation_date', ['quotation_date']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS quotations");
        echo "✅ Table 'quotations' dropped\n";
    }
}