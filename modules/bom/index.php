<?php
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
session_start();
include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? 'guest';

if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} elseif ($user_type === 'accounts') {
    include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php';
} elseif ($user_type === 'operation') {
    include_once ROOT_DIR_PATH . 'operationadmin/sidebar.php';
} elseif ($user_type === 'production') {
    include_once ROOT_DIR_PATH . 'productionadmin/sidebar.php';
}

global $conn;

$sql = "SELECT *, CASE WHEN jci_assigned = 1 THEN 'Assigned' ELSE 'Available' END as status FROM bom_main ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$bom_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mb-5">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Bill Of Material List</h6>
            <div class="d-flex align-items-center gap-3">
                <input type="text" id="bomSearchInput" class="form-control form-control-sm" placeholder="Search BOM..." style="width: 250px;">
                <a href="add.php" class="btn btn-primary btn-sm">Add New BOM</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive dataTables_wrapper_custom">
                <table class="table table-bordered table-striped" id="bomTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Serial No</th>
                            <th>Bill Of Material Number</th>
                            <th>Costing Sheet Number</th>
                            <th>Client Name</th>
                            <th>Prepared By</th>
                            <th>Created Date</th>
                            <th>Status</th>
                            <th>Item Details</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sn = 1; foreach ($bom_list as $bom): ?>
                        <tr>
                            <td><?php echo $sn++; ?></td>
                            <td><?php echo htmlspecialchars($bom['bom_number']); ?></td>
                            <td><?php echo htmlspecialchars($bom['costing_sheet_number'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($bom['client_name']); ?></td>
                            <td><?php echo htmlspecialchars($bom['prepared_by']); ?></td>
                            <td><?php echo htmlspecialchars($bom['order_date']); ?></td>
                            <td><span class="badge badge-<?php echo $bom['jci_assigned'] ? 'warning' : 'success'; ?>"><?php echo $bom['status']; ?></span></td>
                            <td>
                                <button class="btn btn-info btn-sm view-items-btn" data-bom-id="<?php echo $bom['id']; ?>">View Items</button>
                            </td>
                            <td class="d-flex gap-2">
                                <a href="add.php?id=<?php echo $bom['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                <a href="generate_pdf.php?bom_id=<?php echo $bom['id']; ?>" class="btn btn-sm btn-success" target="_blank" style="margin-left:5px;">PDF</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="itemDetailsModal" tabindex="-1" role="dialog" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="itemDetailsModalLabel">BOM Item Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="bomItemTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="modal-wood-tab" data-toggle="tab" href="#modal-wood" role="tab" aria-controls="modal-wood" aria-selected="true">Wood</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="modal-glue-tab" data-toggle="tab" href="#modal-glue" role="tab" aria-controls="modal-glue" aria-selected="false">Glue</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="modal-plynydf-tab" data-toggle="tab" href="#modal-plynydf" role="tab" aria-controls="modal-plynydf" aria-selected="false">PLY/NYDF</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="modal-hardware-tab" data-toggle="tab" href="#modal-hardware" role="tab" aria-controls="modal-hardware" aria-selected="false">Hardware</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="modal-labour-tab" data-toggle="tab" href="#modal-labour" role="tab" aria-controls="modal-labour" aria-selected="false">Labour</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="modal-factory-tab" data-toggle="tab" href="#modal-factory" role="tab" aria-controls="modal-factory" aria-selected="false">Factory</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="modal-margin-tab" data-toggle="tab" href="#modal-margin" role="tab" aria-controls="modal-margin" aria-selected="false">Margin</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="bomItemTabsContent">
                        <div class="tab-pane fade show active" id="modal-wood" role="tabpanel" aria-labelledby="modal-wood-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="woodDetailsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Wood Type</th>
                                            <th>L(ft)</th>
                                            <th>W(in)</th>
                                            <th>T(in)</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>CFT</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="modal-glue" role="tabpanel" aria-labelledby="modal-glue-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="glueDetailsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
<th>Glue Type</th>
<th>Qty (kg)</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="modal-plynydf" role="tabpanel" aria-labelledby="modal-plynydf-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="plynydfDetailsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Qty</th>
                                            <th>Width</th>
                                            <th>Length</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="modal-hardware" role="tabpanel" aria-labelledby="modal-hardware-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="hardwareDetailsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="modal-labour" role="tabpanel" aria-labelledby="modal-labour-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="labourDetailsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Labour Name</th>
                                            <th>Qty</th>
                                            <th>Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="modal-factory" role="tabpanel" aria-labelledby="modal-factory-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="factoryDetailsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Total Amount</th>
                                            <th>Factory Cost (15%)</th>
                                            <th>Updated Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="modal-margin" role="tabpanel" aria-labelledby="modal-margin-tab">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="marginDetailsTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Total Amount</th>
                                            <th>Margin Cost (15%)</th>
                                            <th>Updated Total</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    var table = $('#bomTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthChange: false,
        searching: false
    });
    
    // Custom search functionality
    $('#bomSearchInput').on('keyup', function() {
        table.search(this.value).draw();
    });

    document.querySelectorAll('.view-items-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const bomId = this.getAttribute('data-bom-id');
            fetch('<?php echo BASE_URL; ?>modules/bom/ajax_fetch_bom_items.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'bom_id=' + encodeURIComponent(bomId)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Wood
                    const woodTbody = document.querySelector('#woodDetailsTable tbody');
                    woodTbody.innerHTML = '';
                            if(data.items.wood.length > 0) {
                                data.items.wood.forEach(item => {
                                    woodTbody.innerHTML += `<tr><td>${item.woodtype}</td><td>${item.length_ft}</td><td>${item.width_ft*12}</td><td>${item.thickness_inch}</td><td>${item.quantity}</td><td>${item.price}</td><td>${item.cft}</td><td>${item.total}</td></tr>`;
                                });
                            } else {
                                woodTbody.innerHTML = '<tr><td colspan="9" class="text-center">No wood items.</td></tr>';
                            }

                    // Glue
                    const glueTbody = document.querySelector('#glueDetailsTable tbody');
                    glueTbody.innerHTML = '';
                            if(data.items.glow.length > 0) {
                                data.items.glow.forEach(item => {
                                    glueTbody.innerHTML += `<tr><td>${item.glowtype}</td><td>${item.quantity}</td><td>${item.price}</td><td>${item.total}</td></tr>`;
                                });
                            } else {
                                glueTbody.innerHTML = '<tr><td colspan="5" class="text-center">No glue items.</td></tr>';
                            }

                    // Plynydf
                    const plynydfTbody = document.querySelector('#plynydfDetailsTable tbody');
                    plynydfTbody.innerHTML = '';
                            if(data.items.plynydf.length > 0) {
                                data.items.plynydf.forEach(item => {
                                    plynydfTbody.innerHTML += `<tr><td>${item.quantity}</td><td>${item.width}</td><td>${item.length}</td><td>${item.price}</td><td>${item.total}</td></tr>`;
                                });
                            } else {
                                plynydfTbody.innerHTML = '<tr><td colspan="6" class="text-center">No PLY/NYDF items.</td></tr>';
                            }

                    // Hardware
                    const hardwareTbody = document.querySelector('#hardwareDetailsTable tbody');
                    hardwareTbody.innerHTML = '';
                            if(data.items.hardware.length > 0) {
                                data.items.hardware.forEach(item => {
                                    hardwareTbody.innerHTML += `<tr><td>${item.itemname}</td><td>${item.quantity}</td><td>${item.price}</td><td>${item.totalprice}</td></tr>`;
                                });
                            } else {
                                hardwareTbody.innerHTML = '<tr><td colspan="4" class="text-center">No hardware items.</td></tr>';
                            }

                    // Labour
                    const labourTbody = document.querySelector('#labourDetailsTable tbody');
                    labourTbody.innerHTML = '';
                            if(data.items.labour && data.items.labour.length > 0) {
                                data.items.labour.forEach(item => {
                                    labourTbody.innerHTML += `<tr><td>${item.itemname}</td><td>${item.quantity}</td><td>${item.price}</td><td>${item.totalprice}</td></tr>`;
                                });
                            } else {
                                labourTbody.innerHTML = '<tr><td colspan="4" class="text-center">No labour items.</td></tr>';
                            }

                    // Factory
                    const factoryTbody = document.querySelector('#factoryDetailsTable tbody');
                    factoryTbody.innerHTML = '';
                            if(data.items.factory && data.items.factory.length > 0) {
                                data.items.factory.forEach(item => {
                                    factoryTbody.innerHTML += `<tr><td>${item.total_amount || 0}</td><td>${item.factory_cost || 0}</td><td>${item.updated_total || 0}</td></tr>`;
                                });
                            } else {
                                factoryTbody.innerHTML = '<tr><td colspan="3" class="text-center">No factory cost data.</td></tr>';
                            }

                    // Margin
                    const marginTbody = document.querySelector('#marginDetailsTable tbody');
                    marginTbody.innerHTML = '';
                            if(data.items.margin && data.items.margin.length > 0) {
                                data.items.margin.forEach(item => {
                                    marginTbody.innerHTML += `<tr><td>${item.total_amount || 0}</td><td>${item.margin_cost || 0}</td><td>${item.updated_total || 0}</td></tr>`;
                                });
                            } else {
                                marginTbody.innerHTML = '<tr><td colspan="3" class="text-center">No margin data.</td></tr>';
                            }

                } else {
                    alert('Failed to fetch items: ' + data.message);
                }
                var modal = new bootstrap.Modal(document.getElementById('itemDetailsModal'));
                modal.show();
            })
            .catch(error => {
                alert('Error fetching BOM items: ' + error);
                console.error('Fetch error:', error);
            });
        });
    });
});
</script>