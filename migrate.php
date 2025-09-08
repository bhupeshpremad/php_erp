<?php

// Migration Runner - Laravel style for PHP ERP
require_once 'config/config.php';

class MigrationRunner {
    private $conn;
    private $migrationsPath;
    
    public function __construct($connection) {
        $this->conn = $connection;
        $this->migrationsPath = __DIR__ . '/migrations/';
        $this->createMigrationsTable();
    }
    
    private function createMigrationsTable() {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $this->conn->exec($sql);
    }
    
    public function migrate() {
        echo "🚀 Starting migrations...\n\n";
        
        $migrationFiles = glob($this->migrationsPath . '*.php');
        sort($migrationFiles);
        
        $executedMigrations = $this->getExecutedMigrations();
        $batch = $this->getNextBatch();
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');
            
            if (in_array($migrationName, $executedMigrations)) {
                echo "⏭️  Skipping: {$migrationName} (already executed)\n";
                continue;
            }
            
            echo "🔄 Running: {$migrationName}\n";
            
            require_once $file;
            $className = $this->getClassNameFromFile($migrationName);
            
            if (class_exists($className)) {
                $migration = new $className($this->conn);
                
                try {
                    $migration->up();
                    $this->recordMigration($migrationName, $batch);
                    echo "✅ Completed: {$migrationName}\n\n";
                } catch (Exception $e) {
                    echo "❌ Failed: {$migrationName} - " . $e->getMessage() . "\n\n";
                }
            }
        }
        
        echo "🎉 Migrations completed!\n";
    }
    
    public function rollback($steps = 1) {
        echo "🔄 Rolling back {$steps} migration(s)...\n\n";
        
        $lastBatch = $this->getLastBatch();
        $migrationsToRollback = $this->getMigrationsFromBatch($lastBatch);
        
        foreach (array_reverse($migrationsToRollback) as $migrationName) {
            echo "🔄 Rolling back: {$migrationName}\n";
            
            $file = $this->migrationsPath . $migrationName . '.php';
            if (file_exists($file)) {
                require_once $file;
                $className = $this->getClassNameFromFile($migrationName);
                
                if (class_exists($className)) {
                    $migration = new $className($this->conn);
                    
                    try {
                        $migration->down();
                        $this->removeMigrationRecord($migrationName);
                        echo "✅ Rolled back: {$migrationName}\n\n";
                    } catch (Exception $e) {
                        echo "❌ Failed to rollback: {$migrationName} - " . $e->getMessage() . "\n\n";
                    }
                }
            }
        }
        
        echo "🎉 Rollback completed!\n";
    }
    
    public function status() {
        echo "📊 Migration Status:\n\n";
        
        $migrationFiles = glob($this->migrationsPath . '*.php');
        sort($migrationFiles);
        
        $executedMigrations = $this->getExecutedMigrations();
        
        foreach ($migrationFiles as $file) {
            $migrationName = basename($file, '.php');
            $status = in_array($migrationName, $executedMigrations) ? '✅ Executed' : '⏳ Pending';
            echo "{$status} - {$migrationName}\n";
        }
        
        echo "\n";
    }
    
    private function getClassNameFromFile($filename) {
        // Convert 001_create_admin_users_table to CreateAdminUsersTable
        $parts = explode('_', $filename);
        array_shift($parts); // Remove number prefix
        
        return str_replace(' ', '', ucwords(implode(' ', $parts)));
    }
    
    private function getExecutedMigrations() {
        $stmt = $this->conn->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function getNextBatch() {
        $stmt = $this->conn->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch();
        return ($result['max_batch'] ?? 0) + 1;
    }
    
    private function getLastBatch() {
        $stmt = $this->conn->query("SELECT MAX(batch) as max_batch FROM migrations");
        $result = $stmt->fetch();
        return $result['max_batch'] ?? 0;
    }
    
    private function getMigrationsFromBatch($batch) {
        $stmt = $this->conn->prepare("SELECT migration FROM migrations WHERE batch = ? ORDER BY id DESC");
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    private function recordMigration($migration, $batch) {
        $stmt = $this->conn->prepare("INSERT INTO migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$migration, $batch]);
    }
    
    private function removeMigrationRecord($migration) {
        $stmt = $this->conn->prepare("DELETE FROM migrations WHERE migration = ?");
        $stmt->execute([$migration]);
    }
}

// Command line interface
if (php_sapi_name() === 'cli') {
    $command = $argv[1] ?? 'migrate';
    $runner = new MigrationRunner($conn);
    
    switch ($command) {
        case 'migrate':
            $runner->migrate();
            break;
        case 'rollback':
            $steps = $argv[2] ?? 1;
            $runner->rollback($steps);
            break;
        case 'status':
            $runner->status();
            break;
        default:
            echo "Usage: php migrate.php [migrate|rollback|status]\n";
    }
} else {
    // Web interface
    $action = $_GET['action'] ?? 'status';
    $runner = new MigrationRunner($conn);
    
    echo "<pre>";
    switch ($action) {
        case 'migrate':
            $runner->migrate();
            break;
        case 'rollback':
            $runner->rollback();
            break;
        default:
            $runner->status();
    }
    echo "</pre>";
    
    echo '<br><a href="?action=migrate">Run Migrations</a> | ';
    echo '<a href="?action=rollback">Rollback</a> | ';
    echo '<a href="?action=status">Status</a>';
}