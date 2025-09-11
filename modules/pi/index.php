<?php
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
include_once ROOT_DIR_PATH . 'include/inc/header.php';
session_start();
$user_type = $_SESSION['user_type'] ?? 'guest';

if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} else {
    // Default or guest sidebar or no sidebar
    // include_once ROOT_DIR_PATH . 'include/inc/sidebar.php';
}



try {
    global $conn;
    $sql = "SELECT 
                p.pi_id,
                p.pi_number,
                q.quotation_number,
                p.status,
                p.date_of_pi_raised
            FROM pi p
            INNER JOIN quotations q ON q.id = p.quotation_id
            WHERE q.approve = 1 AND q.is_locked = 1
            ORDER BY p.pi_id DESC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $pis = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $pis = [];
    $error = "Error fetching PIs: " . $e->getMessage();
}
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Proforma Invoices (PIs) List</h6>
            <input type="text" id="piSearchInput" class="form-control form-control-sm w-25" placeholder="Search PIs...">
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="piTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>PI Number</th>
                            <th>Quotation Number</th>
                            <th>Status</th>
                            <th>Date of PI Raised</th>
                            <th>View Quotation</th>
                            <th>Export</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $sr_no = 1; ?>
                        <?php foreach ($pis as $pi) : ?>
                            <tr>
                                <td><?php echo $sr_no++; ?></td>
                                <td><?php echo htmlspecialchars($pi['pi_number']); ?></td>
                                <td><?php echo htmlspecialchars($pi['quotation_number']); ?></td>
                                <td><?php echo htmlspecialchars($pi['status']); ?></td>
                                <td><?php echo htmlspecialchars($pi['date_of_pi_raised']); ?></td>
                                <td>
                                    <?php if (!empty($pi['pi_id'])): ?>
                                        <button class="btn btn-primary btn-sm viewQuotationBtn" data-pi-id="<?php echo $pi['pi_id']; ?>" data-quotation-number="<?php echo htmlspecialchars($pi['quotation_number']); ?>" data-toggle="modal" data-target="#viewQuotationModal_<?php echo $pi['pi_id']; ?>">View Quotation</button>
                                    <?php else: ?>
                                        <span class="text-muted">No PI yet</span>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space: nowrap;">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info btn-sm export-btn sharePiBtn mr-2" data-toggle="modal" data-target="#sharePiModal_<?php echo $pi['pi_id']; ?>" title="Share">
                                            <i class="fas fa-share-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-success btn-sm export-btn exportExcelBtn mr-2" data-id="<?php echo $pi['pi_id']; ?>" title="Export to Excel" <?php echo empty($pi['pi_id']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-file-excel"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm export-btn exportPdfBtn" data-id="<?php echo $pi['pi_id']; ?>" title="Export to PDF" <?php echo empty($pi['pi_id']) ? 'disabled' : ''; ?>>
                                            <i class="fas fa-file-pdf"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
</tbody>
</table>
</div>

<?php foreach ($pis as $pi) : ?>
<div class="modal fade" id="sharePiModal_<?php echo $pi['pi_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="sharePiModalLabel_<?php echo $pi['pi_id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sharePiModalLabel_<?php echo $pi['pi_id']; ?>">Send Email Confirmation</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#sharePiModal_<?php echo $pi['pi_id']; ?>').modal('hide')">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Do you want to send email for PI - <?php echo htmlspecialchars($pi['pi_number']); ?>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#sharePiModal_<?php echo $pi['pi_id']; ?>').modal('hide')">Cancel</button>
                <button type="button" class="btn btn-primary confirmSendEmailBtn" data-pi-id="<?php echo $pi['pi_id']; ?>">Yes</button>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- View Quotation Modals -->
<?php foreach ($pis as $pi) : ?>
<div class="modal fade" id="viewQuotationModal_<?php echo $pi['pi_id']; ?>" tabindex="-1" role="dialog" aria-labelledby="viewQuotationModalLabel_<?php echo $pi['pi_id']; ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewQuotationModalLabel_<?php echo $pi['pi_id']; ?>">Quotation Details - <?php echo htmlspecialchars($pi['quotation_number']); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="quotationModalBody_<?php echo $pi['pi_id']; ?>">
                Loading quotation details...
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css"/>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.25/jspdf.plugin.autotable.min.js"></script>
<script src="<?php echo BASE_URL; ?>modules/quotation/assets/js/lock-quotation.js"></script>

<script>
$(document).ready(function() {
    var table = $('#piTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthChange: false,
        searching: false
    });
    
    // Custom search functionality
    $('#piSearchInput').on('keyup', function() {
        table.search(this.value).draw();
    });
    
    // View Quotation Modal
    $('.viewQuotationBtn').on('click', function() {
        var piId = $(this).data('pi-id');
        var quotationNumber = $(this).data('quotation-number');
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/pi/ajax_get_quotation_details.php',
            type: 'POST',
            data: { pi_id: piId },
            success: function(response) {
                $('#quotationModalBody_' + piId).html(response);
            },
            error: function() {
                $('#quotationModalBody_' + piId).html('Error loading quotation details.');
            }
        });
    });
    
    // Export Excel
    $('.exportExcelBtn').on('click', function() {
        var piId = $(this).data('id');
        window.open('<?php echo BASE_URL; ?>modules/pi/export_pi_excel.php?id=' + piId, '_blank');
    });
    
    // Export PDF
    $('.exportPdfBtn').on('click', function() {
        var piId = $(this).data('id');
        window.open('<?php echo BASE_URL; ?>modules/pi/export_pi_pdf.php?id=' + piId, '_blank');
    });
    
    // Send Email
    $('.confirmSendEmailBtn').on('click', function() {
        var piId = $(this).data('pi-id');
        var button = $(this);
        button.prop('disabled', true).text('Sending...');
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/pi/send_pi_email.php',
            type: 'POST',
            data: {
                pi_id: piId,
                attach_pdf: 1,
                attach_excel: 1
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Email sent successfully');
                    $('#sharePiModal_' + piId).modal('hide');
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('An error occurred while sending the email');
            },
            complete: function() {
                button.prop('disabled', false).text('Yes');
            }
        });
    });
});
</script>
