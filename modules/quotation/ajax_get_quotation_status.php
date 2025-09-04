<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

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
    // Get status history
    $stmt = $conn->prepare("SELECT status_date as date, status_text as status FROM quotation_status WHERE quotation_id = ? ORDER BY status_date DESC");
    $stmt->execute([$quotation_id]);
    $status_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true, 
        'status_history' => $status_history
    ]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>