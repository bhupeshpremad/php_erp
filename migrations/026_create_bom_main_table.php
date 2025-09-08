<?php

require_once 'Migration.php';

class CreateBomMainTable extends Migration {
    
    public function up() {
        $this->createTable('bom_main', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`bom_number` VARCHAR(50) NOT NULL UNIQUE',
            '`costing_sheet_number` VARCHAR(255) NOT NULL',
            '`client_name` VARCHAR(255) NOT NULL',
            '`prepared_by` VARCHAR(255) NOT NULL',
            '`order_date` DATE NOT NULL',
            '`delivery_date` DATE DEFAULT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            '`labour_cost` DECIMAL(10,2) DEFAULT NULL',
            '`factory_cost` DECIMAL(10,2) DEFAULT NULL',
            '`margin` DECIMAL(10,2) DEFAULT NULL',
            '`grand_total_amount` DECIMAL(10,2) DEFAULT NULL',
            '`jci_assigned` TINYINT(1) DEFAULT 0'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS bom_main");
        echo "✅ Table 'bom_main' dropped\n";
    }
}
