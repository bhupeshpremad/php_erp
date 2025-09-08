<?php
// Ensure configuration is loaded and ROOT_DIR_PATH is defined
include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}

session_start();

// Include header for common head elements and initial HTML structure
include_once ROOT_DIR_PATH . 'include/inc/header.php';

// Determine user type for sidebar inclusion
$user_type = $_SESSION['user_type'] ?? 'guest';

// Include appropriate sidebar based on user type
if ($user_type === 'superadmin') {
    include_once ROOT_DIR_PATH . 'superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    include_once ROOT_DIR_PATH . 'salesadmin/sidebar.php';
} elseif ($user_type === 'accounts') {
    include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php';
}

// Access global database connection
global $conn;

// Initialize variables for edit mode and data
$id = $_GET['id'] ?? null;
$edit_mode = false;
$jci_data = []; // Stores main JCI record
$item_data = []; // Stores associated JCI items

// If an ID is provided, activate edit mode and fetch data
if ($id) {
    $edit_mode = true;
    // Fetch the specific jci_main record
    $stmt = $conn->prepare("SELECT * FROM jci_main WHERE id = ?");
    $stmt->execute([$id]);
    $jci_data = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch items related to this specific jci_main ID
    $stmt2 = $conn->prepare("SELECT * FROM jci_items WHERE jci_id = ?");
    $stmt2->execute([$id]);
    $item_data = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Function to generate the base JCI number (like JCI-2025-0001).
 * The 'X' suffix for JOB-YEAR-JCN-X will be handled in ajax_save_jci.php.
 * @param PDO $conn The database connection object.
 * @return string The generated base JCI number.
 */
function generateBaseJCINumber($conn) {
    $year = date('Y');
    $prefix = "JCI-$year-"; // This will be the base for JOB-YEAR-JCN
    $stmt = $conn->prepare("SELECT MAX(
        CASE
            WHEN jci_number LIKE 'JOB-{$year}-%-%' THEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(jci_number, '-', 3), '-', -1) AS UNSIGNED)
            WHEN jci_number LIKE 'JCI-{$year}-%' THEN CAST(SUBSTRING_INDEX(jci_number, '-', -1) AS UNSIGNED)
            ELSE 0
        END
    ) AS last_seq FROM jci_main
    WHERE jci_number LIKE 'JOB-{$year}-%' OR jci_number LIKE 'JCI-{$year}-%';");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $last_seq = (int)$result['last_seq'];
    $next_seq = $last_seq + 1;
    $seqFormatted = str_pad($next_seq, 4, '0', STR_PAD_LEFT);
    return $prefix . $seqFormatted;
}

// Determine the JCI number for the input field
$auto_jci_number = $edit_mode ? ($jci_data['jci_number'] ?? generateBaseJCINumber($conn)) : generateBaseJCINumber($conn);

// In edit mode, if jci_number already has a suffix (e.g., JOB-2024-0001-A),
// we need to extract the base part for the input field display.
if ($edit_mode && preg_match('/JOB-\d{4}-(\d{4})-[A-Z]/', $jci_data['jci_number'] ?? '', $matches)) {
    $auto_jci_number = "JCI-" . date('Y') . "-" . $matches[1];
}

// Set created_by and main JCI date/type
$created_by = $edit_mode ? ($jci_data['created_by'] ?? '') : ($_SESSION['user_name'] ?? '');
// Get sell order ID from jci_main table for edit mode
$sell_order_selected = '';
if ($edit_mode && !empty($jci_data)) {
    // Try to find sell order by sell_order_number from JCI data
    if (!empty($jci_data['sell_order_number'])) {
        $stmt_so = $conn->prepare("SELECT id FROM sell_order WHERE sell_order_number = ?");
        $stmt_so->execute([$jci_data['sell_order_number']]);
        $so_result = $stmt_so->fetch(PDO::FETCH_ASSOC);
        if ($so_result) {
            $sell_order_selected = $so_result['id'];
        }
    }
}

