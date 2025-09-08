<?php

require_once 'Migration.php';

class CreatePoMainTable extends Migration {
    
    public function up() {
        $this->createTable('po_main', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`po_number` VARCHAR(100) NOT NULL',
            '`client_name` VARCHAR(255) DEFAULT NULL',
            '`prepared_by` VARCHAR(255) DEFAULT NULL',
            '`order_date` DATE DEFAULT NULL',
            '`delivery_date` DATE DEFAULT NULL',
            '`sell_order_id` INT(11) DEFAULT NULL',
            '`status` VARCHAR(50) NOT NULL DEFAULT \'Pending\'',
            '`is_locked` TINYINT(1) NOT NULL DEFAULT 0',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            '`sell_order_number` VARCHAR(50) DEFAULT NULL',
            '`jci_number` VARCHAR(50) DEFAULT NULL',
            '`jci_assigned` TINYINT(1) DEFAULT 0',
            'KEY `idx_po_sell_order_number` (`sell_order_number`)'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS po_main");
        echo "✅ Table 'po_main' dropped\n";
    }
}
