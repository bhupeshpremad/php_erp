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

// Get all admins
$stmt = $conn->query("SELECT * FROM admin_users ORDER BY created_at DESC");
$admins = $stmt->fetchAll();
?>

<style>
    .d-flex.gap-2{
        align-items: center !important;
    }

    .d-flex.gap-2 a{
        max-height: 30px !important;
    }
</style>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <div class="row w-100">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h6 class="m-0 font-weight-bold text-primary">All Admin Users (<?= count($admins) ?>)</h6>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-8">
                            <input type="text" class="form-control form-control-sm" placeholder="Search by Name or Email">
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
                            <table id="adminTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>SL No</th>
                                         <th>Name</th>
                                         <th>Email</th>
                                         <th>Department</th>
                                         <th>Phone</th>
                                         <!-- <th>Approved By</th> -->
                                        <th>Status</th>
                                        <!-- <th>Registered</th> -->
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($admins as $admin): ?>
                                    <tr>
                                        <td><?= $admin['id'] ?></td>
                                        <td><?= htmlspecialchars($admin['name']) ?></td>
                                        <td><?= htmlspecialchars($admin['email']) ?></td>
                                        <td><span class="badge badge-info"><?= ucfirst($admin['department'] ?? 'N/A') ?></span></td>
                                        <td><?= htmlspecialchars($admin['phone'] ?? 'N/A') ?></td>
                                        <!-- <td><?= htmlspecialchars($admin['approved_by'] ?? 'N/A') ?></td> -->
                                        <td>
                                            <span class="badge bg-<?= $admin['status'] === 'approved' ? 'success' : ($admin['status'] === 'pending' ? 'warning' : 'danger') ?>">
                                                <?= ucfirst($admin['status']) ?>
                                            </span>
                                        </td>
                                        <!-- <td><?= date('d-m-Y', strtotime($admin['created_at'])) ?></td> -->
                                        <td class="d-flex gap-2">
                                            <a href="view.php?id=<?= $admin['id'] ?>" class="btn btn-sm btn-info mr-2">View</a>
                                            <?php if ($admin['status'] === 'pending'): ?>
                                            <a href="approve.php?id=<?= $admin['id'] ?>" class="btn btn-sm btn-success">Approve</a>
                                            <?php endif; ?>
                                            <?php if ($admin['status'] === 'approved'): ?>
                                            <a href="deactivate.php?id=<?= $admin['id'] ?>" class="btn btn-sm btn-warning mr-2" onclick="return confirm('Are you sure you want to deactivate this admin?')">Deactivate</a>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#changeDeptModal<?= $admin['id'] ?>">Change Dept</button>
                                            <?php endif; ?>
                                            <?php if ($admin['status'] === 'rejected'): ?>
                                            <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#reapproveModal<?= $admin['id'] ?>">Re-Approve</button>
                                            <?php endif; ?>
                                            
                                            <!-- Change Department Modal -->
                                            <div class="modal fade" id="changeDeptModal<?= $admin['id'] ?>" tabindex="-1" aria-labelledby="changeDeptModalLabel<?= $admin['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                         <div class="modal-header">
                                                            <h5 class="modal-title" id="changeDeptModalLabel<?= $admin['id'] ?>">Change Department</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <form method="POST" action="change_department.php">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                                                <div class="mb-3">
                                                                    <label for="dept<?= $admin['id'] ?>" class="form-label">New Department</label>
                                                                    <select class="form-select" name="department" id="dept<?= $admin['id'] ?>" required>
                                                                        <option value="">Select Department</option>
                                                                        <option value="sales" <?= $admin['department'] === 'sales' ? 'selected' : '' ?>>Sales</option>
                                                                        <option value="production" <?= $admin['department'] === 'production' ? 'selected' : '' ?>>Production</option>
                                                                        <option value="accounts" <?= $admin['department'] === 'accounts' ? 'selected' : '' ?>>Accounts</option>
                                                                        <option value="operation" <?= $admin['department'] === 'operation' ? 'selected' : '' ?>>Operation</option>
                                                                    </select>
                                                                </div>
                                                                <p>Change department for <strong><?= htmlspecialchars($admin['name']) ?></strong> from <strong><?= ucfirst($admin['department'] ?? 'N/A') ?></strong>?</p>
                                                            </div>
                                                             <div class="modal-footer">
                                                                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Update Department</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Re-Approve Modal -->
                                            <div class="modal fade" id="reapproveModal<?= $admin['id'] ?>" tabindex="-1" aria-labelledby="reapproveModalLabel<?= $admin['id'] ?>" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                         <div class="modal-header">
                                                            <h5 class="modal-title" id="reapproveModalLabel<?= $admin['id'] ?>">Re-Approve Admin</h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        </div>
                                                        <form method="POST" action="reapprove.php">
                                                            <div class="modal-body">
                                                                <input type="hidden" name="id" value="<?= $admin['id'] ?>">
                                                                <div class="mb-3">
                                                                    <label for="department<?= $admin['id'] ?>" class="form-label">Department</label>
                                                                    <select class="form-select" name="department" id="department<?= $admin['id'] ?>" required>
                                                                        <option value="">Select Department</option>
                                                                        <option value="sales">Sales</option>
                                                                        <option value="production">Production</option>
                                                                        <option value="accounts">Accounts</option>
                                                                        <option value="operation">Operation</option>
                                                                    </select>
                                                                </div>
                                                                <p>Are you sure you want to re-approve <strong><?= htmlspecialchars($admin['name']) ?></strong>?</p>
                                                            </div>
                                                             <div class="modal-footer">
                                                                 <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">Re-Approve</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    $('#adminTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthChange: false,
        searching: false
    });
});
</script>
</body>
</html>