$jci_main_date = $edit_mode ? ($jci_data['jci_date'] ?? date('Y-m-d')) : date('Y-m-d');
$jci_main_type = $edit_mode ? ($jci_data['jci_type'] ?? 'Internal') : 'Internal'; // Default to Internal

/* Temporarily relax sell order query conditions to debug dropdown population */
$stmt_sell_order = $conn->prepare("SELECT so.id, so.sell_order_number, po.po_number, po.client_name FROM sell_order so JOIN po_main po ON so.po_id = po.id ORDER BY so.sell_order_number ASC");
$stmt_sell_order->execute();
$sell_orders = $stmt_sell_order->fetchAll(PDO::FETCH_ASSOC);

// Debug output for sell orders count and numbers
error_log("Sell orders count: " . count($sell_orders));
foreach ($sell_orders as $so) {
    error_log("Sell order: " . $so['sell_order_number']);
}

// For edit mode, include current sell order
if ($edit_mode && !empty($jci_data['sell_order_number'])) {
    $stmt_current = $conn->prepare("SELECT so.id, so.sell_order_number, po.po_number, po.client_name FROM sell_order so JOIN po_main po ON so.po_id = po.id WHERE so.sell_order_number = ?");
    $stmt_current->execute([$jci_data['sell_order_number']]);
    $current_so = $stmt_current->fetch(PDO::FETCH_ASSOC);
    if ($current_so) {
        array_unshift($sell_orders, $current_so);
    }
}
?>

<style>
    /* Custom CSS for Select2 to better integrate with Bootstrap form-control */
    .select2-container .select2-selection--single {
        display: block;
        width: 100%;
        height: calc(1.5em + .75rem + 2px); /* Matches Bootstrap's form-control height */
        padding: .375rem .75rem; /* Matches Bootstrap's form-control padding */
        font-size: 1rem;
        font-weight: 400;
        line-height: 1.5;
        color: #6e707e;
        background-color: #fff;
        background-clip: padding-box;
        border: 1px solid #d1d3e2;
        border-radius: .35rem;
        transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
        width: 100% !important;
        height: calc(1.5em + .75rem + 2px) !important;
        padding: .375rem !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
        color: #6e707e;
        line-height: 1.5; /* Ensure text aligns in the middle */
        padding-left: 0; /* Remove default padding from select2 */
        padding-right: 2rem; /* Make space for the clear button */
        width: 100% !important;
        height: calc(1.5em + .75rem + 2px) !important;
        padding: 0 !important;
    }
    .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
        height: calc(1.5em + .75rem + 2px); /* Match height */
        width: 2rem; /* Give enough space for the arrow */
        position: absolute;
        top: 0;
        right: 0;
    }
    .select2-container--bootstrap4.select2-container--focus .select2-selection--single,
    .select2-container--bootstrap4.select2-container--open .select2-selection--single {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    .select2-container--bootstrap4 .select2-selection__clear {
        float: right;
        font-size: 1em; /* Adjust size of the 'x' */
        position: absolute;
        right: 1.7rem; /* Position it to the left of the arrow */
        top: 50%;
        transform: translateY(-50%);
        padding: 0;
        height: 1.5em; /* Match line height */
        line-height: 1.5em;
        width: 1.5em; /* Make it a square */
        text-align: center;
    }
    .select2-container--bootstrap4 .select2-dropdown {
        border-color: #d1d3e2;
        box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
    }
    .select2-container--bootstrap4 .select2-results__option--highlighted,
    .select2-container--bootstrap4 .select2-results__option--selected {
        background-color: #4e73df !important; /* Primary color from your theme */
        color: #fff !important;
    }

    /* Table container for horizontal scroll */
    .table-responsive-custom {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    /* Table column widths - Adjust as needed */
    #itemsTable {
        min-width: 1500px; /* Minimum width to ensure horizontal scrolling */
    }
    #itemsTable th, #itemsTable td {
        vertical-align: middle;
        white-space: nowrap; /* Prevent text wrapping */
    }
    #itemsTable thead th:nth-child(1) { width: 50px; }  /* Serial Number */
    #itemsTable thead th:nth-child(2) { width: 180px; } /* PO Product */
    #itemsTable thead th:nth-child(3) { width: 150px; } /* Product Name */
    #itemsTable thead th:nth-child(4) { width: 120px; } /* Item Code */
    #itemsTable thead th:nth-child(5) { width: 100px; } /* Original Qty */
    #itemsTable thead th:nth-child(6) { width: 100px; } /* Assign Qty */
    #itemsTable thead th:nth-child(7) { width: 100px; } /* Remaining Qty */ /* Corrected index */
    #itemsTable thead th:nth-child(8) { width: 100px; } /* Labour Cost */ /* Corrected index */
    #itemsTable thead th:nth-child(9) { width: 120px; min-width: 155px;} /* Total */ /* Corrected index */
    #itemsTable thead th:nth-child(10) { width: 140px; min-width: 140px; } /* Delivery Date */ /* Corrected index */
    #itemsTable thead th:nth-child(11) { width: 140px; } /* Job Card Date */ /* Corrected index */
    #itemsTable thead th:nth-child(12) { width: 140px; } /* Job Card Type */ /* Corrected index */
    #itemsTable thead th:nth-child(13) { width: 180px; } /* Contracture Name */ /* Corrected index */
    #itemsTable thead th:nth-child(14) { width: 80px; }  /* Action */ /* Corrected index */

    /* Ensure specific input types are narrow enough within table cells */
    #itemsTable input[type="number"],
    #itemsTable input[type="date"],
    #itemsTable select {
        width: 100%;
    }
