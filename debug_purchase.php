<?php
require_once 'config/config.php';

echo "<h2>Purchase Module Debug</h2>";

try {
    // Check if purchase_main table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'purchase_main'");
    $table_exists = $stmt->rowCount() > 0;
    
    if ($table_exists) {
        echo "✅ purchase_main table exists<br>";
        
        // Check table structure
        $stmt = $conn->query("DESCRIBE purchase_main");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<strong>Table Structure:</strong><br>";
        foreach($columns as $col) {
            echo "- " . $col['Field'] . " (" . $col['Type'] . ")<br>";
        }
        
        // Check data count
        $stmt = $conn->query("SELECT COUNT(*) as count FROM purchase_main");
        $count = $stmt->fetch()['count'];
        echo "<br><strong>Records count:</strong> " . $count . "<br>";
        
        if ($count > 0) {
            // Show sample data
            $stmt = $conn->query("SELECT * FROM purchase_main LIMIT 3");
            $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<br><strong>Sample Data:</strong><br>";
            foreach($samples as $row) {
                echo "ID: " . $row['id'] . ", PO: " . ($row['po_number'] ?? 'NULL') . "<br>";
            }
        }
        
    } else {
        echo "❌ purchase_main table does NOT exist<br>";
        echo "<strong>Available tables:</strong><br>";
        
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach($tables as $table) {
            if (strpos($table, 'purchase') !== false) {
                echo "- " . $table . " (purchase related)<br>";
            }
        }
    }
    
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>