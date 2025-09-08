<?php

require_once 'Migration.php';

class CreateCustomersTable extends Migration {
    
    public function up() {
        $this->createTable('customers', [
            '`id` INT AUTO_INCREMENT PRIMARY KEY',
            '`customer_name` VARCHAR(255) NOT NULL',
            '`customer_email` VARCHAR(255) DEFAULT NULL',
            '`customer_phone` VARCHAR(20) DEFAULT NULL',
            '`company_name` VARCHAR(255) DEFAULT NULL',
            '`address` TEXT DEFAULT NULL',
            '`city` VARCHAR(100) DEFAULT NULL',
            '`state` VARCHAR(100) DEFAULT NULL',
            '`country` VARCHAR(100) DEFAULT NULL',
            '`postal_code` VARCHAR(20) DEFAULT NULL',
            '`status` ENUM("active", "inactive") DEFAULT "active"',
            '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('customers', 'idx_customer_email', ['customer_email']);
        $this->createIndex('customers', 'idx_customer_name', ['customer_name']);
        $this->createIndex('customers', 'idx_company_name', ['company_name']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS customers");
        echo "✅ Table 'customers' dropped\n";
    }
}