<?php
include '../../config/config.php';

echo "<h3>Quotations Table Structure:</h3>";
$stmt = $conn->query("DESCRIBE quotations");
while ($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}

echo "<br><h3>Leads Table Structure:</h3>";
$stmt = $conn->query("DESCRIBE leads");
while ($row = $stmt->fetch()) {
    echo $row['Field'] . " - " . $row['Type'] . "<br>";
}
?>