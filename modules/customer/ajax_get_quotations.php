<?php
include_once __DIR__ . '/../../config/config.php';

$lead_id = $_GET['lead_id'] ?? null;

if (!$lead_id) {
    echo '<p>No lead ID provided.</p>';
    exit;
}

try {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM quotations WHERE lead_id = ? ORDER BY id DESC");
    $stmt->execute([$lead_id]);
    $quotations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($quotations) {
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr><th>Quotation Number</th><th>Customer Name</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>';
        echo '<tbody>';
        foreach ($quotations as $quotation) {
            $status = 'Draft';
            if ($quotation['approve']) $status = 'Approved';
            if ($quotation['locked']) $status = 'Locked';
            
            echo '<tr>';
            echo '<td>' . htmlspecialchars($quotation['quotation_number']) . '</td>';
            echo '<td>' . htmlspecialchars($quotation['customer_name']) . '</td>';
            echo '<td><span class="badge badge-' . ($status == 'Approved' ? 'success' : ($status == 'Locked' ? 'danger' : 'warning')) . '">' . $status . '</span></td>';
            echo '<td>' . htmlspecialchars($quotation['quotation_date']) . '</td>';
            echo '<td><a href="../quotation/add.php?id=' . $quotation['id'] . '" class="btn btn-sm btn-primary">View</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No quotations found for this customer.</p>';
    }
    
} catch (Exception $e) {
    echo '<p>Error loading quotations: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>