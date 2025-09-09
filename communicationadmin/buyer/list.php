<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'communicationadmin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

include_once ROOT_DIR_PATH . 'communicationadmin/sidebar.php';

// Get all buyers
$stmt = $conn->query("SELECT * FROM buyers ORDER BY created_at DESC");
$buyers = $stmt->fetchAll();
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Buyers (<?= count($buyers) ?>)</h6>
            <a href="pending.php" class="btn btn-warning btn-sm">Pending Approvals</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($buyers as $buyer): ?>
                        <tr>
                            <td><?= $buyer['id'] ?></td>
                            <td><?= htmlspecialchars($buyer['company_name']) ?></td>
                            <td><?= htmlspecialchars($buyer['contact_person_name']) ?></td>
                            <td><?= htmlspecialchars($buyer['contact_person_email']) ?></td>
                            <td><?= htmlspecialchars($buyer['contact_person_phone']) ?></td>
                            <td>
                                <?php 
                                $statusClass = $buyer['status'] === 'approved' ? 'success' : ($buyer['status'] === 'pending' ? 'warning' : 'danger');
                                ?>
                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($buyer['status']) ?></span>
                            </td>
                            <td><?= date('d-m-Y', strtotime($buyer['created_at'])) ?></td>
                            <td>
                                <?php if ($buyer['status'] === 'pending'): ?>
                                <a href="approve.php?id=<?= $buyer['id'] ?>&action=approve" class="btn btn-sm btn-success">Approve</a>
                                <a href="approve.php?id=<?= $buyer['id'] ?>&action=reject" class="btn btn-sm btn-danger">Reject</a>
                                <?php endif; ?>
                                <a href="quotations/index.php?buyer_id=<?= $buyer['id'] ?>" class="btn btn-sm btn-info">Quotations</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>