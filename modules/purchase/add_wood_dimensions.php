<?php
// Add Wood dimensions columns to purchase_items table
header('Content-Type: text/html');
echo '<pre>';

try {
    // Database connection
    $host = 'localhost';
    $dbname = 'u404997496_erp';
    $username = 'u404997496_erp_u404997496';
    $password = 'PUrewood@2025#';
    
    $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully.\n";
    
    // Check if columns already exist
    $stmt = $conn->query("SHOW COLUMNS FROM purchase_items LIKE 'length_ft'");
    if ($stmt->rowCount() === 0) {
        // Add Wood dimension columns
        $conn->exec("
            ALTER TABLE purchase_items 
            ADD COLUMN length_ft DECIMAL(10,2) DEFAULT NULL,
            ADD COLUMN width_ft DECIMAL(10,2) DEFAULT NULL,
            ADD COLUMN thickness_inch DECIMAL(10,2) DEFAULT NULL
        ");
        echo "Wood dimension columns added successfully!\n";
        
        // Verify columns were added
        $stmt = $conn->query("SHOW COLUMNS FROM purchase_items WHERE Field IN ('length_ft', 'width_ft', 'thickness_inch')");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Added columns:\n";
        foreach ($columns as $col) {
            echo "- {$col['Field']} ({$col['Type']})\n";
        }
    } else {
        echo "Wood dimension columns already exist.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
echo '</pre>';
?>