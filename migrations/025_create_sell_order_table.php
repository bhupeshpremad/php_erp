<?php

require_once 'Migration.php';

class CreateSellOrderTable extends Migration {
    
    public function up() {
        $this->createTable('sell_order', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`sell_order_number` VARCHAR(50) NOT NULL UNIQUE',
            '`po_id` INT(11) NOT NULL',
            '`created_at` DATETIME DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            '`jci_created` TINYINT(1) DEFAULT 0',
            'FOREIGN KEY (`po_id`) REFERENCES `po_main` (`id`)'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS sell_order");
        echo "✅ Table 'sell_order' dropped\n";
    }
}
