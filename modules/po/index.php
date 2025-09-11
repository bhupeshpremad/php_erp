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

$stmt = $conn->prepare("SELECT id, po_number, client_name, prepared_by, created_at, updated_at, status, is_locked, jci_assigned, CASE WHEN jci_assigned = 1 THEN 'JCI Created' ELSE 'Available' END as jci_status FROM po_main ORDER BY id DESC");
$stmt->execute();
$po_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mb-5">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Purchase Orders</h6>
            <input type="text" id="poSearchInput" class="form-control form-control-sm w-25" placeholder="Search Purchase Orders...">
            <a href="add.php" class="btn btn-primary btn-sm">Add New PO</a>
        </div>
        <div class="card-body">
            <?php if (count($po_list) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="poTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>PO Number</th>
                            <th>Client Name</th>
                            <th>Prepared By</th>
                            <!-- <th>Created At</th> -->
                            <th>Updated At</th>
                            <th>JCI Status</th>
                            <th>Item Details</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($po_list as $po): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($po['id']); ?></td>
                            <td><?php echo htmlspecialchars($po['po_number']); ?></td>
                            <td><?php echo htmlspecialchars($po['client_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($po['prepared_by'] ?? ''); ?></td>
                            <!-- <td><?php echo htmlspecialchars($po['created_at'] ?? ''); ?></td> -->
                            <!-- <td><?php echo htmlspecialchars($po['updated_at'] ?? ''); ?></td> -->
                            <td><span class="badge badge-<?php echo $po['jci_assigned'] ? 'warning' : 'success'; ?>"><?php echo $po['jci_status']; ?></span></td>
                            <td>
                                <button class="btn btn-info btn-sm view-items-btn" data-po-id="<?php echo $po['id']; ?>">View Items</button>
                            </td>
                            <td class="d-flex gap-2">
                                <a href="add.php?id=<?php echo $po['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="generate_pdf.php?po_id=<?php echo $po['id']; ?>" class="btn btn-sm btn-success" target="_blank">PDF</a>
                            </td>
                            <td>
                                <?php if ($po['status'] != 'Approved'): ?>
                                    <button class="btn btn-sm btn-success approve-po-btn" data-po-id="<?php echo $po['id']; ?>">Approve</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p>No Purchase Orders found.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="poItemDetailsModal" tabindex="-1" aria-labelledby="poItemDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="poItemDetailsModalLabel">PO Item Details - Total: <span id="poTotalAmount">0.00</span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="poItemDetailsTable">
            <thead>
                <tr>
                    <th>Serial No</th>
                    <th>Product Image</th>
                    <th>Product Code</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <!-- Removed Print column header -->
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
    var poTable = $('#poTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthChange: false,
        searching: true,
        dom: 'rt<"bottom"p>'
    });
    
    // Custom search functionality
    $('#poSearchInput').on('keyup', function() {
        poTable.search(this.value).draw();
    });

    document.querySelectorAll('.view-items-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const poId = this.getAttribute('data-po-id');
            fetch('<?php echo BASE_URL; ?>modules/po/ajax_fetch_po_items.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'po_id=' + encodeURIComponent(poId)
            })
            .then(res => {
                if (!res.ok) {
                    return res.text().then(text => { throw new Error(text); });
                }
                return res.json();
            })
            .then(data => {
                const tbody = document.querySelector('#poItemDetailsTable tbody');
                tbody.innerHTML = '';
                if (data.success && data.items.length > 0) {
                    let totalAmount = 0;
                    data.items.forEach((item, idx) => {
                        totalAmount += parseFloat(item.total_amount || 0);
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${idx + 1}</td>
                            <td>${item.product_image ? `<img src="<?php echo BASE_URL; ?>modules/po/uploads/${item.product_image}" style="width:60px;height:60px;object-fit:cover;border:1px solid #ddd;" alt="Product">` : 'No Image'}</td>
                            <td>${item.product_code || ''}</td>
                            <td>${item.product_name || ''}</td>
                            <td>${item.quantity || ''}</td>
                            <td>${item.price || ''}</td>
<!-- Removed print button from modal item row -->
                        `;
                        tbody.appendChild(tr);
                    });
                    document.getElementById('poTotalAmount').textContent = totalAmount.toFixed(2);
                } else if (data.success && data.items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center">No items found for this PO.</td></tr>';
                    document.getElementById('poTotalAmount').textContent = '0.00';
                } else {
                    tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Error: ${data.message || 'Failed to load items.'}</td></tr>`;
                    document.getElementById('poTotalAmount').textContent = '0.00';
                }
                var modal = new bootstrap.Modal(document.getElementById('poItemDetailsModal'));
                modal.show();
            })
            .catch(error => {
                const tbody = document.querySelector('#poItemDetailsTable tbody');
                tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger">Network error or failed to parse response: ${error.message}</td></tr>`;
                document.getElementById('poTotalAmount').textContent = '0.00';
                var modal = new bootstrap.Modal(document.getElementById('poItemDetailsModal'));
                modal.show();
                console.error('Fetch error:', error);
            });
        });
    });

    $('#poTable').on('click', '.approve-po-btn', function () {
        const poId = $(this).data('po-id');
        if (confirm('Are you sure you want to approve this Purchase Order?')) {
            fetch('<?php echo BASE_URL; ?>modules/po/approve_po.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'po_id=' + encodeURIComponent(poId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while approving the PO.');
            });
        }
    });

    $('#poTable').on('click', '.lock-po-btn', function () {
        const poId = $(this).data('po-id');
        if (confirm('Are you sure you want to lock this Purchase Order? Once locked, it cannot be edited.')) {
            fetch('<?php echo BASE_URL; ?>modules/po/lock_po.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'po_id=' + encodeURIComponent(poId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while locking the PO.');
            });
        }
    });

    $('#poTable').on('click', '.unlock-po-btn', function () {
        const poId = $(this).data('po-id');
        if (confirm('Unlock this Purchase Order? This will allow assigned admins to edit again.')) {
            fetch('<?php echo BASE_URL; ?>modules/po/unlock_po.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'po_id=' + encodeURIComponent(poId)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    toastr.success(data.message);
                    setTimeout(() => { location.reload(); }, 1000);
                } else {
                    toastr.error(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                toastr.error('An error occurred while unlocking the PO.');
            });
        }
    });
});

function printPO() {
    window.print();
}
</script>