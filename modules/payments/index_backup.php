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
} elseif ($user_type === 'accounts') {
    include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php';
}

?>
<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <!-- Removed old payments list table and its PHP query as per user request -->

    <!-- New Vendors with Payment Status Section -->
    <div class="card shadow mb-4 mt-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Vendors Payment Status</h6>
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
                            <th>Payment Status</th>
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

    <!-- JCI Job Cards Modal -->
    <div class="modal fade" id="jciJobCardsModal" tabindex="-1" role="dialog" aria-labelledby="jciJobCardsModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="jciJobCardsModalLabel">Job Cards for JCI Number</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <table class="table table-bordered" id="jciJobCardsTable">
              <thead>
                <tr>
                  <th>Job Card Number</th>
                  <th>Job Card Type</th>
                  <th>Contracture Name</th>
                  <th>Labour Cost</th>
                  <th>Quantity</th>
                  <th>Total Amount</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
    <div class="mt-5">
        <?php include_once ROOT_DIR_PATH . 'include/inc/footer-top.php'; ?>
    </div>
</div>

<!-- Item Details Modal -->
<div class="modal fade" id="itemDetailsModal" tabindex="-1" role="dialog" aria-labelledby="itemDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="itemDetailsModalLabel">Payment Item Details</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-bordered" id="itemDetailsTable">
          <thead>
            <tr>
              <th>Payment Category</th>
              <th>Supplier/Contractor</th>
              <th>Payment Type</th>
              <th>Cheque/RTGS Number</th>
              <th>Amount</th>
              <th>Payment Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="6" class="text-right">Total Amount:</th>
              <th id="totalItemAmount"></th>
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



<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" role="dialog" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
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
              <th>Payment Type</th>
              <th>Cheque/RTGS Number</th>
              <th>PD ACC Number</th>
              <th>Full/Partial</th>
              <th>Amount</th>
              <th>Invoice Date</th>
            </tr>
          </thead>
          <tbody>
          </tbody>
          <tfoot>
            <tr>
              <th colspan="6" class="text-right">Total Amount:</th>
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

