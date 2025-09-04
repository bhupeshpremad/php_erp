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

$buyer_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM buyers WHERE id = ?");
$stmt->execute([$buyer_id]);
$buyer = $stmt->fetch();

if (!$buyer) {
    header('Location: list.php');
    exit;
}
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
                <div class="card">
                    <div class="card-header">
                        <h4>Buyer Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Company Information</h5>
                                <p><strong>Company Name:</strong> <?= htmlspecialchars($buyer['company_name']) ?></p>
                                <p><strong>Contact Person:</strong> <?= htmlspecialchars($buyer['contact_person']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($buyer['email']) ?></p>
                                <p><strong>Phone:</strong> <?= htmlspecialchars($buyer['phone']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Address & Status</h5>
                                <p><strong>Address:</strong> <?= htmlspecialchars($buyer['address']) ?></p>
                                <p><strong>Status:</strong> 
                                    <span class="badge bg-<?= $buyer['status'] === 'approved' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($buyer['status']) ?>
                                    </span>
                                </p>
                                <p><strong>Registered:</strong> <?= date('d-m-Y H:i', strtotime($buyer['created_at'])) ?></p>
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