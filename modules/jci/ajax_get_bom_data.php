<?php
include_once __DIR__ . '/../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$bom_id = $_POST['bom_id'] ?? null;

if (!$bom_id) {
    echo json_encode(['success' => false, 'message' => 'BOM ID required']);
    exit;
}

try {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, bom_number, client_name, grand_total_amount FROM bom_main WHERE id = ?");
    $stmt->execute([$bom_id]);
    $bom_data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($bom_data) {
        echo json_encode(['success' => true, 'bom_data' => $bom_data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'BOM not found']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>