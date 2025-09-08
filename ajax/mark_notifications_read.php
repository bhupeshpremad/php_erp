<?php
session_start();
require_once '../config/config.php';
require_once '../core/NotificationSystem.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

NotificationSystem::init($conn);

$user_type = $_SESSION['user_type'] ?? $_SESSION['role'] ?? 'guest';
$user_id = $_SESSION['user_id'] ?? null;

if ($action === 'mark_all_read') {
    $result = NotificationSystem::markAllAsRead($user_type, $user_id);
    echo json_encode(['success' => $result]);
} elseif ($action === 'mark_read' && isset($input['id'])) {
    $result = NotificationSystem::markAsRead($input['id']);
    echo json_encode(['success' => $result]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>