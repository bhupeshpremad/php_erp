<?php

require_once 'Migration.php';

class CreateSoDetailsTable extends Migration {
    
    public function up() {
        $this->createTable('so', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`so_number` VARCHAR(50) NOT NULL',
            '`customer_name` VARCHAR(255) NOT NULL',
            '`customer_email` VARCHAR(255) DEFAULT NULL',
            '`so_date` DATE NOT NULL',
            '`delivery_date` DATE DEFAULT NULL',
            '`total_amount` DECIMAL(15,2) DEFAULT 0.00',
            '`status` ENUM("draft", "confirmed", "processing", "shipped", "delivered", "cancelled") DEFAULT "draft"',
            '`notes` TEXT DEFAULT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('so', 'idx_so_number', ['so_number']);
        $this->createIndex('so', 'idx_customer_name', ['customer_name']);
        $this->createIndex('so', 'idx_so_date', ['so_date']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS so");
        echo "✅ Table 'so' dropped\n";
    }
}
