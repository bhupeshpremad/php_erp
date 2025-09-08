<?php

require_once 'Migration.php';

class CreateBomGlowTable extends Migration {
    
    public function up() {
        $this->createTable('bom_glow', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`bom_main_id` INT(11) NOT NULL',
            '`glowtype` VARCHAR(255) DEFAULT NULL',
            '`quantity` DECIMAL(10,3) DEFAULT NULL',
            '`price` DECIMAL(10,2) DEFAULT NULL',
            '`total` DECIMAL(10,2) DEFAULT NULL',
            'FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS bom_glow");
        echo "✅ Table 'bom_glow' dropped\n";
    }
}
