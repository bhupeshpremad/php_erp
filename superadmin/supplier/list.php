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
} elseif ($user_type === 'salesadmin') {
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} else {
    header('Location: ../../index.php');
    exit;
}

// Get all suppliers
$stmt = $conn->query("SELECT * FROM suppliers ORDER BY created_at DESC");
$suppliers = $stmt->fetchAll();
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <div class="row w-100">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h6 class="m-0 font-weight-bold text-primary">All Suppliers (<?= count($suppliers) ?>)</h6>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-8">
                            <input type="text" class="form-control form-control-sm" placeholder="Search by Company or Email">
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4 text-right">
                            <a href="pending.php" class="btn btn-warning btn-sm">Pending Approvals</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Contact Email</th>
                            <th>Contact Phone</th>
                            <th>GSTIN</th>
                            <th>Address</th>
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
                            <td><?= htmlspecialchars($supplier['company_address'] ?? 'N/A') ?></td>
                            <td>
                                <?php 
                                $statusClass = $supplier['status'] === 'approved' ? 'success' : ($supplier['status'] === 'pending' ? 'warning' : 'danger');
                                ?>
                                <span class="badge badge-<?= $statusClass ?>"><?= ucfirst($supplier['status']) ?></span>
                            </td>
                            <td><?= date('d-m-Y', strtotime($supplier['created_at'])) ?></td>
                            <td>
                                <a href="view.php?id=<?= $supplier['id'] ?>" class="btn btn-sm btn-info">View</a>
                                <?php if ($supplier['status'] === 'pending'): ?>
                                <a href="pending.php" class="btn btn-sm btn-success">Approve</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>

</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>