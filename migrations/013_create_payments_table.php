<?php

require_once 'Migration.php';

class CreatePaymentsTable extends Migration {
    
    public function up() {
        $this->createTable('payments', [
            '`id` INT AUTO_INCREMENT PRIMARY KEY',
            '`payment_number` VARCHAR(50) UNIQUE NOT NULL',
            '`payment_type` ENUM("received", "made") NOT NULL',
            '`party_name` VARCHAR(255) NOT NULL',
            '`amount` DECIMAL(15,2) NOT NULL',
            '`payment_method` ENUM("cash", "cheque", "bank_transfer", "online") DEFAULT "cash"',
            '`payment_date` DATE NOT NULL',
            '`reference_number` VARCHAR(100) DEFAULT NULL',
            '`description` TEXT DEFAULT NULL',
            '`status` ENUM("pending", "completed", "cancelled") DEFAULT "pending"',
            '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('payments', 'idx_payment_number', ['payment_number']);
        $this->createIndex('payments', 'idx_party_name', ['party_name']);
        $this->createIndex('payments', 'idx_payment_date', ['payment_date']);
        $this->createIndex('payments', 'idx_payment_type', ['payment_type']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS payments");
        echo "✅ Table 'payments' dropped\n";
    }
}