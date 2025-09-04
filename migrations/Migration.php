<?php

class Migration {
    protected $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    protected function createTable($tableName, $columns) {
        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (\n";
        $sql .= implode(",\n", $columns);
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        try {
            $this->conn->exec($sql);
            echo "✅ Table '{$tableName}' created successfully\n";
        } catch (PDOException $e) {
            echo "❌ Error creating table '{$tableName}': " . $e->getMessage() . "\n";
        }
    }
    
    protected function addColumn($tableName, $columnName, $columnDefinition) {
        $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnDefinition}";
        
        try {
            $this->conn->exec($sql);
            echo "✅ Column '{$columnName}' added to '{$tableName}'\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column name') === false) {
                echo "❌ Error adding column '{$columnName}': " . $e->getMessage() . "\n";
            }
        }
    }
    
    protected function createIndex($tableName, $indexName, $columns) {
        $sql = "CREATE INDEX `{$indexName}` ON `{$tableName}` (" . implode(', ', $columns) . ")";
        
        try {
            $this->conn->exec($sql);
            echo "✅ Index '{$indexName}' created on '{$tableName}'\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') === false) {
                echo "❌ Error creating index '{$indexName}': " . $e->getMessage() . "\n";
            }
        }
    }
}