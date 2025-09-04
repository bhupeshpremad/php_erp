<?php
include_once __DIR__ . '/../../config/config.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

global $conn;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$quotation_id = $_POST['quotation_id'] ?? null;
if (!$quotation_id) {
    echo json_encode(['success' => false, 'message' => 'Quotation ID is required']);
    exit;
}

try {
    $stmt = $conn->prepare("UPDATE quotations SET locked = 0 WHERE id = ?");
    $stmt->execute([$quotation_id]);
    echo json_encode(['success' => true, 'message' => 'Quotation unlocked successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

