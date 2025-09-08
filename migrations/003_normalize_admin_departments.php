<?php

require_once 'Migration.php';

class NormalizeAdminDepartments extends Migration {

    public function up() {
        // Fix legacy values that block login lookups
        $this->conn->exec("UPDATE admin_users SET department = 'sales' WHERE department = 'salesadmin'");
        $this->conn->exec("UPDATE admin_users SET department = 'operation' WHERE department = 'operations'");
        echo "✅ Normalized existing admin_users.department values ('salesadmin'→'sales', 'operations'→'operation')\n";
    }

    public function down() {
        // Best effort revert
        $this->conn->exec("UPDATE admin_users SET department = 'salesadmin' WHERE department = 'sales'");
        $this->conn->exec("UPDATE admin_users SET department = 'operations' WHERE department = 'operation'");
        echo "↩️ Reverted normalization ('sales'→'salesadmin', 'operation'→'operations')\n";
    }
}


