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

$admin_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM admin_users WHERE id = ?");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

if (!$admin) {
    header('Location: list.php');
    exit;
}
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
                <div class="card">
                    <div class="card-header">
                        <h4>Admin Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Personal Information</h5>
                                <p><strong>Name:</strong> <?= htmlspecialchars($admin['name']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($admin['email']) ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($admin['phone'] ?? 'N/A') ?></p>
                                <p><strong>Department:</strong> <span class="badge badge-info"><?= ucfirst($admin['department'] ?? 'N/A') ?></span></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Status & Department</h5>
                                <p><strong>Department:</strong> <?= htmlspecialchars($admin['department']) ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-<?= $admin['status'] === 'approved' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($admin['status']) ?>
                                    </span>
                                </p>
                                <p><strong>Registered:</strong> <?= date('d-m-Y H:i', strtotime($admin['created_at'])) ?></p>
                            </div>
                        </div>
                        <a href="list.php" class="btn btn-secondary">Back to List</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
</body>
</html>