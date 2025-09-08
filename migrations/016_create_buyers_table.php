<?php

require_once 'Migration.php';

class CreateBuyersTable extends Migration {
    
    public function up() {
        $this->createTable('buyers', [
            '`id` INT AUTO_INCREMENT PRIMARY KEY',
            '`company_name` VARCHAR(255) NOT NULL',
            '`contact_person_name` VARCHAR(255) NOT NULL',
            '`contact_person_email` VARCHAR(255) UNIQUE NOT NULL',
            '`contact_person_phone` VARCHAR(20) NOT NULL',
            '`company_address` TEXT NOT NULL',
            '`password` VARCHAR(255) NOT NULL',
            '`status` ENUM("pending", "approved", "rejected") DEFAULT "pending"',
            '`approved_by` INT DEFAULT NULL',
            '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('buyers', 'idx_email', ['contact_person_email']);
        $this->createIndex('buyers', 'idx_status', ['status']);
        $this->createIndex('buyers', 'idx_company', ['company_name']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS buyers");
        echo "✅ Table 'buyers' dropped\n";
    }
}