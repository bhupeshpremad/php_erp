<?php
session_start();
include_once __DIR__ . '/../../config/config.php';

// Ensure ROOT_DIR_PATH is defined if not already from config.php
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', str_replace('\\', '/', dirname(__DIR__, 2)) . '/');
}

include_once ROOT_DIR_PATH . 'include/inc/header.php';

$user_type = $_SESSION['user_type'] ?? 'guest';
$is_superadmin = ($user_type === 'superadmin');

// The sidebar is now included conditionally in header.php
// include_once ROOT_DIR_PATH . 'accountsadmin/sidebar.php'; // THIS LINE IS NOW REMOVED

// Check if user is logged in as accountsadmin or superadmin
if (!isset($_SESSION['user_type']) || ($_SESSION['user_type'] !== 'accountsadmin' && $_SESSION['user_type'] !== 'superadmin')) {
    // Redirect to login page if not logged in as accountsadmin or superadmin
    header("Location: " . BASE_URL . "login.php");
    exit();
}

global $conn;

// Check if this is edit mode
$edit_mode = false;
$purchase_id = $_GET['id'] ?? null;
$purchase_data = [];
$purchase_items = [];

if ($purchase_id) {
    $edit_mode = true;
    try {
        // Check if created_by column exists
        $has_created_by = false;
        try {
            $stmt_check = $conn->query("SHOW COLUMNS FROM purchase_main LIKE 'created_by'");
            $has_created_by = $stmt_check->rowCount() > 0;
        } catch (Exception $e) {
            $has_created_by = false;
        }
        
        // Get current user ID for filtering
        $current_user_id = $_SESSION['user_id'] ?? $_SESSION['admin_id'] ?? null;
        
        // Fetch purchase main data with user filtering (superadmin can edit all)
        if (!$has_created_by || $_SESSION['user_type'] === 'superadmin') {
            // Superadmin can edit all purchases, or if created_by column doesn't exist
            $stmt = $conn->prepare("SELECT * FROM purchase_main WHERE id = ?");
            $stmt->execute([$purchase_id]);
        } else {
            // Regular users can only edit their own purchases
            $stmt = $conn->prepare("SELECT * FROM purchase_main WHERE id = ? AND created_by = ?");
            $stmt->execute([$purchase_id, $current_user_id]);
        }
        
        $purchase_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$purchase_data) {
            echo "<div class='alert alert-danger'>Purchase record not found or you don't have permission to edit this record.</div>";
            exit();
        }
        
        if ($purchase_data) {
            // Fetch purchase items
            $stmt_items = $conn->prepare("SELECT * FROM purchase_items WHERE purchase_main_id = ?");
            $stmt_items->execute([$purchase_id]);
            $purchase_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        echo "Error loading purchase data: " . $e->getMessage();
    }
}

