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
if (($_POST['action'] ?? '') === 'approve' && isset($_POST['buyer_id'])) {
    $stmt = $conn->prepare("UPDATE buyers SET status = 'approved' WHERE id = ?");
    $stmt->execute([$_POST['buyer_id']]);
    $success = "Buyer approved successfully!";
}

if (($_POST['action'] ?? '') === 'reject' && isset($_POST['buyer_id'])) {
    $stmt = $conn->prepare("UPDATE buyers SET status = 'rejected' WHERE id = ?");
    $stmt->execute([$_POST['buyer_id']]);
    $success = "Buyer rejected!";
}

// Get pending buyers
$stmt = $conn->query("SELECT * FROM buyers WHERE status = 'pending' ORDER BY created_at DESC");
$buyers = $stmt->fetchAll();
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
                <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h4>Pending Buyer Approvals</h4>
                        <span class="badge bg-warning"><?= count($buyers) ?> Pending</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($buyers)): ?>
                        <div class="alert alert-info">No pending buyer registrations.</div>
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
                                    <?php foreach ($buyers as $buyer): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($buyer['company_name']) ?></td>
                                        <td><?= htmlspecialchars($buyer['contact_person_name']) ?></td>
                                        <td><?= htmlspecialchars($buyer['contact_person_email']) ?></td>
                                        <td><?= htmlspecialchars($buyer['contact_person_phone']) ?></td>
                                        <td><?= htmlspecialchars($buyer['company_address']) ?></td>
                                        <td><?= date('d-m-Y H:i', strtotime($buyer['created_at'])) ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="buyer_id" value="<?= $buyer['id'] ?>">
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