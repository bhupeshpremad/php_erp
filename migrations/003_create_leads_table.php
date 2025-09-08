<?php

require_once 'Migration.php';

class CreateLeadsTable extends Migration {
    
    public function up() {
        $this->createTable('leads', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`lead_number` VARCHAR(50) NOT NULL',
            '`entry_date` DATE NOT NULL',
            '`lead_source` VARCHAR(255) DEFAULT NULL',
            '`company_name` VARCHAR(255) DEFAULT NULL',
            '`contact_name` VARCHAR(255) NOT NULL',
            '`contact_phone` VARCHAR(50) DEFAULT NULL',
            '`contact_email` VARCHAR(255) NOT NULL',
            '`country` VARCHAR(100) NOT NULL',
            '`state` VARCHAR(100) NOT NULL',
            '`city` VARCHAR(100) DEFAULT NULL',
            '`created_status` VARCHAR(50) DEFAULT \'new\'',
            '`approve` TINYINT(1) DEFAULT 0',
            '`status` VARCHAR(50) DEFAULT \'active\'',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('leads', 'idx_lead_number', ['lead_number']);
        $this->createIndex('leads', 'idx_status', ['status']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS leads");
        echo "✅ Table 'leads' dropped\n";
    }
}