<?php
session_start();
include '../config/config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['supplier_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $supplier_id = $_SESSION['supplier_id'];
    $company_name = trim($_POST['company_name']);
    $contact_person_name = trim($_POST['contact_person_name']);
    $contact_person_email = trim($_POST['contact_person_email']);
    $contact_person_phone = trim($_POST['contact_person_phone']);
    $company_address = trim($_POST['company_address']);
    
    // Validation
    if (empty($company_name) || empty($contact_person_name) || empty($contact_person_email) || empty($contact_person_phone) || empty($company_address)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit;
    }
    
    if (!filter_var($contact_person_email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        exit;
    }
    
    // Check if email exists for other suppliers
    $stmt = $conn->prepare("SELECT id FROM suppliers WHERE contact_person_email = ? AND id != ?");
    $stmt->execute([$contact_person_email, $supplier_id]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        exit;
    }
    
    // Update profile
    $stmt = $conn->prepare("UPDATE suppliers SET company_name = ?, contact_person_name = ?, contact_person_email = ?, contact_person_phone = ?, company_address = ? WHERE id = ?");
    $stmt->execute([$company_name, $contact_person_name, $contact_person_email, $contact_person_phone, $company_address, $supplier_id]);
    
    // Update session
    $_SESSION['supplier_name'] = $contact_person_name;
    $_SESSION['company_name'] = $company_name;
    $_SESSION['supplier_email'] = $contact_person_email;
    
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>