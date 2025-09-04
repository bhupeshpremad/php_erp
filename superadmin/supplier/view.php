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

$supplier_id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$supplier_id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    header('Location: list.php');
    exit;
}
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Supplier Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Company Information</h5>
                    <p><strong>Company Name:</strong> <?= htmlspecialchars($supplier['company_name']) ?></p>
                    <p><strong>Contact Person:</strong> <?= htmlspecialchars($supplier['contact_person']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($supplier['email']) ?></p>
                    <p><strong>Phone:</strong> <?= htmlspecialchars($supplier['phone']) ?></p>
                </div>
                <div class="col-md-6">
                    <h5>Address & Status</h5>
                    <p><strong>Address:</strong> <?= htmlspecialchars($supplier['address']) ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge badge-<?= $supplier['status'] === 'approved' ? 'success' : 'warning' ?>">
                            <?= ucfirst($supplier['status']) ?>
                        </span>
                    </p>
                    <p><strong>Registered:</strong> <?= date('d-m-Y H:i', strtotime($supplier['created_at'])) ?></p>
                </div>
            </div>
            <a href="list.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
    
    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>

</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>