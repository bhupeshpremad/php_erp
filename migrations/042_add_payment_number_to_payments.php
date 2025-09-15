<?php

require_once 'Migration.php';

class AddPaymentNumberToPayments extends Migration {
    
    public function up() {
        $sql = "ALTER TABLE `payments` ADD COLUMN `payment_number` VARCHAR(100) UNIQUE AFTER `id`;";
        $this->conn->exec($sql);
        echo "✅ Column 'payment_number' added to 'payments' table.\n";
    }
    
    public function down() {
        $sql = "ALTER TABLE `payments` DROP COLUMN `payment_number`;";
        $this->conn->exec($sql);
        echo "✅ Column 'payment_number' dropped from 'payments' table.\n";
    }
}