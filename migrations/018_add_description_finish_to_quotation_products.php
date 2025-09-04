<?php

require_once 'Migration.php';

class AddDescriptionFinishToQuotationProducts extends Migration {
    
    public function up() {
        try {
            // Add description field if it doesn't exist
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM quotation_products LIKE 'description'");
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $this->conn->exec("ALTER TABLE quotation_products ADD COLUMN description TEXT DEFAULT NULL AFTER item_code");
                echo "Added 'description' column to quotation_products table\n";
            } else {
                echo "Column 'description' already exists in quotation_products table\n";
            }
            
            // Add finish field if it doesn't exist
            $stmt = $this->conn->prepare("SHOW COLUMNS FROM quotation_products LIKE 'finish'");
            $stmt->execute();
            if ($stmt->rowCount() == 0) {
                $this->conn->exec("ALTER TABLE quotation_products ADD COLUMN finish VARCHAR(255) DEFAULT NULL AFTER no_of_packet");
                echo "Added 'finish' column to quotation_products table\n";
            } else {
                echo "Column 'finish' already exists in quotation_products table\n";
            }
            
            return true;
        } catch (Exception $e) {
            echo "Error in migration: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function down() {
        try {
            // Remove the added columns
            $this->conn->exec("ALTER TABLE quotation_products DROP COLUMN IF EXISTS description");
            $this->conn->exec("ALTER TABLE quotation_products DROP COLUMN IF EXISTS finish");
            echo "Removed 'description' and 'finish' columns from quotation_products table\n";
            return true;
        } catch (Exception $e) {
            echo "Error in rollback: " . $e->getMessage() . "\n";
            return false;
        }
    }
}


