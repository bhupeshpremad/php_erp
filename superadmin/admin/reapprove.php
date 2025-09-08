<?php
include_once __DIR__ . '/../../config/config.php';
session_start();

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit;
}

$admin_id = (int)($_POST['id'] ?? 0);
$department = trim($_POST['department'] ?? '');

if ($admin_id > 0 && !empty($department)) {
    try {
        // Check if admin exists and is rejected
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ? AND status = 'rejected'");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Update admin status and department
            $stmt = $conn->prepare("UPDATE admin_users SET status = 'approved', department = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$department, $admin_id]);
            
            $_SESSION['success'] = 'Admin re-approved successfully with department: ' . $department;
        } else {
            $_SESSION['error'] = 'Admin not found or not in rejected status';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid request';
}

header('Location: list.php');
exit;
?>
