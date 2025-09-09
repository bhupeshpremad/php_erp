<?php
require_once 'config/config.php';

echo "<h2>Running Communication Admin Migration</h2>";

try {
    // Read migration file
    $sql = file_get_contents('migrations/create_communication_admin_tables.sql');
    
    // Split by semicolon and execute each statement
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $conn->exec($statement);
            echo "✅ Executed: " . substr($statement, 0, 50) . "...<br>";
        }
    }
    
    echo "<br><strong>✅ Migration completed successfully!</strong>";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>