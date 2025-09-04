<?php
include_once __DIR__ . '/../../config/config.php';

$pi_id = $_POST['pi_id'] ?? null;

if (!$pi_id) {
    echo '<p>No PI ID provided.</p>';
    exit;
}

try {
    global $conn;
    
    // Get quotation details from PI
    $stmt = $conn->prepare("SELECT quotation_id FROM pi WHERE pi_id = ?");
    $stmt->execute([$pi_id]);
    $pi = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pi) {
        echo '<p>PI not found.</p>';
        exit;
    }
    
    // Get quotation details
    $stmt = $conn->prepare("SELECT * FROM quotations WHERE id = ?");
    $stmt->execute([$pi['quotation_id']]);
    $quotation = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($quotation) {
        echo '<table class="table table-bordered">';
        echo '<tr><th>Quotation Number</th><td>' . htmlspecialchars($quotation['quotation_number']) . '</td></tr>';
        echo '<tr><th>Customer Name</th><td>' . htmlspecialchars($quotation['customer_name']) . '</td></tr>';
        echo '<tr><th>Customer Email</th><td>' . htmlspecialchars($quotation['customer_email']) . '</td></tr>';
        echo '<tr><th>Customer Phone</th><td>' . htmlspecialchars($quotation['customer_phone']) . '</td></tr>';
        echo '<tr><th>Quotation Date</th><td>' . htmlspecialchars($quotation['quotation_date']) . '</td></tr>';
        echo '<tr><th>Delivery Terms</th><td>' . htmlspecialchars($quotation['delivery_term']) . '</td></tr>';
        echo '<tr><th>Terms of Delivery</th><td>' . htmlspecialchars($quotation['terms_of_delivery']) . '</td></tr>';
        echo '<tr><th>Status</th><td>';
        if ($quotation['locked']) echo '<span class="badge badge-danger">Locked</span>';
        elseif ($quotation['approve']) echo '<span class="badge badge-success">Approved</span>';
        else echo '<span class="badge badge-warning">Draft</span>';
        echo '</td></tr>';
        echo '</table>';
        
        // Get quotation products
        $stmt = $conn->prepare("SELECT * FROM quotation_products WHERE quotation_id = ?");
        $stmt->execute([$quotation['id']]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if ($products) {
            echo '<h6 class="mt-3">Products:</h6>';
            echo '<table class="table table-bordered table-sm">';
            echo '<thead><tr><th>Item Name</th><th>Quantity</th><th>Price USD</th><th>Total USD</th></tr></thead>';
            echo '<tbody>';
            $total = 0;
            foreach ($products as $product) {
                $productTotal = $product['quantity'] * $product['price_usd'];
                $total += $productTotal;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($product['item_name']) . '</td>';
                echo '<td>' . htmlspecialchars($product['quantity']) . '</td>';
                echo '<td>$' . number_format($product['price_usd'], 2) . '</td>';
                echo '<td>$' . number_format($productTotal, 2) . '</td>';
                echo '</tr>';
            }
            echo '<tr class="font-weight-bold"><td colspan="3">Total</td><td>$' . number_format($total, 2) . '</td></tr>';
            echo '</tbody></table>';
        }
    } else {
        echo '<p>Quotation not found.</p>';
    }
    
} catch (Exception $e) {
    echo '<p>Error loading quotation details: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>