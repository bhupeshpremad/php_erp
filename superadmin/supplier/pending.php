<?php
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';
session_start();
$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';

if ($user_type === 'superadmin' || ($_SESSION['department'] ?? '') === 'communication') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} else {
    header('Location: ../../index.php');
    exit;
}

// Handle approval/rejection
if ($_POST['action'] ?? '' === 'approve' && isset($_POST['supplier_id'])) {
    $stmt = $conn->prepare("UPDATE suppliers SET status = 'approved' WHERE id = ?");
    $stmt->execute([$_POST['supplier_id']]);
    $success = "Supplier approved successfully!";
}

if ($_POST['action'] ?? '' === 'reject' && isset($_POST['supplier_id'])) {
    $stmt = $conn->prepare("UPDATE suppliers SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$_POST['supplier_id']]);
    $success = "Supplier rejected!";
}

// Get pending suppliers
$stmt = $conn->query("SELECT * FROM suppliers WHERE status = 'pending' ORDER BY created_at DESC");
$suppliers = $stmt->fetchAll();
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Pending Supplier Approvals</h4>
                        <span class="badge bg-warning"><?= count($suppliers) ?> Pending</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($suppliers)): ?>
                        <div class="alert alert-info">No pending supplier registrations.</div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Company</th>
                                        <th>Contact Person</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($suppliers as $supplier): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($supplier['company_name']) ?></td>
                                        <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                                        <td><?= htmlspecialchars($supplier['email']) ?></td>
                                        <td><?= htmlspecialchars($supplier['phone']) ?></td>
                                        <td><?= htmlspecialchars($supplier['address']) ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($supplier['created_at'])) ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="supplier_id" value="<?= $supplier['id'] ?>">
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