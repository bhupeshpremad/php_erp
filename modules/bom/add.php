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
}

global $conn;

$id = $_GET['id'] ?? null;
$edit_mode = false;
$bom_data = [];
$item_data = [];

if ($id) {
    $edit_mode = true;
    $stmt = $conn->prepare("SELECT * FROM bom_main WHERE id = ?");
    $stmt->execute([$id]);
    $bom_data = $stmt->fetch(PDO::FETCH_ASSOC);

    $item_data = [
        'wood' => [],
        'glow' => [],
        'plynydf' => [],
        'hardware' => [],
        'labour' => [],
        'factory' => [],
        'margin' => []
    ];

    $stmt_wood = $conn->prepare("SELECT * FROM bom_wood WHERE bom_main_id = ?");
    $stmt_wood->execute([$id]);
    $item_data['wood'] = $stmt_wood->fetchAll(PDO::FETCH_ASSOC);

    $stmt_glow = $conn->prepare("SELECT * FROM bom_glow WHERE bom_main_id = ?");
    $stmt_glow->execute([$id]);
    $item_data['glow'] = $stmt_glow->fetchAll(PDO::FETCH_ASSOC);

    $stmt_plynydf = $conn->prepare("SELECT * FROM bom_plynydf WHERE bom_main_id = ?");
    $stmt_plynydf->execute([$id]);
    $item_data['plynydf'] = $stmt_plynydf->fetchAll(PDO::FETCH_ASSOC);

    $stmt_hardware = $conn->prepare("SELECT * FROM bom_hardware WHERE bom_main_id = ?");
    $stmt_hardware->execute([$id]);
    $item_data['hardware'] = $stmt_hardware->fetchAll(PDO::FETCH_ASSOC);

    $stmt_labour = $conn->prepare("SELECT * FROM bom_labour WHERE bom_main_id = ?");
    $stmt_labour->execute([$id]);
    $item_data['labour'] = $stmt_labour->fetchAll(PDO::FETCH_ASSOC);

    $stmt_factory = $conn->prepare("SELECT * FROM bom_factory WHERE bom_main_id = ?");
    $stmt_factory->execute([$id]);
    $item_data['factory'] = $stmt_factory->fetchAll(PDO::FETCH_ASSOC);

    $stmt_margin = $conn->prepare("SELECT * FROM bom_margin WHERE bom_main_id = ?");
    $stmt_margin->execute([$id]);
    $item_data['margin'] = $stmt_margin->fetchAll(PDO::FETCH_ASSOC);
}

function generateBOMNumber($conn) {
    $year = date('Y');
    $prefix = "BOM-$year-";
    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(bom_number, '-', -1) AS UNSIGNED)) AS last_seq FROM bom_main WHERE bom_number LIKE ?");
    $stmt->execute(["BOM-$year-%"]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $last_seq = (int)$result['last_seq'];
    $next_seq = $last_seq + 1;
    $seqFormatted = str_pad($next_seq, 4, '0', STR_PAD_LEFT);
    return $prefix . $seqFormatted;
}

$auto_bom_number = $edit_mode ? $bom_data['bom_number'] : generateBOMNumber($conn);
$costing_sheet_number = $edit_mode ? ($bom_data['costing_sheet_number'] ?? '') : '';
$client_name = $edit_mode ? $bom_data['client_name'] : '';
// FIX: Format created_at to YYYY-MM-DD for input type="date"
$created_date = $edit_mode ? substr($bom_data['created_at'], 0, 10) : date('Y-m-d');
$prepared_by = $edit_mode ? $bom_data['prepared_by'] : '';
?>

