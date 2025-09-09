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

?>
<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <!-- Vendors with Payment Status Section -->
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Vendors Payment Status</h6>
            <input type="text" id="paymentSearchInput" class="form-control form-control-sm w-25" placeholder="Search Payments...">
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="vendorsPaymentStatusTable">
                    <thead>
                        <tr>
                            <th>Sl Number</th>
                            <th>JCI Number</th>
                            <th>PO Number</th>
                            <th>SO Number</th>
                            <th>Total Amount</th>
                            <th>Payment Amount</th>
                            <th>Payment Status</th>
                            <th>Report</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filled by JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="paymentDetailsModalLabel">Payment Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="paymentDetailsTable">
          <thead>
            <tr>
              <th>Payment Category</th>
              <th>Supplier/Contractor</th>
              <th>Payment Type</th>
              <th>Cheque/RTGS Number</th>
              <th>Amount</th>
              <th>Payment Date</th>
              <th>Invoice Image</th>
              <th>Builty Image</th>
              <th>Status</th>
              <th>Report</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="9" class="text-right">Total Amount:</th>
              <th id="totalPaymentAmount"></th>
            </tr>
          </tfoot>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css"/>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
$(document).ready(function() {
    // Load Vendors Payment Status Table
    function loadVendorsPaymentStatus() {
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/payments/ajax_fetch_payments_list.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var rows = '';
                    if (response.payments.length > 0) {
                        response.payments.forEach(function(payment, index) {
                            var paymentStatus = '';
                            var totalSuppliers = parseInt(payment.total_suppliers) || 0;
                            var paidSuppliers = parseInt(payment.paid_suppliers) || 0;
                            var completedPayments = parseInt(payment.completed_payments) || 0;
                            
                            if (totalSuppliers === 0) {
                                paymentStatus = '<span class="badge badge-secondary">No Suppliers</span>';
                            } else if (paidSuppliers === totalSuppliers && completedPayments > 0) {
                                paymentStatus = '<span class="badge badge-success">Completed</span>';
                            } else if (paidSuppliers > 0) {
                                paymentStatus = '<span class="badge badge-info">Partial (' + paidSuppliers + '/' + totalSuppliers + ')</span>';
                            } else {
                                paymentStatus = '<span class="badge badge-warning">Pending</span>';
                            }
                            
                            rows += '<tr>';
                            rows += '<td>' + (index + 1) + '</td>';
                            rows += '<td>' + (payment.jci_number || '') + '</td>';
                            rows += '<td>' + (payment.po_number || '') + '</td>';
                            rows += '<td>' + (payment.sell_order_number || '') + '</td>';
                            rows += '<td>' + (parseFloat(payment.total_amount || 0).toFixed(2)) + '</td>';
                            rows += '<td>' + (parseFloat(payment.supplier_paid_amount || 0).toFixed(2)) + '</td>';
                            rows += '<td>' + paymentStatus + '</td>';
                            rows += '<td><button class="btn btn-sm btn-outline-primary" onclick="downloadPaymentReport(' + payment.id + ')">Report</button></td>';
                            rows += '<td>';
                            rows += '<a href="add.php?id=' + payment.id + '" class="btn btn-primary btn-sm mr-1">Edit</a>';
                            rows += '<button class="btn btn-info btn-sm view-payment-details-btn" data-payment-id="' + payment.id + '">View Details</button>';
                            rows += '</td>';
                            rows += '</tr>';
                        });
                    } else {
                        rows = '<tr><td colspan="9" class="text-center">No payments found.</td></tr>';
                    }
                    $('#vendorsPaymentStatusTable tbody').html(rows);
                    
                    // Initialize DataTable after data is loaded
                    if ($.fn.DataTable.isDataTable('#vendorsPaymentStatusTable')) {
                        $('#vendorsPaymentStatusTable').DataTable().destroy();
                    }
                    var table = $('#vendorsPaymentStatusTable').DataTable({
                        order: [[0, 'desc']],
                        pageLength: 10,
                        lengthChange: false,
                        searching: false
                    });
                    
                    // Custom search functionality
                    $('#paymentSearchInput').off('keyup').on('keyup', function() {
                        table.search(this.value).draw();
                    });
                } else {
                    $('#vendorsPaymentStatusTable tbody').html('<tr><td colspan="9" class="text-center text-danger">Error loading payments.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                $('#vendorsPaymentStatusTable tbody').html('<tr><td colspan="9" class="text-center text-danger">Error loading payments.</td></tr>');
            }
        });
    }

    // Payment Details Modal
    $(document).on('click', '.view-payment-details-btn', function() {
        var paymentId = $(this).data('payment-id');
        $('#paymentDetailsTable tbody').empty();
        $('#totalPaymentAmount').text('');
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/payments/ajax_fetch_payment_details_by_jci.php',
            type: 'GET',
            data: { payment_id: paymentId },
            dataType: 'json',
            success: function(response) {
                var totalAmount = 0;
                if (response.success && response.payment_details && response.payment_details.length > 0) {
                    response.payment_details.forEach(function(detail) {
                        var supplierInfo = '';
                        if (detail.payment_category === 'Supplier') {
                            supplierInfo = detail.supplier_name || 'Unknown Supplier';
                        } else if (detail.payment_category === 'Job Card') {
                            supplierInfo = detail.contracture_name || 'Job Card Payment';
                        }
                        
                        var paymentStatus = '';
                        if (detail.payment_date && detail.cheque_number) {
                            paymentStatus = '<span class="badge badge-success">Paid</span>';
                        } else {
                            paymentStatus = '<span class="badge badge-warning">Pending</span>';
                        }
                        
                        // Add image columns
                        var invoiceImage = '';
                        var builtyImage = '';
                        
                        // Add image columns
                        if (detail.invoice_image) {
                            invoiceImage = '<img src="<?php echo BASE_URL; ?>modules/purchase/uploads/invoice/' + detail.invoice_image + '" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;" onclick="window.open(\'<?php echo BASE_URL; ?>modules/purchase/uploads/invoice/' + detail.invoice_image + '\', \'_blank\')" title="Click to view full image">';
                        } else {
                            invoiceImage = '<span class="text-muted">No Image</span>';
                        }
                        
                        if (detail.builty_image) {
                            builtyImage = '<img src="<?php echo BASE_URL; ?>modules/purchase/uploads/Builty/' + detail.builty_image + '" style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;" onclick="window.open(\'<?php echo BASE_URL; ?>modules/purchase/uploads/Builty/' + detail.builty_image + '\', \'_blank\')" title="Click to view full image">';
                        } else {
                            builtyImage = '<span class="text-muted">No Image</span>';
                        }
                        
                        var row = '<tr>' +
                            '<td>' + (detail.payment_category || '') + '</td>' +
                            '<td>' + supplierInfo + '</td>' +
                            '<td>' + (detail.payment_type || '') + '</td>' +
                            '<td>' + (detail.cheque_number || '') + '</td>' +
                            '<td>' + parseFloat(detail.ptm_amount || 0).toFixed(2) + '</td>' +
                            '<td>' + (detail.payment_date || detail.payment_invoice_date || '') + '</td>' +
                            '<td>' + invoiceImage + '</td>' +
                            '<td>' + builtyImage + '</td>' +
                            '<td>' + paymentStatus + '</td>' +
                            '<td><button class="btn btn-sm btn-outline-primary" onclick="downloadPaymentReport(' + paymentId + ')">Download Report</button></td>' +
                            '</tr>';
                        $('#paymentDetailsTable tbody').append(row);
                        totalAmount += parseFloat(detail.ptm_amount || 0);
                    });
                } else {
                    $('#paymentDetailsTable tbody').append('<tr><td colspan="10" class="text-center">No payment details found.</td></tr>');
                }
                $('#totalPaymentAmount').text(totalAmount.toFixed(2));
                $('#paymentDetailsModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error("AJAX error fetching payment details for modal: ", status, error, xhr.responseText);
                toastr.error('Error loading payment details. Check console for more details.');
                $('#paymentDetailsTable tbody').append('<tr><td colspan="10" class="text-center">Error loading payment details.</td></tr>');
                $('#paymentDetailsModal').modal('show');
            }
        });
    });


    // Load data on page load
    loadVendorsPaymentStatus();
});

// Global function for downloading payment reports
function downloadPaymentReport(paymentId) {
    var url = '<?php echo BASE_URL; ?>modules/payments/export_payment_report.php?payment_id=' + encodeURIComponent(paymentId);
    window.open(url, '_blank');
}
</script>
<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>