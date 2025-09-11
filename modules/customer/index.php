<?php
session_start();
include_once __DIR__ . '/../../config/config.php';
include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? 'guest';
?>
<script>
    console.log("User Type: '<?php echo $user_type; ?>'");
</script>
<?php

if ($user_type === 'superadmin') {
    ?>
    <script>console.log("Including superadmin sidebar");</script>
    <?php
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    ?>
    <script>console.log("Including salesadmin sidebar");</script>
    <?php
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} elseif ($user_type === 'accounts') {
    ?>
    <script>console.log("Including accountsadmin sidebar");</script>
    <?php
    include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php';
} else {
    ?>
    <script>console.log("No sidebar included");</script>
    <?php
    // include_once ROOT_DIR_PATH . 'include/inc/sidebar.php';
}
// ...existing code...

try {
    global $conn;

    // Simple query to get all leads as customers
    $stmt = $conn->query("
        SELECT 
            l.id as lead_id, 
            l.company_name, 
            l.contact_email, 
            l.contact_phone,
            (SELECT COUNT(*) FROM quotations q WHERE q.lead_id = l.id) as total_quotations,
            (SELECT COUNT(*) FROM pi p JOIN quotations q2 ON p.quotation_id = q2.id WHERE q2.lead_id = l.id) as total_pis
        FROM leads l
        WHERE l.approve = 1
        ORDER BY l.company_name ASC
    ");
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $customers = [];
    $error = "Error fetching customers: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <div id="content">
        <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Customers List</h6>
                <input type="text" id="customerSearchInput" class="form-control form-control-sm w-25" placeholder="Search Customers...">
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="customersTable" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sl Number</th>
                                <th>Customer Name</th>
                                <th>Customer Email</th>
                                <th>Customer Phone</th>
                                <th>Total Leads</th>
                                <th>Total Quotations</th>
                                <th>Total PIs</th>
                                <th>Create Quotation</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sr_no = 1; ?>
                            <?php foreach ($customers as $customer) : ?>
                                <tr>
                                    <td><?php echo $sr_no++; ?></td>
                                    <td><?php echo htmlspecialchars($customer['company_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['contact_email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['contact_phone']); ?></td>
                                    <td><a href="#" class="view-leads" data-lead-id="<?php echo $customer['lead_id']; ?>">1</a></td>
                                    <td><a href="#" class="view-quotations" data-lead-id="<?php echo $customer['lead_id']; ?>"><?php echo $customer['total_quotations']; ?></a></td>
                                    <td><a href="#" class="view-pis" data-lead-id="<?php echo $customer['lead_id']; ?>"><?php echo $customer['total_pis']; ?></a></td>
                                    <td><a href="../quotation/add.php?lead_id=<?php echo $customer['lead_id']; ?>" class="btn btn-primary btn-sm">Create Quotation</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="leadsModal" tabindex="-1" role="dialog" aria-labelledby="leadsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="leadsModalLabel">Leads List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body" id="leadsModalBody">
                <!-- Leads list will be loaded here -->
                </div>
            </div>
            </div>
        </div>

        <div class="modal fade" id="quotationsModal" tabindex="-1" role="dialog" aria-labelledby="quotationsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="quotationsModalLabel">Quotations List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body" id="quotationsModalBody">
                <!-- Quotations list will be loaded here -->
                </div>
            </div>
            </div>
        </div>

        <div class="modal fade" id="pisModal" tabindex="-1" role="dialog" aria-labelledby="pisModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                <h5 class="modal-title" id="pisModalLabel">PIs List</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                </div>
                <div class="modal-body" id="pisModalBody">
                <!-- PIs list will be loaded here -->
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
    var customersTable = $('#customersTable').DataTable({
        order: [[1, 'asc']],
        pageLength: 10,
        lengthChange: false,
        searching: true,
        dom: 'rt<"bottom"p>'
    });
    
    // Custom search functionality
    $('#customerSearchInput').on('keyup', function() {
        customersTable.search(this.value).draw();
    });

    // Load leads list in modal
    $('.view-leads').on('click', function(e) {
        e.preventDefault();
        var leadId = $(this).data('lead-id');
        $('#leadsModalBody').html('Loading...');
        $('#leadsModal').modal('show');
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/customer/ajax_get_leads.php',
            method: 'GET',
            data: { lead_id: leadId },
            success: function(data) {
                $('#leadsModalBody').html(data);
            },
            error: function() {
                $('#leadsModalBody').html('Error loading leads.');
            }
        });
    });

    // Load quotations list in modal
    $('.view-quotations').on('click', function(e) {
        e.preventDefault();
        var leadId = $(this).data('lead-id');
        $('#quotationsModalBody').html('Loading...');
        $('#quotationsModal').modal('show');
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/customer/ajax_get_quotations.php',
            method: 'GET',
            data: { lead_id: leadId },
            success: function(data) {
                $('#quotationsModalBody').html(data);
            },
            error: function() {
                $('#quotationsModalBody').html('Error loading quotations.');
            }
        });
    });

    // Load PIs list in modal
    $('.view-pis').on('click', function(e) {
        e.preventDefault();
        var leadId = $(this).data('lead-id');
        $('#pisModalBody').html('Loading...');
        $('#pisModal').modal('show');
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/customer/ajax_get_pis.php',
            method: 'GET',
            data: { lead_id: leadId },
            success: function(data) {
                $('#pisModalBody').html(data);
            },
            error: function() {
                $('#pisModalBody').html('Error loading PIs.');
            }
        });
    });
});
</script>
