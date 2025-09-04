<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($conn)) {
    global $conn;
}

$response = ['success' => false, 'message' => 'Unknown error occurred'];

$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$records_per_page = isset($_POST['records_per_page']) ? (int)$_POST['records_per_page'] : 20;
$search = isset($_POST['search']) ? trim($_POST['search']) : '';

$offset = ($page - 1) * $records_per_page;

try {
    $whereClauses = [];
    $params = [];

    if ($search !== '') {
        $searchWildcard = '%' . $search . '%';
        $whereClauses[] = "(quotation_number LIKE :search OR customer_name LIKE :search OR customer_email LIKE :search)";
        $params[':search'] = $searchWildcard;
    }

    $whereSql = '';
    if (count($whereClauses) > 0) {
        $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
    }

    // Get total records count
    $countQuery = "SELECT COUNT(*) FROM quotations $whereSql";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetchColumn();

    // Get paginated records
    $dataQuery = "SELECT id, lead_id, quotation_number, customer_name, customer_email, customer_phone, delivery_term, terms_of_delivery, approve, locked FROM quotations $whereSql ORDER BY id DESC LIMIT :offset, :limit";
    $dataStmt = $conn->prepare($dataQuery);
    foreach ($params as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $dataStmt->execute();
    $quotations = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = [
        'quotations' => $quotations,
        'total_records' => $totalRecords
    ];
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>
