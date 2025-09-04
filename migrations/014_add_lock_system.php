<?php

require_once 'Migration.php';

class AddLockSystem extends Migration {
    
    public function up() {
        // Add lock columns to quotations
        $this->addColumn('quotations', 'is_locked', 'TINYINT(1) DEFAULT 0');
        $this->addColumn('quotations', 'locked_by', 'INT DEFAULT NULL');
        $this->addColumn('quotations', 'locked_at', 'TIMESTAMP NULL DEFAULT NULL');
        
        // Add lock columns to po
        $this->addColumn('po', 'is_locked', 'TINYINT(1) DEFAULT 0');
        $this->addColumn('po', 'locked_by', 'INT DEFAULT NULL');
        $this->addColumn('po', 'locked_at', 'TIMESTAMP NULL DEFAULT NULL');
        
        echo "✅ Lock system columns added\n";
    }
    
    public function down() {
        $this->conn->exec("ALTER TABLE quotations DROP COLUMN is_locked, DROP COLUMN locked_by, DROP COLUMN locked_at");
        $this->conn->exec("ALTER TABLE po DROP COLUMN is_locked, DROP COLUMN locked_by, DROP COLUMN locked_at");
        echo "✅ Lock system columns removed\n";
    }
}