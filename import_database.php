<?php
/**
 * Simple Database Import Script for PHP ERP3
 * This script imports the live database from pup_erp2 and applies the schema updates
 */

require_once 'config/config.php';

function importDatabase() {
    try {
        // Get database configuration
        $config = AppConfig::getConfig();
        
        // Connect to MySQL server
        $dsn = "mysql:host=" . $config['host'] . ";charset=" . $config['charset'];
        $conn = new PDO($dsn, $config['user'], $config['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        
        echo "<h2>Creating database: " . $config['db'] . "</h2>\n";
        
        // Drop and create database
        $conn->exec("DROP DATABASE IF EXISTS `" . $config['db'] . "`");
        $conn->exec("CREATE DATABASE `" . $config['db'] . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $conn->exec("USE `" . $config['db'] . "`");
        
        echo "<p>✅ Database created successfully</p>\n";
        
        // Import the live data SQL file
        $sqlFile = __DIR__ . '/../pup_erp2/u404997496_crm_purewood.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: $sqlFile");
        }
        
        echo "<h2>Importing live data...</h2>\n";
        
        $sql = file_get_contents($sqlFile);
        
        // Clean up the SQL
        $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
        $sql = preg_replace('/USE.*?;/i', '', $sql);
        $sql = preg_replace('/--.*?Database:.*?\n/i', '', $sql);
        
        // Execute the entire SQL at once
        try {
            $conn->exec($sql);
            echo "<p>✅ SQL file imported successfully</p>\n";
        } catch (PDOException $e) {
            echo "<p>❌ SQL import error: " . $e->getMessage() . "</p>\n";
            throw $e;
        }
        
        echo "<p>✅ Imported $count SQL statements</p>\n";
        
        // Apply schema updates
        echo "<h2>Applying schema updates...</h2>\n";
        
        // Add new columns and tables that are in php_erp but not in pup_erp2
        $updates = [
            // Add communication department to admin_users
            "ALTER TABLE `admin_users` MODIFY `department` enum('sales','accounts','operation','production','communication') DEFAULT NULL",
            
            // Add profile picture column
            "ALTER TABLE `admin_users` ADD COLUMN `profile_picture` varchar(255) DEFAULT NULL",
            
            // Add approval status to purchase_main if it doesn't exist
            "ALTER TABLE `purchase_main` ADD COLUMN `approval_status` enum('pending','approved','rejected') DEFAULT 'pending'",
            
            // Add approval status to purchase_items if it doesn't exist
            "ALTER TABLE `purchase_items` ADD COLUMN `approval_status` enum('pending','approved','rejected') DEFAULT 'pending'",
            
            // Add bom_id to jci_main if it doesn't exist
            "ALTER TABLE `jci_main` ADD COLUMN `bom_id` int(11) DEFAULT NULL",
            
            // Add additional columns to payments table
            "ALTER TABLE `payments` ADD COLUMN `jci_number` varchar(50) DEFAULT NULL",
            "ALTER TABLE `payments` ADD COLUMN `po_number` varchar(50) DEFAULT NULL", 
            "ALTER TABLE `payments` ADD COLUMN `payment_number` varchar(50) DEFAULT NULL",
            
            // Add excel file to quotations
            "ALTER TABLE `quotations` ADD COLUMN `excel_file` varchar(255) DEFAULT NULL",
            
            // Create admin_otps table
            "CREATE TABLE IF NOT EXISTS `admin_otps` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `admin_email` varchar(255) NOT NULL,
                `otp_code` varchar(10) NOT NULL,
                `expires_at` timestamp NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_admin_email` (`admin_email`),
                KEY `idx_expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Create buyer_otps table
            "CREATE TABLE IF NOT EXISTS `buyer_otps` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `buyer_email` varchar(255) NOT NULL,
                `otp_code` varchar(10) NOT NULL,
                `expires_at` timestamp NOT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_buyer_email` (`buyer_email`),
                KEY `idx_expires_at` (`expires_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
            
            // Create buyer_quotations table
            "CREATE TABLE IF NOT EXISTS `buyer_quotations` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `buyer_id` int(11) NOT NULL,
                `quotation_number` varchar(100) NOT NULL,
                `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
                `currency` varchar(10) DEFAULT 'USD',
                `validity_days` int(11) DEFAULT 30,
                `delivery_time` varchar(100) DEFAULT NULL,
                `payment_terms` text DEFAULT NULL,
                `notes` text DEFAULT NULL,
                `file_path` varchar(500) DEFAULT NULL,
                `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
                `submitted_at` timestamp NULL DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                PRIMARY KEY (`id`),
                KEY `idx_buyer_id` (`buyer_id`),
                KEY `idx_status` (`status`),
                KEY `idx_quotation_number` (`quotation_number`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        ];
        
        $updateCount = 0;
        foreach ($updates as $update) {
            try {
                $conn->exec($update);
                $updateCount++;
            } catch (PDOException $e) {
                // Skip if column/table already exists
                if (strpos($e->getMessage(), 'Duplicate column') === false && 
                    strpos($e->getMessage(), 'already exists') === false) {
                    echo "<p>⚠️ Update warning: " . $e->getMessage() . "</p>\n";
                }
            }
        }
        
        echo "<p>✅ Applied $updateCount schema updates</p>\n";
        
        // Add communication admin user if not exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM admin_users WHERE email = 'communication@thepurewood.com'");
        $stmt->execute();
        if ($stmt->fetchColumn() == 0) {
            $stmt = $conn->prepare("INSERT INTO admin_users (name, email, password, department, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([
                'Communication Admin',
                'communication@thepurewood.com', 
                '$2y$10$defaultpasswordhash',
                'communication',
                'approved'
            ]);
            echo "<p>✅ Added communication admin user</p>\n";
        }
        
        echo "<h2>✅ Database import completed successfully!</h2>\n";
        echo "<p><strong>Database:</strong> " . $config['db'] . "</p>\n";
        echo "<p><strong>Next steps:</strong></p>\n";
        echo "<ul>\n";
        echo "<li>Visit: <a href='http://localhost/Comparing/php_erp3/'>http://localhost/Comparing/php_erp3/</a></li>\n";
        echo "<li>Login with your existing credentials</li>\n";
        echo "<li>All your data and uploaded files are preserved</li>\n";
        echo "</ul>\n";
        
    } catch (Exception $e) {
        echo "<h2>❌ Import failed!</h2>\n";
        echo "<p>Error: " . $e->getMessage() . "</p>\n";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP ERP3 Database Import</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1, h2 { color: #333; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        ul { margin: 20px 0; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>PHP ERP3 Database Import</h1>
    
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['import'])) {
        importDatabase();
    } else {
    ?>
        <p>This will import the live database from pup_erp2 into php_erp3 with all schema updates.</p>
        <p><strong>Warning:</strong> This will create a new database called 'php_erp3_db' and import all data.</p>
        
        <form method="post">
            <button type="submit" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Import Database
            </button>
        </form>
        
        <p>Or click here to import directly: <a href="?import=1">Import Now</a></p>
    <?php
    }
    ?>
</body>
</html>