</style>

<div class="container-fluid mb-5">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Job Card</h6>
        </div>
        <div class="card-body">
            <form id="jciForm" autocomplete="off">
                <?php if ($edit_mode): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
                <?php endif; ?>
                <div class="row mb-3">

                    <div class="col-lg-4 mb-2">
                        <label for="base_jci_number" class="form-label">JCI Number</label>
                        <input type="text" class="form-control" id="base_jci_number" name="base_jci_number" readonly value="<?php echo htmlspecialchars($auto_jci_number); ?>">
                    </div>
                    <div class="col-lg-4 mb-2">
                        <label for="sell_order_id" class="form-label">Select Sale Order Number</label>
                        <select class="form-control select2-enabled" id="sell_order_id" name="sell_order_id" required style="width:100%;">
                            <option value="">Select Sale Order Number</option>
                            <?php foreach ($sell_orders as $so): ?>
                                <option value="<?php echo htmlspecialchars($so['id']); ?>" data-po-number="<?php echo htmlspecialchars($so['po_number']); ?>" data-client="<?php echo htmlspecialchars($so['client_name']); ?>" <?php echo ($sell_order_selected == $so['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($so['sell_order_number']); ?> (<?php echo htmlspecialchars($so['po_number']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-4 mb-2">
                        <label for="po_number_display" class="form-label">PO Number</label>
                        <input type="text" class="form-control" id="po_number_display" name="po_number_display" readonly value="">
                        <input type="hidden" id="po_number" name="po_number">
                        <input type="hidden" id="sell_order_number" name="sell_order_number">
                    </div>

                    <div class="col-lg-4 mb-2">
                        <label for="bom_id" class="form-label">Select BOM</label>
                        <select class="form-control select2-enabled" id="bom_id" name="bom_id" required style="width:100%;">
                            <option value="">Select a BOM Number</option>
                            <?php
                            // Fetch only BOMs not assigned to JCI OR current BOM in edit mode
                            if ($edit_mode && !empty($jci_data['bom_id'])) {
                                $stmt_bom = $conn->prepare("SELECT id, bom_number FROM bom_main WHERE jci_assigned = 0 OR id = ? ORDER BY bom_number ASC");
                                $stmt_bom->execute([$jci_data['bom_id']]);
                            } else {
                                $stmt_bom = $conn->prepare("SELECT id, bom_number FROM bom_main WHERE jci_assigned = 0 ORDER BY bom_number ASC");
                                $stmt_bom->execute();
                            }
                            $bom_numbers = $stmt_bom->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($bom_numbers as $bom):
                            ?>
                                <option value="<?php echo htmlspecialchars($bom['id']); ?>" <?php echo (isset($jci_data['bom_id']) && $jci_data['bom_id'] == $bom['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($bom['bom_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-lg-4 mb-2">
                        <label for="created_by" class="form-label">Created By</label>
                        <input type="text" class="form-control" id="created_by" name="created_by" value="<?php echo htmlspecialchars($created_by); ?>" required>
                    </div>
                    <div class="col-lg-4 mb-2">
                        <label for="jci_date" class="form-label">Job Card Date</label>
                        <input type="date" class="form-control" id="jci_date" name="jci_date" value="<?php echo htmlspecialchars($jci_main_date); ?>" required>
                    </div>
                </div>

                <div id="contractureDetails">
                    <h5>Add Contracture Details</h5>
                    <div class="table-responsive-custom">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th>Sl No.</th>
                                    <th>Job Card Number</th>
                                    <th>PO Product</th>
                                    <th>Product Name</th>
                                    <th>Item Code</th>
                                    <th>Original Qty</th>
                                    <th>Assign Qty</th>
                                    <th>Remaining Qty</th>
                                    <th>Labour Cost</th>
                                    <th>Total</th>
                                    <th>Delivery Date</th>
                                    <th>Job Card Date</th>
                                    <th>Job Card Type</th>
                                    <th>Contracture Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="7" class="text-right">Grand Total Labour Cost</th>
                                    <th>
                                        <input type="text" id="grandTotal" class="form-control" readonly value="0">
                                    </th>
                                    <th colspan="5"></th>
                                    <th>
                                        <button type="button" class="btn btn-secondary btn-sm ms-2 mb-3" id="addRowBtn" title="Add Row">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $edit_mode ? 'Update' : 'Save'; ?> Job Card(s)</button>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/js/all.min.js" crossorigin="anonymous"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for the main PO dropdown
    $('.select2-enabled').select2({
        placeholder: "Select Sale Order Number",
        allowClear: true,
        theme: "bootstrap4"
    });

    let poProducts = [];
    let rowCount = 0;

    function calculateRowTotal(row) {
        const costInput = row.querySelector('.labour-cost');
        const assignedQtyInput = row.querySelector('.assign-qty');
        const totalInput = row.querySelector('.item-total');

        const cost = parseFloat(costInput.value) || 0;
        const assignedQty = parseFloat(assignedQtyInput.value) || 0;
        const total = cost * assignedQty;

        totalInput.value = total.toFixed(2);
        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let grandTotal = 0;
        document.querySelectorAll('.item-total').forEach(function(input) {
            grandTotal += parseFloat(input.value) || 0;
        });
        document.getElementById('grandTotal').value = grandTotal.toFixed(2);
    }

    function updateSerialNumbers() {
        const rows = document.querySelectorAll('#itemsTable tbody tr');
        rows.forEach((row, index) => {
            row.querySelector('td:first-child').textContent = index + 1;
        });
    }

    function toggleContractureName(rowElement) {
        const jobCardTypeSelect = rowElement.querySelector('.job-card-type');
        const contractureNameCell = rowElement.querySelector('.contracture-name-cell');
        const contractureNameInput = rowElement.querySelector('.contracture-name');

        if (jobCardTypeSelect.value === 'Contracture') {
            contractureNameCell.style.display = '';
            contractureNameInput.required = true;
            contractureNameInput.disabled = false;
        } else {
            contractureNameCell.style.display = 'none';
            contractureNameInput.required = false;
            contractureNameInput.disabled = true;
            contractureNameInput.value = '';
        }
    }

    function addRow(itemData = {}) {
        rowCount++;
        const tbody = document.querySelector('#itemsTable tbody');
        const tr = document.createElement('tr');
        tr.dataset.rowIndex = rowCount;

        const jciItemId = itemData.id || '';
        const poProductId = itemData.po_product_id || '';
        const productName = itemData.product_name || '';
        const itemCode = itemData.item_code || '';
        const originalPoQuantity = itemData.original_po_quantity || '';
        const assignedQuantity = itemData.quantity || '';
        const labourCost = itemData.labour_cost || '';
        const total = itemData.total_amount || '';
        const deliveryDate = itemData.delivery_date || '';
        const jobCardDate = itemData.job_card_date || '';
        const jobCardType = itemData.job_card_type || 'Contracture';
        const contractureName = itemData.contracture_name || '';
        const jobCardNumber = itemData.job_card_number !== undefined ? itemData.job_card_number : '';

        tr.innerHTML = `
            <td>${rowCount}</td>
            <td><input type="text" name="job_card_number[]" class="form-control job-card-number" value="${jobCardNumber}" readonly></td>
            <td>
                <input type="hidden" name="jci_item_id[]" value="${jciItemId}">
                <select name="po_product_id[]" class="form-control po-product-select" required style="width:100%;">
                    <option value="">Select Product</option>
                    ${poProducts.map(p => `<option value="${p.id}" data-product-name="${p.product_name}" data-item-code="${p.item_code}" data-quantity="${p.quantity}" ${poProductId == p.id ? 'selected' : ''}>${p.product_name} (${p.item_code})</option>`).join('')}
                </select>
            </td>
            <td><input type="text" name="product_name[]" class="form-control product-name" value="${productName}" readonly></td>
            <td><input type="text" name="item_code[]" class="form-control item-code" value="${itemCode}" readonly></td>
            <td><input type="number" name="original_po_quantity[]" class="form-control original-po-qty" step="0.01" min="0" value="${originalPoQuantity}" readonly></td>
            <td><input type="number" name="assign_quantity[]" class="form-control assign-qty" step="0.01" min="0" value="${assignedQuantity}" required></td>
            <td><input type="number" name="remaining_quantity[]" class="form-control remaining-qty" step="0.01" min="0" value="0" readonly></td>
            <td><input type="number" name="labour_cost[]" class="form-control labour-cost" step="0.01" min="0" value="${labourCost}" required></td>
            <td><input type="text" name="total[]" class="form-control item-total" readonly value="${total || 0}"></td>
            <td><input type="date" name="delivery_date[]" class="form-control delivery-date" value="${deliveryDate}" required></td>
            <td><input type="date" name="job_card_date[]" class="form-control" value="${jobCardDate}" required></td>
            <td><select name="job_card_type[]" class="form-control job-card-type" required>
                <option value="Contracture" ${jobCardType === 'Contracture' ? 'selected' : ''}>Contracture</option>
                <option value="In-House" ${jobCardType === 'In-House' ? 'selected' : ''}>In-House</option>
            </select></td>
            <td class="contracture-name-cell"><input type="text" name="contracture_name[]" class="form-control contracture-name" value="${contractureName}" required></td>
            <td><button type="button" class="btn btn-danger btn-sm removeRowBtn" title="Delete Row"><i class="fas fa-trash"></i></button></td>
        `;

        tbody.appendChild(tr);

        $(tr.querySelector('.po-product-select')).select2({
            placeholder: "Select Product",
            allowClear: true,
            theme: "bootstrap4",
            dropdownAutoWidth: true,
            width: 'style'
        });

        tr.querySelector('.labour-cost').addEventListener('input', () => calculateRowTotal(tr));
        tr.querySelector('.assign-qty').addEventListener('input', function() {
            updateRemainingQuantities();
            calculateRowTotal(tr);
        });

        updateRemainingQuantities();
        calculateGrandTotal();

        tr.querySelector('.removeRowBtn').addEventListener('click', () => {
            tr.remove();
            calculateGrandTotal();
            updateSerialNumbers();
            updateRemainingQuantities();
        });

        tr.querySelector('.job-card-type').addEventListener('change', () => toggleContractureName(tr));

        $(tr.querySelector('.po-product-select')).on('change', function() {
            const selectedOption = $(this).find(':selected');
            const product = poProducts.find(p => p.id == selectedOption.val());

            if (product) {
                tr.querySelector('.product-name').value = product.product_name;
                tr.querySelector('.item-code').value = product.item_code;
                tr.querySelector('.original-po-qty').value = product.quantity;
                if (assignedQuantity === '' || assignedQuantity === null) {
                     tr.querySelector('.assign-qty').value = product.quantity;
                }
                // Set delivery date from PO
                if (window.poDeliveryDate) {
                    tr.querySelector('.delivery-date').value = window.poDeliveryDate;
                }
            } else {
                tr.querySelector('.product-name').value = '';
                tr.querySelector('.item-code').value = '';
                tr.querySelector('.original-po-qty').value = '';
                tr.querySelector('.assign-qty').value = '';
            }
            updateRemainingQuantities();
            calculateRowTotal(tr);
        });

        if (Object.keys(itemData).length > 0) {
            setTimeout(() => {
                $(tr.querySelector('.po-product-select')).val(poProductId).trigger('change');
                toggleContractureName(tr);
                updateRemainingQuantities();
                calculateRowTotal(tr);
            }, 100);
        } else {
            toggleContractureName(tr);
        }

        updateSerialNumbers();
    }

    function updateRemainingQuantities() {
        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const originalQtyInput = row.querySelector('input[name="original_po_quantity[]"]');
            const assignQtyInput = row.querySelector('input[name="assign_quantity[]"]');
            const remainingQtyInput = row.querySelector('input[name="remaining_quantity[]"]');

            const originalQty = parseFloat(originalQtyInput.value) || 0;
            const assignQty = parseFloat(assignQtyInput.value) || 0;

            const remainingQty = originalQty - assignQty;
            remainingQtyInput.value = remainingQty >= 0 ? remainingQty.toFixed(2) : '0.00';

            assignQtyInput.max = originalQty;

            if (assignQty > originalQty) {
                assignQtyInput.value = originalQty;
                alert('Assigned Quantity has been adjusted to not exceed the Original Quantity.');
            }

            calculateRowTotal(row);
        });
    }

$('#sell_order_id').on('change', function(e) {
    var sellOrderId = $(this).val();
    var selectedOption = $(this).find(':selected');
    poProducts = [];
    $('#itemsTable tbody').empty();
    rowCount = 0;
    calculateGrandTotal();

    if (sellOrderId) {
        // Set sell order number and PO number from dropdown data
        var sellOrderText = selectedOption.text();
        var sellOrderNumber = sellOrderText.split(' (')[0];
        var poNumber = selectedOption.attr('data-po-number') || '';
        
        $('#sell_order_number').val(sellOrderNumber);
        $('#po_number_display').val(poNumber);
        $('#po_number').val(poNumber);
        
        
        // Fetch PO products directly using sell order ID
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/jci/ajax_fetch_po_products.php',
            type: 'POST', 
            data: { sell_order_id: sellOrderId },
            dataType: 'json',
            success: function(productResponse) {
                if (productResponse.success && productResponse.products.length > 0) {
                    poProducts = productResponse.products;
                    // Don't add row automatically
                } else {
                    alert('No products found for the selected Sale Order.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Products fetch error:', status, error, xhr.responseText);
                alert('Error fetching products.');
            }
        });
    } else {
        $('#sell_order_number').val('');
        $('#po_number_display').val('');
        $('#po_number').val('');
        // Row will be added manually by clicking Add Row button
    }
});
    
    // Also handle select2 events
    $('#sell_order_id').on('select2:select', function(e) {
        $(this).trigger('change');
    });

    <?php if ($edit_mode): ?>
        var initialSellOrderId = '<?php echo htmlspecialchars($sell_order_selected); ?>';
        
        // Always load existing data directly in edit mode
        var itemData = <?php echo json_encode($item_data); ?>;
        var jciData = <?php echo json_encode($jci_data); ?>;
        
        if (itemData && itemData.length > 0) {
            // First get PO ID from JCI data to load products
            if (jciData && jciData.po_id) {
                $.ajax({
                    url: '<?php echo BASE_URL; ?>modules/jci/ajax_fetch_po_products.php',
                    type: 'POST',
                    data: { po_id: jciData.po_id },
                    dataType: 'json',
                    success: function(productResponse) {
                        if (productResponse.success && productResponse.products.length > 0) {
                            poProducts = productResponse.products;
                            
                            // Set dropdown values without triggering change events
                            if (initialSellOrderId) {
                                $('#sell_order_id').val(initialSellOrderId).trigger('change.select2');
                            }
                            
                            // Set BOM dropdown value with delay for select2
                            if (jciData.bom_id) {
                                setTimeout(function() {
                                    $('#bom_id').val(jciData.bom_id);
                                    $('#bom_id').select2('trigger', 'select', {
                                        data: { id: jciData.bom_id }
                                    });
                                }, 200);
                            }
                            
                            // Set display fields
                            if (jciData.sell_order_number) {
                                $('#sell_order_number').val(jciData.sell_order_number);
                            }
                            
                            // Get PO number from PO main table
                            $.ajax({
                                url: '<?php echo BASE_URL; ?>modules/jci/ajax_get_po_details.php',
                                type: 'POST', 
                                data: { po_id: jciData.po_id },
                                dataType: 'json',
                                success: function(poResponse) {
                                    if (poResponse.success && poResponse.po_number) {
                                        $('#po_number_display').val(poResponse.po_number);
                                    }
                                }
                            });
                            
                            // Now add rows with existing data
                            itemData.forEach(function(item) {
                                addRow(item);
                            });
                            
                            calculateGrandTotal();
                            updateRemainingQuantities();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading products for edit mode:', status, error);
                        addRow();
                    }
                });
            } else {
                addRow();
            }
        } else {
            addRow();
        }
        
        // Remove the else block since we're handling everything above
        if (false) {
            // This block is now handled above
        }
    <?php else: ?>
        // In add mode, user will manually add rows using Add Row button
    <?php endif; ?>
    

    
    // Handle sell order change for both add and edit modes
    $('#sell_order_id').off('select2:select').on('select2:select', function(e) {
        var sellOrderId = $(this).val();
        
        if (sellOrderId) {
            // Always trigger change event
            $('#sell_order_id').trigger('change');
        }
    });

    document.getElementById('addRowBtn').addEventListener('click', function() {
        if ($('#sell_order_id').val()) {
            addRow();
        } else {
            alert('Please select a Sale Order Number first to add contracture details.');
        }
    });

    document.getElementById('jciForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if ($('#sell_order_id').val() && document.querySelectorAll('#itemsTable tbody tr').length === 0) {
            alert('Please add at least one contracture detail.');
            return;
        }

        let isValid = true;
        document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
            const jobCardTypeSelect = row.querySelector('.job-card-type');
            const contractureNameInput = row.querySelector('.contracture-name');
            if (jobCardTypeSelect.value === 'Contracture' && contractureNameInput.value.trim() === '') {
                isValid = false;
                alert('Contracture Name is required for Job Card Type "Contracture".');
                contractureNameInput.focus();
                return;
            }
        });

        if (!isValid) {
            return;
        }

        const formData = new FormData(this);

        fetch('<?php echo BASE_URL; ?>modules/jci/ajax_save_jci.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const toastContainer = document.body;
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white ${data.success ? 'bg-success' : 'bg-danger'} border-0`;
            toast.role = 'alert';
            toast.ariaLive = 'assertive';
            toast.ariaAtomic = 'true';
            toast.style.position = 'fixed';
            toast.style.top = '20px';
            toast.style.right = '20px';
            toast.style.zIndex = '1055';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${data.message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            toastContainer.appendChild(toast);
            var bsToast = new bootstrap.Toast(toast);
            bsToast.show();

            if (data.success && !<?php echo $edit_mode ? 'true' : 'false'; ?>) {
                document.getElementById('jciForm').reset();
                $('#itemsTable tbody').empty();
                rowCount = 0;
                addRow();
                calculateGrandTotal();
                updateRemainingQuantities();
            }
        })
        .catch(error => {
            console.error('Error saving JCI:', error);
            alert('An error occurred while saving the Job Card. Please try again.');
        });
    });
});
</script>
<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>