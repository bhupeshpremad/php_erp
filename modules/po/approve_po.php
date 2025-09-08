<?php

include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

function generateSellOrderNumber($conn) {
    $prefix = 'SALE';
    $year = date('Y');
    $likePattern = $prefix . '-' . $year . '-%';

    $stmt = $conn->prepare("SELECT sell_order_number FROM sell_order WHERE sell_order_number LIKE :pattern ORDER BY id DESC LIMIT 1");
    $stmt->bindValue(':pattern', $likePattern);
    $stmt->execute();
    $lastNumber = $stmt->fetchColumn();

    if ($lastNumber) {
        if (preg_match('/-(\d+)$/', $lastNumber, $matches)) {
            $lastSeq = (int)$matches[1];
        } else {
            $lastSeq = 0;
        }
        $nextSeq = $lastSeq + 1;
    } else {
        $nextSeq = 1;
    }

    $nextNumber = sprintf('%s-%s-%04d', $prefix, $year, $nextSeq);
    return $nextNumber;
}

if (!isset($conn) || !$conn instanceof PDO) {
    echo json_encode(['success' => false, 'message' => 'Database connection not initialized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

$po_id = (int)($_POST['po_id'] ?? 0);

if ($po_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid Purchase Order ID.']);
    exit;
}

try {
    $conn->beginTransaction();

    $stmt_check = $conn->prepare("SELECT status, is_locked FROM po_main WHERE id = :po_id");
    $stmt_check->bindValue(':po_id', $po_id, PDO::PARAM_INT);
    $stmt_check->execute();
    $po_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$po_data) {
        throw new Exception("Purchase Order not found.");
    }
    // Auto-unlock if locked to allow re-approval
    if ($po_data['is_locked'] == 1) {
        $stmt_unlock = $conn->prepare("UPDATE po_main SET is_locked = 0 WHERE id = :po_id");
        $stmt_unlock->bindValue(':po_id', $po_id, PDO::PARAM_INT);
        $stmt_unlock->execute();
    }
    if ($po_data['status'] == 'Approved') {
        throw new Exception("Purchase Order is already approved.");
    }

    $newSellOrderNumber = generateSellOrderNumber($conn);

    $stmt_insert_sell_order = $conn->prepare("INSERT INTO sell_order (sell_order_number, po_id, created_at, updated_at) VALUES (:sell_order_number, :po_id, NOW(), NOW())");
    $stmt_insert_sell_order->bindValue(':sell_order_number', $newSellOrderNumber);
    $stmt_insert_sell_order->bindValue(':po_id', $po_id, PDO::PARAM_INT);
    $stmt_insert_sell_order->execute();
    $sell_order_id = $conn->lastInsertId();

    $stmt_fetch_jci = $conn->prepare("SELECT jci_number FROM jci_main WHERE po_id = :po_id ORDER BY id DESC LIMIT 1");
    $stmt_fetch_jci->bindValue(':po_id', $po_id, PDO::PARAM_INT);
    $stmt_fetch_jci->execute();
    $jci_number = $stmt_fetch_jci->fetchColumn() ?: '';

    $stmt_update_po = $conn->prepare("UPDATE po_main SET status = 'Approved', sell_order_id = :sell_order_id, sell_order_number = :sell_order_number, jci_number = :jci_number, updated_at = NOW() WHERE id = :po_id");
    $stmt_update_po->bindValue(':sell_order_id', $sell_order_id, PDO::PARAM_INT);
    $stmt_update_po->bindValue(':sell_order_number', $newSellOrderNumber);
    $stmt_update_po->bindValue(':jci_number', $jci_number);
    $stmt_update_po->bindValue(':po_id', $po_id, PDO::PARAM_INT);
    $stmt_update_po->execute();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Purchase Order approved successfully.', 'sell_order_number' => $newSellOrderNumber]);

} catch (Exception $e) {
    $conn->rollBack();
    error_log("Error approving PO: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;