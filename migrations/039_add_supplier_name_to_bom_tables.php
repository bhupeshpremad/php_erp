<?php

require_once 'Migration.php';

class AddSupplierNameToBomTables extends Migration {
    
    public function up() {
        $tables = ['bom_glow', 'bom_hardware', 'bom_plynydf', 'bom_wood'];
        $columnDefinition = 'VARCHAR(255) DEFAULT NULL';

        foreach ($tables as $table) {
            $this->addColumn($table, 'supplier_name', $columnDefinition);
        }
    }
    
    public function down() {
        $tables = ['bom_glow', 'bom_hardware', 'bom_plynydf', 'bom_wood'];

        foreach ($tables as $table) {
            $this->dropColumn($table, 'supplier_name');
        }
    }

    // Helper function to add column (assuming it exists in your Migration class)
    protected function addColumn($tableName, $columnName, $columnDefinition) {
        try {
            $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnDefinition}";
            $this->conn->exec($sql);
            echo "✅ Column '{$columnName}' added to '{$tableName}'\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                echo "❌ Error adding column '{$columnName}': " . $e->getMessage() . "\n";
            } else {
                echo "⚠️ Column '{$columnName}' already exists in '{$tableName}'\n";
            }
        }
    }

    // Helper function to drop column (assuming it exists or needs to be added)
    protected function dropColumn($tableName, $columnName) {
        try {
            $sql = "ALTER TABLE `{$tableName}` DROP COLUMN `{$columnName}`";
            $this->conn->exec($sql);
            echo "✅ Column '{$columnName}' dropped from '{$tableName}'\n";
        } catch (PDOException $e) {
            // Ignore error if column doesn't exist during rollback
            echo "❌ Error dropping column '{$columnName}' from '{$tableName}': " . $e->getMessage() . "\n";
        }
    }
}


