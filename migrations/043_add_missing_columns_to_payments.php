<?php

require_once 'Migration.php';

class AddMissingColumnsToPayments extends Migration {
    
    public function up() {
        // Add all missing columns to payments table
        $columns = [
            "ALTER TABLE `payments` ADD COLUMN `payment_type` VARCHAR(50) DEFAULT 'made' AFTER `payment_number`;",
            "ALTER TABLE `payments` ADD COLUMN `party_name` VARCHAR(255) AFTER `payment_type`;",
            "ALTER TABLE `payments` ADD COLUMN `amount` DECIMAL(15,2) DEFAULT 0 AFTER `party_name`;",
            "ALTER TABLE `payments` ADD COLUMN `payment_date` DATE AFTER `amount`;",
            "ALTER TABLE `payments` ADD COLUMN `status` VARCHAR(50) DEFAULT 'pending' AFTER `payment_date`;",
            "ALTER TABLE `payments` ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `status`;",
            "ALTER TABLE `payments` ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;"
        ];
        
        foreach ($columns as $sql) {
            try {
                $this->conn->exec($sql);
                echo "✅ Executed: " . substr($sql, 0, 50) . "...\n";
            } catch (Exception $e) {
                if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                    echo "⚠️ Column already exists: " . substr($sql, 0, 50) . "...\n";
                } else {
                    throw $e;
                }
            }
        }
    }
    
    public function down() {
        $columns = ['payment_type', 'party_name', 'amount', 'payment_date', 'status', 'created_at', 'updated_at'];
        foreach ($columns as $column) {
            $sql = "ALTER TABLE `payments` DROP COLUMN `$column`;";
            $this->conn->exec($sql);
        }
        echo "✅ All columns dropped from 'payments' table.\n";
    }
}