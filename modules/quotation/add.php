<?php
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', realpath(__DIR__ . '/../../'));
}
include_once ROOT_DIR_PATH . '/config/config.php';
include_once ROOT_DIR_PATH . '/include/inc/header.php';
session_start();
$user_type = $_SESSION['user_type'] ?? 'guest';

if ($user_type === 'superadmin') {
    include_once __DIR__ . '/../../superadmin/sidebar.php';
} elseif ($user_type === 'salesadmin') {
    include_once __DIR__ . '/../../salesadmin/sidebar.php';
}

$editMode = false;
$quotation = null;
$products = [];
$error = null;

$disableLeadDropdown = false;
$selectedLeadId = null;
if (isset($_GET['lead_id']) && is_numeric($_GET['lead_id'])) {
    $selectedLeadId = intval($_GET['lead_id']);
    $disableLeadDropdown = true;
}

global $conn;

if (!$conn instanceof PDO) {
    $error = 'Database connection not established. Check config.php.';
    $approvedLeads = [];
} else {
    try {
        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            $editMode = true;
            $quotationId = intval($_GET['id']);

            $stmt = $conn->prepare("SELECT *, is_locked, locked_by FROM quotations WHERE id = :id");
            $stmt->execute([':id' => $quotationId]);
            $quotation = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($quotation) {
                // Check if the quotation is locked
                $isLocked = ($quotation['is_locked'] == 1);
                $lockedBy = $quotation['locked_by'];
                $currentUserIsSuperadmin = ($_SESSION['user_type'] ?? '') === 'superadmin';
                
                $disableForm = $isLocked && !$currentUserIsSuperadmin;

                $stmt2 = $conn->prepare("SELECT * FROM quotation_products WHERE quotation_id = :id");
                $stmt2->execute([':id' => $quotationId]);
                $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $error = "Quotation not found.";
            }
        }

        $stmt = $conn->prepare("SELECT * FROM leads WHERE approve = 1 ORDER BY lead_number ASC");
        $stmt->execute();
        $approvedLeads = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $approvedLeads = [];
        $error = "Error fetching data: " . $e->getMessage();
    }
}
?>

<?php if ($error): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $error; ?>
    </div>
<?php endif; ?>

