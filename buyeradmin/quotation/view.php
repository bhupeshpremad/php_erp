<?php
session_start();
include '../../config/config.php';

if (!isset($_SESSION['buyer_id'])) {
    echo 'Unauthorized access.';
    exit;
}

$quotationId = $_GET['id'] ?? null;

if (!$quotationId) {
    echo 'Quotation ID is required.';
    exit;
}

$buyer_id = $_SESSION['buyer_id'];

try {
    // Fetch main quotation details
    $stmt = $conn->prepare("SELECT * FROM buyer_quotations WHERE id = ? AND buyer_id = ?");
    $stmt->execute([$quotationId, $buyer_id]);
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$quotation) {
        echo 'Quotation not found or you do not have permission to view it.';
        exit;
    }

    // Fetch quotation products
    $stmt = $conn->prepare("SELECT * FROM quotation_products WHERE quotation_id = ?");
    $stmt->execute([$quotationId]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $uploadDir = BASE_URL . 'assets/images/upload/buyer_quotations/';

?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <p><strong>Quotation Number:</strong> <?php echo htmlspecialchars($quotation['quotation_number']); ?></p>
            <p><strong>Quotation Date:</strong> <?php echo date('d M Y', strtotime($quotation['quotation_date'])); ?></p>
            <p><strong>Customer Name:</strong> <?php echo htmlspecialchars($quotation['customer_name']); ?></p>
            <p><strong>Customer Email:</strong> <?php echo htmlspecialchars($quotation['customer_email']); ?></p>
            <p><strong>Customer Phone:</strong> <?php echo htmlspecialchars($quotation['customer_phone']); ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>Delivery Term:</strong> <?php echo htmlspecialchars($quotation['delivery_term']); ?></p>
            <p><strong>Terms of Delivery:</strong> <?php echo htmlspecialchars($quotation['terms_of_delivery']); ?></p>
            <p><strong>Total Amount:</strong> $<?php echo number_format($quotation['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <span class="badge badge-info"><?php echo htmlspecialchars(ucfirst($quotation['status'])); ?></span></p>
        </div>
    </div>

    <h5 class="mt-4">Products</h5>
    <?php if (empty($products)): ?>
        <p>No products found for this quotation.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price USD</th>
                        <th>Total USD</th>
                        <th>Comments</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $index => $product): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td>
                                <?php if (!empty($product['product_image_name'])): ?>
                                    <img src="<?php echo $uploadDir . htmlspecialchars($product['product_image_name']); ?>" 
                                         alt="Product Image" width="50" height="50" style="object-fit: cover;">
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($product['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                            <td>$<?php echo number_format($product['price_usd'], 2); ?></td>
                            <td>$<?php echo number_format($product['total_usd'], 2); ?></td>
                            <td><?php echo htmlspecialchars($product['comments']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php
} catch (PDOException $e) {
    echo 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
