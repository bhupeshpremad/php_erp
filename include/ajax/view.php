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
$whereColumn = $_POST['whereColumn'] ?? null;
$whereValue = $_POST['whereValue'] ?? null;

if (empty($table) || empty($columns)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$view = new View($conn);
$response = $view->view($table, $columns, $whereColumn, $whereValue);
echo json_encode($response);
?>
