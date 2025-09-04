<?php

require_once 'Migration.php';

class CreateJciDetailsTable extends Migration {
    
    public function up() {
        $this->createTable('jci', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`jci_number` VARCHAR(50) NOT NULL',
            '`job_title` VARCHAR(255) NOT NULL',
            '`customer_name` VARCHAR(255) NOT NULL',
            '`start_date` DATE NOT NULL',
            '`end_date` DATE DEFAULT NULL',
            '`estimated_cost` DECIMAL(15,2) DEFAULT 0.00',
            '`actual_cost` DECIMAL(15,2) DEFAULT 0.00',
            '`status` ENUM("pending", "in_progress", "completed", "cancelled") DEFAULT "pending"',
            '`description` TEXT DEFAULT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('jci', 'idx_jci_number', ['jci_number']);
        $this->createIndex('jci', 'idx_customer_name', ['customer_name']);
        $this->createIndex('jci', 'idx_start_date', ['start_date']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS jci");
        echo "✅ Table 'jci' dropped\n";
    }
}
