<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include_once __DIR__ . '/../../config/config.php'; // Ensure this path is correct and it properly initializes $conn

/**
 * @var PDO $conn
 */
global $conn; // Make sure $conn is truly global and accessible here

$response = ['success' => false, 'message' => 'Unknown error occurred'];

// Basic check to ensure $conn is a valid PDO object
if (!$conn instanceof PDO) {
    $response['message'] = 'Database connection not established. Check config.php.';
    echo json_encode($response);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_next_lead_number') {
    try {
        $currentYear = date('Y');
        $query = "SELECT lead_number FROM leads WHERE lead_number LIKE 'LEAD-$currentYear-%' ORDER BY id DESC LIMIT 1";
        $stmt = $conn->prepare($query);

        if ($stmt === false) { // Check if prepare failed
            throw new Exception('Failed to prepare statement for get_next_lead_number.');
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result && isset($result['lead_number'])) {
            // Extract the numeric part and increment
            $parts = explode('-', $result['lead_number']);
            $lastNumber = intval(end($parts));
            $nextNumber = $lastNumber + 1;
        } else {
            // If no lead number found for this year, start with 1
            $nextNumber = 1;
        }

        // Format the new lead number
        $nextLeadNumber = sprintf("LEAD-%s-%04d", $currentYear, $nextNumber);

        $response['success'] = true;
        $response['lead_number'] = $nextLeadNumber;
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

} elseif ($action === 'get_lead') {
    $lead_id = $_POST['lead_id'] ?? null;
    if ($lead_id) {
        try {
            $query = "SELECT * FROM leads WHERE id = :lead_id";
            $stmt = $conn->prepare($query);

            if ($stmt === false) { // Check if prepare failed
                throw new Exception('Failed to prepare statement for get_lead.');
            }

            $stmt->bindParam(':lead_id', $lead_id, PDO::PARAM_INT);
            $stmt->execute();
            $lead = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($lead) {
                $response['success'] = true;
                $response['lead'] = $lead;
            } else {
                $response['message'] = 'Lead not found.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Missing lead ID.';
    }
} elseif ($action === 'get_status_history') {
    $lead_id = $_POST['lead_id'] ?? null;
    if ($lead_id) {
        try {
            $query = "SELECT status_text, status_date, created_at FROM status WHERE lead_id = :lead_id ORDER BY status_date DESC";
            $stmt = $conn->prepare($query);

            if ($stmt === false) { // Check if prepare failed
                throw new Exception('Failed to prepare statement for get_status_history.');
            }

            $stmt->bindParam(':lead_id', $lead_id, PDO::PARAM_INT);
            $stmt->execute();
            $statuses = $stmt->fetchAll(PDO::FETCH_ASSOC); // This will now be called on a PDOStatement object
            $response['success'] = true;
            $response['statuses'] = $statuses;
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Missing lead ID.';
    }
} elseif ($action === 'create' || $action === 'update') {
    $lead_id = $_POST['lead_id'] ?? null;
    $lead_number = $_POST['lead_number'] ?? '';
    $entry_date = $_POST['entry_date'] ?? '';
    $company_name = $_POST['company_name'] ?? '';
    $contact_person = $_POST['contact_person'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $country = $_POST['country'] ?? '';
    $state = $_POST['state'] ?? '';
    $city = $_POST['city'] ?? '';
    $lead_source = $_POST['lead_source'] ?? '';

    try {
        if ($action === 'create') {
            $query = "INSERT INTO leads (lead_number, entry_date, company_name, contact_name, contact_phone, contact_email, country, state, city, lead_source) VALUES (:lead_number, :entry_date, :company_name, :contact_person, :phone, :email, :country, :state, :city, :lead_source)";
            $stmt = $conn->prepare($query);

            if ($stmt === false) { // Check if prepare failed
                throw new Exception('Failed to prepare statement for create lead.');
            }

            $stmt->bindParam(':lead_number', $lead_number);
            $stmt->bindParam(':entry_date', $entry_date);
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':contact_person', $contact_person);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':country', $country);
            $stmt->bindParam(':state', $state);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':lead_source', $lead_source);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Lead created successfully.';
            } else {
                $response['message'] = 'Failed to create lead.';
            }
        } elseif ($action === 'update' && $lead_id) {
            $query = "UPDATE leads SET lead_number = :lead_number, entry_date = :entry_date, company_name = :company_name, contact_name = :contact_person, contact_phone = :phone, contact_email = :email, country = :country, state = :state, city = :city, lead_source = :lead_source WHERE id = :lead_id";
            $stmt = $conn->prepare($query);

            if ($stmt === false) { // Check if prepare failed
                throw new Exception('Failed to prepare statement for update lead.');
            }

            $stmt->bindParam(':lead_number', $lead_number);
            $stmt->bindParam(':entry_date', $entry_date);
            $stmt->bindParam(':company_name', $company_name);
            $stmt->bindParam(':contact_person', $contact_person);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':country', $country);
            $stmt->bindParam(':state', $state);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':lead_source', $lead_source);
            $stmt->bindParam(':lead_id', $lead_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Lead updated successfully.';
            } else {
                $response['message'] = 'Failed to update lead.';
            }
        } else {
            $response['message'] = 'Invalid request parameters.';
        }
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} elseif ($action === 'add_status') {
    $lead_id = $_POST['lead_id'] ?? null;
    $status_text = $_POST['status_text'] ?? '';
    $status_date = $_POST['status_date'] ?? '';
    if ($lead_id && $status_text && $status_date) {
        try {
            $query = "INSERT INTO status (lead_id, status_text, status_date) VALUES (:lead_id, :status_text, :status_date)";
            $stmt = $conn->prepare($query);

            if ($stmt === false) { // Check if prepare failed
                throw new Exception('Failed to prepare statement for add_status.');
            }

            $stmt->bindParam(':lead_id', $lead_id, PDO::PARAM_INT);
            $stmt->bindParam(':status_text', $status_text);
            $stmt->bindParam(':status_date', $status_date);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Status added successfully.';
            } else {
                $response['message'] = 'Failed to add status.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Please fill all status fields.';
    }
} elseif ($action === 'toggle_status') {
    $lead_id = $_POST['lead_id'] ?? null;
    $status = $_POST['status'] ?? null;
    if ($lead_id && $status) {
        try {
            $query = "UPDATE leads SET status = :status WHERE id = :lead_id";
            $stmt = $conn->prepare($query);

            if ($stmt === false) { // Check if prepare failed
                throw new Exception('Failed to prepare statement for toggle_status.');
            }

            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':lead_id', $lead_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Status updated to $status.";
            } else {
                $response['message'] = 'Failed to update status.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Missing lead ID or status.';
    }
} elseif ($action === 'toggle_approve') {
    $lead_id = $_POST['lead_id'] ?? null;
    $approve = $_POST['approve'] ?? null;
    if ($lead_id !== null && $approve !== null) {
        try {
            $query = "UPDATE leads SET approve = :approve WHERE id = :lead_id";
            $stmt = $conn->prepare($query);

            if ($stmt === false) { // Check if prepare failed
                throw new Exception('Failed to prepare statement for toggle_approve.');
            }

            $stmt->bindParam(':approve', $approve, PDO::PARAM_INT);
            $stmt->bindParam(':lead_id', $lead_id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = "Approval updated.";
            } else {
                $response['message'] = 'Failed to update approval.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Missing lead ID or approval value.';
    }
} elseif ($action === 'search_leads') {
    $search = $_POST['search'] ?? '';
    try {
        $searchTerm = '%' . $search . '%';
        $query = "SELECT * FROM leads WHERE lead_number LIKE :search OR contact_name LIKE :search OR contact_email LIKE :search OR country LIKE :search ORDER BY id DESC";
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            throw new Exception('Failed to prepare statement for search_leads.');
        }

        $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response['success'] = true;
        $response['leads'] = $leads;
    } catch (Exception $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['message'] = 'Invalid request.';
}

echo json_encode($response);
exit;