<div class="container-fluid mb-5">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $edit_mode ? 'Edit' : 'Add'; ?> Bill Of Material</h6>
        </div>
        <div class="card-body">
            <form id="bomForm" autocomplete="off">
                <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($id ?? ''); ?>">
                <div class="row mb-3">
                    <div class="col-lg-4">
                        <label for="bom_number" class="form-label">Bill Of Material Number</label>
                        <input type="text" class="form-control" id="bom_number" name="bom_number" value="<?php echo htmlspecialchars($auto_bom_number); ?>" readonly>
                    </div>
                    <div class="col-lg-4">
                        <label for="costing_sheet_number" class="form-label">Costing Sheet Number</label>
                        <input type="text" class="form-control" id="costing_sheet_number" name="costing_sheet_number" value="<?php echo htmlspecialchars($costing_sheet_number); ?>" required>
                    </div>
                    <div class="col-lg-4">
                        <label for="client_name" class="form-label">Client Name</label>
                        <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-lg-4">
                        <label for="created_date" class="form-label">Created Date</label>
                        <input type="date" class="form-control" id="created_date" name="created_date" value="<?php echo htmlspecialchars($created_date); ?>" required>
                    </div>
                    <div class="col-lg-4">
                        <label for="prepared_by" class="form-label">Prepared By</label>
                        <input type="text" class="form-control" id="prepared_by" name="prepared_by" value="<?php echo htmlspecialchars($prepared_by); ?>" required>
                    </div>
                    <div class="col-lg-4">
                        <label for="grand_total_amount" class="form-label">Grand Total Amount</label>
                        <input type="text" class="form-control" id="grand_total_amount" name="grand_total_amount" readonly>
                    </div>
                </div>

                <ul class="nav nav-tabs mb-3" id="purchaseTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="wood-tab" data-toggle="tab" href="#wood" role="tab" aria-controls="wood" aria-selected="true">Wood</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="glow-tab" data-toggle="tab" href="#glow" role="tab" aria-controls="glow" aria-selected="false">Glue</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="plynydf-tab" data-toggle="tab" href="#plynydf" role="tab" aria-controls="plynydf" aria-selected="false">PLY/NYDF</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="hardware-tab" data-toggle="tab" href="#hardware" role="tab" aria-controls="hardware" aria-selected="false">Hardware</a>
                    </li>
                        <li class="nav-item">
                        <a class="nav-link" id="labour-tab" data-toggle="tab" href="#labour" role="tab" aria-controls="labour" aria-selected="false">Labour Cost</a>
                    </li>
                        <li class="nav-item">
                        <a class="nav-link" id="factory-tab" data-toggle="tab" href="#factory" role="tab" aria-controls="factory" aria-selected="false">Factory Cost</a>
                    </li>
                        <li class="nav-item">
                        <a class="nav-link" id="margin-tab" data-toggle="tab" href="#margin" role="tab" aria-controls="margin" aria-selected="false">Factory Margin</a>
                    </li>
                </ul>
                <div class="tab-content" id="purchaseTabsContent">
                    <div class="tab-pane fade show active" id="wood" role="tabpanel" aria-labelledby="wood-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="woodTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Wood Type</th>
                                        <th>Length (ft)</th>
                                        <th>Width (inch)</th>
                                        <th>Thickness (inch)</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>CFT</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7" class="text-end">Total Wood Amount:</th>
                                        <th>
                                            <input type="text" class="form-control" id="total_wood_amount" name="total_wood_amount" readonly>
                                        </th>
                                        <th class="text-center">
                                            <button type="button" class="btn btn-success btn-sm add-row-btn add-wood-row"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 save-tab-btn" data-section="wood">Save Wood</button>
                    </div>
                    <div class="tab-pane fade" id="glow" role="tabpanel" aria-labelledby="glow-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="glowTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Glue Type</th>
                                        <th>Quantity (kg)</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total Glue Amount:</th>
                                        <th><input type="text" class="form-control" id="total_glow_amount" name="total_glow_amount" readonly></th>
                                        <th class="text-center">
                                            <button type="button" class="btn btn-success btn-sm add-row-btn add-glow-row"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 save-tab-btn" data-section="glow">Save Glue</button>
                    </div>
                    <div class="tab-pane fade" id="plynydf" role="tabpanel" aria-labelledby="plynydf-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="plynydfTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Quantity</th>
                                        <th>Width (ft)</th>
                                        <th>Length (ft)</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-end">Total PLY/NYDF Amount:</th>
                                        <th><input type="text" class="form-control" id="total_plynydf_amount" name="total_plynydf_amount" readonly></th>
                                        <th class="text-center">
                                            <button type="button" class="btn btn-success btn-sm add-row-btn add-plynydf-row"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 save-tab-btn" data-section="plynydf">Save PLY/NYDF</button>
                    </div>
                    <div class="tab-pane fade" id="hardware" role="tabpanel" aria-labelledby="hardware-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="hardwareTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total Hardware Amount:</th>
                                        <th><input type="text" class="form-control" id="total_hardware_amount" name="total_hardware_amount" readonly></th>
                                        <th class="text-center">
                                            <button type="button" class="btn btn-success btn-sm add-row-btn add-hardware-row"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 save-tab-btn" data-section="hardware">Save Hardware</button>
                    </div>
                    <div class="tab-pane fade" id="labour" role="tabpanel" aria-labelledby="labour-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="labourTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Labour Name</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Total Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end">Total Labour Amount:</th>
                                        <th><input type="text" class="form-control" id="total_labour_amount" name="total_labour_amount" readonly></th>
                                        <th class="text-center">
                                            <button type="button" class="btn btn-success btn-sm add-row-btn add-labour-row"><i class="fas fa-plus"></i></button>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 save-tab-btn" data-section="labour">Save Labour</button>
                    </div>
                    <div class="tab-pane fade" id="factory" role="tabpanel" aria-labelledby="factory-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="factoryTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Total Amount</th>
                                        <th>Factory Cost</th>
                                        <th>Updated Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control" id="total_factory_amount" name="factory[0][total_amount]" readonly></td>
                                        <td><input type="number" step="0.01" class="form-control" id="total_factory_cost" name="factory[0][factory_cost]" placeholder="Enter Factory Cost"></td>
                                        <td><input type="text" class="form-control" id="total_factory_updated" name="factory[0][updated_total]" readonly></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 save-tab-btn" data-section="factory">Save Factory Cost</button>
                    </div>
                    <div class="tab-pane fade" id="margin" role="tabpanel" aria-labelledby="margin-tab">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="marginTable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Total Amount</th>
                                        <th>Margin Cost</th>
                                        <th>Updated Total Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" class="form-control" id="total_margin_amount" name="margin[0][total_amount]" readonly></td>
                                        <td><input type="number" step="0.01" class="form-control" id="total_margin_cost" name="margin[0][margin_cost]" placeholder="Enter Margin Cost"></td>
                                        <td><input type="text" class="form-control" id="total_margin_updated" name="margin[0][updated_total]" readonly></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-primary mt-3 save-tab-btn" data-section="margin">Save Margin</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.1/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
    .table th, .table td { vertical-align: middle; padding: 0.5rem; }
    .table input.form-control, .table select.form-control { border: 1px solid #ced4da; padding: 0.375rem 0.75rem; height: auto; }
    .add-row-btn, .remove-row { font-size: 0.9rem; padding: 0.3rem 0.6rem; line-height: 1; }
    .save-tab-btn { min-width: 120px; }
    .thead-light th { background-color: #f8f9fc; }

    /* Explicit toastr success and error colors */
    #toast-container > .toast-success {
        background-color: #51a351 !important; /* green */
        color: white !important;
    }
    #toast-container > .toast-error {
        background-color: #bd362f !important; /* red */
        color: white !important;
    }
