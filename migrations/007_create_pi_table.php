<?php

require_once 'Migration.php';

class CreatePiTable extends Migration {
    
    public function up() {
        $this->createTable('pi', [
            '`pi_id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`quotation_id` INT(11) NOT NULL',
            '`quotation_number` VARCHAR(50) NOT NULL',
            '`pi_number` VARCHAR(20) UNIQUE NOT NULL',
            '`status` VARCHAR(20) NOT NULL DEFAULT \'Generated\'',
            '`inspection` TEXT DEFAULT NULL',
            '`date_of_pi_raised` DATE DEFAULT NULL',
            '`sample_approval_date` DATE DEFAULT NULL',
            '`detailed_seller_address` TEXT DEFAULT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (`quotation_id`) REFERENCES `quotations`(`id`) ON DELETE CASCADE'
        ]);
        
        $this->createIndex('pi', 'idx_pi_number', ['pi_number']);
        $this->createIndex('pi', 'idx_quotation_id', ['quotation_id']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS pi");
        echo "✅ Table 'pi' dropped\n";
    }
}