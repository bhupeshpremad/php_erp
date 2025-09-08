<?php
session_start();
require_once '../../config/config.php';
require_once '../../core/auth.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit;
}

$admin_id = $_GET['id'] ?? 0;

if ($admin_id) {
    try {
        // Reject admin
        $stmt = $conn->prepare("UPDATE admin_users SET status = 'rejected' WHERE id = ?");
        $result = $stmt->execute([$admin_id]);
        
        if ($result) {
            $_SESSION['success'] = 'Admin rejected successfully!';
        } else {
            $_SESSION['error'] = 'Failed to reject admin.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid admin ID.';
}

header('Location: pending.php');
exit;
?>