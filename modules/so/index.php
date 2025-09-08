<?php
session_start();

include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? 'guest';

if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} elseif ($user_type === 'accounts') {
    include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php';
}

global $conn;

// Fetch ONLY Approved and Locked Purchase Orders to display them as Sell Orders
// We also fetch the sell_order_number here and JCI status
$stmt = $conn->prepare("SELECT id, po_number, client_name, prepared_by, order_date, delivery_date, created_at, updated_at, status, is_locked, sell_order_number, jci_assigned, CASE WHEN jci_assigned = 1 THEN 'JCI Created' ELSE 'Available' END as jci_status
                        FROM po_main
                        WHERE status IN ('Approved', 'Locked')
                        ORDER BY created_at DESC");
$stmt->execute();
$so_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mb-5">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Sell Orders (Approved/Locked POs)</h6>
            <input type="text" id="soSearchInput" class="form-control form-control-sm w-25" placeholder="Search Sell Orders...">
        </div>
        <div class="card-body">
            <?php if (count($so_list) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="soTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>SO Number</th>
                            <th>PO Number</th>
                            <th>Client Name</th>
                            <th>Prepared By</th>
                            <th>Order Date</th>
                            <th>Delivery Date</th>
                            <th>Status</th>
                            <th>JCI Status</th>
                            <th>Item List</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($so_list as $so): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($so['id']); ?></td>
                            <td><?php echo htmlspecialchars($so['sell_order_number'] ?? $so['po_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($so['po_number'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($so['client_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($so['prepared_by'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($so['order_date'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($so['delivery_date'] ?? ''); ?></td>
                            <td>
                                <span class="badge
                                    <?php
                                        if ($so['status'] == 'Approved') echo 'bg-success';
                                        else if ($so['status'] == 'Locked') echo 'bg-danger';
                                        else echo 'bg-info text-dark';
                                    ?>">
                                    <?php echo htmlspecialchars($so['status'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td><span class="badge badge-<?php echo $so['jci_assigned'] ? 'warning' : 'success'; ?>"><?php echo $so['jci_status']; ?></span></td>
                            <td>
                                <button class="btn btn-info btn-sm view-so-items-btn" data-so-id="<?php echo $so['id']; ?>">View Items</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p>No Approved or Locked Sell Orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="soItemDetailsModal" tabindex="-1" aria-labelledby="soItemDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="soItemDetailsModalLabel">Sell Order Item Details - Total: <span id="soTotalAmount">0.00</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="soItemDetailsTable">
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Product Code</th>
                    <th>Product Name</th>

                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Print</th>
                </tr>
            </thead>
            <tbody>
                </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
    $(document).ready(function () {
        $('#soTable').DataTable({
            order: [[0, 'desc']],
            pageLength: 10,
            lengthChange: false,
            searching: false
        });

        $(document).on('click', '.view-so-items-btn', function() {
            const poId = $(this).data('so-id');

            $.ajax({
                url: '<?php echo BASE_URL; ?>modules/so/ajax_fetch_so_items.php',
                type: 'POST',
                data: { po_id: poId },
                dataType: 'json',
                success: function(data) {
                    const tbody = $('#soItemDetailsTable tbody');
                    tbody.empty();

                    if (data.success && data.items.length > 0) {
                        let totalAmount = 0;
                        data.items.forEach((item, idx) => {
                            totalAmount += parseFloat(item.total_amount || 0);
                            const tr = `
                                <tr>
                                    <td>${idx + 1}</td>
                                    <td>${item.product_code || ''}</td>
                                    <td>${item.product_name || ''}</td>
                                    <td>${item.quantity || ''}</td>
                                    <td>${item.price || ''}</td>
                                    <td><button class="btn btn-sm btn-primary" onclick="printSO()">PDF</button></td>
                                </tr>
                            `;
                            tbody.append(tr);
                        });
                        $('#soTotalAmount').text(totalAmount.toFixed(2));
                    } else if (data.success && data.items.length === 0) {
                        tbody.append('<tr><td colspan="6" class="text-center">No items found for this Sell Order.</td></tr>');
                        $('#soTotalAmount').text('0.00');
                    } else {
                        tbody.append(`<tr><td colspan="6" class="text-center text-danger">Error: ${data.message || 'Failed to load items.'}</td></tr>`);
                        $('#soTotalAmount').text('0.00');
                        toastr.error(data.message || 'Failed to load Sell Order items.');
                    }
                    var modal = new bootstrap.Modal(document.getElementById('soItemDetailsModal'));
                    modal.show();
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", status, error, xhr.responseText);
                    $('#soItemDetailsTable tbody').empty().append(`<tr><td colspan="6" class="text-center text-danger">Network error or failed to parse response: ${error}</td></tr>`);
                    $('#soTotalAmount').text('0.00');
                    toastr.error('An error occurred while fetching Sell Order items.');
                    var modal = new bootstrap.Modal(document.getElementById('soItemDetailsModal'));
                    modal.show();
                }
            });
        });
    });
    
    function printSO() {
        window.print();
    }
</script>