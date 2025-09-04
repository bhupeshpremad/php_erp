<?php
// This script scans all modules for SQL files and executes them on the database.
// Usage: Run this script from CLI or web to apply all module SQL scripts to the database.

require_once __DIR__ . '/../config/config.php';

$modulesDir = __DIR__ . '/../modules';
$pdo = null;

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("DB Connection Failed: " . $e->getMessage());
}

function executeSqlFile(PDO $pdo, $filePath) {
    echo "Executing SQL file: $filePath\n";
    $sql = file_get_contents($filePath);
    if ($sql === false) {
        echo "Failed to read file: $filePath\n";
        return false;
    }
    try {
        $pdo->exec($sql);
        echo "Successfully executed: $filePath\n";
        return true;
    } catch (PDOException $e) {
        echo "Error executing $filePath: " . $e->getMessage() . "\n";
        return false;
    }
}

$modules = array_filter(scandir($modulesDir), function($item) use ($modulesDir) {
    return $item !== '.' && $item !== '..' && is_dir($modulesDir . DIRECTORY_SEPARATOR . $item);
});

foreach ($modules as $module) {
    $sqlDir = $modulesDir . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'sql';
    if (is_dir($sqlDir)) {
        $sqlFiles = glob($sqlDir . DIRECTORY_SEPARATOR . '*.sql');
        if ($sqlFiles) {
            foreach ($sqlFiles as $sqlFile) {
                executeSqlFile($pdo, $sqlFile);
            }
        } else {
            echo "No SQL files found in $sqlDir\n";
        }
    } else {
        echo "No sql directory in module: $module\n";
    }
}

echo "All module SQL scripts executed.\n";
?>
