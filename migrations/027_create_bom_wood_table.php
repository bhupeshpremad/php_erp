<?php

require_once 'Migration.php';

class CreateBomWoodTable extends Migration {
    
    public function up() {
        $this->createTable('bom_wood', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`bom_main_id` INT(11) NOT NULL',
            '`woodtype` VARCHAR(255) DEFAULT NULL',
            '`length_ft` DECIMAL(10,2) DEFAULT NULL',
            '`width_ft` DECIMAL(10,2) DEFAULT NULL',
            '`thickness_inch` DECIMAL(10,2) DEFAULT NULL',
            '`quantity` INT(11) DEFAULT NULL',
            '`price` DECIMAL(10,2) DEFAULT NULL',
            '`cft` DECIMAL(10,2) DEFAULT NULL',
            '`total` DECIMAL(10,2) DEFAULT NULL',
            'FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS bom_wood");
        echo "✅ Table 'bom_wood' dropped\n";
    }
}
