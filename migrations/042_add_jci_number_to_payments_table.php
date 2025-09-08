<?php

require_once __DIR__ . '/Migration.php';

class AddJciNumberToPaymentsTable extends Migration
{
    public function up()
    {
        $sql = "
            ALTER TABLE `payments`
            ADD COLUMN `jci_number` VARCHAR(255) AFTER `id`;
        ";
        
        try {
            // Check if column already exists
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM `payments` LIKE 'jci_number'");
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $this->conn->exec($sql);
                echo "Column `jci_number` added to `payments` table.\n";
            } else {
                echo "Column `jci_number` already exists in `payments` table, skipping.\n";
            }
        } catch (PDOException $e) {
            echo "Error adding `jci_number` to `payments` table: " . $e->getMessage() . "\n";
            // Rethrow or handle as necessary
            throw $e;
        }
    }

    public function down()
    {
        $sql = "
            ALTER TABLE `payments`
            DROP COLUMN `jci_number`;
        ";
        try {
            // Check if column exists before dropping
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM `payments` LIKE 'jci_number'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $this->conn->exec($sql);
                echo "Column `jci_number` dropped from `payments` table.\n";
            } else {
                echo "Column `jci_number` does not exist in `payments` table, skipping drop.\n";
            }
        } catch (PDOException $e) {
            echo "Error dropping `jci_number` from `payments` table: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}


