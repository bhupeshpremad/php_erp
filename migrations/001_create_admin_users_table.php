<?php

require_once 'Migration.php';

class CreateAdminUsersTable extends Migration {
    
    public function up() {
        $this->createTable('admin_users', [
            '`id` INT AUTO_INCREMENT PRIMARY KEY',
            '`name` VARCHAR(255) NOT NULL',
            '`email` VARCHAR(255) UNIQUE NOT NULL',
            '`password` VARCHAR(255) NOT NULL',
            '`department` ENUM("sales", "accounts", "operation", "production", "communication") DEFAULT NULL',
            '`status` ENUM("pending", "approved", "rejected") DEFAULT "pending"',
            '`approved_by` INT DEFAULT NULL',
            '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            '`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
        ]);
        
        $this->createIndex('admin_users', 'idx_email', ['email']);
        $this->createIndex('admin_users', 'idx_department', ['department']);
        $this->createIndex('admin_users', 'idx_status', ['status']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS admin_users");
        echo "✅ Table 'admin_users' dropped\n";
    }
}