<?php

require_once __DIR__ . '/Migration.php';

class AddPoNumberAndSellOrderNumberToPaymentsTable extends Migration
{
    public function up()
    {
        // Add po_number
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM `payments` LIKE 'po_number'");
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $sql_po = "ALTER TABLE `payments` ADD COLUMN `po_number` VARCHAR(255) AFTER `jci_number`;";
                $this->conn->exec($sql_po);
                echo "Column `po_number` added to `payments` table.\n";
            } else {
                echo "Column `po_number` already exists in `payments` table, skipping.\n";
            }
        } catch (PDOException $e) {
            echo "Error adding `po_number` to `payments` table: " . $e->getMessage() . "\n";
            throw $e;
        }

        // Add sell_order_number
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM `payments` LIKE 'sell_order_number'");
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $sql_son = "ALTER TABLE `payments` ADD COLUMN `sell_order_number` VARCHAR(255) AFTER `po_number`;";
                $this->conn->exec($sql_son);
                echo "Column `sell_order_number` added to `payments` table.\n";
            } else {
                echo "Column `sell_order_number` already exists in `payments` table, skipping.\n";
            }
        } catch (PDOException $e) {
            echo "Error adding `sell_order_number` to `payments` table: " . $e->getMessage() . "\n";
            throw $e;
        }
    }

    public function down()
    {
        // Drop sell_order_number
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM `payments` LIKE 'sell_order_number'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $sql_son = "ALTER TABLE `payments` DROP COLUMN `sell_order_number`;";
                $this->conn->exec($sql_son);
                echo "Column `sell_order_number` dropped from `payments` table.\n";
            } else {
                echo "Column `sell_order_number` does not exist in `payments` table, skipping drop.\n";
            }
        } catch (PDOException $e) {
            echo "Error dropping `sell_order_number` from `payments` table: " . $e->getMessage() . "\n";
            throw $e;
        }

        // Drop po_number
        try {
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM `payments` LIKE 'po_number'");
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $sql_po = "ALTER TABLE `payments` DROP COLUMN `po_number`;";
                $this->conn->exec($sql_po);
                echo "Column `po_number` dropped from `payments` table.\n";
            } else {
                echo "Column `po_number` does not exist in `payments` table, skipping drop.\n";
            }
        } catch (PDOException $e) {
            echo "Error dropping `po_number` from `payments` table: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
}


