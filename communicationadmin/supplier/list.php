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

// Get all suppliers
$stmt = $conn->query("SELECT * FROM suppliers ORDER BY created_at DESC");
$suppliers = $stmt->fetchAll();
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">All Suppliers (<?= count($suppliers) ?>)</h6>
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
                            <th>GSTIN</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                        <tr>
                            <td><?= $supplier['id'] ?></td>
                            <td><?= htmlspecialchars($supplier['company_name']) ?></td>
                            <td><?= htmlspecialchars($supplier['contact_person_name'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($supplier['contact_person_email'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($supplier['contact_person_phone'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($supplier['gstin'] ?? 'N/A') ?></td>
                            <td>
                                <?php 
                                $statusClass = $supplier['status'] === 'approved' ? 'success' : ($supplier['status'] === 'pending' ? 'warning' : 'danger');
                                ?>
                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($supplier['status']) ?></span>
                            </td>
                            <td><?= date('d-m-Y', strtotime($supplier['created_at'])) ?></td>
                            <td>
                                <?php if ($supplier['status'] === 'pending'): ?>
                                <a href="approve.php?id=<?= $supplier['id'] ?>&action=approve" class="btn btn-sm btn-success">Approve</a>
                                <a href="approve.php?id=<?= $supplier['id'] ?>&action=reject" class="btn btn-sm btn-danger">Reject</a>
                                <?php endif; ?>
                                <a href="quotations/index.php?supplier_id=<?= $supplier['id'] ?>" class="btn btn-sm btn-info">Quotations</a>
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