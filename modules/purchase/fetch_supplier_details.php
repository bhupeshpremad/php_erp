<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
global $conn;

if (!isset($_POST['purchase_id'])) {
    echo '<div class="alert alert-danger">Purchase ID is required</div>';
    exit;
}

$purchase_id = intval($_POST['purchase_id']);
$user_type = $_POST['user_type'] ?? 'guest';
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;

try {
    // First check if user has access to this purchase
    if ($_SESSION['user_type'] !== 'superadmin') {
        $stmt_check = $conn->prepare("SELECT id FROM purchase_main WHERE id = ? AND created_by = ?");
        $stmt_check->execute([$purchase_id, $current_user_id]);
        if (!$stmt_check->fetch()) {
            echo '<div class="alert alert-danger">Access denied. You can only view your own purchases.</div>';
            exit;
        }
    }
    
    // Fetch purchase items grouped by supplier
    $stmt_items = $conn->prepare("SELECT supplier_name, product_type, product_name, assigned_quantity, price, total, invoice_image, builty_image, invoice_number FROM purchase_items WHERE purchase_main_id = ? ORDER BY supplier_name, id ASC");
    $stmt_items->execute([$purchase_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    if (empty($items)) {
        echo '<div class="alert alert-info">No items found for this purchase.</div>';
        exit;
    }

    // Group items by supplier
    $suppliers = [];
    foreach ($items as $item) {
        $supplier_name = $item['supplier_name'] ?? 'Unknown Supplier';
        if (!isset($suppliers[$supplier_name])) {
            $suppliers[$supplier_name] = [];
        }
        $suppliers[$supplier_name][] = $item;
    }

    ?>
    <div class="row">
        <div class="col-12">
            <h6><strong>Supplier Details</strong></h6>
            <?php foreach ($suppliers as $supplier_name => $supplier_items): ?>
                <div class="card mb-3">
                    <div class="card-header">
                        <h6 class="mb-0"><strong><?php echo htmlspecialchars($supplier_name); ?></strong></h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Product Type</th>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Invoice</th>
                                        <th>Builty</th>
                                        <th>Status</th>
                                        <?php if ($user_type === 'superadmin'): ?>
                                        <th>Action</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $has_complete_items = false;
                                    foreach ($supplier_items as $item): 
                                        $has_invoice = !empty($item['invoice_image']);
                                        $has_builty = !empty($item['builty_image']);
                                        $has_invoice_number = !empty($item['invoice_number']);
                                        $is_complete = $has_invoice && $has_builty && $has_invoice_number;
                                        if ($is_complete) $has_complete_items = true;
                                    ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['product_type']); ?></td>
                                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                            <td><?php echo htmlspecialchars($item['assigned_quantity']); ?></td>
                                            <td><?php echo htmlspecialchars($item['price']); ?></td>
                                            <td><?php echo htmlspecialchars($item['total']); ?></td>
                                            <td>
                                                <?php if ($has_invoice): ?>
                                                    <a href="<?php echo BASE_URL; ?>modules/purchase/uploads/invoice/<?php echo rawurlencode($item['invoice_image']); ?>" target="_blank">
                                                        <img src="<?php echo BASE_URL; ?>modules/purchase/uploads/invoice/<?php echo rawurlencode($item['invoice_image']); ?>" style="width:50px;height:50px;object-fit:cover;border:1px solid #ddd;border-radius:4px;" />
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($has_builty): ?>
                                                    <a href="<?php echo BASE_URL; ?>modules/purchase/uploads/Builty/<?php echo rawurlencode($item['builty_image']); ?>" target="_blank">
                                                        <img src="<?php echo BASE_URL; ?>modules/purchase/uploads/Builty/<?php echo rawurlencode($item['builty_image']); ?>" style="width:50px;height:50px;object-fit:cover;border:1px solid #ddd;border-radius:4px;" />
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">No image</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($is_complete): ?>
                                                    <span class="badge badge-success">Complete</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Incomplete</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php if ($user_type === 'superadmin'): ?>
                                            <td>
                                                <?php if ($is_complete): ?>
                                                    <button class="btn btn-success btn-sm approve-supplier-btn" data-supplier-id="<?php echo htmlspecialchars($supplier_name); ?>" data-purchase-id="<?php echo $purchase_id; ?>">Send Approval</button>
                                                <?php else: ?>
                                                    <span class="text-muted">Incomplete</span>
                                                <?php endif; ?>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading supplier details: ' . $e->getMessage() . '</div>';
}
?>