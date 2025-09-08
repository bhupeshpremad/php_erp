<?php

require_once 'Migration.php';

class CreateBomHardwareTable extends Migration {
    
    public function up() {
        $this->createTable('bom_hardware', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`bom_main_id` INT(11) NOT NULL',
            '`itemname` VARCHAR(255) DEFAULT NULL',
            '`quantity` INT(11) DEFAULT NULL',
            '`price` DECIMAL(10,2) DEFAULT NULL',
            '`totalprice` DECIMAL(10,2) DEFAULT NULL',
            'FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS bom_hardware");
        echo "✅ Table 'bom_hardware' dropped\n";
    }
}
