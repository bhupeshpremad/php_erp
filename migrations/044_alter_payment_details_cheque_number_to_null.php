<?php

require_once __DIR__ . '/Migration.php';

class AlterPaymentDetailsChequeNumberToNull extends Migration
{
    public function up()
    {
        // Check if payment_details table exists first
        $stmt = $this->conn->query("SHOW TABLES LIKE 'payment_details'");
        if ($stmt->rowCount() == 0) {
            echo "⚠️ Skipping: payment_details table doesn't exist yet\n";
            return;
        }
        
        // Check if a unique index exists on cheque_number and drop it if it does
        try {
            $stmt = $this->conn->prepare("SHOW INDEX FROM `payment_details` WHERE Column_name = 'cheque_number' AND Non_unique = 0");
            $stmt->execute();
            $uniqueIndexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($uniqueIndexes as $index) {
                $indexName = $index['Key_name'];
                if ($indexName !== 'PRIMARY') { // Don't drop the primary key
                    $sql_drop_unique = "ALTER TABLE `payment_details` DROP INDEX `{$indexName}`";
                    $this->conn->exec($sql_drop_unique);
                    echo "Dropped unique index '{$indexName}' on `cheque_number` in `payment_details` table.\n";
                }
            }
        } catch (PDOException $e) {
            // Log error but don't stop migration if index doesn't exist or can't be dropped
            error_log("Error checking/dropping unique index on cheque_number: " . $e->getMessage());
            echo "Warning: Could not check/drop unique index on cheque_number. Error: " . $e->getMessage() . "\n";
        }

        // Alter column to be nullable
        $sql_alter = "ALTER TABLE `payment_details` MODIFY COLUMN `cheque_number` VARCHAR(255) NULL;";
        
        try {
            // Check if column is already nullable
            $stmt = $this->conn->prepare("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_details' AND COLUMN_NAME = 'cheque_number'");
            $stmt->execute();
            $isNullable = $stmt->fetchColumn();

            if ($isNullable === 'NO') {
                $this->conn->exec($sql_alter);
                echo "Column `cheque_number` in `payment_details` table now allows NULL values.\n";
            } else {
                echo "Column `cheque_number` in `payment_details` table already allows NULL values, skipping alter.\n";
            }
        } catch (PDOException $e) {
            echo "Error altering `cheque_number` in `payment_details` table to allow NULL: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function down()
    {
        // Revert column to NOT NULL (if desired, though not strictly necessary for functionality after fix)
        // Note: Re-adding a UNIQUE constraint would require careful consideration of existing NULLs
        $sql_revert = "ALTER TABLE `payment_details` MODIFY COLUMN `cheque_number` VARCHAR(255) NOT NULL;";
        try {
            // Check if column allows NULL before reverting
            $stmt = $this->conn->prepare("SELECT IS_NULLABLE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'payment_details' AND COLUMN_NAME = 'cheque_number'");
            $stmt->execute();
            $isNullable = $stmt->fetchColumn();

            if ($isNullable === 'YES') {
                $this->conn->exec($sql_revert);
                echo "Column `cheque_number` in `payment_details` table reverted to NOT NULL.\n";
            } else {
                echo "Column `cheque_number` in `payment_details` table is already NOT NULL, skipping revert.\n";
            }
        } catch (PDOException $e) {
            echo "Error reverting `cheque_number` in `payment_details` table to NOT NULL: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}


