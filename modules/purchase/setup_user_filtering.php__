<?php
// Setup script to add user filtering to purchase module
session_start();
include_once __DIR__ . '/../../config/config.php';

// Check if user is superadmin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    die('Access denied. Only superadmin can run this setup.');
}

global $conn;

try {
    echo "<h3>Setting up user filtering for Purchase Module</h3>";
    
    // Check if created_by column exists
    $stmt = $conn->query("SHOW COLUMNS FROM purchase_main LIKE 'created_by'");
    if ($stmt->rowCount() == 0) {
        echo "<p>Adding created_by column to purchase_main table...</p>";
        $conn->exec("ALTER TABLE purchase_main ADD COLUMN created_by INT(11) NULL AFTER bom_number");
        echo "<p style='color: green;'>✓ created_by column added successfully</p>";
        
        // Update existing records
        echo "<p>Updating existing records...</p>";
        $conn->exec("UPDATE purchase_main SET created_by = 1 WHERE created_by IS NULL");
        echo "<p style='color: green;'>✓ Existing records updated</p>";
        
        // Add index
        echo "<p>Adding index for better performance...</p>";
        $conn->exec("ALTER TABLE purchase_main ADD INDEX idx_created_by (created_by)");
        echo "<p style='color: green;'>✓ Index added successfully</p>";
    } else {
        echo "<p style='color: blue;'>ℹ created_by column already exists</p>";
    }
    
    echo "<h4 style='color: green;'>Setup completed successfully!</h4>";
    echo "<p><strong>Changes made:</strong></p>";
    echo "<ul>";
    echo "<li>Added user-based filtering to index.php</li>";
    echo "<li>Added created_by field tracking in ajax_save_purchase.php</li>";
    echo "<li>Added user access control in add.php and fetch_supplier_details.php</li>";
    echo "<li>Fixed image paths for proper display</li>";
    echo "<li>Added database field for user tracking</li>";
    echo "</ul>";
    echo "<p><strong>Now each user will only see their own purchase entries (except superadmin who can see all).</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>