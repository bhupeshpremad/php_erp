<?php
require_once 'config/config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    echo "✅ Database connected successfully!<br>";
    echo "Database: " . AppConfig::getConfig()['db'] . "<br>";
    echo "User: " . AppConfig::getConfig()['user'] . "<br>";
    echo "Host: " . AppConfig::getConfig()['host'] . "<br><br>";
    
    // Test some basic queries
    $stmt = $conn->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<strong>Tables found (" . count($tables) . "):</strong><br>";
    foreach($tables as $table) {
        echo "- " . $table . "<br>";
    }
    
    echo "<br><strong>✅ Database is working properly!</strong>";
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>