<!-- jQuery (First) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Popper.js (for Bootstrap 4 compatibility) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<!-- Bootstrap 4 JS (for modal) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- Toastr JS (optional, if you want notifications) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    // Payment Details Modal (View Details button) - Enhanced
    $(document).on('click', '.view-payment-details-btn', function() {
        var paymentId = $(this).data('payment-id');
        $('#itemDetailsTable tbody').empty();
        $('#totalItemAmount').text('');
        
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
                        
                        var row = '<tr>' +
                            '<td>' + (detail.payment_category || '') + '</td>' +
                            '<td>' + supplierInfo + '</td>' +
                            '<td>' + (detail.payment_type || '') + '</td>' +
                            '<td>' + (detail.cheque_number || '') + '</td>' +
                            '<td>' + parseFloat(detail.ptm_amount || 0).toFixed(2) + '</td>' +
                            '<td>' + (detail.payment_date || detail.payment_invoice_date || '') + '</td>' +
                            '<td>' + paymentStatus + '</td>' +
                            '</tr>';
                        $('#itemDetailsTable tbody').append(row);
                        totalAmount += parseFloat(detail.ptm_amount || 0);
                    });
                } else {
                    $('#itemDetailsTable tbody').append('<tr><td colspan="7" class="text-center">No payment details found.</td></tr>');
                }
                $('#totalItemAmount').text(totalAmount.toFixed(2));
                $('#itemDetailsModal').modal('show');
            },
            error: function(xhr, status, error) {
                $('#itemDetailsTable tbody').append('<tr><td colspan="7" class="text-center">Error loading payment details.</td></tr>');
                $('#itemDetailsModal').modal('show');
            }
        });
    });

    // Job Cards Payment Status Table Load
    function loadJobCardsPaymentStatus() {
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/payments/ajax_fetch_job_cards_with_payment_status.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var rows = '';
                    if (response.job_cards.length > 0) {
                        response.job_cards.forEach(function(jobCard) {
                            rows += '<tr>';
                            rows += '<td>' + jobCard.jci_number + '</td>';
                            rows += '<td>' + (jobCard.po_number || '') + '</td>';
                            rows += '<td>' + (jobCard.sell_order_number || '') + '</td>';
                            rows += '<td>' + jobCard.payment_status + '</td>';
                            rows += '<td><button class="btn btn-info btn-sm view-jobcard-details-btn" data-jci-number="' + jobCard.jci_number + '">View Details</button></td>';
                            rows += '</tr>';
                        });
                    } else {
                        rows = '<tr><td colspan="5" class="text-center">No job cards found.</td></tr>';
                    }
                    $('#jobCardsPaymentStatusTable tbody').html(rows);
                } else {
                    $('#jobCardsPaymentStatusTable tbody').html('<tr><td colspan="5" class="text-center text-danger">Error loading job cards.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                $('#jobCardsPaymentStatusTable tbody').html('<tr><td colspan="5" class="text-center text-danger">Error loading job cards.</td></tr>');
            }
        });
    }

    loadVendorsPaymentStatus();

    // Payment Details Modal
    $(document).on('click', '.view-payment-details-btn', function() {
        var paymentId = $(this).data('payment-id');
        $('#paymentDetailsTable tbody').empty();
        $('#totalPaymentAmount').text('');
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/payments/ajax_get_payment_items.php',
            type: 'GET',
            data: { payment_id: paymentId },
            dataType: 'json',
            success: function(data) {
                console.log('Response received:', data);
                if (data && data.success) {
                    var jobCards = data.job_cards || [];
                    var suppliers = data.suppliers || [];
                    var payments = data.payment_details || [];

                    // Job card details removed

                    // Populate Payment Details Table
                    var totalPaymentAmount = 0;
                    $('#paymentDetailsTable tbody').empty();
                    if (payments.length > 0) {
                        payments.forEach(function(payment) {
                            var row = `<tr class="payment-row" data-entity-type="${payment.payment_category === 'Job Card' ? 'job_card' : 'supplier'}" data-entity-id="">
                                <td><input type="checkbox" class="select_payment" title="Select Payment"></td>
                                <td>${payment.payment_category === 'Job Card' ? 'Job Card: ' : 'Supplier: '}${payment.supplier_name || ''}</td>
                                <td style="width: 15%;">
                                    <select class="form-control cheque_type" name="cheque_type[]">
                                        <option value="">Select Type</option>
                                        <option value="Cheque" ${payment.payment_type === 'Cheque' ? 'selected' : ''}>Cheque</option>
                                        <option value="RTGS" ${payment.payment_type === 'RTGS' ? 'selected' : ''}>RTGS</option>
                                    </select>
                                </td>
                                <td style="width: 13%;"><input type="text" class="form-control cheque_number" name="cheque_number[]" placeholder="Enter Cheque/RTGS Number" value="${payment.cheque_number || ''}"></td>
                                <td style="width: 12%;"><input type="number" class="form-control pd_acc_number" name="pd_acc_number[]" value="${payment.pd_acc_number || ''}"></td>
                                <td style="width: 10%;"><input type="number" class="form-control ptm_amount" name="ptm_amount[]" min="0" step="0.01" value="${parseFloat(payment.ptm_amount).toFixed(2)}"></td>
                                <td style="width: 9%;"><input type="text" class="form-control invoice_number" name="invoice_number[]" value="${payment.invoice_number || ''}"></td>
                                <td style="width: 8%;"><input type="date" class="form-control invoice_date" name="invoice_date[]" value="${payment.payment_invoice_date || ''}"></td>
                            </tr>`;
                            $('#paymentDetailsTable tbody').append(row);
                            totalPaymentAmount += parseFloat(payment.ptm_amount);
                        });
                    } else {
                        $('#paymentDetailsTable tbody').append('<tr><td colspan="8" class="text-center">No payment details found.</td></tr>');
                    }
                    $('#totalPaymentAmount').text(totalPaymentAmount.toFixed(2));

                    // Job card modal removed
                } else {
                    alert('Failed to load payment status details: ' + (data.message || 'Unknown error.'));
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", status, error, xhr.responseText);
                alert('Error fetching items: ' + error);
            }
        });
    });

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
                            var paymentStatus = 'Completed';
                            rows += '<tr>';
                            rows += '<td>' + (index + 1) + '</td>';
                            rows += '<td>' + (payment.jci_number || '') + '</td>';
                            rows += '<td>' + (payment.po_number || '') + '</td>';
                            rows += '<td>' + (payment.sell_order_number || '') + '</td>';
                            rows += '<td><span class="badge badge-success">' + paymentStatus + '</span></td>';
                            rows += '<td>';
                            rows += '<a href="add.php?id=' + payment.id + '" class="btn btn-primary btn-sm mr-1">Edit</a>';
                            rows += '<button class="btn btn-info btn-sm view-payment-details-btn" data-payment-id="' + payment.id + '">View Details</button>';
                            rows += '</td>';
                            rows += '</tr>';
                        });
                    } else {
                        rows = '<tr><td colspan="6" class="text-center">No payments found.</td></tr>';
                    }
                    $('#vendorsPaymentStatusTable tbody').html(rows);
                } else {
                    $('#vendorsPaymentStatusTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading payments.</td></tr>');
                }
            },
            error: function(xhr, status, error) {
                $('#vendorsPaymentStatusTable tbody').html('<tr><td colspan="6" class="text-center text-danger">Error loading payments.</td></tr>');
            }
        });
    }

    loadVendorsPaymentStatus();

    // Job Card Details Modal
    $(document).on('click', '.view-jobcards-btn', function() {
        var paymentId = $(this).data('payment-id');
        $('#jobCardDetailsTable tbody').empty();
        $('#totalJobCardAmount').text('');
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/payments/ajax_get_payment_details.php',
            type: 'GET',
            data: { payment_id: paymentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var totalAmount = 0;
                    $('#jobCardDetailsTable tbody').empty();
                    if (data.job_cards && data.job_cards.length > 0) {
                        data.job_cards.forEach(function(jobCard) {
                            var jcNumber = jobCard.jc_number.replace(/\+/g, '');
                            var jcAmt = jobCard.jc_amt.toString().replace(/\+/g, '');
                            var row = '<tr>' +
                                '<td>' + jcNumber + '</td>' +
                                '<td>' + jcAmt + '</td>' +
                                '</tr>';
                            $('#jobCardDetailsTable tbody').append(row);
                            totalAmount += parseFloat(jcAmt);
                        });
                    } else {
                        $('#jobCardDetailsTable tbody').append('<tr><td colspan="2" class="text-center">No job cards found.</td></tr>');
                    }
                    $('#totalJobCardAmount').text(totalAmount.toFixed(2));
                    // Job card modal removed
                } else {
                    alert('Failed to load job card details: ' + (response.message || 'Unknown error.'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error loading job card details.');
                console.error("AJAX Error: ", status, error, xhr.responseText);
            }
        });
    });

    // Payment Details Modal
    $(document).on('click', '.view-payments-btn', function() {
        var paymentId = $(this).data('payment-id');
        $('#paymentDetailsTable tbody').empty();
        $('#totalPaymentAmount').text('');
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/payments/ajax_get_payment_details.php',
            type: 'GET',
            data: { payment_id: paymentId },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var data = response.data;
                    var totalAmount = 0;
                    $('#paymentDetailsTable tbody').empty();
                    if (data.payments && data.payments.length > 0) {
                        data.payments.forEach(function(payment) {
                            var paymentCategory = payment.payment_category.replace(/\+/g, '');
                            var paymentType = payment.payment_type.replace(/\+/g, '');
                            var chequeNumber = payment.cheque_number.replace(/\+/g, '');
                            var pdAccNumber = payment.pd_acc_number.replace(/\+/g, '');
                            var paymentFullPartial = payment.payment_full_partial.replace(/\+/g, '');
                            var ptmAmount = payment.ptm_amount.toString().replace(/\+/g, '');
                            var paymentInvoiceDate = payment.payment_invoice_date.replace(/\+/g, '');

                            var row = '<tr>' +
                                '<td>' + paymentCategory + '</td>' +
                                '<td>' + paymentType + '</td>' +
                                '<td>' + chequeNumber + '</td>' +
                                '<td>' + pdAccNumber + '</td>' +
                                '<td>' + paymentFullPartial + '</td>' +
                                '<td>' + ptmAmount + '</td>' +
                                '<td>' + paymentInvoiceDate + '</td>' +
                                '</tr>';
                            $('#paymentDetailsTable tbody').append(row);
                            totalAmount += parseFloat(ptmAmount);
                        });
                    } else {
                        $('#paymentDetailsTable tbody').append('<tr><td colspan="7" class="text-center">No payment details found.</td></tr>');
                    }
                    $('#totalPaymentAmount').text(totalAmount.toFixed(2));
                    $('#paymentDetailsModal').modal('show');
                } else {
                    alert('Failed to load payment details: ' + (response.message || 'Unknown error.'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error loading payment details.');
                console.error("AJAX Error: ", status, error, xhr.responseText);
            }
        });
    });

    // Search input handler with debounce
    let searchTimeout = null;
    $('#searchPaymentInput').on('input', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        searchTimeout = setTimeout(function() {
            $.ajax({
                url: '<?php echo BASE_URL; ?>modules/payments/ajax_get_payment_details.php',
                type: 'POST',
                data: { action: 'search_payments', search: query },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.payments) {
                        let rows = '';
                        if (response.payments.length > 0) {
                            response.payments.forEach(function(payment) {
                                rows += '<tr>';
                                rows += '<td>' + payment.id + '</td>';
                                rows += '<td>' + payment.pon_number + '</td>';
                                rows += '<td>' + payment.po_amt + '</td>';
                                rows += '<td>' + payment.son_number + '</td>';
                                rows += '<td>' + payment.invoice_numbers + '</td>';
                                rows += '<td>' + payment.total_invoice_amount + '</td>';
                                rows += '<td>' + payment.latest_payment_invoice_date + '</td>';
                                rows += '<td><button class="btn btn-info btn-sm view-jobcards-btn" data-payment-id="' + payment.id + '">View Job Cards</button></td>';
                                rows += '<td><button class="btn btn-info btn-sm view-payments-btn" data-payment-id="' + payment.id + '">View Payments</button></td>';
                                rows += '<td><button class="btn btn-info btn-sm view-items-btn" data-payment-id="' + payment.id + '">View Items</button></td>';
                                rows += '<td><a href="add.php?payment_id=' + encodeURIComponent(payment.id) + '" class="btn btn-primary btn-sm">Edit</a></td>';
                                rows += '</tr>';
                            });
                        } else {
                            rows = '<tr><td colspan="11" class="text-center">No payments found.</td></tr>';
                        }
                        $('#paymentsTable tbody').html(rows);
                    } else {
                        $('#paymentsTable tbody').html('<tr><td colspan="11" class="text-center text-danger">Error loading payments.</td></tr>');
                    }
                },
                error: function(xhr, status, error) {
                    $('#paymentsTable tbody').html('<tr><td colspan="11" class="text-center text-danger">Error loading payments.</td></tr>');
                }
            });
        }, 300); // debounce delay 300ms
    });
});
</script>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>