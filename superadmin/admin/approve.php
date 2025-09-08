<?php
session_start();
require_once '../../config/config.php';
require_once '../../core/auth.php';

if (!isLoggedIn() || ($_SESSION['role'] !== 'superadmin' && $_SESSION['user_type'] !== 'superadmin')) {
    header('Location: ../../index.php');
    exit;
}

$admin_id = $_GET['id'] ?? 0;

if ($admin_id) {
    try {
        // Get admin details first
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Approve admin
            $stmt = $conn->prepare("UPDATE admin_users SET status = 'approved' WHERE id = ?");
            $result = $stmt->execute([$admin_id]);
            
            if ($result) {
                $_SESSION['success'] = 'Admin approved successfully!';
                
                // Add notification
                require_once '../../core/NotificationSystem.php';
                NotificationSystem::init($conn);
                NotificationSystem::create(
                    'admin',
                    'Admin Approved',
                    $admin['name'] . ' (' . $admin['email'] . ') has been approved for ' . $admin['department'] . ' department.',
                    'superadmin',
                    1
                );
            } else {
                $_SESSION['error'] = 'Failed to approve admin.';
            }
        } else {
            $_SESSION['error'] = 'Admin not found.';
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