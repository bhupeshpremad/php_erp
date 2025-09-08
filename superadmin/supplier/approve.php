<?php
session_start();
require_once '../../config/config.php';
require_once '../../core/auth.php';

if (!isLoggedIn() || ($_SESSION['role'] !== 'superadmin' && ($_SESSION['department'] ?? '') !== 'communication')) {
    header('Location: ../../index.php');
    exit;
}

$supplier_id = $_GET['id'] ?? 0;

if ($supplier_id) {
    try {
        // Approve supplier
        $stmt = $conn->prepare("UPDATE suppliers SET status = 'approved' WHERE id = ?");
        $result = $stmt->execute([$supplier_id]);
        
        if ($result) {
            $_SESSION['success'] = 'Supplier approved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to approve supplier.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid supplier ID.';
}

header('Location: pending.php');
exit;
?>