<div class="container-fluid" style="width: 85%;">
    <?php include_once ROOT_DIR_PATH . '/include/inc/topbar.php'; ?>
    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <form id="quotationForm" enctype="multipart/form-data">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><?php echo $editMode ? 'Edit' : 'Add'; ?> Quotation</h6>
            </div>
            <div class="card-body">
                <?php if ($editMode && isset($isLocked) && $isLocked): ?>
                    <div class="alert alert-info">
                        This Quotation is currently locked by <?php echo htmlspecialchars($lockedBy ?? 'an admin'); ?>. Only Super Admins can make changes.
                    </div>
                <?php endif; ?>
                <fieldset class="mb-4" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>>
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3">
                            <label for="lead_number" class="form-label">Lead Number</label>
                            <select class="form-control" id="lead_number" name="lead_id" required <?php echo $disableLeadDropdown ? 'disabled' : ''; ?>>
                                <option value="">Select Lead</option>
                                <?php foreach ($approvedLeads as $lead) : ?>
                                    <option value="<?php echo htmlspecialchars($lead['id']); ?>"
                                        data-company-name="<?php echo htmlspecialchars($lead['company_name']); ?>"
                                        data-contact-email="<?php echo htmlspecialchars($lead['contact_email']); ?>"
                                        data-contact-phone="<?php echo htmlspecialchars($lead['contact_phone']); ?>"
                                        <?php
                                        if ($disableLeadDropdown && $selectedLeadId == $lead['id']) {
                                            echo 'selected';
                                        } elseif ($editMode && $quotation && $quotation['lead_id'] == $lead['id']) {
                                            echo 'selected';
                                        }
                                        ?>
                                    >
                                        <?php echo htmlspecialchars($lead['lead_number'] . ' - ' . $lead['company_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quotation_date" class="form-label">Date of Quote Raised</label>
                            <input type="date" class="form-control" id="quotation_date" name="quotation_date" value="<?php echo $editMode && $quotation ? htmlspecialchars($quotation['quotation_date']) : date('Y-m-d'); ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quotation_number" class="form-label">Quotation Number</label>
                            <input type="text" class="form-control" id="quotation_number" name="quotation_number" readonly value="<?php echo $editMode && $quotation ? htmlspecialchars($quotation['quotation_number']) : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="customer_name" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customer_name" name="customer_name" readonly value="<?php echo $editMode && $quotation ? htmlspecialchars($quotation['customer_name']) : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="customer_email" class="form-label">Customer Email</label>
                            <input type="email" class="form-control" id="customer_email" name="customer_email" readonly value="<?php echo $editMode && $quotation ? htmlspecialchars($quotation['customer_email']) : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="customer_phone" class="form-label">Customer Phone</label>
                            <input type="tel" class="form-control" id="customer_phone" name="customer_phone" readonly value="<?php echo $editMode && $quotation ? htmlspecialchars($quotation['customer_phone']) : ''; ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="delivery_term" class="form-label">Payment Terms</label>
                            <input type="text" class="form-control" id="delivery_term" name="delivery_term" value="<?php echo $editMode && $quotation ? htmlspecialchars($quotation['delivery_term']) : ''; ?>" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="terms_of_delivery" class="form-label">Terms of Delivery</label>
                            <input type="text" class="form-control" id="terms_of_delivery" name="terms_of_delivery" value="<?php echo $editMode && $quotation ? htmlspecialchars($quotation['terms_of_delivery']) : ''; ?>" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>>
                        </div>
                    </div>
                </fieldset>

                <div>
                    <input type="file" id="productFileInput" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="display:none;" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>>
                    <button type="button" class="btn btn-primary mt-3 mb-3" id="uploadProductsBtn" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>>Upload Products (Excel/CSV)</button>
                    <div id="csvProcessingMsg" style="display:none; color: #007bff; font-weight: bold; margin-bottom: 10px;">Processing file, please wait...</div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="table-responsive" style="overflow-x:auto; max-width: 100%; white-space: nowrap;">
                                <table id="productTable" class="table table-bordered table-striped table-hover" style="min-width: 1800px;">
                                <style>
                                    #productTable input.form-control-sm { min-width: 80px; width: 100%; }
                                    #productTable td { padding: 4px; vertical-align: middle; }
                                    #productTable th:nth-child(4), #productTable td:nth-child(4) { min-width: 430px; }
                                    #productTable th:nth-child(5), #productTable td:nth-child(5) { min-width: 100px; }
                                </style>
                                    <thead>
                                        <tr>
                                            <th>Sno</th>
                                            <th>Product Image</th>
                                            <th>Preview</th>
                                            <th>Item Name</th>
                                            <th>Item Code</th>
                                            <th>Assembly</th>
<th colspan="3" class="text-center">Item Dimension (cms)</th>
<th colspan="3" class="text-center">Box Dimension (cms)</th>
                                            <th>CBM</th>
                                            <th>Wood/Marble Type</th>
                                            <th>No. of Packet</th>
                                            <th>Quantity</th>
                                            <th>Price USD</th>
                                            <th>Total USD</th>
                                            <th>Comments</th>
                                            <th>Action</th>
                                        </tr>
                                        <tr>
                                            <th></th><th></th><th></th><th></th><th></th>
                                            <th></th>
<th class="small">W</th><th class="small">D</th><th class="small">H</th>
<th class="small">W</th><th class="small">D</th><th class="small">H</th>
                                            <th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="productTableBody">
                                        <?php if ($editMode && !empty($products)) : ?>
                                            <?php foreach ($products as $index => $product) : ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td>
                                                        <input type="file" class="form-control form-control-sm product-image-input" name="product_image[]" accept="image/*" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>>
                                                        <input type="hidden" class="existing-image-name" name="existing_image_name[]" value="<?php echo htmlspecialchars($product['product_image_name']); ?>">
                                                    </td>
                                                    <td>
                                                        <?php 
                                                            $imgSrc = '';
                                                            $imageName = htmlspecialchars($product['product_image_name']);
                                                            if (!empty($imageName)) {
                                                                // Define base URL for images, adjust if needed for live environment
                                                                $baseImageUrl = BASE_URL . 'assets/images/upload/quotation/';
                                                                $imagePath = $baseImageUrl . $imageName;
                                                                
                                                                // Note: Client-side JS will handle image preview from local files
                                                                // No need for server-side file_exists check here for client-side rendering
                                                                
                                                                // Add timestamp to prevent caching
                                                                $imgSrc = $imagePath . '?t=' . time();
                                                            }
                                                        ?>
                                                        <img class="product-image-preview" src="<?php echo $imgSrc; ?>" alt="Preview" style="max-width: 80px; max-height: 80px; display: <?php echo empty($imgSrc) ? 'none' : 'block'; ?>;">
                                                    </td>
                                                    <td><input type="text" class="form-control form-control-sm" name="item_name[]" value="<?php echo htmlspecialchars($product['item_name']); ?>" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><input type="text" class="form-control form-control-sm" name="item_code[]" value="<?php echo htmlspecialchars($product['item_code']); ?>" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><input type="text" class="form-control form-control-sm" name="assembly[]" value="<?php echo htmlspecialchars($product['assembly']); ?>" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
<td><input type="number" class="form-control form-control-sm item-w" name="item_w[]" value="<?php echo htmlspecialchars($product['item_w']); ?>" step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
<td><input type="number" class="form-control form-control-sm item-d" name="item_d[]" value="<?php echo htmlspecialchars($product['item_d']); ?>" step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
<td><input type="number" class="form-control form-control-sm item-h" name="item_h[]" value="<?php echo htmlspecialchars($product['item_h']); ?>" step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
<td><input type="number" class="form-control form-control-sm box-w" name="box_w[]" value="<?php echo htmlspecialchars($product['box_w']); ?>" step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
<td><input type="number" class="form-control form-control-sm box-d" name="box_d[]" value="<?php echo htmlspecialchars($product['box_d']); ?>" step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
<td><input type="number" class="form-control form-control-sm box-h" name="box_h[]" value="<?php echo htmlspecialchars($product['box_h']); ?>" step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><input type="number" class="form-control form-control-sm cbm-field" name="cbm[]" value="<?php echo htmlspecialchars($product['cbm']); ?>" readonly step="0.0001" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><input type="text" class="form-control form-control-sm" name="wood_type[]" value="<?php echo htmlspecialchars($product['wood_type']); ?>" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><input type="number" class="form-control form-control-sm" name="no_of_packet[]" value="<?php echo htmlspecialchars($product['no_of_packet']); ?>" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>"></td>
                                                    <td><input type="number" class="form-control form-control-sm quantity-field" name="quantity[]" value="<?php echo htmlspecialchars($product['quantity']); ?>" step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><input type="number" class="form-control form-control-sm price-field" name="price_usd[]" value="<?php echo htmlspecialchars($product['price_usd']); ?>" step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><input type="number" class="form-control form-control-sm total-field" name="total_usd[]" value="<?php echo is_numeric($product['quantity']) && is_numeric($product['price_usd']) ? ($product['quantity'] * $product['price_usd']) : ''; ?>" readonly step="0.01" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><input type="text" class="form-control form-control-sm" name="comments[]" value="<?php echo htmlspecialchars($product['comments']); ?>" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>></td>
                                                    <td><button type="button" class="btn btn-danger btn-sm remove-row" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>>Remove</button></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <input type="hidden" name="lead_id" id="lead_id" value="<?php echo $editMode && $quotation ? htmlspecialchars($quotation['lead_id']) : ''; ?>">

            <button type="submit" class="btn btn-primary mt-3 mb-3 mx-3" <?php echo isset($disableForm) && $disableForm ? 'disabled' : ''; ?>><?php echo $editMode ? 'Update Quotation' : 'Submit Quotation'; ?></button>
            <?php if ($editMode): ?>
                <input type="hidden" name="quotation_id" value="<?php echo htmlspecialchars($quotation['id']); ?>">
            <?php endif; ?>
        </form>
    </div>

    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

    <div>
        <?php include_once ROOT_DIR_PATH . '/include/inc/footer.php'; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="<?php echo BASE_URL; ?>modules/quotation/progress_handler.js"></script>

<script>
$(document).ready(function() {
    var editMode = <?php echo $editMode ? 'true' : 'false'; ?>;
    var disableLeadDropdown = <?php echo $disableLeadDropdown ? 'true' : 'false'; ?>;

    if (disableLeadDropdown) {
        $('#lead_number').trigger('change');
    }
    
    $('#uploadProductsBtn').click(function() {
        $('#productFileInput').click();
    });

    async function processFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        const reader = new FileReader();

        return new Promise((resolve, reject) => {
            reader.onload = function(e) {
                try {
                    let jsonData = [];
                    if (ext === 'xlsx' || ext === 'xls') {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, { type: 'array' });
                        const sheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[sheetName];
                        jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
                    } else if (ext === 'csv') {
                        const text = e.target.result;
                        jsonData = text.split(/\r?\n/).map(line => line.split(','));
                    } else {
                        throw new Error('Unsupported file type. Please upload an Excel (xlsx, xls) or CSV file.');
                    }
                    resolve(jsonData);
                } catch (err) {
                    reject(err);
                }
            };
            reader.onerror = (error) => reject(error);

            if (ext === 'xlsx' || ext === 'xls') {
                reader.readAsArrayBuffer(file);
            } else if (ext === 'csv') {
                reader.readAsText(file);
            } else {
                reject(new Error('Unsupported file type.'));
            }
        });
    }

    $('#productFileInput').on('change', async function(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Store the file for later upload
        window.uploadedExcelFile = file;

        // Show progress modal for large files
        if (file.size > 1024 * 1024) { // Files larger than 1MB
            ProgressHandler.show();
            ProgressHandler.updateText('Reading Excel file...');
        } else {
            $('#csvProcessingMsg').show();
        }
        
        try {
            const jsonData = await processFile(file);
            
            if (file.size > 1024 * 1024) {
                ProgressHandler.updateText('Processing data rows...');
            }
            
            fillProductTable(jsonData);
            
            // Show success message
            const rowCount = jsonData.length - 1; // Exclude header
            if (rowCount > 0) {
                alert(`Successfully loaded ${rowCount} products from Excel file.`);
            }
            
        } catch (error) {
            console.error("File parsing error:", error);
            alert('Error processing file: ' + error.message);
        } finally {
            if (file.size > 1024 * 1024) {
                ProgressHandler.hide();
            } else {
                $('#csvProcessingMsg').hide();
            }
            // Don't clear the file input so we can upload it
        }
    });
    
    function fillProductTable(data) {
        $('#productTableBody').empty();
        if (data.length < 2) { 
            alert("File must contain at least a header row and one data row.");
            return;
        }

        let rowNum = 1;
        const headers = data[0].map(h => (h || '').toString().trim().toLowerCase());
        const dataRows = data.slice(1);
        
        // Create header mapping for flexible column names
        const headerMap = {
            's.no': 'sno',
            'sno': 'sno',
            'item name': 'item_name',
            'item_name': 'item_name',
            'product name': 'item_name',
            'item code': 'item_code',
            'item_code': 'item_code',
            'assembly': 'assembly',
            // Item dimensions
            'item width (cm)': 'item_w',
            'item depth (cm)': 'item_d',
            'item height (cm)': 'item_h',
            'item w': 'item_w',
            'item_w': 'item_w',
            'item d': 'item_d', 
            'item_d': 'item_d',
            'item h': 'item_h',
            'item_h': 'item_h',
            // Box dimensions
            'box width (cm)': 'box_w',
            'box depth (cm)': 'box_d',
            'box height (cm)': 'box_h',
            'box w': 'box_w',
            'box_w': 'box_w',
            'box d': 'box_d',
            'box_d': 'box_d',
            'box h': 'box_h',
            'box_h': 'box_h',
            // Other fields
            'wood/marble type': 'wood_type',
            'wood type': 'wood_type',
            'wood_type': 'wood_type',
            'material': 'wood_type',
            'no. of packet': 'no_of_packet',
            'no of packet': 'no_of_packet',
            'packets': 'no_of_packet',
            'quantity': 'quantity',
            'qty': 'quantity',
            'price usd': 'price_usd',
            'price': 'price_usd',
            'unit price': 'price_usd',
            'comments': 'comments',
            'remarks': 'comments',
            'notes': 'comments'
        };
        
        // Debug: Log headers to console
        console.log('Excel headers detected:', headers);
        console.log('Header mapping will be:', headers.map(h => headerMap[h] || h)); 

        dataRows.forEach(rowData => {
            if (!rowData || rowData.length === 0 || rowData.every(cell => !cell || cell.toString().trim() === '')) {
                return;
            }

            let product = {};
            headers.forEach((header, index) => {
                const mappedKey = headerMap[header] || header;
                const value = rowData[index] ? rowData[index].toString().trim() : '';
                product[mappedKey] = value;
                // Debug: Log mapping for dimension fields
                if (header.includes('width') || header.includes('depth') || header.includes('height')) {
                    console.log(`Mapping: ${header} -> ${mappedKey} = ${value}`);
                }
            });

            const getVal = (key) => product[key] || '';
            
            let itemData = {
                item_name: getVal('item_name'),
                item_code: getVal('item_code'),
                assembly: getVal('assembly'),
                item_w: getVal('item_w'),
                item_d: getVal('item_d'),
                item_h: getVal('item_h'),
                box_w: getVal('box_w'),
                box_d: getVal('box_d'),
                box_h: getVal('box_h'),
                wood_type: getVal('wood_type'),
                no_of_packet: getVal('no_of_packet'),
                quantity: getVal('quantity'),
                price_usd: getVal('price_usd'),
                comments: getVal('comments')
            };
            
            // Skip rows without item name
            if (!itemData.item_name) {
                return;
            }

            const rowHTML = `<tr>
                <td>${rowNum++}</td>
                <td><input type="file" class="form-control form-control-sm product-image-input" name="product_image[]" accept="image/*"><input type="hidden" class="existing-image-name" name="existing_image_name[]" value=""></td>
                <td><img class="product-image-preview" src="" alt="Preview" style="max-width: 80px; max-height: 80px; display: none;"></td>
                <td><input type="text" class="form-control form-control-sm" name="item_name[]" value="${itemData.item_name}"></td>
                <td><input type="text" class="form-control form-control-sm" name="item_code[]" value="${itemData.item_code}"></td>
                <td><input type="text" class="form-control form-control-sm" name="assembly[]" value="${itemData.assembly}"></td>
                <td><input type="number" class="form-control form-control-sm item-w" name="item_w[]" value="${itemData.item_w}" step="0.01"></td>
                <td><input type="number" class="form-control form-control-sm item-d" name="item_d[]" value="${itemData.item_d}" step="0.01"></td>
                <td><input type="number" class="form-control form-control-sm item-h" name="item_h[]" value="${itemData.item_h}" step="0.01"></td>
                <td><input type="number" class="form-control form-control-sm box-w" name="box_w[]" value="${itemData.box_w}" step="0.01"></td>
                <td><input type="number" class="form-control form-control-sm box-d" name="box_d[]" value="${itemData.box_d}" step="0.01"></td>
                <td><input type="number" class="form-control form-control-sm box-h" name="box_h[]" value="${itemData.box_h}" step="0.01"></td>
                <td><input type="number" class="form-control form-control-sm cbm-field" name="cbm[]" readonly step="0.0001"></td>
                <td><input type="text" class="form-control form-control-sm" name="wood_type[]" value="${itemData.wood_type}"></td>
                <td><input type="number" class="form-control form-control-sm" name="no_of_packet[]" value="${itemData.no_of_packet}"></td>
                <td><input type="number" class="form-control form-control-sm quantity-field" name="quantity[]" value="${itemData.quantity}" step="0.01"></td>
                <td><input type="number" class="form-control form-control-sm price-field" name="price_usd[]" value="${itemData.price_usd}" step="0.01"></td>
                <td><input type="number" class="form-control form-control-sm total-field" name="total_usd[]" readonly step="0.01"></td>
                <td><input type="text" class="form-control form-control-sm" name="comments[]" value="${itemData.comments}"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
            </tr>`;
            
            const $row = $(rowHTML);
            $('#productTableBody').append($row);
            
            // Trigger calculations immediately after row is added
            updateCBM($row);
            updateTotalUSD($row);
        });
        
        console.log(`Loaded ${rowNum - 1} products from Excel file`);
    }
    
    function updateCBM(row) {
        const w = parseFloat(row.find('.box-w').val()) || 0;
        const d = parseFloat(row.find('.box-d').val()) || 0;
        const h = parseFloat(row.find('.box-h').val()) || 0;
        const cbm = (w * d * h) / 1000000;
        row.find('.cbm-field').val(cbm > 0 ? cbm.toFixed(4) : '0.0000');
    }

    function updateTotalUSD(row) {
        const qty = parseFloat(row.find('.quantity-field').val()) || 0;
        const price = parseFloat(row.find('.price-field').val()) || 0;
        const total = qty * price;
        row.find('.total-field').val(total > 0 ? total.toFixed(2) : '');
    }

    $(document).on('input change keyup', '.box-w, .box-d, .box-h', function() { 
        updateCBM($(this).closest('tr')); 
    });
    $(document).on('input change keyup', '.quantity-field, .price-field', function() { 
        updateTotalUSD($(this).closest('tr')); 
    });
    $('#productTableBody').on('click', '.remove-row', function() { 
        $(this).closest('tr').remove();
        // Update row numbers
        $('#productTableBody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    });

    // Initialize calculations for existing rows
    setTimeout(function() {
        $('#productTableBody tr').each(function() {
            updateCBM($(this));
            updateTotalUSD($(this));
        });
    }, 100);

    function populateLeadDetails() {
        var selectedOption = $('#lead_number').find('option:selected');
        $('#customer_name').val(selectedOption.data('company-name') || '');
        $('#customer_email').val(selectedOption.data('contact-email') || '');
        $('#customer_phone').val(selectedOption.data('contact-phone') || '');
        $('#lead_id').val(selectedOption.val());

        if (!editMode) {
            $.ajax({
                url: '<?php echo BASE_URL; ?>modules/quotation/get_latest_quotation_number.php',
                type: 'GET',
                dataType: 'json',
                success: function(data) { if (data.success) $('#quotation_number').val(data.latest_quotation_number); },
                error: function() { console.error('Error fetching quotation number.'); }
            });
        }
    }

    $('#lead_number').change(populateLeadDetails);
    if (disableLeadDropdown || editMode) {
        populateLeadDetails();
    }
    
    $('#quotationForm').submit(function(e) {
        e.preventDefault();
        
        // Show loading state
        var submitBtn = $(this).find('button[type="submit"]');
        var originalText = submitBtn.text();
        submitBtn.prop('disabled', true).text('Saving...');
        
        var formData = new FormData(this);
        var productsData = [];
        
        // Add the uploaded Excel file if it exists
        if (window.uploadedExcelFile) {
            formData.append('excel_file', window.uploadedExcelFile);
        }

        $('#productTableBody tr').each(function(index) {
            var $row = $(this);
            var productData = {
                item_name: $row.find('[name="item_name[]"]').val() || '',
                item_code: $row.find('[name="item_code[]"]').val() || '',
                assembly: $row.find('[name="assembly[]"]').val() || '',
                item_w: $row.find('[name="item_w[]"]').val() || '',
                item_d: $row.find('[name="item_d[]"]').val() || '',
                item_h: $row.find('[name="item_h[]"]').val() || '',
                box_w: $row.find('[name="box_w[]"]').val() || '',
                box_d: $row.find('[name="box_d[]"]').val() || '',
                box_h: $row.find('[name="box_h[]"]').val() || '',
                cbm: $row.find('[name="cbm[]"]').val() || '',
                wood_type: $row.find('[name="wood_type[]"]').val() || '',
                no_of_packet: $row.find('[name="no_of_packet[]"]').val() || '',
                quantity: $row.find('[name="quantity[]"]').val() || '0',
                price_usd: $row.find('[name="price_usd[]"]').val() || '0',
                comments: $row.find('[name="comments[]"]').val() || '',
                existing_image_name: $row.find('[name="existing_image_name[]"]').val() || ''
            };
            
            console.log('Processing row ' + index + ':', productData);
            
            // Only add products with item_name
            if (productData.item_name.trim() !== '') {
                productsData.push(productData);
                
                // Handle image files
                var imageInput = $row.find('[name="product_image[]"]')[0];
                if (imageInput && imageInput.files[0]) {
                    formData.append('product_images_' + index, imageInput.files[0]); // Changed to unique names
                    console.log('Added image for row ' + index);
                }
            } else {
                console.log('Skipping row ' + index + ' - no item name');
            }
        });
        
        console.log('Total products to save:', productsData.length);
        console.log('Products data:', productsData);
        
        formData.append('products', JSON.stringify(productsData));
        if ($('#lead_number').is(':disabled')) {
            formData.append('lead_id', $('#lead_number').val());
        }
        
        // Debug form data
        console.log('Form data being sent:');
        for (var pair of formData.entries()) {
            if (pair[0] !== 'products') {
                console.log(pair[0] + ': ' + pair[1]);
            }
        }
        
        var toastEl = document.getElementById('liveToast');
        var toast = new bootstrap.Toast(toastEl);

        // Show progress for large datasets
        if (productsData.length > 50) {
            ProgressHandler.show();
            ProgressHandler.updateText(`Saving ${productsData.length} products...`);
        }
        
        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/quotation/store.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            timeout: 600000, // 10 minutes timeout
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                // Upload progress
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable && productsData.length > 50) {
                        var percentComplete = evt.loaded / evt.total * 100;
                        ProgressHandler.updateText(`Uploading... ${Math.round(percentComplete)}%`);
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                console.log('Server response:', response);
                try {
                    var res = typeof response === 'string' ? JSON.parse(response) : response;
                    console.log('Parsed response:', res);
                    
                    $('#liveToast .toast-body').text(res.message || 'Operation completed.');
                    toast.show();
                    
                    if (res.success) {
                        console.log('Success! Quotation ID:', res.quotation_id);
                        setTimeout(function() {
                            window.location.href = 'index.php';
                        }, 1500);
                    } else {
                        console.error('Server returned error:', res.message);
                    }
                } catch (e) {
                    console.error("Error parsing server response:", e);
                    console.log('Raw response:', response);
                    $('#liveToast .toast-body').text('Response parsing error. Check console for details.');
                    toast.show();
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusCode: xhr.status
                });
                
                var errorMsg = 'Error occurred while saving.';
                if (status === 'timeout') {
                    errorMsg = 'Request timed out. Please check if data was saved.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error. Check debug.log for details.';
                }
                
                $('#liveToast .toast-body').text(errorMsg);
                toast.show();
            },
            complete: function() {
                if (productsData.length > 50) {
                    ProgressHandler.hide();
                }
                submitBtn.prop('disabled', false).text(originalText);
            }
        });
    });

    $(document).on('change', '.product-image-input', function() {
        const input = this;
        const $preview = $(this).closest('tr').find('.product-image-preview');
        const maxFileSize = 2 * 1024 * 1024; 

        if (input.files && input.files[0]) {
            if (input.files[0].size > maxFileSize) {
                alert('File size exceeds 2MB. Please choose a smaller file.');
                $(input).val('');
                $preview.hide().attr('src', '');
                return;
            }
            const reader = new FileReader();
            reader.onload = e => $preview.attr('src', e.target.result).show();
            reader.readAsDataURL(input.files[0]);
        } else {
            $preview.hide().attr('src', '');
        }
    });
});
</script>