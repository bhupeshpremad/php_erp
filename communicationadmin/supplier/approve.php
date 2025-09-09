<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'communicationadmin') {
    header('Location: ' . BASE_URL . 'index.php');
    exit;
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE suppliers SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Supplier approved successfully!";
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE suppliers SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
        $message = "Supplier rejected successfully!";
    }
    
    header('Location: list.php?message=' . urlencode($message));
    exit;
}

header('Location: list.php');
exit;
?>