</style>

<script>
    $(document).ready(function () {
    var sections = ['wood', 'glow', 'plynydf', 'hardware', 'labour', 'factory', 'margin'];

    function calculateRowTotal(row, section) {
        var total = 0;
        if (section === 'wood') {
            var length = parseFloat(row.find('input[name$="[length]"]').val()) || 0;
            var width_inch = parseFloat(row.find('input[name$="[width]"]').val()) || 0;
            var thickness_inch = parseFloat(row.find('input[name$="[thickness]"]').val()) || 0;
            var quantity = parseFloat(row.find('input[name$="[quantity]"]').val()) || 0;
            var price = parseFloat(row.find('input[name$="[price]"]').val()) || 0;

            var width_ft = width_inch / 12;
            var thickness_ft = thickness_inch / 12;

            var cft = (length * width_ft * thickness_ft);
            var cftInput = row.find('input[name$="[cft]"]');
            if (cftInput.length) {
                cftInput.val((cft * quantity).toFixed(2));
            }
            total = price * quantity * cft;
        } else if (section === 'glow') {
            var quantity = parseFloat(row.find('input[name$="[quantity]"]').val()) || 0;
            var price = parseFloat(row.find('input[name$="[price]"]').val()) || 0;
            total = quantity * price;
        } else if (section === 'plynydf') {
            var quantity = parseFloat(row.find('input[name$="[quantity]"]').val()) || 0;
            var width = parseFloat(row.find('input[name$="[width]"]').val()) || 0;
            var length = parseFloat(row.find('input[name$="[length]"]').val()) || 0;
            var price = parseFloat(row.find('input[name$="[price]"]').val()) || 0;
            total = quantity * width * length * price;
        } else if (section === 'hardware') {
            var quantity = parseFloat(row.find('input[name$="[quantity]"]').val()) || 0;
            var price = parseFloat(row.find('input[name$="[price]"]').val()) || 0;
            total = quantity * price;
        } else if (section === 'labour') {
            var quantity = parseFloat(row.find('input[name$="[quantity]"]').val()) || 0;
            var price = parseFloat(row.find('input[name$="[price]"]').val()) || 0;
            total = quantity * price;
        }
        return total;
    }

    function updateTotals(section) {
        var tableBody = $('#' + section + 'Table tbody');
        var rows = tableBody.find('tr');
        var sectionTotal = 0;
        rows.each(function () {
            var row = $(this);
            var totalInput = row.find('input[name$="[total]"], input[name$="[totalprice]"]');
            if (totalInput.length) {
                var rowTotal = calculateRowTotal(row, section);
                totalInput.val(rowTotal.toFixed(2));
                sectionTotal += rowTotal;
            }
        });
        if(section === 'factory') {
            sectionTotal = parseFloat($('#total_factory_cost').val()) || 0;
        } else if(section === 'margin') {
            sectionTotal = parseFloat($('#total_margin_cost').val()) || 0;
        }
        $('#total_' + section + '_amount').val(sectionTotal.toFixed(2));
        updateGrandTotal();
    }

    function updateGrandTotal() {
        var grandTotal = 0;
        var subTotalBeforeFactory = 0;

        sections.forEach(function (section) {
            if (section === 'wood' || section === 'glow' || section === 'plynydf' || section === 'hardware' || section === 'labour') {
                subTotalBeforeFactory += parseFloat($('#total_' + section + '_amount').val()) || 0;
            }
        });

        // Update factory amount (base amount before factory cost)
        $('#total_factory_amount').val(subTotalBeforeFactory.toFixed(2));
        
        // Get manually entered factory cost only
        var factoryCost = parseFloat($('#total_factory_cost').val()) || 0;
        
        var totalAfterFactory = subTotalBeforeFactory + factoryCost;
        $('#total_factory_updated').val(totalAfterFactory.toFixed(2));

        // Update margin amount (base amount after factory cost)
        $('#total_margin_amount').val(totalAfterFactory.toFixed(2));
        
        // Get manually entered margin cost only
        var marginCost = parseFloat($('#total_margin_cost').val()) || 0;

        var totalAfterMargin = totalAfterFactory + marginCost;
        $('#total_margin_updated').val(totalAfterMargin.toFixed(2));

        grandTotal = totalAfterMargin;
        
        $('#grand_total_amount').val(grandTotal.toFixed(2));
    }



    function updateRowNames(section) {
        var tbody = $('#' + section + 'Table tbody');
        var rows = tbody.find('tr');
        rows.each(function (index) {
            var inputs = $(this).find('input, select');
            inputs.each(function () {
                var name = $(this).attr('name');
                if (name) {
                    var nameParts = name.split('[');
                    if (nameParts.length > 2) {
                        $(this).attr('name', section + '[' + index + '][' + nameParts[2].replace(']', '') + ']');
                    }
                }
            });
        });
    }

    $(document).on('input', 'table tbody input', function () {
        var section = $(this).closest('table').attr('id').replace('Table', '');
        updateTotals(section);
    });
    
    // Handle manual factory and margin cost input
    $(document).on('input', '#total_factory_cost, #total_margin_cost', function () {
        updateGrandTotal();
    });

    $(document).on('click', '.add-row-btn', function () {
        var section = $(this).closest('table').attr('id').replace('Table', '');
        var tableBody = $('#' + section + 'Table tbody');
        var rowCount = tableBody.find('tr').length;
        var html = '<tr>';

        if (section === 'wood') {
            html += '<td><select name="wood[' + rowCount + '][woodtype]" class="form-control" required>';
            html += '<option value="">Select Wood Type</option>';
            html += '<option value="Mango">Mango</option>';
            html += '<option value="Babool">Babool</option>';
            html += '<option value="Oak">Oak</option>';
            html += '<option value="Seesam">Seesam</option>';
            html += '<option value="Other">Other</option>';
            html += '</select></td>';
            html += '<td><input type="number" step="0.01" name="wood[' + rowCount + '][length]" class="form-control" required placeholder="Feet"></td>';
            html += '<td><input type="number" step="0.01" name="wood[' + rowCount + '][width]" class="form-control" required placeholder="Inch"></td>';
            html += '<td><input type="number" step="0.01" name="wood[' + rowCount + '][thickness]" class="form-control" required placeholder="Inch"></td>';
            html += '<td><input type="number" step="0.01" name="wood[' + rowCount + '][quantity]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="wood[' + rowCount + '][price]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="wood[' + rowCount + '][cft]" class="form-control" readonly></td>';
            html += '<td><input type="number" step="0.01" name="wood[' + rowCount + '][total]" class="form-control" readonly></td>';
            html += '<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>';
        } else if (section === 'glow') {
            html += '<td><input type="text" name="glow[' + rowCount + '][glowtype]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="glow[' + rowCount + '][quantity]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="glow[' + rowCount + '][price]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="glow[' + rowCount + '][total]" class="form-control" readonly></td>';
            html += '<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>';
        } else if (section === 'plynydf') {
            html += '<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][quantity]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][width]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][length]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][price]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][total]" class="form-control" readonly></td>';
            html += '<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>';
        } else if (section === 'hardware') {
            html += '<td><input type="text" name="hardware[' + rowCount + '][itemname]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="hardware[' + rowCount + '][quantity]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="hardware[' + rowCount + '][price]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="hardware[' + rowCount + '][totalprice]" class="form-control" readonly></td>';
            html += '<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>';
        } else if (section === 'labour') {
            html += '<td><input type="text" name="labour[' + rowCount + '][itemname]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="labour[' + rowCount + '][quantity]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="labour[' + rowCount + '][price]" class="form-control" required></td>';
            html += '<td><input type="number" step="0.01" name="labour[' + rowCount + '][totalprice]" class="form-control" readonly></td>';
            html += '<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>';
        }
        
        html += '</tr>';
        tableBody.append(html);
        updateRowNames(section);
        updateTotals(section);
    });

    $(document).on('click', '.remove-row', function () {
        var section = $(this).closest('table').attr('id').replace('Table', '');
        $(this).closest('tr').remove();
        updateRowNames(section);
        updateTotals(section);
    });

    $('.save-tab-btn').on('click', function () {
        var section = $(this).data('section');
        var formData = $('#bomForm').serializeArray();

        var relevantData = {};
        formData.forEach(function (item) {
            if (item.name === 'bom_number' || item.name === 'costing_sheet_number' || item.name === 'client_name' || item.name === 'created_date' || item.name === 'prepared_by' || item.name === 'id') {
                relevantData[item.name] = item.value;
            }
            if (item.name.startsWith(section + '[')) {
                var match = item.name.match(/(\w+)\[(\d+)\]\[(\w+)\]/);
                if (match) {
                    var mainKey = match[1];
                    var index = match[2];
                    var field = match[3];

                    if (!relevantData[mainKey]) {
                        relevantData[mainKey] = {};
                    }
                    if (!relevantData[mainKey][index]) {
                        relevantData[mainKey][index] = {};
                    }
                    relevantData[mainKey][index][field] = item.value;
                }
            }
        });
        
        // Special handling for factory and margin sections
        if (section === 'factory') {
            relevantData.factory = [{
                total_amount: $('#total_factory_amount').val(),
                factory_cost: $('#total_factory_cost').val(),
                updated_total: $('#total_factory_updated').val()
            }];
        }
        
        if (section === 'margin') {
            relevantData.margin = [{
                total_amount: $('#total_margin_amount').val(),
                margin_cost: $('#total_margin_cost').val(),
                updated_total: $('#total_margin_updated').val()
            }];
        }

        for (var key in relevantData) {
            if (relevantData.hasOwnProperty(key) && typeof relevantData[key] === 'object' && !Array.isArray(relevantData[key])) {
                if (sections.includes(key)) {
                    relevantData[key] = Object.values(relevantData[key]);
                }
            }
        }

        var bomId = <?php echo json_encode($id); ?>;
        if (bomId) {
            relevantData['bom_id'] = bomId;
        }

        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/bom/ajax_save_bom.php?section=' + section,
            type: 'POST',
            data: JSON.stringify(relevantData),
            contentType: 'application/json',
            success: function (response) {
                console.log('AJAX success response:', response);
                if (response.success) {
                    toastr.clear();
                    toastr.success(response.message, 'Success', { timeOut: 3000, progressBar: true, closeButton: true });
                    if (response.bom_id && !$('#id').val()) {
                        $('#id').val(response.bom_id);
                    }
                } else {
                    toastr.clear();
                    toastr.error(response.message || 'An error occurred.', 'Error', { timeOut: 5000, progressBar: true, closeButton: true });
                }
            },
            error: function (xhr, status, error) {
                toastr.clear();
                toastr.error('AJAX error: ' + error, 'Error', { timeOut: 5000, progressBar: true, closeButton: true });
                console.error('AJAX Error:', status, error, xhr.responseText);
            }
        });
    });

    function populateForm(items) {
        if (!items) return;

        for (const section in items) {
            if (items.hasOwnProperty(section)) {
                const sectionItems = items[section];
                if (Array.isArray(sectionItems)) {
                    sectionItems.forEach(item => {
                        var tableBody = $('#' + section + 'Table tbody');
                        var rowCount = tableBody.find('tr').length;
                        var html = [];
                        html.push('<tr>');

                        if (section === 'wood') {
                            html.push('<td><select name="wood[' + rowCount + '][woodtype]" class="form-control" required>');
                            html.push('<option value="">Select Wood Type</option>');
                            ['Mango', 'Babool', 'Oak', 'Seesam', 'Other'].forEach(function (woodtype) {
                                html.push('<option value="' + woodtype + '"' + (item.woodtype === woodtype ? ' selected' : '') + '>' + woodtype + '</option>');
                            });
                            html.push('</select></td>');
                            html.push('<td><input type="number" step="0.01" name="wood[' + rowCount + '][length]" class="form-control" value="' + (item.length_ft || '') + '" required placeholder="Feet"></td>');
                            html.push('<td><input type="number" step="0.01" name="wood[' + rowCount + '][width]" class="form-control" value="' + ((item.width_ft || 0) * 12).toFixed(2) + '" required placeholder="Inch"></td>');
                            html.push('<td><input type="number" step="0.01" name="wood[' + rowCount + '][thickness]" class="form-control" value="' + (item.thickness_inch || '') + '" required placeholder="Inch"></td>');
                            html.push('<td><input type="number" step="0.01" name="wood[' + rowCount + '][quantity]" class="form-control" value="' + (item.quantity || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="wood[' + rowCount + '][price]" class="form-control" value="' + (item.price || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="wood[' + rowCount + '][cft]" class="form-control" value="' + (item.cft || '') + '" readonly></td>');
                            html.push('<td><input type="number" step="0.01" name="wood[' + rowCount + '][total]" class="form-control" value="' + (item.total || '') + '" readonly></td>');
                            html.push('<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>');
                        } else if (section === 'glow') {
                            html.push('<td><input type="text" name="glow[' + rowCount + '][glowtype]" class="form-control" value="' + (item.glowtype || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="glow[' + rowCount + '][quantity]" class="form-control" value="' + (item.quantity || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="glow[' + rowCount + '][price]" class="form-control" value="' + (item.price || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="glow[' + rowCount + '][total]" class="form-control" value="' + (item.total || '') + '" readonly></td>');
                            html.push('<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>');
                        } else if (section === 'plynydf') {
                            html.push('<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][quantity]" class="form-control" value="' + (item.quantity || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][width]" class="form-control" value="' + (item.width || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][length]" class="form-control" value="' + (item.length || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][price]" class="form-control" value="' + (item.price || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="plynydf[' + rowCount + '][total]" class="form-control" value="' + (item.total || '') + '" readonly></td>');
                            html.push('<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>');
                        } else if (section === 'hardware') {
                            html.push('<td><input type="text" name="hardware[' + rowCount + '][itemname]" class="form-control" value="' + (item.itemname || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="hardware[' + rowCount + '][quantity]" class="form-control" value="' + (item.quantity || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="hardware[' + rowCount + '][price]" class="form-control" value="' + (item.price || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="hardware[' + rowCount + '][totalprice]" class="form-control" value="' + (item.totalprice || '') + '" readonly></td>');
                            html.push('<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>');
                        } else if (section === 'labour') {
                            html.push('<td><input type="text" name="labour[' + rowCount + '][itemname]" class="form-control" value="' + (item.itemname || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="labour[' + rowCount + '][quantity]" class="form-control" value="' + (item.quantity || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="labour[' + rowCount + '][price]" class="form-control" value="' + (item.price || '') + '" required></td>');
                            html.push('<td><input type="number" step="0.01" name="labour[' + rowCount + '][totalprice]" class="form-control" value="' + (item.totalprice || '') + '" readonly></td>');
                            html.push('<td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-trash"></i></button></td>');
                        }
                        
                        html.push('</tr>');
                        tableBody.append(html.join(''));
                    });
                    updateRowNames(section);
                    updateTotals(section);
                }
            }
        }
    }

    sections.forEach(function (section) {
        if (section !== 'factory' && section !== 'margin') {
            updateTotals(section);
        }
    });
    
    // Update grand total after all sections are loaded
    updateGrandTotal();

    var editMode = <?php echo json_encode($edit_mode); ?>;
    if (editMode) {
        var itemData = <?php echo json_encode($item_data); ?>;
        populateForm(itemData);
        
        // Populate factory and margin data if available
        if (itemData.factory && itemData.factory.length > 0) {
            var factory = itemData.factory[0];
            $('#total_factory_amount').val(factory.total_amount || '');
            $('#total_factory_cost').val(factory.factory_cost || '');
            $('#total_factory_updated').val(factory.updated_total || '');
        }
        
        if (itemData.margin && itemData.margin.length > 0) {
            var margin = itemData.margin[0];
            $('#total_margin_amount').val(margin.total_amount || '');
            $('#total_margin_cost').val(margin.margin_cost || '');
            $('#total_margin_updated').val(margin.updated_total || '');
        }
        
        // Set grand total if available
        var bomData = <?php echo json_encode($bom_data); ?>;
        if (bomData.grand_total_amount) {
            $('#grand_total_amount').val(bomData.grand_total_amount);
        }
    }
});


</script>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>