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

// Get all supplier quotations (check if table exists)
try {
    $stmt = $conn->query("
        SELECT sq.*, s.company_name, s.contact_person 
        FROM supplier_quotations sq 
        JOIN suppliers s ON sq.supplier_id = s.id 
        ORDER BY sq.created_at DESC
    ");
    $quotations = $stmt->fetchAll();
} catch (Exception $e) {
    // Table doesn't exist yet
    $quotations = [];
}
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <div class="row w-100">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h6 class="m-0 font-weight-bold text-primary">Supplier Quotations (<?= count($quotations) ?>)</h6>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-8">
                            <input type="text" class="form-control form-control-sm" placeholder="Search by RFQ or Supplier">
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4 text-right">
                            <a href="list.php" class="btn btn-info btn-sm">All Suppliers</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if (empty($quotations)): ?>
            <div class="alert alert-info">No supplier quotations found.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Supplier</th>
                            <th>RFQ Reference</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($quotations as $quotation): ?>
                        <tr>
                            <td><?= $quotation['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($quotation['company_name']) ?></strong><br>
                                <small><?= htmlspecialchars($quotation['contact_person']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($quotation['rfq_reference']) ?></td>
                            <td>₹<?= number_format($quotation['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge badge-<?= $quotation['status'] === 'submitted' ? 'success' : 'warning' ?>">
                                    <?= ucfirst($quotation['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d-m-Y H:i', strtotime($quotation['created_at'])) ?></td>
                            <td>
                                <a href="view_quotation.php?id=<?= $quotation['id'] ?>" class="btn btn-sm btn-info">View</a>
                                <a href="download_quotation.php?id=<?= $quotation['id'] ?>" class="btn btn-sm btn-success">Download</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>

</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>