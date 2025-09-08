<?php
// ... existing code ...
class Migration_045_alter_payments_payment_number_to_null extends Migration
{
    public function up()
    {
        // Check if payments table exists first
        $stmt = $this->conn->query("SHOW TABLES LIKE 'payments'");
        if ($stmt->rowCount() == 0) {
            echo "⚠️ Skipping: payments table doesn't exist yet\n";
            return;
        }
        
        // Check if index exists before dropping
        $stmt = $this->conn->query("SHOW INDEX FROM payments WHERE Key_name = 'payment_number'");
        if ($stmt->rowCount() > 0) {
            $this->conn->exec("ALTER TABLE `payments` DROP INDEX `payment_number`;");
        }
        
        $this->conn->exec("ALTER TABLE `payments` CHANGE `payment_number` `payment_number` VARCHAR(50) NULL DEFAULT NULL;");
        echo "✅ Updated payments.payment_number to allow NULL\n";
    }

    public function down()
    {
        // Re-add the unique constraint and NOT NULL if rolling back
        $this->conn->exec("ALTER TABLE `payments` CHANGE `payment_number` `payment_number` VARCHAR(50) NOT NULL;");
        $this->conn->exec("ALTER TABLE `payments` ADD UNIQUE KEY `payment_number` (`payment_number`);");
    }
}

