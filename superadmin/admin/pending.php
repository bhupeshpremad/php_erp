<?php
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';
session_start();
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';

if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} else {
    header('Location: ../../index.php');
    exit;
}

// Handle approval/rejection
$message = '';
if (($_POST['action'] ?? '') === 'approve' && isset($_POST['admin_id'])) {
    $stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_POST['admin_id']]);
    $admin = $stmt->fetch();
    
    $stmt = $conn->prepare("UPDATE admin_users SET status = 'approved', department = ? WHERE id = ?");
    if ($stmt->execute([$admin['department'], $_POST['admin_id']])) {
        $message = "Admin approved successfully!";
        $message_type = "success";
    }
}

if (($_POST['action'] ?? '') === 'reject' && isset($_POST['admin_id'])) {
    $stmt = $conn->prepare("UPDATE admin_users SET status = 'rejected' WHERE id = ?");
    if ($stmt->execute([$_POST['admin_id']])) {
        $message = "Admin rejected successfully!";
        $message_type = "danger";
    }
}

// Get pending admins
$stmt = $conn->query("SELECT * FROM admin_users WHERE status = 'pending' ORDER BY created_at DESC");
$admins = $stmt->fetchAll();
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
                <?php if (!empty($message)): ?>
                <div class="alert alert-<?= $message_type ?? 'info' ?>"><?= $message ?></div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Pending Admin Approvals</h4>
                        <span class="badge bg-warning"><?= count($admins) ?> Pending</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($admins)): ?>
                        <div class="alert alert-info">No pending admin registrations.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Department</th>
                                        <th>Phone</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($admin['name']) ?></td>
                                        <td><?= htmlspecialchars($admin['email']) ?></td>
                                        <td><span class="badge badge-info"><?= ucfirst($admin['department'] ?? 'N/A') ?></span></td>
                                        <td><?= htmlspecialchars($admin['phone'] ?? 'N/A') ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($admin['created_at'])) ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="admin_id" value="<?= $admin['id'] ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-sm btn-success">Approve</button>
                                                <button type="submit" name="action" value="reject" class="btn btn-sm btn-danger">Reject</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
</body>
</html>