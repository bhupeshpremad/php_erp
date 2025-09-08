<?php

require_once 'Migration.php';

class CreatePoDetailsTable extends Migration {
    
    public function up() {
        $this->createTable('po', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`po_number` VARCHAR(50) NOT NULL',
            '`supplier_name` VARCHAR(255) NOT NULL',
            '`po_date` DATE NOT NULL',
            '`delivery_date` DATE DEFAULT NULL',
            '`total_amount` DECIMAL(15,2) DEFAULT 0.00',
            '`status` ENUM("draft", "sent", "approved", "delivered", "cancelled") DEFAULT "draft"',
            '`terms_conditions` TEXT DEFAULT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            '`is_locked` TINYINT(1) DEFAULT 0',
            '`locked_by` INT(11) DEFAULT NULL',
            '`locked_at` TIMESTAMP NULL DEFAULT NULL'
        ]);
        
        $this->createIndex('po', 'idx_po_number', ['po_number']);
        $this->createIndex('po', 'idx_supplier_name', ['supplier_name']);
        $this->createIndex('po', 'idx_po_date', ['po_date']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS po");
        echo "✅ Table 'po' dropped\n";
    }
}
