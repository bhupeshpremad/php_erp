<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">Missing purchase ID</div>';
    exit;
}

$purchase_id = intval($_GET['id']);

global $conn;

try {
    // Fetch purchase main
    $stmt_main = $conn->prepare("SELECT id, po_number, jci_number, sell_order_number, bom_number, approval_status, created_at, updated_at FROM purchase_main WHERE id = ? LIMIT 1");
    $stmt_main->execute([$purchase_id]);
    $main = $stmt_main->fetch(PDO::FETCH_ASSOC);

    if (!$main) {
        echo '<div class="alert alert-danger">Purchase record not found</div>';
        exit;
    }

    // Fetch items
    $stmt_items = $conn->prepare("SELECT id, supplier_name, product_type, product_name, job_card_number, assigned_quantity, price, total, invoice_number, builty_number, invoice_image, builty_image, length_ft, width_ft, thickness_inch, row_id, date FROM purchase_items WHERE purchase_main_id = ? ORDER BY job_card_number ASC, row_id ASC, id ASC");
    $stmt_items->execute([$purchase_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    // Group items by job card
    $by_jc = [];
    foreach ($items as $it) {
        $jc = $it['job_card_number'] ?: 'N/A';
        if (!isset($by_jc[$jc])) { $by_jc[$jc] = []; }
        $by_jc[$jc][] = $it;
    }

    $baseUrl = rtrim(BASE_URL, '/');
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>JCI:</strong> <?php echo htmlspecialchars($main['jci_number']); ?></div>
                        <div class="col-md-3"><strong>PO:</strong> <?php echo htmlspecialchars($main['po_number']); ?></div>
                        <div class="col-md-3"><strong>SON:</strong> <?php echo htmlspecialchars($main['sell_order_number']); ?></div>
                        <div class="col-md-3"><strong>BOM:</strong> <?php echo htmlspecialchars($main['bom_number']); ?></div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-3"><strong>Status:</strong> <?php echo htmlspecialchars($main['approval_status'] ?? 'pending'); ?></div>
                        <div class="col-md-3"><strong>Created:</strong> <?php echo htmlspecialchars($main['created_at'] ?? ''); ?></div>
                        <div class="col-md-3"><strong>Updated:</strong> <?php echo htmlspecialchars($main['updated_at'] ?? ''); ?></div>
                        <div class="col-md-3 text-right">
                            <?php if (in_array($_SESSION['user_type'] ?? '', ['superadmin','accountsadmin'])): ?>
                                <button class="btn btn-success btn-sm approve-btn" data-id="<?php echo $main['id']; ?>">Approve</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($items)): ?>
        <div class="alert alert-info">No items saved yet.</div>
    <?php else: ?>
        <?php foreach ($by_jc as $jc => $jcItems): ?>
            <div class="card mb-3">
                <div class="card-header"><strong>Job Card: <?php echo htmlspecialchars($jc); ?></strong></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Supplier</th>
                                    <th>Type</th>
                                    <th>Product</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th>Invoice #</th>
                                    <th>Invoice Img</th>
                                    <th>Builty #</th>
                                    <th>Builty Img</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=1; foreach ($jcItems as $row): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td><?php echo htmlspecialchars($row['supplier_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['assigned_quantity']); ?></td>
                                    <td><?php echo htmlspecialchars(number_format((float)$row['price'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars(number_format((float)$row['total'], 2)); ?></td>
                                    <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                                    <td>
                                        <?php if (!empty($row['invoice_image'])): ?>
                                            <img src="<?php echo $baseUrl; ?>/modules/purchase/uploads/invoice/<?php echo urlencode($row['invoice_image']); ?>" alt="inv" style="width:40px;height:40px;object-fit:cover;" />
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['builty_number']); ?></td>
                                    <td>
                                        <?php if (!empty($row['builty_image'])): ?>
                                            <img src="<?php echo $baseUrl; ?>/modules/purchase/uploads/Builty/<?php echo urlencode($row['builty_image']); ?>" alt="blt" style="width:40px;height:40px;object-fit:cover;" />
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['date']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}
?>