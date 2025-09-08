<?php
error_reporting(0);
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
    // Redirect to login page if not logged in as accountsadmin or superadmin
    header("Location: " . BASE_URL . "login.php");
    exit();
}

global $conn;

// Check if edit mode
$payment_id = $_GET['id'] ?? null;
$edit_mode = !empty($payment_id);
$existing_payment_data = null;

if ($edit_mode) {
    // Fetch existing payment data
    $stmt = $conn->prepare("SELECT * FROM payments WHERE id = ?");
    $stmt->execute([$payment_id]);
    $existing_payment_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fetch Job Card Numbers with related PO and SON for dropdown
// In edit mode, include the current JCI even if payment is completed
if ($edit_mode && $existing_payment_data) {
    $stmt = $conn->prepare("SELECT j.jci_number, j.po_id, p.po_number, p.sell_order_number FROM jci_main j LEFT JOIN po_main p ON j.po_id = p.id WHERE j.purchase_created = 1 AND (j.payment_completed = 0 OR j.jci_number = ?) ORDER BY j.jci_number ASC");
    $stmt->execute([$existing_payment_data['jci_number']]);
} else {
    $stmt = $conn->prepare("SELECT j.jci_number, j.po_id, p.po_number, p.sell_order_number FROM jci_main j LEFT JOIN po_main p ON j.po_id = p.id WHERE j.purchase_created = 1 AND j.payment_completed = 0 ORDER BY j.jci_number ASC");
    $stmt->execute();
}
$jci_numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>



<div class="container-fluid">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary" id="formTitle"><?php echo $edit_mode ? 'Edit Payment Details' : 'Add Payment Details'; ?></h6>
        </div>
        <div class="card-body">
            <form id="vendorPayment_form" autocomplete="off">
                <input type="hidden" name="payment_id" id="payment_id" value="<?php echo htmlspecialchars($payment_id ?? ''); ?>">
                <input type="hidden" name="lead_id" id="lead_id" value="">

                <div class="row mb-3">
                    <div class="col-lg-4">
                        <label for="jci_number" class="form-label">JCI Number</label>
                        <select class="form-control" id="jci_number" name="jci_number" required <?php echo $edit_mode ? 'disabled' : ''; ?>>
                            <option value="">Select JCI Number</option>
                            <?php
                            foreach ($jci_numbers as $jci) {
                                $selected = ($edit_mode && $existing_payment_data && $existing_payment_data['jci_number'] == $jci['jci_number']) ? 'selected' : '';
                                echo '<option value="' . htmlspecialchars($jci['jci_number']) . '" data-po-id="' . htmlspecialchars($jci['po_id']) . '" data-po-number="' . htmlspecialchars($jci['po_number']) . '" data-son="' . htmlspecialchars($jci['sell_order_number']) . '" ' . $selected . '>' . htmlspecialchars($jci['jci_number']) . '</option>';
                            }
                            ?>
                        </select>
                        <?php if ($edit_mode): ?>
                        <input type="hidden" name="jci_number" value="<?php echo htmlspecialchars($existing_payment_data['jci_number'] ?? ''); ?>">
                        <?php endif; ?>
                        <input type="hidden" id="po_id" name="po_id" value="">
                    </div>
                    <div class="col-lg-4">
                        <label for="po_number_display" class="form-label">Purchase Order Number (PO Number)</label>
                        <input type="text" class="form-control" id="po_number_display" name="po_number_display" readonly>
                        <input type="hidden" id="po_number" name="po_number">
                    </div>
                    <div class="col-lg-4">
                        <label for="son_number" class="form-label">Sale Order Number (SON)</label>
                        <input type="text" class="form-control" id="son_number" name="son_number" readonly>
                    </div>
                    <input type="hidden" id="po_amt" name="po_amt">
                    <input type="hidden" id="soa_number" name="soa_number">
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Job Card Details</label>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="job_card_details_table">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width: 15%;">Job Card Number</th>
                                    <th style="width: 15%;">Job Card Type</th>
                                    <th style="width: 20%;">Contracture Name</th>
                                    <th style="width: 15%;">Labour Cost</th>
                                    <th style="width: 15%;">Quantity</th>
                                    <th style="width: 15%;">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-end" colspan="5">Total Job Card Amount:</th>
                                    <th><input type="text" class="form-control" id="total_jc_amount" name="total_jc_amount" readonly></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Supplier Information</label>
                    <div id="suppliers_container"></div>
                </div>

                <div class="mb-3">
                    <label class="form-label font-weight-bold">Payment Mode Information</label>
                    <div style="overflow-x: auto; max-width: 100%; white-space: nowrap;">
                        <table class="table table-bordered table-sm" id="payment_details_table" style="min-width: 1300px;">
<thead class="thead-light">
    <tr>
<th style="width: 5%;"><input type="checkbox" id="select_all_payments"></th>
<th style="width: 12%;">Payee</th>
<th style="width: 18%;min-width: 135px;">Type</th>
<th style="width: 13%;">Cheque/RTGS Number</th>
<th style="width: 12%;">PD ACC Number</th>
<th style="width: 10%;min-width: 120px;">Amount</th>
                <th style="width: 8%; min-width: 100px;">GST %</th>
<th style="width: 10%;">GST Amount</th>
<th style="width: 12%;">Total Amount</th>
<th style="width: 8%;">Invoice Number</th>
<th style="width: 7%;">Invoice Date</th>
<th style="width: 7%;">Payment Date</th>
                <th style="width: 7%;">Action</th>
    </tr>
</thead>
<tbody>
</tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="8" class="text-end">Total Payment Amount (Incl. GST):</th>
                                    <td colspan="2"> <input type="text" class="form-control d-inline-block w-auto" id="total_ptm_amount" name="total_ptm_amount" readonly>
                                    </td>
                                </tr>
                                <tr style="display:none;">
                                    <th colspan="7" class="text-end">Margin:</th> <td><span id="margin_percentage" class="ms-2">N/A</span></td> </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3" id="submitBtn">Submit</button>
            </form>
        </div>
    </div>
    <?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
    $(document).ready(function() {
        // Auto-load data in edit mode
        <?php if ($edit_mode && $existing_payment_data): ?>
            console.log('Edit mode detected, loading existing payment data');
            var existingJci = '<?php echo htmlspecialchars($existing_payment_data['jci_number'] ?? ''); ?>';
            console.log('Existing JCI:', existingJci);
            if (existingJci) {
                // Set the dropdown value and trigger change
                $('#jci_number').val(existingJci);
                console.log('JCI dropdown set to:', $('#jci_number').val());
                // Trigger change after a small delay to ensure DOM is ready
                setTimeout(function() {
                    $('#jci_number').trigger('change');
                }, 100);
            }
        <?php endif; ?>

        $('#jci_number').on('change', function() {
            const jciNumber = $(this).val();

            if (!jciNumber) {
                $('#po_number_display, #po_number, #son_number, #po_amt, #soa_number, #total_jc_amount, #total_supplier_amount, #total_ptm_amount').val('');
                $('#job_card_details_table tbody, #suppliers_container, #payment_details_table tbody').empty();
                $('#po_amt_validation_msg').hide().text('');
                return;
            }

            $.ajax({
                url: '<?php echo BASE_URL; ?>modules/payments/ajax_fetch_job_card_details.php',
                type: 'GET',
                data: { jci_number: jciNumber, user_type: '<?php echo $user_type; ?>' }, // Pass user_type
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        console.log("Supplier data received:", response.suppliers); // Added console log for supplier data
                        $('#po_number_display').val(response.po_number || '');
                        $('#po_number').val(response.po_number || '');
                        $('#son_number').val(response.sell_order_number || '');

                        const jobCardTbody = $('#job_card_details_table tbody');
                        jobCardTbody.empty();
                        if (response.job_cards.length > 0) {
                            response.job_cards.forEach(function(jc) {
                                const row = `
                                    <tr class="jobcard-row" data-jc-id="${jc.id || ''}">
                                        <td><input type="text" class="form-control jc_number" name="jc_number[]" value="${jc.jci_number || ''}" readonly></td>
                                        <td><input type="text" class="form-control jc_type" name="jc_type[]" value="${jc.jci_type || ''}" readonly></td>
                                        <td><input type="text" class="form-control contracture_name" name="contracture_name[]" value="${jc.contracture_name || ''}" readonly></td>
                                        <td><input type="number" class="form-control labour_cost" name="labour_cost[]" value="${jc.labour_cost || ''}" readonly></td>
                                        <td><input type="number" class="form-control quantity" name="quantity[]" value="${jc.quantity || ''}" readonly></td>
                                        <td><input type="number" class="form-control total_amount" name="total_amount[]" value="${parseFloat(jc.total_amount).toFixed(2) || '0.00'}" readonly></td>
                                    </tr>`;
                                jobCardTbody.append(row);
                            });
                        } else {
                            jobCardTbody.append('<tr><td colspan="6" class="text-center">No Job Card Details found.</td></tr>');
                        }

                        const suppliersContainer = $('#suppliers_container');
                        suppliersContainer.empty();
                        if (response.suppliers.length > 0) {
                            const woodItems = [];
                            const glowItems = [];
                            const plyItems = [];
                            const hardwareItems = [];

                            response.suppliers.forEach(function(supplier) {
                                // Use a Set to track unique item IDs to avoid duplicates
                                const uniqueItemIds = new Set();
                                supplier.items.forEach(function(item) {
                                    if (!uniqueItemIds.has(item.id)) {
                                        uniqueItemIds.add(item.id);
                                        item.supplier_id = supplier.id;
                                        item.supplier_name = supplier.supplier_name;
                                        const itemName = item.item_name.toLowerCase();
                                        const productType = (item.product_type || '').toLowerCase();

                                        // Calculate item_amount as quantity * price
                                        item.item_amount = (parseFloat(item.item_quantity) * parseFloat(item.item_price)).toFixed(2);

                                        // Use product_type for better categorization
                                        if (productType.includes('wood') || itemName.includes('wood') || itemName.includes('mango') || itemName.includes('teak') || itemName.includes('sheesham')) {
                                            woodItems.push(item);
                                        } else if (productType.includes('glow') || itemName.includes('glow') || itemName.includes('fevicol') || itemName.includes('favicole')) {
                                            glowItems.push(item);
                                        } else if (productType.includes('plynydf') || productType.includes('ply') || itemName.includes('ply')) {
                                            plyItems.push(item);
                                        } else if (productType.includes('hardware') || itemName.includes('hardware') || itemName.includes('screw')) {
                                            hardwareItems.push(item);
                                        } else {
                                            // Default to appropriate category based on product_type
                                            if (productType) {
                                                if (productType.includes('wood')) woodItems.push(item);
                                                else if (productType.includes('glow')) glowItems.push(item);
                                                else if (productType.includes('ply')) plyItems.push(item);
                                                else if (productType.includes('hardware')) hardwareItems.push(item);
                                                else plyItems.push(item); // fallback
                                            } else {
                                                plyItems.push(item); // fallback for unknown items
                                            }
                                        }
                                    }
                                });
                            });

                            const generateSupplierTable = (items, label) => {
                                let tableHtml = `
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label"><strong>${label}</strong></label>
                                            <table class="table table-bordered supplier-item-table">
                                                <thead>
                                                    <tr>
                                                        <th style="width: 30%;">Item Name</th>
                                                        <th style="width: 20%;">Quantity</th>
                                                        <th style="width: 20%;">Price</th>
                                                        <th style="width: 30%;">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>`;
                                if (items.length > 0) {
                                    items.forEach(function(item) {
                                        tableHtml += `
                                            <tr class="supplier-item-row" data-supplier-id="${item.supplier_id || ''}" data-item-id="${item.id || ''}">
                                                <td><input type="text" class="form-control item_name" value="${item.item_name || ''}" readonly></td>
                                                <td><input type="number" class="form-control item_quantity" value="${item.item_quantity || ''}" readonly></td>
                                                <td><input type="number" class="form-control item_price" value="${item.item_price || ''}" readonly></td>
                                                <td><input type="number" class="form-control item_amount" value="${parseFloat(item.item_amount).toFixed(2) || '0.00'}" readonly></td>
                                                <input type="hidden" class="item_product_type" value="${item.product_type || ''}">
                                            </tr>`;
                                    });
                                } else {
                                    tableHtml += `<tr><td colspan="4" class="text-center">No ${label} found.</td></tr>`;
                                }
                                tableHtml += `
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>`;
                                return tableHtml;
                            };

                            let fullSupplierHtml = `
                                <div class="card card-body mb-3 supplier-group">
                                    ${generateSupplierTable(woodItems, 'Wood Items')}
                                    ${generateSupplierTable(glowItems, 'Glow Items')}
                                    ${generateSupplierTable(plyItems, 'Ply Items')}
                                    ${generateSupplierTable(hardwareItems, 'Hardware Items')}
                                </div>`;
                            suppliersContainer.append(fullSupplierHtml);
                        } else {
                            suppliersContainer.append('<p>No Supplier Information found.</p>');
                        }

                        $('#po_amt').val(response.po_amount || '');
                        $('#soa_number').val(response.soa_number || '');
                        $('#payment_details_table tbody').empty();

                        window.latestFetchedResponse = response;
                        generatePaymentRows(response.job_cards, response.suppliers, response.existing_payments || []);

                        calculateJobCardAmounts();
                        calculateSupplierItemAmounts();
                        calculatePaymentAmounts();
                        checkTotalAmountsAgainstPO();

                    } else {
                        toastr.error('Failed to fetch Job Card details: ' + (response.message || 'Unknown error'));
                        $('#po_number_display, #po_number, #son_number, #po_amt, #soa_number, #total_jc_amount, #total_supplier_amount, #total_ptm_amount').val('');
                        $('#job_card_details_table tbody, #suppliers_container, #payment_details_table tbody').empty();
                        $('#po_amt_validation_msg').hide().text('');
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error fetching Job Card details: ", status, error, xhr.responseText);
                    toastr.error('AJAX error fetching Job Card details: ' + error + '. Check console for more details.');
                }
            });
        });

        function generatePaymentRows(jobCards, suppliers, existingPayments = []) {
            $('#payment_details_table tbody').empty();

            // In edit mode, fetch existing payment details from database
            <?php if ($edit_mode): ?>
            // Load existing payment details for edit mode
            $.ajax({
                url: '<?php echo BASE_URL; ?>modules/payments/ajax_get_payment_for_edit.php',
                type: 'GET',
                data: { payment_id: <?php echo $payment_id; ?> },
                dataType: 'json',
                async: false,
                success: function(response) {
                    console.log('Raw payment edit response:', response);
                    if (response.success && response.data && response.data.payment_details) {
                        // Transform the data to match expected format (include GST fields if present)
                        existingPayments = response.data.payment_details.map(function(detail) {
                            console.log('Processing payment detail:', detail);
                            return {
                                payment_category: detail.payment_category,
                                entity_name: detail.entity_name,
                                payment_type: detail.payment_type,
                                cheque_number: detail.cheque_number,
                                pd_acc_number: detail.pd_acc_number,
                                ptm_amount: detail.ptm_amount,
                                payment_date: detail.payment_date,
                                payment_invoice_date: detail.payment_invoice_date,
                                gst_percent: (typeof detail.gst_percent !== 'undefined') ? detail.gst_percent : null,
                                gst_amount: (typeof detail.gst_amount !== 'undefined') ? detail.gst_amount : null,
                                total_with_gst: (typeof detail.total_with_gst !== 'undefined') ? detail.total_with_gst : null
                            };
                        });
                        console.log('Loaded existing payments for edit:', existingPayments);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading existing payments:', error);
                }
            });
            <?php endif; ?>

            jobCards.forEach(function(jc) {
                // Find existing payment for this job card
                const existingPayment = existingPayments.find(p => 
                    p.payment_category === 'Job Card' &&
                    p.payment_date // Only consider as paid if payment_date exists
                );
                const isReadOnly = !!(existingPayment && existingPayment.payment_date);
                console.log('Job Card:', jc.jci_number, 'Existing Payment:', existingPayment, 'ReadOnly:', isReadOnly);

                // Use existing payment data if available
                let chequeType = '';
                let chequeNumber = '';
                let pdAccNumber = '';
                let ptmAmount = parseFloat(jc.total_amount).toFixed(2);
                let paymentDate = '';
                let invoiceNumber = '';
                let invoiceDate = '';
                
                if (existingPayment) {
                    chequeType = existingPayment.payment_type || '';
                    chequeNumber = existingPayment.cheque_number || '';
                    pdAccNumber = existingPayment.pd_acc_number || '';
                    ptmAmount = parseFloat(existingPayment.ptm_amount || jc.total_amount).toFixed(2);
                    paymentDate = existingPayment.payment_date || '';
                    invoiceNumber = existingPayment.invoice_number || ''; // Populate invoice number for Job Card
                    invoiceDate = existingPayment.payment_invoice_date || ''; // Populate invoice date for Job Card
                }

                const disabledSelectAttr = isReadOnly ? 'disabled' : '';
                const disabledInputAttr = isReadOnly ? 'readonly disabled' : '';
                const ptmAmountReadonlyAttr = isReadOnly ? 'readonly' : '';
                const checkedAttr = isReadOnly ? 'checked' : '';

                const newRow = `
                    <tr class="payment-row" data-entity-type="job_card" data-entity-id="${jc.id || ''}" data-original-amount="${parseFloat(jc.total_amount).toFixed(2)}">
                        <td><input type="checkbox" class="select_payment" ${checkedAttr} ${isReadOnly ? 'disabled' : ''}></td>
                        <td>Job Card: ${jc.jci_number}</td>
                        <td style="width: 18%;">
                            <select class="form-control cheque_type" name="cheque_type[]" ${disabledSelectAttr}>
                                <option value="">Select Type</option>
                                <option value="Cheque" ${chequeType === 'Cheque' ? 'selected' : ''}>Cheque</option>
                                <option value="RTGS" ${chequeType === 'RTGS' ? 'selected' : ''}>RTGS</option>
                            </select>
                        </td>
                        <td style="width: 13%;"><input type="text" class="form-control cheque_number" name="cheque_number[]" placeholder="Enter Cheque/RTGS Number" value="${chequeNumber}" ${disabledInputAttr}></td>
                        <td style="width: 12%;"><input type="number" class="form-control pd_acc_number" name="pd_acc_number[]" value="${pdAccNumber}" ${disabledInputAttr}></td>
                        <td style="width: 10%;"><input type="number" class="form-control ptm_amount" name="ptm_amount[]" min="0" step="0.01" value="${ptmAmount}" ${ptmAmountReadonlyAttr}></td>
                        <td style="width: 8%;"><input type="number" class="form-control gst_percent" name="gst_percent[]" min="0" step="0.01" value="${parseFloat(existingPayment?.gst_percent || 0).toFixed(2)}"></td>
                        <td style="width: 10%;"><input type="number" class="form-control gst_amount" name="gst_amount[]" min="0" step="0.01" value="${parseFloat(existingPayment?.gst_amount || 0).toFixed(2)}" readonly></td>
                        <td style="width: 12%;"><input type="number" class="form-control total_with_gst" name="total_with_gst[]" min="0" step="0.01" value="${parseFloat(existingPayment?.total_with_gst || ptmAmount).toFixed(2)}" readonly></td>
                        <td style="width: 8%;"><input type="text" class="form-control invoice_number" name="invoice_number[]" value="${invoiceNumber}" readonly></td>
                        <td style="width: 7%;"><input type="date" class="form-control invoice_date" name="invoice_date[]" value="${invoiceDate}"></td>
                        <td style="width: 7%;"><input type="date" class="form-control payment_date" name="payment_date[]" value="${paymentDate}" ${isReadOnly ? 'readonly' : ''}></td>
                        <td style="width: 7%;"><button type="button" class="btn btn-sm btn-primary save-payment-row">Save</button></td>
                    </tr>`;
                $('#payment_details_table tbody').append(newRow);
            });

            // Trigger initial GST calculation for all rows
            $('#payment_details_table tbody .payment-row').each(function() {
                recalcRowTotals($(this));
            });

            suppliers.forEach(function(supplier, index) {
                console.log('Processing supplier:', supplier.supplier_name, 'Invoice:', supplier.invoice_number);
                console.log('Available existing payments:', existingPayments);
                
                // Find existing payment for this SPECIFIC supplier by matching invoice number in cheque_number field
                const existingPayment = existingPayments.find(p => {
                    console.log('Checking payment:', p, 'against supplier invoice:', supplier.invoice_number);
                    return p.payment_category === 'Supplier' && 
                           p.cheque_number === supplier.invoice_number &&
                           p.payment_date; // Only consider as paid if payment_date exists
                });
                const isReadOnly = !!(existingPayment && existingPayment.payment_date);
                
                console.log('Supplier:', supplier.supplier_name, 'Invoice:', supplier.invoice_number, 'Existing Payment:', existingPayment, 'ReadOnly:', isReadOnly);

                // Use existing payment data if available, otherwise use supplier data
                let chequeType = '';
                let chequeNumber = '';
                let pdAccNumber = '';
                let ptmAmount = parseFloat(supplier.invoice_amount || '0').toFixed(2);
                let invoiceNumber = supplier.invoice_number || '';
                let invoiceDate = supplier.invoice_date || '';
                let paymentDate = '';
                
                if (existingPayment) {
                    chequeType = existingPayment.payment_type || '';
                    chequeNumber = existingPayment.cheque_number || '';
                    pdAccNumber = existingPayment.pd_acc_number || '';
                    ptmAmount = parseFloat(existingPayment.ptm_amount || 0).toFixed(2);
                    paymentDate = existingPayment.payment_date || '';
                    invoiceNumber = supplier.invoice_number || ''; // Use supplier invoice number, not cheque_number
                    invoiceDate = existingPayment.payment_invoice_date || supplier.invoice_date || ''; // Populate invoice date
                }

                const disabledSelectAttr = isReadOnly ? 'disabled' : '';
                const disabledInputAttr = isReadOnly ? 'readonly disabled' : '';
                const ptmAmountReadonlyAttr = isReadOnly ? 'readonly' : '';
                const checkedAttr = isReadOnly ? 'checked' : '';

                // Create display name with invoice info
                let displayName = `Supplier: ${supplier.supplier_name}`;
                if (supplier.invoice_number) {
                    displayName += ` (Invoice: ${supplier.invoice_number})`;
                }

                const newRow = `
                    <tr class="payment-row" data-entity-type="supplier" data-entity-id="${supplier.id || ''}" data-supplier-name="${supplier.supplier_name}" data-invoice-number="${supplier.invoice_number}" data-original-amount="${ptmAmount}" ${isReadOnly ? 'style="background-color: #f8f9fa;"' : ''}>
                        <td><input type="checkbox" class="select_payment" ${checkedAttr} ${isReadOnly ? 'disabled' : ''}></td>
                        <td>${displayName}</td>
                        <td style="width: 18%;min-width: 135px;">
                            <select class="form-control cheque_type" name="cheque_type[]" ${disabledSelectAttr}>
                                <option value="">Select Type</option>
                                <option value="Cheque" ${chequeType === 'Cheque' ? 'selected' : ''}>Cheque</option>
                                <option value="RTGS" ${chequeType === 'RTGS' ? 'selected' : ''}>RTGS</option>
                            </select>
                        </td>
                        <td style="width: 13%;"><input type="text" class="form-control cheque_number" name="cheque_number[]" placeholder="Enter Cheque/RTGS Number" value="${chequeNumber}" ${disabledInputAttr}></td>
                        <td style="width: 12%;"><input type="number" class="form-control pd_acc_number" name="pd_acc_number[]" value="${pdAccNumber}" ${disabledInputAttr}></td>
                        <td style="width: 10%;min-width: 120px;"><input type="number" class="form-control ptm_amount" name="ptm_amount[]" min="0" step="0.01" value="${ptmAmount}" ${ptmAmountReadonlyAttr}></td>
                        <td style="width: 8%;"><input type="number" class="form-control gst_percent" name="gst_percent[]" min="0" step="0.01" value="${parseFloat(existingPayment?.gst_percent || 0).toFixed(2)}"></td>
                        <td style="width: 10%;"><input type="number" class="form-control gst_amount" name="gst_amount[]" min="0" step="0.01" value="${parseFloat(existingPayment?.gst_amount || 0).toFixed(2)}" readonly></td>
                        <td style="width: 12%;"><input type="number" class="form-control total_with_gst" name="total_with_gst[]" min="0" step="0.01" value="${parseFloat(existingPayment?.total_with_gst || ptmAmount).toFixed(2)}" readonly></td>
                        <td style="width: 8%;"><input type="text" class="form-control invoice_number" name="invoice_number[]" value="${invoiceNumber}" readonly></td>
                        <td style="width: 7%;"><input type="date" class="form-control invoice_date" name="invoice_date[]" value="${invoiceDate}"></td>
                        <td style="width: 7%;"><input type="date" class="form-control payment_date" name="payment_date[]" value="${paymentDate}" ${isReadOnly ? 'readonly' : ''}></td>
                        <td style="width: 7%;"><button type="button" class="btn btn-sm btn-primary save-payment-row">Save</button></td>
                    </tr>`;
                $('#payment_details_table tbody').append(newRow);
            });

            // Trigger initial GST calculation for all rows
            $('#payment_details_table tbody .payment-row').each(function() {
                recalcRowTotals($(this));
            });
        }

        // Added event handler to enable/disable inputs based on checkbox state
        $('#payment_details_table').on('change', '.select_payment', function() {
            const $row = $(this).closest('.payment-row');
            const isChecked = $(this).is(':checked');
            // Only enable/disable if not in read-only state (i.e., not already existing payment)
            if (!$(this).is(':disabled')) {
                if (isChecked) {
                    $row.find('input, select').not('.select_payment, .invoice_number, .invoice_date').prop('disabled', false);
                    $row.find('.ptm_amount').prop('readonly', false);
                    $row.find('.payment_date').focus(); // Focus on payment date after checking
                } else {
                    $row.find('input, select').not('.select_payment, .invoice_number, .invoice_date').prop('disabled', true);
                    $row.find('.ptm_amount').prop('readonly', true);
                    // Clear values when unchecked, unless they are invoice details
                    $row.find('.cheque_type, .cheque_number, .pd_acc_number, .ptm_amount, .payment_date').val('');
                    // Restore original amount for unchecked rows (optional, based on desired behavior)
                    const originalAmount = $row.data('original-amount');
                    $row.find('.ptm_amount').val(originalAmount);
                }
                calculatePaymentAmounts(); // Recalculate total payment amount after checkbox change
            }
        });

        // GST calculations per row
        function recalcRowTotals($row) {
            const amount = parseFloat($row.find('.ptm_amount').val()) || 0;
            const gstPct = parseFloat($row.find('.gst_percent').val()) || 0;
            const gstAmt = (amount * gstPct) / 100;
            const totalWithGst = amount + gstAmt;
            $row.find('.gst_amount').val(gstAmt.toFixed(2));
            $row.find('.total_with_gst').val(totalWithGst.toFixed(2));
        }

        $('#payment_details_table').on('input', '.ptm_amount, .gst_percent', function() {
            const $row = $(this).closest('.payment-row');
            recalcRowTotals($row);
            calculatePaymentAmounts();
        });


        function calculateJobCardAmounts() {
            let totalJobCardAmount = 0;
            $('#job_card_details_table tbody .jobcard-row').each(function() {
                let jcAmt = parseFloat($(this).find('.total_amount').val()) || 0;
                totalJobCardAmount += jcAmt;
            });
            $('#total_jc_amount').val(totalJobCardAmount.toFixed(2));
        }

        function calculateSupplierItemAmounts() {
            let totalGrandSupplierAmount = 0;
            $('#suppliers_container .supplier-group').each(function() {
                $(this).find('.supplier-item-table tbody .supplier-item-row').each(function() {
                    let amount = parseFloat($(this).find('.item_amount').val()) || 0;
                    totalGrandSupplierAmount += amount;
                });
            });
            $('#total_supplier_amount').val(totalGrandSupplierAmount.toFixed(2));
        }

        function calculatePaymentAmounts() {
            let totalPaymentAmount = 0;
            $('#payment_details_table tbody .payment-row').each(function() {
                const $row = $(this);
                // Removed the conditional check for checkbox state or disabled state.
                // Now, all visible rows will contribute to the total.
                let totalWithGst = parseFloat($row.find('.total_with_gst').val()) || 0;
                    totalPaymentAmount += totalWithGst;
            });
            $('#total_ptm_amount').val(totalPaymentAmount.toFixed(2));
        }

        function checkTotalAmountsAgainstPO() {
            const poAmt = parseFloat($('#po_amt').val()) || 0;
            const totalJcAmount = parseFloat($('#total_jc_amount').val()) || 0;
            const totalSupplierItemsAmount = parseFloat($('#total_supplier_amount').val()) || 0;
            const combinedJcAndSupplierAmount = totalJcAmount + totalSupplierItemsAmount;
            const tenPercentPoAmt = poAmt * 1.10;
            const $validationMsg = $('#po_amt_validation_msg');

            $validationMsg.text('');
            $validationMsg.hide();

            if (poAmt === 0 && combinedJcAndSupplierAmount > 0) {
                $validationMsg.text('PO Amount cannot be zero if Job Card or Supplier Item amounts are entered.');
                $validationMsg.show();
            } else if (combinedJcAndSupplierAmount > tenPercentPoAmt) {
                $validationMsg.text(`Combined JC & Item Amount (${combinedJcAndSupplierAmount.toFixed(2)}) exceeds 110% of PO Amount (${poAmt.toFixed(2)}).`);
                $validationMsg.show();
            }
        }

        // New: Handle individual row saving
        $(document).on('click', '.save-payment-row', function() {
            const $row = $(this).closest('.payment-row');
            const $checkbox = $row.find('.select_payment');
            
            // Ensure the checkbox is checked before saving an individual row
            if (!$checkbox.is(':checked')) {
                toastr.error('Please select the payment row checkbox before saving.');
                return;
            }

            const paymentData = getPaymentDataFromRow($row);

            if (!paymentData) {
                toastr.error('Please fill all required fields (Type, Cheque Number, Payment Date) for the selected payment.');
                return;
            }

            savePaymentData([paymentData], function(response) {
                if (response.success) {
                    toastr.success('Payment row saved successfully!');
                    // Make the saved row read-only and checked
                    $checkbox.prop('checked', true).prop('disabled', true);
                    $row.find('input, select').not('.select_payment, .save-payment-row').prop('disabled', true);
                    $row.find('.ptm_amount').prop('readonly', true);
                    $row.addClass('existing-payment').css('background-color', '#f8f9fa');
                    $row.find('td').eq(1).append(' <small class="text-muted existing-payment-note">(Already Paid)</small>');
                    calculatePaymentAmounts(); // Recalculate totals
                } else {
                    toastr.error(response.message || 'Failed to save payment row.');
                }
            });
        });

        // Function to extract payment data from a single row
        function getPaymentDataFromRow($row) {
            const chequeType = $row.find('.cheque_type').val();
            const chequeNumber = $row.find('.cheque_number').val();
            const paymentDate = $row.find('.payment_date').val();
                
                if (!chequeType || !chequeNumber || !paymentDate) {
                return null; // Incomplete data
            }

            return {
                entity_type: $row.data('entity-type'),
                entity_id: $row.data('entity-id'),
                payee: $row.find('td').eq(1).text().trim(),
                invoice_number: $row.find('.invoice_number').val(),
                invoice_date: $row.find('.invoice_date').val(),
                    payment_date: paymentDate,
                    cheque_type: chequeType,
                    cheque_number: chequeNumber,
                pd_acc_number: $row.find('.pd_acc_number').val(),
                ptm_amount: $row.find('.ptm_amount').val(),
                gst_percent: $row.find('.gst_percent').val(),
                gst_amount: $row.find('.gst_amount').val(),
                total_with_gst: $row.find('.total_with_gst').val()
            };
        }

        // Function to send payment data to the backend
        function savePaymentData(paymentsArray, callback) {
            let formData = {};
            formData['payment_id'] = $('#payment_id').val();
            formData['jci_number'] = $('#jci_number').val();
            formData['po_number'] = $('#po_number').val(); // Correct key
            formData['sell_order_number'] = $('#son_number').val(); // Correct key
            formData['payments'] = JSON.stringify(paymentsArray);

            $.ajax({
                url: '<?php echo BASE_URL; ?>modules/payments/ajax_save_payment.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.payment_id) {
                        $('#payment_id').val(response.payment_id); // Update main payment_id if new
                    }
                    callback(response);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error during save_payment: ", status, error, xhr.responseText);
                    callback({ success: false, message: 'An error occurred while saving the payment. Please check the browser console for details.' });
                }
            });
        }

        // Modify submitFormData to use the new savePaymentData function for bulk save
        function submitFormData() {
            let paymentsData = [];
                                $('#payment_details_table tbody .payment-row').each(function() {
                                    const $row = $(this);
                // Only consider checked and editable rows for submission
                if ($row.find('.select_payment').is(':checked') && !$row.find('.select_payment').is(':disabled')) {
                    const payment = getPaymentDataFromRow($row);
                    if (payment) paymentsData.push(payment);
                }
            });

            if (paymentsData.length === 0) {
                toastr.error('No valid payments selected to save.');
                return;
            }

            // The savePaymentData function already constructs the basic formData,
            // so we just need to pass the payments array.
            savePaymentData(paymentsData, function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // After bulk save, reload all payment details to reflect new read-only states
                    const jciNumber = $('#jci_number').val();
                    if (jciNumber) {
                        $('#jci_number').trigger('change'); // Re-fetch all data to refresh state
                    }
                } else {
                    toastr.error(response.message);
                }
            });
        }

        // Modify select_all_payments to only check/uncheck editable rows
        $('#select_all_payments').on('change', function() {
            const isChecked = $(this).is(':checked');
            $('#payment_details_table tbody .select_payment').each(function() {
                const $checkbox = $(this);
                if (!$checkbox.is(':disabled')) { // Only toggle if not disabled (i.e., not already paid)
                    $checkbox.prop('checked', isChecked).trigger('change'); // Trigger change to update row state
                }
            });
        });

        // Ensure payment_date is populated when select_payment checkbox is checked
        $('#payment_details_table').on('change', '.select_payment', function() {
            const $row = $(this).closest('.payment-row');
            const isChecked = $(this).is(':checked');
            const $paymentDateInput = $row.find('.payment_date');

            if (isChecked && !$paymentDateInput.val()) {
                const today = new Date().toISOString().split('T')[0];
                $paymentDateInput.val(today);
            }
            // Existing logic to enable/disable other inputs will run via trigger('change')
            // This ensures only editable rows are enabled/disabled
            const isReadOnly = $(this).is(':disabled'); // Check if the checkbox itself is disabled (meaning it's an existing payment)
            if (!isReadOnly) {
                $row.find('input, select').not('.select_payment, .invoice_number, .invoice_date, .save-payment-row').prop('disabled', !isChecked);
                $row.find('.ptm_amount').prop('readonly', !isChecked);
            }
            calculatePaymentAmounts();
        });

        // Update the form submission to use the new submitFormData function
        $('#vendorPayment_form').off('submit').on('submit', function(e) {
            e.preventDefault();

            if (!$('#jci_number').val()) {
                toastr.error('Please select a Job Card Number before submitting.');
                return;
            }

            const selectedEditablePayments = $('#payment_details_table tbody .select_payment:checked').filter(function() {
                return !$(this).is(':disabled'); // Only count editable checked rows
            });

            if (selectedEditablePayments.length === 0) {
                toastr.error('Please select at least one *new* payment row to save.');
                return;
            }
            
            // Validate that all selected payments have required fields, including non-empty cheque_number for suppliers
            let hasIncompletePayments = false;
            selectedEditablePayments.each(function() {
                const row = $(this).closest('tr');
                const chequeType = row.find('.cheque_type').val();
                const chequeNumber = row.find('.cheque_number').val();
                const paymentDate = row.find('.payment_date').val();
                const entityType = row.data('entity-type'); // Get entity type
                
                if (!chequeType || !paymentDate || !chequeNumber) { // chequeNumber is now generally required
                    hasIncompletePayments = true;
                    return false;
                }

                // Additional validation for supplier payments: cheque_number cannot be empty for uniqueness
                if (entityType === 'supplier' && !chequeNumber.trim()) {
                    hasIncompletePayments = true;
                    toastr.error('Cheque/RTGS Number cannot be empty for selected supplier payments.');
                    return false;
                }
            });
            
            if (hasIncompletePayments) {
                toastr.error('Please fill all required fields (Type, Cheque Number, Payment Date) for selected payments.');
                return;
            }

            submitFormData();
        });

        // Re-attach existing event handlers after any dynamic content changes
        calculateJobCardAmounts();
        calculateSupplierItemAmounts();
        calculatePaymentAmounts();
        checkTotalAmountsAgainstPO();
    });
</script>
