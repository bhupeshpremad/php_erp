<?php

require_once 'Migration.php';

class CreateBomFactoryTable extends Migration {
    
    public function up() {
        $this->createTable('bom_factory', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`bom_main_id` INT(11) NOT NULL',
            '`total_amount` DECIMAL(10,2) DEFAULT NULL',
            '`factory_percentage` DECIMAL(5,2) DEFAULT 15.00',
            '`factory_cost` DECIMAL(10,2) DEFAULT NULL',
            '`updated_total` DECIMAL(10,2) DEFAULT NULL',
            'FOREIGN KEY (`bom_main_id`) REFERENCES `bom_main` (`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS bom_factory");
        echo "✅ Table 'bom_factory' dropped\n";
    }
}
