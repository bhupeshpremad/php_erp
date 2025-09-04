<?php

require_once 'Migration.php';

class CreateBomPlynydfTable extends Migration {
    
    public function up() {
        $this->createTable('bom_plynydf', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`bom_main_id` INT(11) NOT NULL',
            '`quantity` INT(11) DEFAULT NULL',
            '`width` DECIMAL(10,2) DEFAULT NULL',
            '`length` DECIMAL(10,2) DEFAULT NULL',
            '`price` DECIMAL(10,2) DEFAULT NULL',
            '`total` DECIMAL(10,2) DEFAULT NULL',
            'FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS bom_plynydf");
        echo "✅ Table 'bom_plynydf' dropped\n";
    }
}
