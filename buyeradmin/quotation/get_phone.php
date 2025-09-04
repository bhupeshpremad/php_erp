<?php
session_start();
include '../../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['buyer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$buyer_id = $_SESSION['buyer_id'];

try {
    $stmt = $conn->prepare("SELECT contact_person_phone FROM buyers WHERE id = ?");
    $stmt->execute([$buyer_id]);
    $buyer = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($buyer) {
        echo json_encode(['success' => true, 'phone' => $buyer['contact_person_phone']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Buyer not found.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
