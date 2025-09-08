<?php
$_ENV['APP_ENV'] = 'local';
include_once 'config/config.php';

echo "<h3>Checking JCI tables:</h3>";
$stmt = $conn->query("SHOW TABLES LIKE '%jci%'");
while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
    echo "Table: " . $row[0] . "<br>";
}

echo "<hr><h3>Checking if jci_main exists:</h3>";
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM jci_main LIMIT 1");
    echo "jci_main table exists<br>";
} catch (Exception $e) {
    echo "jci_main table does NOT exist<br>";
}
?>