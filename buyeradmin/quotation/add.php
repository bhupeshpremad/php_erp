<?php
session_start();
include '../../config/config.php';

// Check if buyer is logged in
if (!isset($_SESSION['buyer_id'])) {
    header('Location: ../login.php');
    exit;
}

$buyer_id = $_SESSION['buyer_id'];
$buyer_name = $_SESSION['buyer_name'];
$company_name = $_SESSION['company_name'];

$error = '';
$success = '';

// Generate quotation number
$quotation_number = 'BQUOTE-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
?>

<?php include '../../include/inc/header.php'; ?>

<body id="page-top">
    <style>
        #wrapper {
            display: flex;
            width: 100%;
        }
        #content-wrapper {
            flex: 1;
            overflow-x: hidden;
        }
    </style>
    <div id="wrapper">
        
        <?php include '../sidebar.php'; ?>
        
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo htmlspecialchars($buyer_name); ?></span>
                                <img class="img-profile rounded-circle" src="../../assets/images/undraw_profile.svg">
                            </a>
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?php echo BASE_URL; ?>superadmin/profile.php">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../logout.php">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                
                <!-- Begin Page Content -->
                <div class="container-fluid">
                    
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Add Quotation</h1>
                        <a href="list.php" class="btn btn-primary">
                            <i class="fas fa-list mr-2"></i>View Quotations
                        </a>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php endif; ?>
                    
                    <!-- Quotation Form -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Quotation Details</h6>
                        </div>
                        <div class="card-body">
                            <form id="quotationForm" enctype="multipart/form-data">
                                
                                <!-- Row 1: Basic Information -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="quotation_number" class="form-label">Quotation Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="quotation_number" name="quotation_number" 
                                               value="<?php echo htmlspecialchars($quotation_number); ?>" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="quotation_date" class="form-label">Quotation Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="quotation_date" name="quotation_date" 
                                               value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="customer_name" class="form-label">Customer Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" value="<?php echo htmlspecialchars($company_name); ?>" required>
                                    </div>
                                </div>
                                
                                <!-- Row 2: Contact Information -->
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label for="customer_email" class="form-label">Customer Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="customer_email" name="customer_email" value="<?php echo htmlspecialchars($_SESSION['buyer_email']); ?>" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="customer_phone" class="form-label">Customer Phone <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="delivery_term" class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="delivery_term" name="delivery_term" required>
                                    </div>
                                </div>
                                
                                <!-- Row 3: Terms -->
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="terms_of_delivery" class="form-label">Terms of Delivery <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="terms_of_delivery" name="terms_of_delivery" required>
                                    </div>
                                </div>
                                
                                <!-- Products Section -->
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="m-0 font-weight-bold text-primary">Products/Services</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <input type="file" id="productFileInput" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" style="display:none;">
                                            <button type="button" class="btn btn-success mr-2" id="downloadTemplateBtn">
                                                <i class="fas fa-download mr-2"></i>Download Quotation Format
                                            </button>
                                            <button type="button" class="btn btn-primary" id="uploadProductsBtn">
                                                <i class="fas fa-upload mr-2"></i>Upload Products (Excel/CSV)
                                            </button>
                                            <div id="csvProcessingMsg" style="display:none; color: #007bff; font-weight: bold; margin: 10px 0;">Processing file, please wait...</div>
                                        </div>
                                        
                                        <div class="table-responsive">
                                            <table id="productTable" class="table table-bordered table-striped table-hover">
                                                <style>
                                                    #productTable input.form-control-sm { min-width: 70px; width: 100%; font-size: 12px; }
                                                    #productTable td { padding: 2px; vertical-align: middle; font-size: 12px; }
                                                    #productTable th { padding: 4px; font-size: 11px; text-align: center; }
                                                    .table-responsive { max-height: 400px; overflow-y: auto; }
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
                                                    <!-- Products will be loaded here -->
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <div class="row mt-3">
                                            <div class="col-md-8"></div>
                                            <div class="col-md-4">
                                                <div class="card">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-6"><strong>Grand Total:</strong></div>
                                                            <div class="col-6 text-right"><strong id="grand-total">$0.00</strong></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-left">
                                    <button type="submit" class="btn btn-primary" id="saveBtn">
                                        <i class="fas fa-save mr-2"></i><span id="btnText">Create Quotation</span>
                                    </button>
                                    <a href="list.php" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                                
                            </form>
                        </div>
                    </div>
                    
                </div>
                
            </div>
        </div>
        
    </div>
    
    <script src="../../assets/js/jquery.min.js"></script>
    <script src="../../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/sb-admin-2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Load buyer phone
            loadBuyerPhone();
            
            // Check if editing
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('id');
            if (editId) {
                $('#btnText').text('Update Quotation');
                loadQuotationData(editId);
            }
            
            // Download template
            $('#downloadTemplateBtn').click(function() {
                window.location.href = '../download_template.php'; // Need to create a buyer-specific template or use a generic one
            });
            
            // Upload products
            $('#uploadProductsBtn').click(function() {
                $('#productFileInput').click();
            });
            
            // File upload handler
            $('#productFileInput').change(function() {
                const file = this.files[0];
                if (file) {
                    $('#csvProcessingMsg').show();
                    processExcelFile(file);
                }
            });
            
            // Form submit
            $('#quotationForm').submit(function(e) {
                e.preventDefault();
                saveQuotation();
            });
        });
        
        function loadBuyerPhone() {
            $.ajax({
                url: '../get_phone.php', // Need to create a buyer-specific get_phone.php
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#customer_phone').val(response.phone);
                    }
                }
            });
        }
        
        async function processExcelFile(file) {
            try {
                const data = await readExcelFile(file);
                loadProductsToTable(data);
                $('#csvProcessingMsg').hide();
                alert('Excel file loaded successfully!');
            } catch (error) {
                $('#csvProcessingMsg').hide();
                alert('Error processing file: ' + error.message);
            }
        }
        
        function readExcelFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, { type: 'array' });
                        const sheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[sheetName];
                        const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
                        resolve(jsonData);
                    } catch (err) {
                        reject(err);
                    }
                };
                reader.onerror = (error) => reject(error);
                reader.readAsArrayBuffer(file);
            });
        }
        
        function loadProductsToTable(data) {
            const tbody = $('#productTableBody');
            tbody.empty();
            
            if (data.length < 2) {
                alert('File must contain at least a header row and one data row.');
                return;
            }
            
            // Skip header row and process data
            for (let i = 1; i < data.length; i++) {
                const row = data[i];
                if (!row[1]) continue; // Skip if no item name
                
                const product = {
                    item_name: row[1] || '',
                    item_code: row[2] || '',
                    assembly: row[3] || '',
                    item_w: row[4] || '',
                    item_d: row[5] || '',
                    item_h: row[6] || '',
                    box_w: row[7] || '',
                    box_d: row[8] || '',
                    box_h: row[9] || '',
                    wood_type: row[10] || '',
                    no_of_packet: row[11] || '',
                    quantity: row[12] || '',
                    price_usd: row[13] || '',
                    comments: row[14] || '',
                    cbm: '',
                    total_usd: '',
                    image_path: ''
                };
                
                addProductRow(product, i - 1);
            }
            
            calculateTotals();
        }
        
        function addProductRow(product, index) {
            // Calculate CBM and total
            const boxW = parseFloat(product.box_w) || 0;
            const boxD = parseFloat(product.box_d) || 0;
            const boxH = parseFloat(product.box_h) || 0;
            const cbm = (boxW * boxD * boxH) / 1000000;
            
            const quantity = parseFloat(product.quantity) || 0;
            const price = parseFloat(product.price_usd) || 0;
            const total = quantity * price;
            
            const row = `
                <tr>
                    <td>${index + 1}</td>
                    <td><input type="file" class="form-control form-control-sm" name="product_images_${index}" accept="image/*"></td>
                    <td><img class="product-preview" width="60" height="60" style="border:1px solid #ddd; object-fit:cover;"
                             src="${product.image_path ? '<?php echo BASE_URL; ?>assets/images/upload/buyer_quotations/' + product.image_path + '?t=' + new Date().getTime() : ''}"
                             onerror="this.style.display='none'" 
                             onload="this.style.display=''"
                             style="${product.image_path ? '' : 'display:none;'}">
                    </td>
                    <td><input type="text" class="form-control form-control-sm" name="item_name_${index}" value="${product.item_name}" required></td>
                    <td><input type="text" class="form-control form-control-sm" name="item_code_${index}" value="${product.item_code}"></td>
                    <td><input type="text" class="form-control form-control-sm" name="assembly_${index}" value="${product.assembly}"></td>
                    <td><input type="number" class="form-control form-control-sm item-w" name="item_w_${index}" value="${product.item_w}" step="0.01"></td>
                    <td><input type="number" class="form-control form-control-sm item-d" name="item_d_${index}" value="${product.item_d}" step="0.01"></td>
                    <td><input type="number" class="form-control form-control-sm item-h" name="item_h_${index}" value="${product.item_h}" step="0.01"></td>
                    <td><input type="number" class="form-control form-control-sm box-w" name="box_w_${index}" value="${product.box_w}" step="0.01"></td>
                    <td><input type="number" class="form-control form-control-sm box-d" name="box_d_${index}" value="${product.box_d}" step="0.01"></td>
                    <td><input type="number" class="form-control form-control-sm box-h" name="box_h_${index}" value="${product.box_h}" step="0.01"></td>
                    <td><input type="number" class="form-control form-control-sm cbm-field" name="cbm_${index}" value="${cbm.toFixed(4)}" step="0.0001" readonly></td>
                    <td><input type="text" class="form-control form-control-sm" name="wood_type_${index}" value="${product.wood_type}"></td>
                    <td><input type="number" class="form-control form-control-sm" name="no_of_packet_${index}" value="${product.no_of_packet}"></td>
                    <td><input type="number" class="form-control form-control-sm quantity-field" name="quantity_${index}" value="${product.quantity}" step="0.01" required></td>
                    <td><input type="number" class="form-control form-control-sm price-field" name="price_usd_${index}" value="${product.price_usd}" step="0.01" required></td>
                    <td><input type="number" class="form-control form-control-sm total-field" name="total_usd_${index}" value="${total.toFixed(2)}" step="0.01" readonly></td>
                    <td><input type="text" class="form-control form-control-sm" name="comments_${index}" value="${product.comments}"></td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-row">Remove</button></td>
                </tr>
            `;
            $('#productTableBody').append(row);
        }
        
        // Calculate CBM and totals
        $(document).on('input', '.quantity-field, .price-field, .box-w, .box-d, .box-h', function() {
            const row = $(this).closest('tr');
            
            // Calculate CBM
            const boxW = parseFloat(row.find('.box-w').val()) || 0;
            const boxD = parseFloat(row.find('.box-d').val()) || 0;
            const boxH = parseFloat(row.find('.box-h').val()) || 0;
            const cbm = (boxW * boxD * boxH) / 1000000;
            row.find('.cbm-field').val(cbm.toFixed(4));
            
            // Calculate total
            const quantity = parseFloat(row.find('.quantity-field').val()) || 0;
            const price = parseFloat(row.find('.price-field').val()) || 0;
            const total = quantity * price;
            row.find('.total-field').val(total.toFixed(2));
            
            calculateTotals();
        });
        
        // Image preview
        $(document).on('change', 'input[type="file"][name^="product_images_"]', function() {
            const file = this.files[0];
            const preview = $(this).closest('tr').find('.product-preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.attr('src', e.target.result).show();
                };
                reader.readAsDataURL(file);
            } else {
                preview.hide().attr('src', '');
            }
        });
        
        // Remove row
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            calculateTotals();
        });
        
        function calculateTotals() {
            let grandTotal = 0;
            $('.total-field').each(function() {
                grandTotal += parseFloat($(this).val()) || 0;
            });
            $('#grand-total').text('$' + grandTotal.toFixed(2));
        }
        
        function saveQuotation() {
            const formData = new FormData($('#quotationForm')[0]);
            const urlParams = new URLSearchParams(window.location.search);
            const editId = urlParams.get('id');
            if (editId) formData.append('quotation_id', editId);
            formData.append('buyer_id', <?php echo json_encode($buyer_id); ?>);
            
            $('#saveBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Saving...');
            
            $.ajax({
                url: 'save.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        setTimeout(() => window.location.href = 'list.php', 1500);
                    } else {
                        showToast('error', response.message);
                    }
                },
                error: function() {
                    showToast('error', 'Failed to save quotation');
                },
                complete: function() {
                    $('#saveBtn').prop('disabled', false).html('<i class="fas fa-save mr-2"></i>' + $('#btnText').text());
                }
            });
        }
        
        function showToast(type, message) {
            const bgClass = type === 'success' ? 'bg-success' : 'bg-danger';
            const toast = `
                <div class="toast" style="position: fixed; top: 20px; right: 20px; z-index: 9999;">
                    <div class="toast-header ${bgClass} text-white">
                        <strong class="mr-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                        <button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast">&times;</button>
                    </div>
                    <div class="toast-body">${message}</div>
                </div>
            `;
            $('body').append(toast);
            $('.toast').toast({delay: 3000}).toast('show').on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
        
        function loadQuotationData(id) {
            $.ajax({
                url: 'get.php?id=' + id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const data = response.data;
                        $('#quotation_date').val(data.quotation_date);
                        $('#customer_name').val(data.customer_name);
                        $('#customer_email').val(data.customer_email);
                        $('#customer_phone').val(data.customer_phone);
                        $('#delivery_term').val(data.delivery_term);
                        $('#terms_of_delivery').val(data.terms_of_delivery);
                        
                        // Load products
                        const tbody = $('#productTableBody');
                        tbody.empty();
                        data.products.forEach((product, index) => {
                            addProductRow(product, index);
                        });
                        calculateTotals();
                    }
                }
            });
        }
    </script>
    
</body>
</html>
