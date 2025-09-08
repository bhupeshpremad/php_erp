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

// Get all buyers
$stmt = $conn->query("SELECT * FROM buyers ORDER BY created_at DESC");
$buyers = $stmt->fetchAll();
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <div class="row w-100">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h6 class="m-0 font-weight-bold text-primary">All Buyers (<?= count($buyers) ?>)</h6>
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
                                <a href="view.php?id=<?= $buyer['id'] ?>" class="btn btn-sm btn-info">View</a>
                                <?php if ($buyer['status'] === 'pending'): ?>
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