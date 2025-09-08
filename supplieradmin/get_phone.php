<?php
session_start();
include '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['supplier_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT contact_person_phone FROM suppliers WHERE id = ?");
    $stmt->execute([$_SESSION['supplier_id']]);
    $supplier = $stmt->fetch();
    
    if ($supplier) {
        echo json_encode(['success' => true, 'phone' => $supplier['contact_person_phone']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Supplier not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>