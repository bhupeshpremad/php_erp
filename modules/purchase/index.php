<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// Ensure ROOT_DIR_PATH is defined if not already from config.php
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', str_replace('\\', '/', dirname(__DIR__, 2)) . '/');
}

include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? 'guest';

// Include appropriate sidebar based on user type
if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'accountsadmin') {
    include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php';
}

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

// Check if created_by column exists
$has_created_by = false;
try {
    $stmt_check = $conn->query("SHOW COLUMNS FROM purchase_main LIKE 'created_by'");
    $has_created_by = $stmt_check->rowCount() > 0;
} catch (Exception $e) {
    $has_created_by = false;
}

// Get current user ID for filtering
$current_user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;

// Add user filter to WHERE clauses (only if column exists and not superadmin)
if ($has_created_by && $current_user_id && $_SESSION['user_type'] !== 'superadmin') {
    $whereClauses[] = 'p.created_by = :created_by';
    $params[':created_by'] = $current_user_id;
}

$whereSql = '';
if (count($whereClauses) > 0) {
    $whereSql = 'WHERE ' . implode(' AND ', $whereClauses);
}

// Main data query - include created_by only if column exists
$select_fields = "p.id, p.po_number, p.jci_number, p.sell_order_number, p.approval_status";
if ($has_created_by) {
    $select_fields .= ", p.created_by";
}

$sql = "SELECT $select_fields 
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
                            
                            // Fetch images for this purchase
                            $stmt_images = $conn->prepare("SELECT invoice_image, builty_image FROM purchase_items WHERE purchase_main_id = ?");
                            $stmt_images->execute([$purchase_id]);
                            $images = $stmt_images->fetchAll(PDO::FETCH_ASSOC);
                            
                            echo "<tr>";
                            echo "<td>" . $serial++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['jci_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['sell_order_number']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['po_number']) . "</td>";
                            
                            // Approval Status
                            $status = $row['approval_status'] ?? 'pending';
                            $statusClass = $status === 'approved' ? 'success' : ($status === 'sent_for_approval' ? 'warning' : 'secondary');
                            echo "<td><span class='badge badge-{$statusClass}'>" . ucfirst(str_replace('_', ' ', $status)) . "</span></td>";
                            
                            // View Details
                            echo "<td><button class='btn btn-info btn-sm view-details-btn' data-id='{$purchase_id}'>View Details</button></td>";
                            
                            echo "<td>";
                            // Both superadmin and accountsadmin can edit
                            if ($user_type === 'superadmin' || $user_type === 'accountsadmin') {
                                echo "<a href='add.php?id={$purchase_id}' class='btn btn-primary btn-sm'>Edit</a>";
                            }

                            if ($user_type === 'accountsadmin') {
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

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailsModalLabel">Purchase Details</h5>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
var BASE_URL = '<?php echo BASE_URL; ?>';
$(document).ready(function() {
    $('#purchaseTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthChange: false,
        searching: false
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

    $(document).on('click', '.approve-supplier-btn', function() {
        var supplierId = $(this).data('supplier-id');
        var purchaseId = $(this).data('purchase-id');
        if (confirm('Are you sure you want to approve this supplier?')) {
            $.ajax({
                url: 'ajax_approve_supplier.php',
                method: 'POST',
                data: { supplier_id: supplierId, purchase_id: purchaseId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        showDetails(purchaseId);
                    } else {
                        toastr.error(response.error || 'Error approving supplier.');
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('AJAX error: ' + error);
                }
            });
        }
    });

    function showDetails(purchaseId) {
        $('#detailsModalLabel').text('Purchase Details (ID: ' + purchaseId + ')');
        $('#detailsModalBody').html('<div class="text-center"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...</div>');
        $('#detailsModal').modal('show');
        $.ajax({
            url: 'fetch_supplier_details.php',
            method: 'POST',
            data: { purchase_id: purchaseId, user_type: '<?php echo $user_type; ?>' },
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




});
</script>
<?php
include_once ROOT_DIR_PATH . 'include/inc/footer.php';
?>