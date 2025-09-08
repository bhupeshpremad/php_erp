<?php

require_once 'Migration.php';

class CreateNotificationsTable extends Migration {
    
    public function up() {
        $this->createTable('notifications', [
            '`id` INT AUTO_INCREMENT PRIMARY KEY',
            '`user_type` ENUM("superadmin", "salesadmin", "accounts", "operation", "production") NOT NULL',
            '`user_id` INT DEFAULT NULL',
            '`title` VARCHAR(255) NOT NULL',
            '`message` TEXT NOT NULL',
            '`type` ENUM("info", "success", "warning", "danger") DEFAULT "info"',
            '`module` VARCHAR(50) DEFAULT NULL',
            '`reference_id` INT DEFAULT NULL',
            '`is_read` TINYINT(1) DEFAULT 0',
            '`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
            '`read_at` TIMESTAMP NULL DEFAULT NULL'
        ]);
        
        $this->createIndex('notifications', 'idx_user_type', ['user_type']);
        $this->createIndex('notifications', 'idx_user_id', ['user_id']);
        $this->createIndex('notifications', 'idx_is_read', ['is_read']);
        $this->createIndex('notifications', 'idx_module', ['module']);
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS notifications");
        echo "✅ Table 'notifications' dropped\n";
    }
}