<?php
include_once __DIR__ . '/../../config/config.php';

header('Content-Type: application/json');

if (!isset($conn)) {
    global $conn;
}

$response = ['success' => false, 'message' => 'Unknown error occurred'];

$draw = isset($_POST['draw']) ? (int)$_POST['draw'] : 1;
$start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
$length = isset($_POST['length']) ? (int)$_POST['length'] : 10;
$searchValue = isset($_POST['search']['value']) ? trim($_POST['search']['value']) : '';

$offset = $start;
$records_per_page = $length;
$search = $searchValue;

try {
    // Get total records without filter
    $totalQuery = "SELECT COUNT(*) FROM quotations";
    $totalStmt = $conn->prepare($totalQuery);
    $totalStmt->execute();
    $recordsTotal = $totalStmt->fetchColumn();

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

    // Get filtered records count
    $countQuery = "SELECT COUNT(*) FROM quotations $whereSql";
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $recordsFiltered = $countStmt->fetchColumn();

    // Get paginated records
    $dataQuery = "SELECT id, lead_id, quotation_number, customer_name, customer_email, customer_phone, delivery_term, terms_of_delivery, approve, is_locked, quotation_image as excel_file FROM quotations $whereSql ORDER BY id DESC LIMIT :offset, :limit";
    $dataStmt = $conn->prepare($dataQuery);
    foreach ($params as $key => $value) {
        $dataStmt->bindValue($key, $value);
    }
    $dataStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $dataStmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $dataStmt->execute();
    $quotations = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['draw'] = $draw;
    $response['recordsTotal'] = $recordsTotal;
    $response['recordsFiltered'] = $recordsFiltered;
    $response['data'] = $quotations;
} catch (Exception $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>
