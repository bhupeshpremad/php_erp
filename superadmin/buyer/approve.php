<?php
session_start();
require_once '../../config/config.php';
require_once '../../core/auth.php';

if (!isLoggedIn() || ($_SESSION['role'] !== 'superadmin' && ($_SESSION['department'] ?? '') !== 'communication')) {
    header('Location: ../../index.php');
    exit;
}

$buyer_id = $_GET['id'] ?? 0;

if ($buyer_id) {
    try {
        // Approve buyer
        $stmt = $conn->prepare("UPDATE buyers SET status = 'approved' WHERE id = ?");
        $result = $stmt->execute([$buyer_id]);
        
        if ($result) {
            $_SESSION['success'] = 'Buyer approved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to approve buyer.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid buyer ID.';
}

header('Location: pending.php');
exit;
?>