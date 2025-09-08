<?php
header('Content-Type: application/json');
require_once '../include/inc/db.php';
require_once '../include/oop/CRUDOperations.php';

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$table = $_POST['table'] ?? '';
$columns = $_POST['columns'] ?? [];
$values = $_POST['values'] ?? [];
$whereColumn = $_POST['whereColumn'] ?? '';
$whereValue = $_POST['whereValue'] ?? '';

if (empty($table) || empty($columns) || empty($values) || empty($whereColumn) || empty($whereValue)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$update = new Update($conn);
$response = $update->update($table, $columns, $values, $whereColumn, $whereValue);
echo json_encode($response);
?>
