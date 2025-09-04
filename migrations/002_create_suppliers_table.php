<?php

require_once 'Migration.php';

class CreateSuppliersTable extends Migration {
    
    public function up() {
        $this->createTable('suppliers', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`company_name` VARCHAR(255) NOT NULL',
            '`company_address` TEXT NOT NULL',
            '`country` VARCHAR(100) NOT NULL',
            '`state` VARCHAR(100) NOT NULL',
            '`city` VARCHAR(100) NOT NULL',
            '`zip_code` VARCHAR(20) NOT NULL',
            '`gstin` VARCHAR(15) UNIQUE NOT NULL',
            '`contact_person_name` VARCHAR(255) NOT NULL',
            '`contact_person_phone` VARCHAR(20) NOT NULL',
            '`contact_person_email` VARCHAR(255) UNIQUE NOT NULL',
            '`contract_signed` ENUM("yes", "no") DEFAULT "no"',
            '`password` VARCHAR(255) NOT NULL',
            '`email_verified` TINYINT(1) DEFAULT 0',
            '`verification_token` VARCHAR(100) DEFAULT NULL',
            '`status` ENUM("active", "inactive", "pending") DEFAULT "pending"',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('suppliers', 'idx_email', ['contact_person_email']);
        $this->createIndex('suppliers', 'idx_status', ['status']);
        $this->createIndex('suppliers', 'idx_company', ['company_name']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS suppliers");
        echo "✅ Table 'suppliers' dropped\n";
    }
}