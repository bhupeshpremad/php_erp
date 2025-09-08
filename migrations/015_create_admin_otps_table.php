<?php
// migrations/015_create_admin_otps_table.php

require_once 'Migration.php';

class CreateAdminOtpsTable extends Migration {
    public function up() {
        $sql = "
            CREATE TABLE `admin_otps` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `email` VARCHAR(255) NOT NULL,
                `otp` VARCHAR(6) NOT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expires_at` DATETIME NOT NULL,
                `used` TINYINT(1) DEFAULT 0,
                PRIMARY KEY (`id`),
                INDEX `email_otp_idx` (`email`, `otp`),
                INDEX `expires_at_idx` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
        $this->conn->exec($sql);
    }

    public function down() {
        $sql = "DROP TABLE IF EXISTS `admin_otps`;";
        $this->conn->exec($sql);
    }
};
