<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$bom_id = (int)($_POST['bom_id'] ?? 0);

if ($bom_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid BOM ID provided.']);
    exit;
}

try {
    $items = [
        'wood' => [],
        'glow' => [],
        'plynydf' => [],
        'hardware' => [],
        'labour' => [],
        'factory' => [],
        'margin' => []
    ];

    // Fetch wood items
    $stmt = $conn->prepare("SELECT * FROM bom_wood WHERE bom_main_id = ?");
    $stmt->execute([$bom_id]);
    $items['wood'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch glow items
    $stmt = $conn->prepare("SELECT * FROM bom_glow WHERE bom_main_id = ?");
    $stmt->execute([$bom_id]);
    $items['glow'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch plynydf items
    $stmt = $conn->prepare("SELECT * FROM bom_plynydf WHERE bom_main_id = ?");
    $stmt->execute([$bom_id]);
    $items['plynydf'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch hardware items
    $stmt = $conn->prepare("SELECT * FROM bom_hardware WHERE bom_main_id = ?");
    $stmt->execute([$bom_id]);
    $items['hardware'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch labour items
    $stmt = $conn->prepare("SELECT * FROM bom_labour WHERE bom_main_id = ?");
    $stmt->execute([$bom_id]);
    $items['labour'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch factory items
    $stmt = $conn->prepare("SELECT * FROM bom_factory WHERE bom_main_id = ?");
    $stmt->execute([$bom_id]);
    $items['factory'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch margin items
    $stmt = $conn->prepare("SELECT * FROM bom_margin WHERE bom_main_id = ?");
    $stmt->execute([$bom_id]);
    $items['margin'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'items' => $items]);
    exit;
} catch (Exception $e) {
    error_log("Error fetching BOM items: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred while fetching items. Please try again later.']);
    exit;
}