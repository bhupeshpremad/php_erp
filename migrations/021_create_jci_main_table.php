<?php

require_once 'Migration.php';

class CreateJciMainTable extends Migration {
    
    public function up() {
        $this->createTable('jci_main', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`jci_number` VARCHAR(255) NOT NULL',
            '`po_id` INT(11) DEFAULT NULL',
            '`bom_id` INT(11) DEFAULT NULL',
            '`jci_type` ENUM("Contracture", "In-House") NOT NULL',
            '`created_by` VARCHAR(255) NOT NULL',
            '`jci_date` DATE NOT NULL',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            '`sell_order_number` VARCHAR(50) DEFAULT NULL',
            '`purchase_created` TINYINT(1) DEFAULT 0',
            '`payment_completed` TINYINT(1) DEFAULT 0',
            'UNIQUE KEY `jci_number` (`jci_number`)',
            'KEY `fk_jci_po` (`po_id`)',
            'KEY `idx_jci_sell_order_number` (`sell_order_number`)'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS jci_main");
        echo "✅ Table 'jci_main' dropped\n";
    }
}
