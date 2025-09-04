<?php
include_once __DIR__ . '/../../config/config.php';

$lead_id = $_GET['lead_id'] ?? null;

if (!$lead_id) {
    echo '<p>No lead ID provided.</p>';
    exit;
}

try {
    global $conn;
    
    $stmt = $conn->prepare("SELECT p.*, q.quotation_number 
                           FROM pi p 
                           JOIN quotations q ON p.quotation_id = q.id 
                           WHERE q.lead_id = ? 
                           ORDER BY p.pi_id DESC");
    $stmt->execute([$lead_id]);
    $pis = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($pis) {
        echo '<table class="table table-bordered table-striped">';
        echo '<thead><tr><th>PI Number</th><th>Quotation Number</th><th>Status</th><th>Date</th><th>Action</th></tr></thead>';
        echo '<tbody>';
        foreach ($pis as $pi) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($pi['pi_number']) . '</td>';
            echo '<td>' . htmlspecialchars($pi['quotation_number']) . '</td>';
            echo '<td><span class="badge badge-info">' . htmlspecialchars($pi['status']) . '</span></td>';
            echo '<td>' . htmlspecialchars($pi['date_of_pi_raised']) . '</td>';
            echo '<td><a href="../pi/index.php" class="btn btn-sm btn-info">View PI</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No PIs found for this customer.</p>';
    }
    
} catch (Exception $e) {
    echo '<p>Error loading PIs: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>