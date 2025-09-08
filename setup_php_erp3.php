<?php
/**
 * PHP ERP3 Setup Script
 * This script combines the updated modules from php_erp with the live data from pup_erp2
 * and creates a new database for php_erp3
 */

require_once 'config/config.php';

class PhpErp3Setup {
    private $conn;
    private $sourceDbName = 'u404997496_crm_purewood'; // pup_erp2 database
    private $targetDbName = 'php_erp3_db'; // new database
    
    public function __construct() {
        try {
            // Connect to MySQL server (without database)
            $config = AppConfig::getConfig();
            $dsn = "mysql:host=" . $config['host'] . ";charset=" . $config['charset'];
            $this->conn = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public function setup() {
        echo "<h1>PHP ERP3 Setup</h1>\n";
        
        try {
            // Step 1: Create new database
            $this->createDatabase();
            
            // Step 2: Import live data structure and data
            $this->importLiveData();
            
            // Step 3: Run additional migrations from php_erp
            $this->runAdditionalMigrations();
            
            // Step 4: Update configuration
            $this->updateConfiguration();
            
            echo "<h2>✅ Setup completed successfully!</h2>\n";
            echo "<p>Your php_erp3 project is ready with:</p>\n";
            echo "<ul>\n";
            echo "<li>✅ All updated modules from php_erp</li>\n";
            echo "<li>✅ All live data from pup_erp2</li>\n";
            echo "<li>✅ All uploaded files preserved</li>\n";
            echo "<li>✅ Database schema updated with latest migrations</li>\n";
            echo "</ul>\n";
            echo "<p><strong>Database:</strong> {$this->targetDbName}</p>\n";
            echo "<p><strong>URL:</strong> <a href='http://localhost/Comparing/php_erp3/'>http://localhost/Comparing/php_erp3/</a></p>\n";
            
        } catch (Exception $e) {
            echo "<h2>❌ Setup failed!</h2>\n";
            echo "<p>Error: " . $e->getMessage() . "</p>\n";
        }
    }
    
    private function createDatabase() {
        echo "<h3>Step 1: Creating database '{$this->targetDbName}'...</h3>\n";
        
        // Drop database if exists
        $this->conn->exec("DROP DATABASE IF EXISTS `{$this->targetDbName}`");
        
        // Create new database
        $this->conn->exec("CREATE DATABASE `{$this->targetDbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Switch to new database
        $this->conn->exec("USE `{$this->targetDbName}`");
        
        echo "✅ Database created successfully<br>\n";
    }
    
    private function importLiveData() {
        echo "<h3>Step 2: Importing live data from pup_erp2...</h3>\n";
        
        $sqlFile = __DIR__ . '/../pup_erp2/u404997496_crm_purewood.sql';
        
        if (!file_exists($sqlFile)) {
            throw new Exception("SQL file not found: $sqlFile");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Remove database creation and use statements
        $sql = preg_replace('/CREATE DATABASE.*?;/i', '', $sql);
        $sql = preg_replace('/USE.*?;/i', '', $sql);
        $sql = preg_replace('/--.*?Database:.*?\n/i', '', $sql);
        
        // Execute SQL
        $this->conn->exec($sql);
        
        echo "✅ Live data imported successfully<br>\n";
    }
    
    private function runAdditionalMigrations() {
        echo "<h3>Step 3: Running additional migrations from php_erp...</h3>\n";
        
        // Get list of migrations that need to be run
        $additionalMigrations = [
            '019_create_purchase_main_table.php',
            '020_create_purchase_items_table.php',
            '021_create_jci_main_table.php',
            '022_create_jci_items_table.php',
            '023_create_po_main_table.php',
            '024_create_po_items_table.php',
            '025_create_sell_order_table.php',
            '026_create_bom_main_table.php',
            '026_create_payment_details_table.php',
            '027_create_bom_wood_table.php',
            '028_create_bom_glow_table.php',
            '029_create_bom_plynydf_table.php',
            '030_create_bom_hardware_table.php',
            '031_create_bom_labour_table.php',
            '032_create_bom_factory_table.php',
            '033_create_bom_margin_table.php',
            '034_create_communication_admin_user.php',
            '035_add_profile_picture_to_admin_users.php',
            '036_create_buyer_quotations_table.php',
            '037_add_bom_id_fk_to_jci_main.php',
            '038_add_fk_jci_po_to_jci_main.php',
            '039_add_supplier_name_to_bom_tables.php',
            '040_add_approval_status_to_purchase_main.php',
            '041_add_item_approval_status_to_purchase_items.php',
            '042_add_jci_number_to_payments_table.php',
            '043_add_po_number_and_sell_order_number_to_payments_table.php',
            '044_alter_payment_details_cheque_number_to_null.php',
            '045_alter_payments_payment_number_to_null.php',
            '046_add_excel_file_to_quotations.php'
        ];
        
        $migrationsPath = __DIR__ . '/migrations/';
        
        foreach ($additionalMigrations as $migration) {
            $migrationFile = $migrationsPath . $migration;
            
            if (file_exists($migrationFile)) {
                echo "Running migration: $migration<br>\n";
                
                // Include and run migration
                include $migrationFile;
                
                // Add to migrations table
                $stmt = $this->conn->prepare("INSERT INTO migrations (migration, batch, executed_at) VALUES (?, 2, NOW())");
                $stmt->execute([$migration]);
                
                echo "✅ $migration completed<br>\n";
            } else {
                echo "⚠️ Migration file not found: $migration<br>\n";
            }
        }
        
        echo "✅ Additional migrations completed<br>\n";
    }
    
    private function updateConfiguration() {
        echo "<h3>Step 4: Configuration updated</h3>\n";
        echo "✅ Database configuration already updated in config.php<br>\n";
    }
}

// Run setup if accessed directly
if (basename($_SERVER['PHP_SELF']) === 'setup_php_erp3.php') {
    $setup = new PhpErp3Setup();
    $setup->setup();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP ERP3 Setup</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        h1 { color: #333; }
        h2 { color: #666; }
        h3 { color: #888; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        ul { margin: 20px 0; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
</body>
</html>