try {
    // Fetch JCIs with complete sell order numbers
    if ($edit_mode && !empty($purchase_data['jci_number'])) {
        $stmt = $conn->prepare("SELECT j.jci_number, j.po_id, j.bom_id, 
                                      COALESCE(so.sell_order_number, TRIM(p.sell_order_number), 'N/A') as sell_order_number
                               FROM jci_main j 
                               LEFT JOIN po_main p ON j.po_id = p.id 
                               LEFT JOIN sell_order so ON p.id = so.po_id 
                               WHERE j.purchase_created = 0 OR j.jci_number = ? 
                               ORDER BY j.jci_number DESC");
        $stmt->execute([$purchase_data['jci_number']]);
    } else {
        $stmt = $conn->query("SELECT j.jci_number, j.po_id, j.bom_id, 
                                    COALESCE(so.sell_order_number, TRIM(p.sell_order_number), 'N/A') as sell_order_number
                             FROM jci_main j 
                             LEFT JOIN po_main p ON j.po_id = p.id 
                             LEFT JOIN sell_order so ON p.id = so.po_id 
                             WHERE j.purchase_created = 0 
                             ORDER BY j.jci_number DESC");
    }
    $jci_numbers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Database query error: " . $e->getMessage();
}

?>

<div class="container-fluid mb-5">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <?php echo $edit_mode ? 'Edit Purchase Details' : 'Add Purchase Details'; ?>
                <?php if ($edit_mode && $is_superadmin): ?>
                    <small class="text-success ml-2">(Super Admin - Full Edit Access)</small>
                <?php endif; ?>
            </h6>
        </div>
        <div class="card-body">
            <form id="purchaseDetailsForm">
                <input type="hidden" id="purchase_id" name="purchase_id" value="<?php echo htmlspecialchars($purchase_id ?? ''); ?>">
                <input type="hidden" id="po_number" name="po_number" value="<?php echo htmlspecialchars($purchase_data['po_number'] ?? ''); ?>">
                <input type="hidden" id="sell_order_number" name="sell_order_number" value="<?php echo htmlspecialchars($purchase_data['sell_order_number'] ?? ''); ?>">
                <input type="hidden" id="jci_number" name="jci_number" value="<?php echo htmlspecialchars($purchase_data['jci_number'] ?? ''); ?>">

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label for="jci_number_search">JCI Card Number:</label>
                        <select id="jci_number_search" class="form-control" required>
                            <option value="">Select Job Card Number</option>
                            <?php foreach ($jci_numbers as $jci): ?>
                                <option value="<?php echo htmlspecialchars($jci['jci_number']); ?>"
                                    data-po-id="<?php echo htmlspecialchars($jci['po_id']); ?>"
                                    data-son="<?php echo htmlspecialchars($jci['sell_order_number']); ?>"
                                    <?php echo (isset($purchase_data['jci_number']) && $purchase_data['jci_number'] == $jci['jci_number']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($jci['jci_number']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small id="jobCardCount" class="form-text text-muted mt-1"></small>
                        <ul id="jobCardList" class="list-group mt-1" style="max-height: 150px; overflow-y: auto;"></ul>
                    </div>
<div class="form-group col-md-4">
    <label for="sell_order_number_display">Sell Order Number (SON):</label>
    <input type="text" id="sell_order_number_display" class="form-control" readonly style="text-align: left; width: 100%; overflow: visible;"
           value="<?php echo htmlspecialchars(trim($purchase_data['sell_order_number'] ?? '')); ?>">
</div>
                    <div class="form-group col-md-4">
                        <label for="po_number_display">PO Number:</label>
                        <input type="text" id="po_number_display" class="form-control" readonly
                               value="<?php echo htmlspecialchars($purchase_data['po_number'] ?? ''); ?>">
                    </div>
                    <div class="form-group col-md-4">
                        <label for="bom_number_display">BOM Number(s):</label>
                        <input type="text" id="bom_number_display" class="form-control" readonly>
                    </div>
                </div>
            </form>
            <div class="form-group mt-3">
                <button type="submit" form="purchaseDetailsForm" class="btn btn-primary" id="saveAllSelectedBtn">Save All Selected</button>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">BOM Details</h6>
                </div>
                <div class="card-body p-0" id="bomTableContainer">
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    #toast-container > .toast-success {
        background-color: #51a351 !important;
        color: white !important;
    }
    #toast-container > .toast-error {
        background-color: #bd362f !important;
        color: white !important;
    }
    .table th, .table td { vertical-align: middle; padding: 0.5rem; }
    .table input.form-control, .table select.form-control { border: 1px solid #ced4da; padding: 0.375rem 0.75rem; height: auto; }
</style>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Purchase Editable JS -->
<script src="js/purchase-editable.js"></script>

<script>
$(document).ready(function() {
$('#jci_number_search').on('change', function() {
    var selectedJciNumber = $(this).val();
    var selectedOption = $(this).find('option:selected');
    var poId = selectedOption.data('po-id');
    var sellOrderNumber = selectedOption.data('son');

    var cleanSellOrderNumber = sellOrderNumber ? sellOrderNumber.toString().trim() : '';
    $('#sell_order_number_display').val(cleanSellOrderNumber);
    $('#sell_order_number').val(cleanSellOrderNumber);
    $('#purchase_id').val('');
    $('#po_number').val('');
    $('#sell_order_number').val(sellOrderNumber);
    $('#jci_number').val(selectedJciNumber);
    $('#bomTableContainer').empty();

    if (!poId) {
        $('#po_number_display').val('');
        $('#bom_number_display').val('');
        return;
    }

    $.ajax({
        url: 'ajax_fetch_po_number.php',
        method: 'POST',
        data: { po_id: poId },
        dataType: 'json',
        success: function(poData) {
            if (poData && poData.po_number) {
                $('#po_number_display').val(poData.po_number);
                $('#po_number').val(poData.po_number);
            } else {
                $('#po_number_display').val('');
                $('#po_number').val('');
            }
        },
        error: function() {
            $('#po_number_display').val('');
        }
    });

    $.ajax({
        url: 'ajax_fetch_job_cards.php',
        method: 'POST',
        data: { jci_number: selectedJciNumber },
        dataType: 'json',
        success: function(jobCardsData) {
            if (jobCardsData && jobCardsData.job_cards && jobCardsData.job_cards.length > 0) {
                $('#bomTableContainer').empty();
                var jobCards = jobCardsData.job_cards;
                var jobCardCount = jobCards.length;

                // Display job card count and list
                $('#jobCardCount').text('Job Cards Count: ' + jobCardCount);
                $('#jobCardList').empty();
                jobCards.forEach(function(card) {
                    $('#jobCardList').append('<li class="list-group-item p-1">' + card + '</li>');
                });

                // Update BOM Number display for the selected JCI
                $.ajax({
                    url: 'ajax_get_bom_number_by_jci.php',
                    method: 'POST',
                    data: { jci_number: selectedJciNumber },
                    dataType: 'json',
                    success: function(bomData) {
                        if (bomData && bomData.bom_number) {
                            $('#bom_number_display').val(bomData.bom_number);
                        } else {
                            $('#bom_number_display').val('');
                        }
                    },
                    error: function() {
                        $('#bom_number_display').val('');
                    }
                });

                // Fetch BOM items once for the JCI number
                $.ajax({
                    url: 'ajax_fetch_bom_items_by_job_card.php',
                    method: 'POST',
                    data: { jci_number: selectedJciNumber },
                    dataType: 'json',
                    success: function(bomItemsData) {
                        console.log('BOM Items Data:', bomItemsData);
                        if (bomItemsData && bomItemsData.length > 0) {
                            toastr.info('BOM items found: ' + bomItemsData.length);
                            // Clear previous BOM tables
                            $('#bomTableContainer').empty();

                            // Check for existing purchase data first
                            $.ajax({
                                url: 'ajax_fetch_saved_purchase.php',
                                method: 'POST',
                                data: { jci_number: selectedJciNumber },
                                dataType: 'json',
                                success: function(purchaseData) {
                                    console.log('Existing purchase data:', purchaseData);
                                    var existingItems = purchaseData.has_purchase ? purchaseData.purchase_items : [];
                                    renderBomTable(jobCards, bomItemsData, existingItems);
                                },
                                error: function() {
                                    console.log('Error fetching existing purchase data');
                                    // Fallback: render tables without existing data
                                    renderBomTable(jobCards, bomItemsData, []);
                                }
                            });
                        } else {
                            console.log('No BOM items found for JCI:', selectedJciNumber);
                            toastr.warning('No BOM items found for the selected JCI.');
                        }
                    },
                    error: function() {
                        console.log('Error fetching BOM items for JCI:', selectedJciNumber);
                        toastr.error('Error fetching BOM items for the selected JCI.');
                    }
                });
            } else if (jobCardsData && jobCardsData.error) {
                toastr.error('Error fetching job cards: ' + jobCardsData.error);
                $('#bomTableContainer').empty();
                $('#jobCardCount').text('');
                $('#jobCardList').empty();
            } else {
                $('#bomTableContainer').empty();
                $('#jobCardCount').text('');
                $('#jobCardList').empty();
            }
        },
        error: function() {
            toastr.error('AJAX error fetching job cards');
            $('#bomTableContainer').empty();
            $('#jobCardCount').text('');
            $('#jobCardList').empty();
        }
    });
});

function getTableColumnIndex(categoryName, keyName) {
    var baseOffset = 1; // Sr. No. column only, removed checkbox column
    var headerKeys = [];

    var columnOrder = {
        'BOM Glow': ['supplier_name', 'glowtype', 'quantity', 'price', 'total'],
        'BOM Hardware': ['supplier_name', 'itemname', 'quantity', 'price', 'totalprice'],
        'BOM Plynydf': ['supplier_name', 'quantity', 'width', 'length', 'price', 'total'],
        'BOM Wood': ['supplier_name', 'woodtype', 'length_ft', 'width_ft', 'thickness_inch', 'quantity', 'price', 'cft', 'total']
    };

    if (categoryName === 'Unknown') {
        // Skip processing for unknown category
        return -1;
    }

    if (columnOrder[categoryName]) {
        headerKeys = columnOrder[categoryName];
    } else {
        console.warn("Unknown category for getTableColumnIndex:", categoryName);
        return -1;
    }

    var dataColIndex = headerKeys.indexOf(keyName);
    if (dataColIndex !== -1) {
        return baseOffset + dataColIndex;
    }

    var quantityIndex = headerKeys.indexOf('quantity');
    if (quantityIndex === -1 && categoryName === 'BOM Plynydf') { // For Plynydf, 'total' might act as quantity
        quantityIndex = headerKeys.indexOf('total');
    }

    // Adjust for job card and assigned quantity columns, which appear after the main data columns
    if (quantityIndex !== -1) {
        if (keyName === 'job_card_select') {
            return baseOffset + quantityIndex + 1; // +1 because it's after the quantity/total column
        }
        if (keyName === 'assigned_quantity_input') {
            return baseOffset + quantityIndex + 2; // +2 because it's after quantity/total and job_card_select
        }
    }

    console.warn("Could not find column index for:", keyName, "in category:", categoryName);
    return -1;
}

// Updated createBomTable function to accept jobCardCount
function createBomTable(categoryName, data, savedItems, jobCardCount) {
    if (!data || data.length === 0) {
        return;
    }
    var table = $('<table class="table table-bordered table-sm mb-4"></table>');
    var thead = $('<thead class="thead-light"></thead>');
    var tbody = $('<tbody></tbody>');

    var totalColumns = Object.keys(data[0]).length + 2; // Account for checkbox and Sr. No.
    totalColumns -= (Object.keys(data[0]).includes('bom_main_id') ? 1 : 0); // Exclude bom_main_id from column count
    totalColumns -= (Object.keys(data[0]).includes('id') ? 1 : 0); // Exclude id from column count

    var headerMappings = {
        'BOM Glow': {
            'supplier_name': 'Supplier Name', 'glowtype': 'Glow Type', 'quantity': 'Quantity', 'price': 'Price', 'total': 'Total'
        },
        'BOM Hardware': {
            'supplier_name': 'Supplier Name', 'itemname': 'Item Name', 'quantity': 'Quantity', 'price': 'Price', 'totalprice': 'Total Price'
        },
        'BOM Plynydf': {
            'supplier_name': 'Supplier Name', 'quantity': 'Quantity', 'width': 'Width', 'length': 'Length', 'price': 'Price', 'total': 'Total'
        },
        'BOM Wood': {
            'supplier_name': 'Supplier Name', 'woodtype': 'Wood Type', 'length_ft': 'Length Ft', 'width_ft': 'Width Ft', 'thickness_inch': 'Thickness Inch', 'quantity': 'Quantity', 'price': 'Price', 'cft': 'CFT', 'total': 'Total'
        }
    };

    var currentCategoryHeaders = headerMappings[categoryName] || {};
    var headerKeys = Object.keys(currentCategoryHeaders);

    var firstDataRowKeys = Object.keys(data[0]);
    var hasQuantityOrTotal = firstDataRowKeys.includes('quantity') || (categoryName.includes('BOM Plynydf') && firstDataRowKeys.includes('total'));
    if (hasQuantityOrTotal) {
        totalColumns += 2; // Add columns for "Select Job Card" and "Assigned Quantity"
    }

    var headerRow = $('<tr><th colspan="' + totalColumns + '">' + categoryName + '</th></tr>');
    thead.append(headerRow);

    var colHeaderRow = $('<tr></tr>');
    colHeaderRow.append('<th><input type="checkbox" class="selectAllRows"></th>');
    colHeaderRow.append('<th>Sr. No.</th>');

    headerKeys.forEach(function(key) {
        if (key === 'bom_main_id' || key === 'id') {
            return;
        }
        colHeaderRow.append('<th>' + currentCategoryHeaders[key] + '</th>');
    });

    if (hasQuantityOrTotal) {
        colHeaderRow.append('<th>Select Job Card</th>');
        colHeaderRow.append('<th>Assigned Quantity</th>');
    }
    thead.append(colHeaderRow);

    // Use jobCardCount passed as argument
    // Remove the loop repeating rows by jobCardCount to avoid duplication
    // for (var repeat = 0; repeat < (jobCardCount || 1); repeat++) {
        data.forEach(function(row_data) {
            var tr = $('<tr></tr>');
            var checkboxTd = $('<td><input type="checkbox" class="rowCheckbox"></td>');
            tr.append(checkboxTd);

            var serialTd = $('<td></td>').text('');
            tr.append(serialTd);

            headerKeys.forEach(function(key) {
                if (key === 'bom_main_id' || key === 'id') {
                    return;
                }
                var td = $('<td></td>');
                var inputVal = row_data[key] !== undefined ? row_data[key] : '';

                if (key === 'supplier_name') {
                    var input = $('<input type="text" class="form-control form-control-sm supplierNameInput" value="' + (inputVal || '').toString().replace(/"/g, '&quot;') + '" ');
                    td.append(input);
                } else {
                    var input = $('<input type="text" class="form-control form-control-sm" readonly>').val(inputVal);
                    td.append(input);
                }
                tr.append(td);
            });

            if (hasQuantityOrTotal) {
                var jobCardTd = $('<td></td>');
                var jobCardSelect = $('<select class="form-control form-control-sm jobCardSelect"><option value="">Select Job Card</option></select>');
                jobCardTd.append(jobCardSelect);
                tr.append(jobCardTd);

                var assignedQtyTd = $('<td></td>');
                var assignedQtyInput = $('<input type="number" min="0" class="form-control form-control-sm assignedQtyInput" value="0">');
                assignedQtyTd.append(assignedQtyInput);
                tr.append(assignedQtyTd);
            }

            var matchedSavedItem = null;
            if (savedItems && savedItems.length > 0) {
                savedItems.some(function(savedItem) {
                    var match = false;
                    console.log("Matching savedItem:", savedItem, "with row_data:", row_data, "category:", categoryName);
                    if (categoryName.includes('Glow') && savedItem.product_type === 'Glow Type' && savedItem.product_name === row_data.glowtype && savedItem.supplier_name === row_data.supplier_name) {
                        match = true;
                    } else if (categoryName.includes('Hardware') && savedItem.product_type === 'Item Name' && savedItem.product_name === row_data.itemname && savedItem.supplier_name === row_data.supplier_name) {
                        match = true;
                    } else if (categoryName.includes('Plynydf') && savedItem.product_type === 'Plynydf' && savedItem.supplier_name === row_data.supplier_name && parseFloat(savedItem.assigned_quantity) === parseFloat(row_data.quantity) && parseFloat(savedItem.price) === parseFloat(row_data.price)) {
                        match = true;
                    } else if (categoryName.includes('Wood') && savedItem.product_type === 'Wood Type' && savedItem.product_name === row_data.woodtype && savedItem.supplier_name === row_data.supplier_name) {
                        match = true;
                    }

                    if (match) {
                        matchedSavedItem = savedItem;
                        return true;
                    }
                    return false;
                });
            }

            if (matchedSavedItem) {
                checkboxTd.find('.rowCheckbox').prop('checked', true);
                // Do not disable checkbox to allow user to uncheck if needed
                // checkboxTd.find('.rowCheckbox').prop('disabled', true);
                tr.find('.jobCardSelect').val(matchedSavedItem.job_card_number).prop('disabled', true);
                tr.find('.assignedQtyInput').val(matchedSavedItem.assigned_quantity).prop('readonly', true);

                var totalColIndex = getTableColumnIndex(categoryName, categoryName === 'BOM Hardware' ? 'totalprice' : 'total');
                if (totalColIndex !== -1) {
                    tr.find('td').eq(totalColIndex).find('input').val(matchedSavedItem.total);
                }
            }
            tbody.append(tr);
        });
    // }

    function updateSerialNumbers() {
        tbody.find('tr').each(function(index) {
            $(this).find('td').eq(1).text(index + 1);
        });
    }
    updateSerialNumbers();

    table.append(thead);
    table.append(tbody);
    $('#bomTableContainer').append(table);

    table.find('.selectAllRows').on('change', function() {
        var checked = $(this).is(':checked');
        $(this).closest('table').find('tbody .rowCheckbox:not(:disabled)').prop('checked', checked);
    });

    tbody.on('input', '.assignedQtyInput', function() {
        var row_dom = $(this).closest('tr');
        var tableElement = row_dom.closest('table');
        var category = tableElement.find('thead tr').first().text().trim();

        var originalQuantity = 0;
        var price = 0;

        var quantityColIdx = getTableColumnIndex(category, 'quantity');
        var priceColIdx = getTableColumnIndex(category, 'price');

        if (quantityColIdx !== -1) {
            originalQuantity = parseFloat(row_dom.find('td').eq(quantityColIdx).find('input').val());
        }
        if (priceColIdx !== -1) {
            price = parseFloat(row_dom.find('td').eq(priceColIdx).find('input').val());
        }

        var assignedQty = parseFloat($(this).val());

        if (isNaN(assignedQty) || assignedQty < 0) {
            $(this).val(0);
            assignedQty = 0;
        }

        if (assignedQty > originalQuantity + 0.001) { // Allow small floating point tolerance
            toastr.warning('Assigned quantity (' + assignedQty + ') exceeds BOM quantity (' + originalQuantity + '). Auto-correcting.');
            $(this).val(originalQuantity);
            assignedQty = originalQuantity;
        }

        var totalInputColIndex;
        if (category === 'BOM Hardware') {
            totalInputColIndex = getTableColumnIndex(category, 'totalprice');
        } else {
            totalInputColIndex = getTableColumnIndex(category, 'total');
        }

        if (totalInputColIndex !== -1) {
            var totalInput = row_dom.find('td').eq(totalInputColIndex).find('input');
            if (!isNaN(price) && !isNaN(assignedQty)) {
                totalInput.val((price * assignedQty).toFixed(2));
            } else {
                totalInput.val('');
            }
        }

        var totalAssignedInTable = 0;
        tableElement.find('tbody .assignedQtyInput').each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) {
                totalAssignedInTable += val;
            }
        });

        var totalRow = tableElement.find('.totalAssignedRow');
        if (totalRow.length === 0) {
            totalRow = $('<tr class="totalAssignedRow table-info"><td colspan="' + (row_dom.find('td').length) + '" style="text-align:right; font-weight:bold;">Total Assigned Quantity: <span class="totalAssignedQty">0</span></td></tr>');
            tbody.append(totalRow);
        }
        totalRow.find('.totalAssignedQty').text(totalAssignedInTable);
    });
}

// Function to render BOM tables (moved out of success handler for clarity)
function renderBomTable(jobCards, bomItemsData, existingItems) {
    $('#bomTableContainer').empty();
    jobCards.forEach(function(jobCard) {
        var table = $('<table class="table table-bordered table-sm mb-4"></table>');
        var thead = $('<thead class="thead-light"></thead>');
        var tbody = $('<tbody></tbody>');

        var headerRow = $('<tr><th colspan="12">Job Card: ' + jobCard + '</th></tr>');
        thead.append(headerRow);

        var colHeaderRow = $('<tr></tr>');
        colHeaderRow.append('<th><input type="checkbox" id="selectAllRows"></th>');
        colHeaderRow.append('<th>Sr. No.</th>'); 
        colHeaderRow.append('<th >Supplier Name</th>');
        colHeaderRow.append('<th>Product Type</th>');
        colHeaderRow.append('<th>Product Name</th>');
        colHeaderRow.append('<th>BOM Quantity</th>');
        colHeaderRow.append('<th>BOM Price</th>');
        colHeaderRow.append('<th>Assign Quantity</th>');
        colHeaderRow.append('<th>Invoice No.</th>');
        colHeaderRow.append('<th>Invoice Image</th>');
        colHeaderRow.append('<th>Builty No.</th>');
        colHeaderRow.append('<th>Builty Image</th>');
        colHeaderRow.append('<th>Status</th>');
        colHeaderRow.append('<th>Action</th>');
        thead.append(colHeaderRow);

        bomItemsData.forEach(function(item) {
            var tr = $('<tr></tr>');

            // Find existing purchase item data for this BOM item
            var existingItem = null;
            if (existingItems && existingItems.length > 0) {
                existingItem = existingItems.find(function(pItem) {
                    var pItemProductName = (pItem.product_name !== undefined && pItem.product_name !== null) ? String(pItem.product_name).trim() : '';
                    var itemProductName = (item.product_name !== undefined && item.product_name !== null) ? String(item.product_name).trim() : '';
                    var pItemProductType = (pItem.product_type !== undefined && pItem.product_type !== null) ? String(pItem.product_type).trim() : '';
                    var itemProductType = (item.product_type !== undefined && item.product_type !== null) ? String(item.product_type).trim() : '';

                    return pItemProductType === itemProductType && 
                           (pItemProductName === itemProductName || (itemProductName === '' && pItemProductName === itemProductType)) &&
                           pItem.job_card_number === jobCard;
                });
            }

            var supplierName = existingItem ? (existingItem.supplier_name || '').toString().replace(/"/g, '&quot;') : '';
            var assignedQty = existingItem ? existingItem.assigned_quantity : '0';
            var isChecked = existingItem ? true : false;
            var invoiceNumber = existingItem ? (existingItem.invoice_number || '').toString().trim() : '';
            var builtyNumber = existingItem ? (existingItem.builty_number || '').toString().trim() : '';
            var invoiceImage = existingItem ? (existingItem.invoice_image || '').toString().trim() : '';
            var builtyImage = existingItem ? (existingItem.builty_image || '').toString().trim() : '';
            var isApproved = existingItem && invoiceNumber && invoiceImage ? true : false;
            var isSuperAdmin = <?php echo json_encode($is_superadmin); ?>;

            var inputReadonly = (isApproved && !isSuperAdmin) ? 'readonly' : '';
            var inputDisabled = (isApproved && !isSuperAdmin) ? 'disabled' : '';
            var fileInputVisibility = (invoiceImage && !isSuperAdmin) ? 'style="display:none;"' : '';
            var builtyFileInputVisibility = (builtyImage && !isSuperAdmin) ? 'style="display:none;"' : '';

            // Explicitly append all columns in correct order
            tr.append('<td><input type="checkbox" class="rowCheckbox" ' + (isChecked ? 'checked' : '') + ' ' + inputDisabled + '></td>'); // Checkbox
            tr.append('<td></td>'); // Sr. No. (will be filled by updateSerialNumbers)
            tr.append('<td><input type="text" class="form-control form-control-sm supplierNameInput" value="' + (supplierName || '').toString().replace(/"/g, '&quot;') + '" ' + inputReadonly + '></td>'); // Supplier Name
            tr.append('<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + item.product_type + '" readonly></td>'); // Product Type
            tr.append('<td><input type="text" class="form-control form-control-sm productNameInput" value="' + item.product_name + '" readonly></td>'); // Product Name
            tr.append('<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + item.quantity + '" readonly></td>'); // BOM Quantity
            tr.append('<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + item.price + '" readonly></td>'); // BOM Price
            tr.append('<td><input type="number" min="0" max="' + item.quantity + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="' + assignedQty + '" ' + inputReadonly + '></td>'); // Assign Quantity
            tr.append('<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value="' + invoiceNumber + '" ' + inputReadonly + '></td>'); // Invoice No.
            
            var invoiceImageTdContent = '<input type="file" class="form-control-file form-control-sm invoiceImageInput" ' + inputDisabled + ' ' + fileInputVisibility + '><input type="hidden" class="existingInvoiceImage" value="' + invoiceImage + '">';
            if (invoiceImage) {
                invoiceImageTdContent += '<img src="<?php echo BASE_URL; ?>modules/purchase/uploads/invoice/' + invoiceImage + '?t=' + new Date().getTime() + '" class="invoiceImageThumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;">';
            }
            tr.append('<td>' + invoiceImageTdContent + '</td>'); // Invoice Image
            
            tr.append('<td><input type="text" class="form-control form-control-sm builtyNumberInput" value="' + builtyNumber + '" ' + inputReadonly + '></td>'); // Builty No.

            var builtyImageTdContent = '<input type="file" class="form-control-file form-control-sm builtyImageInput" ' + inputDisabled + ' ' + builtyFileInputVisibility + '><input type="hidden" class="existingBuiltyImage" value="' + builtyImage + '">';
            if (builtyImage) {
                builtyImageTdContent += '<img src="<?php echo BASE_URL; ?>modules/purchase/uploads/Builty/' + builtyImage + '?t=' + new Date().getTime() + '" class="builtyImageThumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer;">';
            }
            tr.append('<td>' + builtyImageTdContent + '</td>'); // Builty Image
            
            if (isApproved) {
                tr.addClass('table-success');
                tr.append('<td><span class="badge badge-success">Invoice Uploaded</span></td>'); // Status
            } else {
                tr.append('<td><span class="badge badge-warning">Pending</span></td>'); // Status
            }
            tr.append('<td><button type="button" class="btn btn-primary btn-sm saveRowBtn" ' + inputDisabled + '>Save</button></td>'); // Action

            tbody.append(tr);
        });

        table.append(thead);
        table.append(tbody);
        $('#bomTableContainer').append(table);
    });

    function updateSerialNumbers() {
        $('#bomTableContainer table tbody').each(function() {
            $(this).find('tr').each(function(index) {
                $(this).find('td').eq(1).text(index + 1);
            });
        });
    }
    updateSerialNumbers();

    $('.selectAllRows').off('change').on('change', function() {
        var checked = $(this).is(':checked');
        $(this).closest('table').find('tbody .rowCheckbox:not(:disabled)').prop('checked', checked);
    });

    // Click handler for invoice image to re-upload
    $('#bomTableContainer').on('click', '.invoiceImageThumb', function() {
        var isSuperAdmin = <?php echo json_encode($is_superadmin); ?>;
        if (isSuperAdmin) {
            $(this).siblings('.invoiceImageInput').trigger('click');
        }
    });

    // Click handler for builty image to re-upload
    $('#bomTableContainer').on('click', '.builtyImageThumb', function() {
        var isSuperAdmin = <?php echo json_encode($is_superadmin); ?>;
        if (isSuperAdmin) {
            $(this).siblings('.builtyImageInput').trigger('click');
        }
    });
}

$('#purchaseDetailsForm').on('submit', function(e) {
    e.preventDefault();
    saveItems(null);
});

$('#bomTableContainer').on('click', '.saveRowBtn', function() {
    var row = $(this).closest('tr');
    var checkbox = row.find('.rowCheckbox');
    
    // Strict validation: This specific row must be checked
    if (!checkbox.is(':checked')) {
        toastr.warning('Please check this row before saving.');
        checkbox.focus();
        return;
    }
    
    // Temporarily uncheck all other rows to ensure only this row is processed
    var allCheckboxes = $('#bomTableContainer .rowCheckbox');
    var otherCheckboxes = allCheckboxes.not(checkbox);
    var otherStates = [];
    
    // Store other checkbox states
    otherCheckboxes.each(function(index) {
        otherStates[index] = $(this).is(':checked');
        $(this).prop('checked', false);
    });
    
    // Save only this row
    saveItems(row);
    
    // Restore other checkbox states after a short delay
    setTimeout(function() {
        otherCheckboxes.each(function(index) {
            $(this).prop('checked', otherStates[index]);
        });
    }, 100);
});

function saveItems(rowToSave) {
    var po_number = $('#po_number').val();
    var jci_number = $('#jci_number').val();
    var sell_order_number = $('#sell_order_number').val();
    var bom_number = $('#bom_number_display').val();
    var isSuperAdmin = <?php echo json_encode($is_superadmin); ?>;

    var items_to_save = [];
    var validationFailed = false;

    var tables = rowToSave ? rowToSave.closest('table') : $('#bomTableContainer table');

    tables.each(function(tableIndex) {
        var currentTable = $(this);
        var job_card_number_from_table = currentTable.find('thead th').first().text().replace('Job Card: ', '').trim();

        var rows = rowToSave ? rowToSave : currentTable.find('tbody tr');

        rows.each(function(rowIndex) {
            var row = $(this);
            
            // For single row save, ONLY process the exact clicked row
            if (rowToSave) {
                if (row[0] !== rowToSave[0]) {
                    return true; // Skip all other rows completely
                }
                // Double check: this specific row MUST be checked
                if (!row.find('.rowCheckbox').is(':checked')) {
                    console.log('Row save cancelled - checkbox not checked');
                    return false; // Stop processing if not checked
                }
            } else {
                // For bulk save, check if row is checked
                if (!row.find('.rowCheckbox').is(':checked')) {
                    return true; // continue to next row if not checked
                }
            }

            var supplier_name_input = row.find('.supplierNameInput');
            var supplier_name = supplier_name_input.length ? (supplier_name_input.val() || '').trim() : '';

            var product_type_input = row.find('.productTypeInput');
            var product_type = product_type_input.length ? (product_type_input.val() || '').trim() : '';

            var product_name_input = row.find('.productNameInput');
            var product_name = product_name_input.length ? (product_name_input.val() || '').trim() : '';

            if (!product_name || product_name === '') {
                product_name = product_type;
            }

            var assigned_quantity_input = row.find('.assignQuantityInput');
            var assigned_quantity = assigned_quantity_input.length ? (assigned_quantity_input.val() || '0') : '0';
            
            var price_input = row.find('.bomPriceInput');
            var price = price_input.length ? (price_input.val() || '0') : '0';
            
            var total = (parseFloat(assigned_quantity) * parseFloat(price)).toFixed(2);

            var invoice_number_input = row.find('.invoiceNumberInput');
            var invoice_number = invoice_number_input.length ? (invoice_number_input.val() || '').trim() : '';
            
            var builty_number_input = row.find('.builtyNumberInput');
            var builty_number = builty_number_input.length ? (builty_number_input.val() || '').trim() : '';

            var existing_invoice_image_input = row.find('.existingInvoiceImage');
            var existing_invoice_image = existing_invoice_image_input.length ? (existing_invoice_image_input.val() || '') : '';

            var existing_builty_image_input = row.find('.existingBuiltyImage');
            var existing_builty_image = existing_builty_image_input.length ? (existing_builty_image_input.val() || '') : '';

            // console.log(`Row ${rowIndex} - supplier_name: '${supplier_name}', product_type: '${product_type}', product_name: '${product_name}', assigned_quantity: '${assigned_quantity}', invoice_number: '${invoice_number}', builty_number: '${builty_number}'`);

            // Enhanced validation for individual row save
            if (rowToSave) {
                // For individual row save, both supplier name and assigned quantity are required
                if (supplier_name.trim() === '') {
                    toastr.error('Supplier name is required.');
                    row.find('.supplierNameInput').focus();
                    validationFailed = true;
                    return false;
                }
                if (isNaN(parseFloat(assigned_quantity)) || parseFloat(assigned_quantity) <= 0) {
                    toastr.error('Assigned quantity must be greater than zero.');
                    row.find('.assignQuantityInput').focus();
                    validationFailed = true;
                    return false;
                }
            } else {
                // For bulk save, original validation
                if ((isNaN(parseFloat(assigned_quantity)) || parseFloat(assigned_quantity) < 0) && supplier_name !== '') {
                    toastr.error('Assigned quantity must be zero or a positive number for all items with supplier name.');
                    row.find('.assignQuantityInput').focus();
                    validationFailed = true;
                    return false;
                }
                if (supplier_name.trim() === '' && parseFloat(assigned_quantity) > 0) {
                    toastr.error('Supplier name is required if assigned quantity is greater than zero.');
                    row.find('.supplierNameInput').focus();
                    validationFailed = true;
                    return false;
                }
            }
            if (!product_type || !product_name) {
                toastr.error('Product type and product name are required.');
                validationFailed = true;
                return false;
            }
            
            // Skip empty rows (no supplier name and no assigned quantity)
            if (supplier_name.trim() === '' && (isNaN(parseFloat(assigned_quantity)) || parseFloat(assigned_quantity) <= 0)) {
                console.log('Skipping empty row:', product_name);
                return true; // Skip this row
            }

            var bomQuantity = parseFloat(row.find('.bomQuantityInput').val()) || 0;
            var assignedQtyFloat = parseFloat(assigned_quantity) || 0;

            if (assignedQtyFloat > bomQuantity + 0.001) {
                toastr.error('Assigned quantity (' + assignedQtyFloat + ') cannot exceed BOM quantity (' + bomQuantity + ') for ' + product_name);
                row.find('.assignQuantityInput').focus();
                validationFailed = true;
                return false;
            }
            
            // Collect items data as an array of objects
            items_to_save.push({
                rowIndex: rowIndex, // Add rowIndex for file association
                supplier_name: supplier_name,
                product_type: product_type,
                product_name: product_name,
                job_card_number: job_card_number_from_table,
                assigned_quantity: assigned_quantity,
                price: price,
                total: total,
                invoice_number: invoice_number,
                builty_number: builty_number,
                existing_invoice_image: existing_invoice_image,
                existing_builty_image: existing_builty_image
            });
        });
        if (validationFailed) { return false; }
    });

    if (validationFailed) {
        return;
    }

    if (items_to_save.length === 0) {
        if (rowToSave) {
            toastr.warning('Please fill supplier name and assigned quantity for this row.');
        } else {
            toastr.warning('Please select at least one item and enter assigned quantity.');
        }
        return;
    }

    var formData = new FormData();
    formData.append('po_number', po_number);
    formData.append('jci_number', jci_number);
    formData.append('sell_order_number', sell_order_number);
    formData.append('bom_number', bom_number);
    formData.append('is_superadmin', isSuperAdmin);
    formData.append('items_json', JSON.stringify(items_to_save)); // Send items as a JSON string
    
    // Add debug info
    console.log('Saving items:', items_to_save);
    console.log('Form data prepared for:', jci_number);
    console.log('Row-specific save:', rowToSave ? 'Yes' : 'No');
    if (rowToSave) {
        console.log('Target row checkbox checked:', rowToSave.find('.rowCheckbox').is(':checked'));
    }

    // Append file inputs to FormData
    var rowsToProcess = rowToSave ? rowToSave : $('#bomTableContainer table tbody tr');
    rowsToProcess.each(function(rowIndex) {
        var row = $(this);
        if (row.find('.rowCheckbox').is(':checked') || rowToSave) {
            var invoiceImageFile = row.find('.invoiceImageInput')[0].files[0];
            var builtyImageFile = row.find('.builtyImageInput')[0].files[0];

            if (invoiceImageFile) {
                formData.append('invoice_image_' + rowIndex, invoiceImageFile);
            }
            if (builtyImageFile) {
                formData.append('builty_image_' + rowIndex, builtyImageFile);
            }
        }
    });

    $.ajax({
        url: 'ajax_save_purchase.php',
        method: 'POST',
        data: formData,
        processData: false, // Important: tell jQuery not to process the data
        contentType: false, // Important: tell jQuery not to set contentType
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                // Reload only if not saving a single row, or if the response indicates a full reload is needed
                if (!rowToSave) {
                    $('#jci_number_search').trigger('change'); // Reload all BOM tables
                } else {
                    // For single row save, update just the row's status/UI without full reload
                    // This would involve updating the badge, disabling inputs etc. which is more complex
                    // For simplicity, we can still trigger a full reload for single saves as well for now
                    $('#jci_number_search').trigger('change');
                }
            } else {
                toastr.error(response.error || 'Unknown error occurred');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('AJAX error: ' + error);
        }
    });
}

// Load existing data in edit mode
<?php if ($edit_mode && $purchase_data): ?>
    console.log('Edit mode detected, loading existing data');
    var existingData = <?php echo json_encode($purchase_data); ?>;
    var existingItems = <?php echo json_encode($purchase_items); ?>;
    
    // Set JCI dropdown value
    if (existingData.jci_number) {
        $('#jci_number_search').val(existingData.jci_number);
        $('#jci_number_search').trigger('change');
    }
<?php else: ?>
    if ($('#jci_number_search').val()) {
        $('#jci_number_search').trigger('change');
    }
<?php endif; ?>
});
</script>

<?php
include_once ROOT_DIR_PATH . 'include/inc/footer.php';
?>
