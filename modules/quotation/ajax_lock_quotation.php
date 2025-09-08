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
    $conn->beginTransaction();
    
    // Update quotation lock status
    $stmt = $conn->prepare("UPDATE quotations SET locked = 1 WHERE id = ? AND approve = 1");
    $stmt->execute([$quotation_id]);
    
    if ($stmt->rowCount() > 0) {
        // Get quotation details
        $quotationStmt = $conn->prepare("SELECT quotation_number, customer_name FROM quotations WHERE id = ?");
        $quotationStmt->execute([$quotation_id]);
        $quotation = $quotationStmt->fetch(PDO::FETCH_ASSOC);
        
        // Generate PI number
        $piNumber = 'PI-' . date('Y') . '-' . str_pad($quotation_id, 3, '0', STR_PAD_LEFT);
        
        // Insert into PI table
        $piStmt = $conn->prepare("INSERT INTO pi (quotation_id, quotation_number, pi_number, status, date_of_pi_raised) VALUES (?, ?, ?, 'Active', ?)");
        $piStmt->execute([
            $quotation_id,
            $quotation['quotation_number'],
            $piNumber,
            date('Y-m-d')
        ]);
        
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Quotation locked and PI created successfully']);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Quotation must be approved before locking']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>