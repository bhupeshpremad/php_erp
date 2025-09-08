<?php
// session_start(); // Removed: Session should be started by the main page before AJAX call
include_once __DIR__ . '/../../config/config.php';
global $conn;

if (!isset($_POST['purchase_id'])) {
    echo '<div class="alert alert-danger">Purchase ID is required</div>';
    exit;
}

$purchase_id = intval($_POST['purchase_id']);

try {
    // Fetch purchase main details
    $stmt = $conn->prepare("SELECT * FROM purchase_main WHERE id = ?");
    $stmt->execute([$purchase_id]);
    $purchase = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$purchase) {
        echo '<div class="alert alert-warning">Purchase record not found</div>';
        exit;
    }

    // Fetch purchase items
    $stmt_items = $conn->prepare("SELECT * FROM purchase_items WHERE purchase_main_id = ? ORDER BY id ASC");
    $stmt_items->execute([$purchase_id]);
    $items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);

    // Fetch JCI details
    $stmt_jci = $conn->prepare("SELECT * FROM jci_main WHERE jci_number = ?");
    $stmt_jci->execute([$purchase['jci_number']]);
    $jci = $stmt_jci->fetch(PDO::FETCH_ASSOC);

    $user_type = $_POST['user_type'] ?? 'guest'; // Get user type from POST
    
    ?>
    <div class="row">
        <div class="col-md-6">
            <h6><strong>Purchase Details</strong></h6>
            <table class="table table-bordered table-sm">
                <tbody><tr>
                    <td><strong>JCI Number:</strong></td>
                    <td><?php echo htmlspecialchars($purchase['jci_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Sell Order Number:</strong></td>
                    <td><?php echo htmlspecialchars($purchase['sell_order_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>PO Number:</strong></td>
                    <td><?php echo htmlspecialchars($purchase['po_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Overall Approval Status:</strong></td>
                    <td>Pending</td>
                </tr>
            </tbody></table>
        </div>
        <div class="col-md-6">
            <h6 class="text-white"><strong> 1</strong></h6>
            <table class="table table-bordered table-sm">
                <tbody><tr>
                    <td><strong>JCI Number:</strong></td>
                    <td><?php echo htmlspecialchars($purchase['jci_number']); ?></td>
                </tr>
                <tr>
                    <td><strong>Sell Order Number:</strong></td>
                    <td><?php echo htmlspecialchars($purchase['sell_order_number']); ?></td>
                </tr>
            </tbody></table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <h6><strong>Purchase Items</strong></h6>
            <?php if (count($items) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>Sr. No.</th>
                                <th>Supplier Name</th>
                                <th>Product Type</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Job Card</th>
                                <th>Invoice</th>
                                <th>Builty</th>
                                <th>Status</th>
                                <th>Approval</th> <!-- New Approval column -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $index => $item): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($item['supplier_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['product_type']); ?></td>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($item['assigned_quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($item['price']); ?></td>
                                    <td><?php echo htmlspecialchars($item['total']); ?></td>
                                    <td><?php echo htmlspecialchars($item['job_card_number']); ?></td>
                                    <td>
                                        <?php
                                            $invoiceImage = $item['invoice_image'] ?? '';
                                            if (!empty($invoiceImage)) {
                                                $invoiceUrl = BASE_URL . 'modules/purchase/uploads/invoice/' . rawurlencode($invoiceImage);
                                                echo '<a href="' . htmlspecialchars($invoiceUrl) . '" target="_blank" download title="Click to view/download">'
                                                    . '<img src="' . htmlspecialchars($invoiceUrl) . '" style="width:60px;height:60px;object-fit:cover;border:1px solid #ddd;border-radius:4px;" />'
                                                    . '</a>';
                                            } else {
                                        ?>
                                            <form class="item-upload-form" enctype="multipart/form-data">
                                                <input type="hidden" name="purchase_id" value="<?php echo (int)$purchase_id; ?>">
                                                <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                                <input type="file" name="invoice_image" accept=".jpg,.jpeg,.png" class="form-control-file form-control-sm mb-1">
                                                <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php
                                            $builtyImage = $item['builty_image'] ?? '';
                                            if (!empty($builtyImage)) {
                                                $builtyUrl = BASE_URL . 'modules/purchase/uploads/Builty/' . rawurlencode($builtyImage);
                                                echo '<a href="' . htmlspecialchars($builtyUrl) . '" target="_blank" download title="Click to view/download">'
                                                    . '<img src="' . htmlspecialchars($builtyUrl) . '" style="width:60px;height:60px;object-fit:cover;border:1px solid #ddd;border-radius:4px;" />'
                                                    . '</a>';
                                            } else {
                                        ?>
                                            <form class="item-upload-form" enctype="multipart/form-data">
                                                <input type="hidden" name="purchase_id" value="<?php echo (int)$purchase_id; ?>">
                                                <input type="hidden" name="item_id" value="<?php echo (int)$item['id']; ?>">
                                                <input type="file" name="builty_image" accept=".jpg,.jpeg,.png" class="form-control-file form-control-sm mb-1">
                                                <button type="submit" class="btn btn-sm btn-primary">Upload</button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $status = 'Pending';
                                        // Consider uploaded if images exist; verified if invoice number also present
                                        $hasInvoiceImg = !empty($item['invoice_image']);
                                        if (!empty($item['invoice_number']) && $hasInvoiceImg) {
                                            $status = '<span class="badge badge-success">Verified</span>';
                                        } elseif ($hasInvoiceImg) {
                                            $status = '<span class="badge badge-warning">Uploaded</span>';
                                        }
                                        echo $status;
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $item_status = 'pending';
                                        if ($user_type === 'superadmin' && $item_status === 'pending') {
                                            echo '<button class="btn btn-success btn-sm approve-item-btn" data-item-id="' . (int)$item['id'] . '">Approve Item</button>';
                                        } else {
                                            echo '<span class="badge badge-info">' . htmlspecialchars($item_status) . '</span>';
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">No items found for this purchase.</div>
            <?php endif; ?>
        </div>
    </div>
    <?php

} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Error loading purchase details: ' . $e->getMessage() . '</div>';
}
?>
