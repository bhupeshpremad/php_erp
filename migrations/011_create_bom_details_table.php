<?php

require_once 'Migration.php';

class CreateBomDetailsTable extends Migration {
    
    public function up() {
        $this->createTable('bom', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`bom_number` VARCHAR(50) NOT NULL',
            '`product_name` VARCHAR(255) NOT NULL',
            '`product_code` VARCHAR(100) DEFAULT NULL',
            '`quantity` DECIMAL(10,2) NOT NULL',
            '`unit` VARCHAR(50) DEFAULT NULL',
            '`material_cost` DECIMAL(15,2) DEFAULT 0.00',
            '`labor_cost` DECIMAL(15,2) DEFAULT 0.00',
            '`overhead_cost` DECIMAL(15,2) DEFAULT 0.00',
            '`total_cost` DECIMAL(15,2) GENERATED ALWAYS AS (`material_cost` + `labor_cost` + `overhead_cost`) STORED',
            '`status` ENUM("active", "inactive") DEFAULT "active"',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('bom', 'idx_bom_number', ['bom_number']);
        $this->createIndex('bom', 'idx_product_name', ['product_name']);
        $this->createIndex('bom', 'idx_product_code', ['product_code']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS bom");
        echo "✅ Table 'bom' dropped\n";
    }
}
