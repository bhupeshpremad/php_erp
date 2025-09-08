<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'superadmin') {
    header('Location: ../../index.php');
    exit;
}

$admin_id = (int)($_POST['id'] ?? 0);
$department = trim($_POST['department'] ?? '');

if ($admin_id > 0 && in_array($department, ['sales', 'accounts', 'operation', 'production'], true)) {
    try {
        $stmt = $conn->prepare("UPDATE admin_users SET department = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$department, $admin_id]);
        $_SESSION['success'] = 'Department updated successfully';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error updating department: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid request';
}

header('Location: list.php');
exit;
?>

<?php
session_start();
require_once '../../config/config.php';
require_once '../../core/auth.php';

if (!isLoggedIn() || ($_SESSION['role'] !== 'superadmin' && $_SESSION['user_type'] !== 'superadmin')) {
    header('Location: ../../index.php');
    exit;
}

$admin_id = $_POST['id'] ?? 0;
$department = $_POST['department'] ?? '';

if ($admin_id > 0 && !empty($department)) {
    try {
        // Get admin details
        $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        
        if ($admin) {
            // Update admin department
            $stmt = $conn->prepare("UPDATE admin_users SET department = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$department, $admin_id]);
            
            $_SESSION['success'] = 'Department updated successfully for ' . $admin['name'];
            
            // Add notification
            require_once '../../core/NotificationSystem.php';
            NotificationSystem::init($conn);
            NotificationSystem::create(
                'admin',
                'Department Updated',
                $admin['name'] . "'s department changed to " . $department,
                'superadmin',
                1
            );
        } else {
            $_SESSION['error'] = 'Admin not found.';
        }
    } catch (Exception $e) {
        $_SESSION['error'] = 'Error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

header('Location: list.php');
exit;
?>
