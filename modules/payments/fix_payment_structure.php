<?php
include_once __DIR__ . '/../../config/config.php';
global $conn;

try {
    // Add new column for actual payment cheque number
    $stmt = $conn->prepare("ALTER TABLE payment_details ADD COLUMN payment_cheque_number VARCHAR(255) DEFAULT NULL AFTER cheque_number");
    $stmt->execute();
    echo "Added payment_cheque_number column successfully.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column payment_cheque_number already exists.\n";
    } else {
        echo "Error adding column: " . $e->getMessage() . "\n";
    }
}

echo "Database structure update completed.\n";
?>
