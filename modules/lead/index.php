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
?>

<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>


    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <div class="row w-100">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <h6 class="m-0 font-weight-bold text-primary">Leads List</h6>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="row">
                        <div class="col-lg-8 col-md-8 col-sm-8">
                            <input type="text" name="searchInput" id="searchInput" class="form-control form-control-sm" placeholder="Search by Lead Number or Contact Name">
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-4 text-right">
                            <a href="add.php" class="btn btn-primary btn-sm">Add New Lead</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="leadsTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lead Number</th>
                            <th>Contact Name</th>
                            <th>Contact Email</th>
                            <th>Country</th>
                            <th>Status</th>
                            <th>Approve</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $stmt = $conn->prepare("SELECT * FROM leads ORDER BY id DESC");
                            $stmt->execute();
                            $leads = $stmt->fetchAll(PDO::FETCH_ASSOC);
                            if ($leads) {
                                foreach ($leads as $lead) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($lead['id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($lead['lead_number']) . "</td>";
                                    echo "<td>" . htmlspecialchars($lead['contact_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($lead['contact_email']) . "</td>";
                                    echo "<td>" . htmlspecialchars($lead['country']) . "</td>";

                                    echo "<td class='d-flex' style='gap:15px;'>
                                        <button class='btn btn-sm btn-info status-modal-btn' data-id='" . $lead['id'] . "' data-lead='" . htmlspecialchars($lead['lead_number']) . "'>Status</button>                                       
                                    </td>";

                                    $approveBtnClass = ($lead['approve'] == 1) ? 'btn-success' : 'btn-warning';
                                    $approveText = ($lead['approve'] == 1) ? 'Approved' : 'Approve';
                                    echo "<td>
                                        <button class='btn btn-sm $approveBtnClass toggle-approve-btn' data-id='" . $lead['id'] . "' data-approve='" . $lead['approve'] . "'>$approveText</button>
                                    </td>";

                                    echo "<td>
                                        <a href='add.php?lead_id=" . urlencode($lead['id']) . "' class='btn btn-sm btn-info'>Edit</a>
                                    </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' class='text-center'>No leads found.</td></tr>";
                            }
                        } catch (Exception $e) {
                            echo "<tr><td colspan='8' class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>

</div>

<!-- Status Modal -->
<div class="modal fade" id="statusModal" tabindex="-1" role="dialog" aria-labelledby="statusModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="statusModalLabel">Status History for <span id="modalLeadNumber"></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="statusHistoryTable">
            <thead>
                <tr>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <!-- Status history will be loaded here -->
            </tbody>
        </table>
        <hr>
        <form id="addStatusForm">
            <input type="hidden" id="statusLeadId" name="lead_id" value="">
            <div class="form-row">
                <div class="col">
                    <input type="text" class="form-control" id="statusText" name="status_text" placeholder="Status" required>
                </div>
                <div class="col">
                    <input type="date" class="form-control" id="statusDate" name="status_date" required>
                </div>
                <div class="col">
                    <button type="submit" class="btn btn-primary">Add Status</button>
                </div>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    $('#leadsTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        lengthChange: false,
        searching: false
    });
    // Status modal button click event
    $(document).on('click', '.status-modal-btn', function() {
        var leadId = $(this).data('id');
        var leadNumber = $(this).data('lead');
        $('#modalLeadNumber').text(leadNumber);
        $('#statusLeadId').val(leadId);
        $('#statusHistoryTable tbody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading status history...</td></tr>');
        
        $('#statusModal').modal('show');

        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/lead/ajax_process_lead.php',
            type: 'POST',
            data: { action: 'get_status_history', lead_id: leadId },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.statuses && response.statuses.length > 0) {
                    var rows = '';
                    response.statuses.forEach(function(status) {
                        rows += '<tr><td>' + status.status_text + '</td><td>' + status.status_date + '</td><td>' + status.created_at + '</td></tr>';
                    });
                    $('#statusHistoryTable tbody').html(rows);
                } else {
                    $('#statusHistoryTable tbody').html('<tr><td colspan="3" class="text-center text-muted"><i class="fas fa-exclamation-circle"></i> No status history found for this lead.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
                $('#statusHistoryTable tbody').html('<tr><td colspan="3" class="text-center text-danger"><i class="fas fa-exclamation-triangle"></i> Error loading status history. Please try again.</td></tr>');
            }
        });
    });

    // Search input handler with debounce
    let searchTimeout = null;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '<?php echo BASE_URL; ?>modules/lead/ajax_process_lead.php',
                type: 'POST',
                data: { action: 'search_leads', search: query },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.leads) {
                        let rows = '';
                        if (response.leads.length > 0) {
                            response.leads.forEach(function(lead) {
                                const approveBtnClass = lead.approve == 1 ? 'btn-success' : 'btn-warning';
                                const approveText = lead.approve == 1 ? 'Approved' : 'Approve';
                                rows += '<tr>';
                                rows += '<td>' + lead.id + '</td>';
                                rows += '<td>' + lead.lead_number + '</td>';
                                rows += '<td>' + lead.contact_name + '</td>';
                                rows += '<td>' + lead.contact_email + '</td>';
                                rows += '<td>' + lead.country + '</td>';
                                rows += '<td class="d-flex" style="gap:15px;">' +
                                    '<button class="btn btn-sm btn-info status-modal-btn" data-id="' + lead.id + '" data-lead="' + lead.lead_number + '">Status</button>' +
                                    '</td>';
                                rows += '<td>' +
                                    '<button class="btn btn-sm ' + approveBtnClass + ' toggle-approve-btn" data-id="' + lead.id + '" data-approve="' + lead.approve + '">' + approveText + '</button>' +
                                    '</td>';
                                rows += '<td>' +
                                    '<a href="add.php?lead_id=' + encodeURIComponent(lead.id) + '" class="btn btn-sm btn-info">Edit</a>' +
                                    '</td>';
                                rows += '</tr>';
                            });
                        } else {
                            rows = '<tr><td colspan="8" class="text-center">No leads found.</td></tr>';
                        }
                        $('#leadsTable').DataTable().clear().rows.add($(rows)).draw();
                    } else {
                        $('#leadsTable tbody').html('<tr><td colspan="8" class="text-center text-danger">Error loading leads.</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Search AJAX error:', error);
                    $('#leadsTable tbody').html('<tr><td colspan="8" class="text-center text-danger">Error loading leads.</td></tr>');
                }
            });
        }, 300); // debounce delay 300ms
    });

    // Add new status
    $('#addStatusForm').on('submit', function(e) {
        e.preventDefault();
        var formData = {
            action: 'add_status',
            lead_id: $('#statusLeadId').val(),
            status_text: $('#statusText').val(),
            status_date: $('#statusDate').val()
        };
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/lead/ajax_process_lead.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Reload status history
                    $('.status-modal-btn[data-id="' + formData.lead_id + '"]').click();
                    // Clear form fields
                    $('#statusText').val('');
                    $('#statusDate').val('');
                } else {
                    toastr.error(response.message || 'Failed to add status.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Add status AJAX error:', error);
                toastr.error('AJAX error while adding status.');
            }
        });
    });

    // Toggle approve
    $(document).on('click', '.toggle-approve-btn', function() {
        var btn = $(this);
        var leadId = btn.data('id');
        var currentApprove = btn.data('approve');
        var newApprove = currentApprove == 1 ? 0 : 1;

        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/lead/ajax_process_lead.php',
            type: 'POST',
            data: { action: 'toggle_approve', lead_id: leadId, approve: newApprove },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    btn.data('approve', newApprove);
                    btn.removeClass('btn-success btn-warning')
                       .addClass(newApprove == 1 ? 'btn-success' : 'btn-warning')
                       .text(newApprove == 1 ? 'Approved' : 'Approve');
                    toastr.success(response.message);
                } else {
                    toastr.error(response.message || 'Failed to update approval.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Toggle approve AJAX error:', error);
                toastr.error('AJAX error while updating approval.');
            }
        });
    });
});
</script>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
