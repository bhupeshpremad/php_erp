<?php
include_once __DIR__ . '/../../config/config.php';

global $conn;

try {
    $currentYear = date('Y');
    // Fetch the latest quotation number for the current year
    $stmt = $conn->prepare("SELECT quotation_number FROM quotations WHERE quotation_number LIKE ? ORDER BY id DESC LIMIT 1");
    $likePattern = "QUOTE-{$currentYear}-%";
    $stmt->execute([$likePattern]);
    $latest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($latest && !empty($latest['quotation_number'])) {
        $lastNumber = $latest['quotation_number'];
        if (preg_match('/(\d+)$/', $lastNumber, $matches)) {
            $number = intval($matches[1]) + 1;
            $newQuotationNumber = sprintf("QUOTE-%s-%05d", $currentYear, $number);
        } else {
            $newQuotationNumber = sprintf("QUOTE-%s-%05d", $currentYear, 1);
        }
    } else {
        // Start sequence for new year
        $newQuotationNumber = sprintf("QUOTE-%s-%05d", $currentYear, 1);
    }

    echo json_encode(['success' => true, 'latest_quotation_number' => $newQuotationNumber]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error fetching latest quotation number: ' . $e->getMessage()]);
}
?>
