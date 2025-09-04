<?php

require_once 'Migration.php';

class FixAdminDepartmentEnum extends Migration {

    public function up() {
        // Normalize department enum to match code: sales, accounts, operation, production
        $sql = "ALTER TABLE admin_users MODIFY COLUMN department ENUM('sales','accounts','operation','production') DEFAULT NULL";
        $this->conn->exec($sql);
        echo "✅ Updated admin_users.department ENUM to ['sales','accounts','operation','production']\n";
    }

    public function down() {
        // Revert to previous (if needed)
        $sql = "ALTER TABLE admin_users MODIFY COLUMN department ENUM('salesadmin','accounts','operation','production') DEFAULT NULL";
        $this->conn->exec($sql);
        echo "↩️ Reverted admin_users.department ENUM to include 'salesadmin'\n";
    }
}


