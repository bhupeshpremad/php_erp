<?php

require_once 'Migration.php';

class CreatePaymentDetailsTable extends Migration {
    
    public function up() {
        $this->createTable('payment_details', [
            '`id` INT(11) AUTO_INCREMENT PRIMARY KEY',
            '`payment_id` INT(11) NOT NULL',
            '`jc_number` VARCHAR(50) DEFAULT NULL',
            '`payment_type` VARCHAR(20) DEFAULT NULL',
            '`cheque_number` VARCHAR(50) DEFAULT NULL',
            '`pd_acc_number` VARCHAR(50) DEFAULT NULL',
            '`ptm_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00',
            '`gst_percent` DECIMAL(6,2) DEFAULT 0.00',
            '`gst_amount` DECIMAL(15,2) DEFAULT 0.00',
            '`total_with_gst` DECIMAL(15,2) DEFAULT 0.00',
            '`payment_invoice_date` DATE DEFAULT NULL',
            '`payment_date` DATE DEFAULT NULL',
            '`payment_category` VARCHAR(20) DEFAULT NULL',
            '`amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00',
            '`created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE'
        ]);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS payment_details");
        echo "✅ Table 'payment_details' dropped\n";
    }
}
