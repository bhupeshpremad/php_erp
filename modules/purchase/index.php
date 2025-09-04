<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// Ensure ROOT_DIR_PATH is defined if not already from config.php
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', str_replace('\\', '/', dirname(__DIR__, 2)) . '/');
}

include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? 'guest';

// The sidebar is now included conditionally in header.php
// include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php'; // THIS LINE IS NOW REMOVED

// Check if user is logged in as accountsadmin or superadmin
if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'accountsadmin' && $_SESSION['user_type'] !== 'superadmin')) {
    header("Location: " . BASE_URL . "index.php"); // Redirect to login page if not authorized
    exit(); // Terminate script execution
}

global $conn;

$search_po = $_GET['search_po'] ?? '';
$search_jci = $_GET['search_jci'] ?? '';
$search_son = $_GET['search_son'] ?? '';

$whereClauses = [];
$params = [];

if ($search_po !== '') {
    $whereClauses[] = 'p.po_number LIKE :po_number';
    $params[':po_number'] = '%' . $search_po . '%';
}
if ($search_jci !== '') {
    $whereClauses[] = 'p.jci_number LIKE :jci_number';
    $params[':jci_number'] = '%' . $search_jci . '%';
}
if ($search_son !== '') {
    $whereClauses[] = 'p.sell_order_number LIKE :sell_order_number';
    $params[':sell_order_number'] = '%' . $search_son . '%';
}

$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Main data query
$sql = "SELECT p.id, p.po_number, p.jci_number, p.sell_order_number, p.approval_status 
        FROM purchase_main p 
        $whereSql 
        ORDER BY p.id DESC";

$stmt = $conn->prepare($sql);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value, PDO::PARAM_STR);
}

$stmt->execute();
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Purchase List</h6>
                <form action="" method="GET" class="d-flex flex-wrap gap-2">
                    <input type="text" name="search_po" class="form-control form-control-sm mb-2" style="max-width:180px;" placeholder="Search PO No." value="<?php echo htmlspecialchars($search_po); ?>">
                    <input type="text" name="search_jci" class="form-control form-control-sm mb-2" style="max-width:180px;" placeholder="Search JCI No." value="<?php echo htmlspecialchars($search_jci); ?>">
                    <input type="text" name="search_son" class="form-control form-control-sm mb-2" style="max-width:180px;" placeholder="Search Sell Order No." value="<?php echo htmlspecialchars($search_son); ?>">
                    <button type="submit" class="btn btn-info btn-sm ms-2 mb-2 mx-2">Search</button>
                    <a href="add.php" class="btn btn-primary btn-sm ms-2 mb-2">Add Purchase</a>
                </form>
        </div>
        <div class="card-body">
            <table class="table table-bordered" id="purchaseTable">
                <thead>
                    <tr>
                        <th>Serial Number</th>
                        <th>JCI Number</th>
                        <th>Sell Order Number</th>
                        <th>PO Number</th>
                        <th>Approval Status</th>
                        <th>View Details</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $serial = 1;
                    if ($result && count($result) > 0) {
                        foreach ($result as $row) {
                            $purchase_id = $row['id'];
                            echo "<tr>";
                            echo "<td>" . $serial++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['jci_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['sell_order_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['po_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['approval_status']) . "</td>";
                            echo "<td><button class='btn btn-info btn-sm view-details-btn' data-id='{$purchase_id}'>View Details</button></td>";
                            echo "<td>";
                            echo "<a href='add.php?id={$purchase_id}' class='btn btn-primary btn-sm'>Edit</a>";

                            if ($user_type === 'accountsadmin' && $row['approval_status'] === 'pending') {
                                echo " <button class='btn btn-warning btn-sm send-approval-btn' data-id='{$purchase_id}'>Send Approval</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center'>No purchase records found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detailsModalBody">
                <div class="text-center">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
var BASE_URL = '<?php echo BASE_URL; ?>';
$(document).ready(function() {
    $('#purchaseTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthChange: false,
        searching: false
    });

    $('.purchase-details-link').click(function(e) {
        e.preventDefault();
        var purchaseId = $(this).data('id');
        showDetails(purchaseId);
    });

    $('.view-details-btn').click(function(e) {
        e.preventDefault();
        var purchaseId = $(this).data('id');
        showDetails(purchaseId);
    });

    $('#purchaseTable').on('click', '.send-approval-btn', function() {
        var purchaseId = $(this).data('id');
        updateApprovalStatus(purchaseId, 'send_for_approval');
    });

    $('#purchaseTable').on('click', '.approve-btn', function() {
        var purchaseId = $(this).data('id');
        updateApprovalStatus(purchaseId, 'approve');
    });

    function updateApprovalStatus(purchaseId, action) {
        if (!confirm('Are you sure you want to ' + action.replace(/_/g, ' ') + ' this purchase?')) {
            return;
        }

        $.ajax({
            url: 'ajax_update_approval_status.php',
            method: 'POST',
            data: { purchase_id: purchaseId, action: action },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Reload the page to reflect the new status
                    location.reload();
                } else {
                    toastr.error(response.error || 'Error updating status.');
                }
            },
            error: function(xhr, status, error) {
                toastr.error('AJAX error: ' + error);
            }
        });
    }

    function showDetails(purchaseId) {
        $('#detailsModalLabel').text('Purchase Details (ID: ' + purchaseId + ')');
        $('#detailsModalBody').html('<div class="text-center"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>');
        $('#detailsModal').modal('show');
        $.ajax({
            url: 'fetch_purchase_details.php?t=' + Date.now(),
            method: 'POST',
            data: { purchase_id: purchaseId, user_type: '<?php echo $user_type; ?>' }, // Pass user_type
            dataType: 'html',
            cache: false,
            success: function(data) {
                $('#detailsModalBody').html(data);
            },
            error: function(xhr, status, error) {
                $('#detailsModalBody').html('<div class="alert alert-danger">Failed to load details. Error: ' + (xhr.responseText || error) + '</div>');
            }
        });
    }

    // Handle per-item approval (moved from fetch_purchase_details.php)
    $(document).on('click', '.approve-item-btn', function() {
        var itemId = $(this).data('item-id');
        var purchaseId = $(this).closest('tr').find('input[name="purchase_id"]').val(); // Get purchaseId if needed for context
        if (confirm('Are you sure you want to approve this item?')) {
            $.ajax({
                url: BASE_URL + 'modules/purchase/ajax_update_item_approval_status.php',
                method: 'POST',
                data: { item_id: itemId, action: 'approve' },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Reload the entire modal content to reflect updated status
                        $('#detailsModal').modal('hide'); // Hide to ensure a clean reload
                        showDetails(purchaseId); // Reload the modal
                    } else {
                        toastr.error(response.error || 'Error approving item.');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('AJAX error: ' + error);
                }
            });
        }
    });
});
</script>
<?php
include_once ROOT_DIR_PATH . 'include/inc/footer.php';
?>