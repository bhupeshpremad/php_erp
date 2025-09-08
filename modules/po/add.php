<?php

session_start();

include_once __DIR__ . '/../../config/config.php';
if (!defined('ROOT_DIR_PATH')) {
    define('ROOT_DIR_PATH', __DIR__ . '/../../' . DIRECTORY_SEPARATOR);
}
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

$po_id = $_GET['id'] ?? null;
$po_data = null;
$po_items = [];
$is_locked = 0;

if ($po_id) {
    $stmt_main = $conn->prepare("SELECT * FROM po_main WHERE id = :id");
    $stmt_main->bindValue(':id', $po_id, PDO::PARAM_INT);
    $stmt_main->execute();
    $po_data = $stmt_main->fetch(PDO::FETCH_ASSOC);

    if ($po_data) {
        $is_locked = $po_data['is_locked'];

        $stmt_items = $conn->prepare("SELECT * FROM po_items WHERE po_id = :po_id ORDER BY id ASC");
        $stmt_items->bindValue(':po_id', $po_id, PDO::PARAM_INT);
        $stmt_items->execute();
        $po_items = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<div class="container-fluid mb-5">
    <?php include_once ROOT_DIR_PATH . 'include/inc/topbar.php'; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary"><?php echo $po_id ? 'Edit Purchase Order' : 'Add New Purchase Order'; ?></h6>
            <a href="index.php" class="btn btn-primary btn-sm">Back to PO List</a>
        </div>
        <div class="card-body">
            <form id="poForm">
                <input type="hidden" name="po_id" value="<?php echo htmlspecialchars($po_id); ?>">

                <fieldset <?php echo ($is_locked == 1 && $user_type !== 'superadmin') ? 'disabled' : ''; ?>>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="po_number" class="form-label">PO Number</label>
                            <input type="text" class="form-control" id="po_number" name="po_number" value="<?php echo htmlspecialchars($po_data['po_number'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="client_name" class="form-label">Client Name</label>
                            <input type="text" class="form-control" id="client_name" name="client_name" value="<?php echo htmlspecialchars($po_data['client_name'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="prepared_by" class="form-label">Prepared By</label>
                            <input type="text" class="form-control" id="prepared_by" name="prepared_by" value="<?php echo htmlspecialchars($po_data['prepared_by'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="order_date" class="form-label">Order Date</label>
                            <input type="date" class="form-control" id="order_date" name="order_date" value="<?php echo htmlspecialchars($po_data['order_date'] ?? date('Y-m-d')); ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="delivery_date" class="form-label">Delivery Date</label>
                            <input type="date" class="form-control" id="delivery_date" name="delivery_date" value="<?php echo htmlspecialchars($po_data['delivery_date'] ?? ''); ?>" required>
                        </div>
                    </div>

                    <hr>
                    <h5>Items</h5>
                    <div id="po-items-container">
                        <?php if (empty($po_items)): ?>
                            <div class="row po-item-row mb-3 border p-2 rounded align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label">Product Code</label>
                                    <input type="text" class="form-control product_code" name="items[0][product_code]" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" class="form-control product_name" name="items[0][product_name]" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Product Image</label>
                                    <input type="file" class="form-control product_image" name="items[0][product_image]" accept="image/*">
                                </div>
                                <div class="col-md-1 text-center">
                                    <label class="form-label">Preview</label>
                                    <div class="image-preview" style="display:none;">
                                        <img src="" alt="Preview" style="width:70px;height:70px;object-fit:cover;border:1px solid #ddd;">
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Quantity</label>
                                    <input type="number" step="0.01" class="form-control quantity" name="items[0][quantity]" value="0" required>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label">Price</label>
                                    <input type="number" step="0.01" class="form-control price" name="items[0][price]" value="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Total</label>
                                    <input type="number" step="0.01" class="form-control total_amount" name="items[0][total_amount]" value="0" readonly>
                                </div>
                                <div class="col-md-1 d-flex align-items-end justify-content-end">
                                    <button type="button" class="btn btn-danger remove-item-btn w-100">Remove</button>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($po_items as $index => $item): ?>
                                <div class="row po-item-row mb-3 border p-2 rounded align-items-end">
                                    <div class="col-md-2">
                                        <label class="form-label">Product Code</label>
                                        <input type="text" class="form-control product_code" name="items[<?php echo $index; ?>][product_code]" value="<?php echo htmlspecialchars($item['product_code'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Product Name</label>
                                        <input type="text" class="form-control product_name" name="items[<?php echo $index; ?>][product_name]" value="<?php echo htmlspecialchars($item['product_name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Product Image</label>
                                        <input type="file" class="form-control product_image" name="items[<?php echo $index; ?>][product_image]" accept="image/*">
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <label class="form-label">Preview</label>
                                        <div class="image-preview" <?php echo empty($item['product_image']) ? 'style="display:none;"' : ''; ?>>
                                            <?php if (!empty($item['product_image'])): ?>
                                                <img src="<?php echo BASE_URL; ?>uploads/po/<?php echo htmlspecialchars($item['product_image']); ?>" alt="Product" style="width:70px;height:70px;object-fit:cover;border:1px solid #ddd;">
                                            <?php else: ?>
                                                <img src="" alt="Preview" style="width:70px;height:70px;object-fit:cover;border:1px solid #ddd; display:none;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" step="0.01" class="form-control quantity" name="items[<?php echo $index; ?>][quantity]" value="<?php echo htmlspecialchars($item['quantity'] ?? '0'); ?>" required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Price</label>
                                        <input type="number" step="0.01" class="form-control price" name="items[<?php echo $index; ?>][price]" value="<?php echo htmlspecialchars($item['price'] ?? '0'); ?>" required>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">Total</label>
                                        <input type="number" step="0.01" class="form-control total_amount" name="items[<?php echo $index; ?>][total_amount]" value="<?php echo htmlspecialchars($item['total_amount'] ?? '0'); ?>" readonly>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end justify-content-end">
                                        <button type="button" class="btn btn-danger remove-item-btn w-100">Remove</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-success btn-sm mt-3" id="addItemBtn">Add Item</button>
                    <button type="submit" class="btn btn-primary mt-3">Save Purchase Order</button>
                </fieldset>
            </form>
        </div>
    </div>
</div>

<?php include_once ROOT_DIR_PATH . 'include/inc/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
$(document).ready(function() {
    // Initialize itemIndex based on existing items
    let itemIndex = <?php echo count($po_items) > 0 ? count($po_items) : 0; ?>; // Start from 0 if no items, otherwise count of existing items

    function calculateTotal(row) {
        let quantity = parseFloat(row.find('.quantity').val()) || 0;
        let price = parseFloat(row.find('.price').val()) || 0;
        let total = quantity * price;
        row.find('.total_amount').val(total.toFixed(2));
    }

    function addCalculations() {
        // Detach previous event handlers to prevent multiple bindings
        $('.po-item-row').find('.quantity, .price').off('input');

        $('.po-item-row').each(function() {
            let row = $(this);
            row.find('.quantity, .price').on('input', function() {
                calculateTotal(row);
            });
            // Initial calculation for existing rows
            calculateTotal(row);
        });
    }

    // Initial setup for existing rows
    addCalculations();

    // Auto calculate delivery date (20 days after order date) - only if delivery date is empty
    $('#order_date').on('change', function() {
        const orderDate = new Date($(this).val());
        const currentDeliveryDate = $('#delivery_date').val();
        
        // Only auto-calculate if delivery date is empty
        if (!currentDeliveryDate && !isNaN(orderDate.getTime())) {
            orderDate.setDate(orderDate.getDate() + 20);
            const deliveryDate = orderDate.toISOString().split('T')[0];
            $('#delivery_date').val(deliveryDate);
        }
    });

    // Trigger initial calculation for delivery date if order_date has a value on load and delivery date is empty
    if ($('#order_date').val() && !$('#delivery_date').val()) {
        $('#order_date').trigger('change');
    }

    // Image preview functionality
    $(document).on('change', '.product_image', function() {
        const file = this.files[0];
        const previewDiv = $(this).closest('.po-item-row').find('.image-preview');
        const img = previewDiv.find('img');
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                img.attr('src', e.target.result);
                previewDiv.show(); // Show the preview div
                img.show(); // Show the img tag itself
            };
            reader.readAsDataURL(file);
        } else {
            img.attr('src', ''); // Clear image source
            img.hide(); // Hide the img tag
            previewDiv.hide(); // Hide the preview div
        }
    });

    $('#addItemBtn').on('click', function() {
        const newItemHtml = `
            <div class="row po-item-row mb-3 border p-2 rounded align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Product Code</label>
                    <input type="text" class="form-control product_code" name="items[${itemIndex}][product_code]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Product Name</label>
                    <input type="text" class="form-control product_name" name="items[${itemIndex}][product_name]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Product Image</label>
                    <input type="file" class="form-control product_image" name="items[${itemIndex}][product_image]" accept="image/*">
                </div>
                <div class="col-md-1 text-center">
                    <label class="form-label">Preview</label>
                    <div class="image-preview" style="display:none;">
                        <img src="" alt="Preview" style="width:70px;height:70px;object-fit:cover;border:1px solid #ddd;">
                    </div>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Quantity</label>
                    <input type="number" step="0.01" class="form-control quantity" name="items[${itemIndex}][quantity]" value="0" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Price</label>
                    <input type="number" step="0.01" class="form-control price" name="items[${itemIndex}][price]" value="0" required>
                </div>
                <div class="col-md-1">
                    <label class="form-label">Total</label>
                    <input type="number" step="0.01" class="form-control total_amount" name="items[${itemIndex}][total_amount]" value="0" readonly>
                </div>
                <div class="col-md-2 d-flex align-items-end justify-content-end">
                    <button type="button" class="btn btn-danger remove-item-btn w-100">Remove</button>
                </div>
            </div>
        `;
        $('#po-items-container').append(newItemHtml);
        addCalculations(); // Apply calculations to the new row
        itemIndex++;
    });

    $(document).on('click', '.remove-item-btn', function() {
        if ($('.po-item-row').length > 1) {
            $(this).closest('.po-item-row').remove();
            // Re-index names after removal to ensure consecutive array keys
            $('.po-item-row').each(function(idx) {
                $(this).find('[name^="items["]').each(function() {
                    const currentName = $(this).attr('name');
                    const newName = currentName.replace(/items\[\d+\]/, `items[${idx}]`);
                    $(this).attr('name', newName);
                });
            });
            // Update itemIndex to reflect the correct next index
            itemIndex = $('.po-item-row').length;
        } else {
            toastr.warning('At least one item is required.');
        }
    });

    $('#poForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Ensure all total_amount fields are updated before submission
        $('.po-item-row').each(function() {
            calculateTotal($(this));
        });

        $.ajax({
            url: '<?php echo BASE_URL; ?>modules/po/save_po.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    if (!<?php echo json_encode((bool)$po_id); ?>) {
                        setTimeout(() => {
                            window.location.href = 'add.php?id=' + response.po_id;
                        }, 1000);
                    } else {
                        setTimeout(() => {
                            location.reload();
                        }, 1000);
                    }
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error: ", status, error, xhr.responseText);
                toastr.error('An error occurred. Please try again.');
            }
        });
    });
});
</script>