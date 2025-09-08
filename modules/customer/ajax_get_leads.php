<?php
include_once __DIR__ . '/../../config/config.php';

$lead_id = $_GET['lead_id'] ?? null;

if (!$lead_id) {
    echo '<p>No lead ID provided.</p>';
    exit;
}

try {
    global $conn;
    
    $stmt = $conn->prepare("SELECT * FROM leads WHERE id = ?");
    $stmt->execute([$lead_id]);
    $lead = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lead) {
        echo '<table class="table table-bordered">';
        echo '<tr><th>Lead Number</th><td>' . htmlspecialchars($lead['lead_number']) . '</td></tr>';
        echo '<tr><th>Company Name</th><td>' . htmlspecialchars($lead['company_name']) . '</td></tr>';
        echo '<tr><th>Contact Email</th><td>' . htmlspecialchars($lead['contact_email']) . '</td></tr>';
        echo '<tr><th>Contact Phone</th><td>' . htmlspecialchars($lead['contact_phone']) . '</td></tr>';
        echo '<tr><th>Status</th><td>' . ($lead['approve'] ? 'Approved' : 'Pending') . '</td></tr>';
        echo '<tr><th>Created Date</th><td>' . htmlspecialchars($lead['created_at']) . '</td></tr>';
        echo '</table>';
    } else {
        echo '<p>Lead not found.</p>';
    }
    
} catch (Exception $e) {
    echo '<p>Error loading lead details: ' . htmlspecialchars($e->getMessage()) . '</p>';
}
?>