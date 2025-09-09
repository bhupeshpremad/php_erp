// Individual row save function - separate from bulk save
function saveIndividualRow(targetRow) {
    var po_number = $('#po_number').val();
    var jci_number = $('#jci_number').val();
    var sell_order_number = $('#sell_order_number').val();
    var bom_number = $('#bom_number_display').val();
    var isSuperAdmin = window.isSuperAdmin || false;

    // Get job card number from table header
    var currentTable = targetRow.closest('table');
    var job_card_number_from_table = currentTable.find('thead th').first().text().replace('Job Card: ', '').trim();

    // Extract data from ONLY the target row
    var supplier_name = (targetRow.find('.supplierNameInput').val() || '').trim();
    var product_type = (targetRow.find('.productTypeInput').val() || '').trim();
    var product_name = (targetRow.find('.productNameInput').val() || '').trim();
    var assigned_quantity = targetRow.find('.assignQuantityInput').val() || '0';
    var price = targetRow.find('.bomPriceInput').val() || '0';
    var invoice_number = (targetRow.find('.invoiceNumberInput').val() || '').trim();
    var builty_number = (targetRow.find('.builtyNumberInput').val() || '').trim();
    var existing_invoice_image = targetRow.find('.existingInvoiceImage').val() || '';
    var existing_builty_image = targetRow.find('.existingBuiltyImage').val() || '';

    // Validation
    if (supplier_name === '') {
        toastr.error('Supplier name is required.');
        targetRow.find('.supplierNameInput').focus();
        return;
    }
    if (isNaN(parseFloat(assigned_quantity)) || parseFloat(assigned_quantity) <= 0) {
        toastr.error('Assigned quantity must be greater than zero.');
        targetRow.find('.assignQuantityInput').focus();
        return;
    }

    // Find unique ID for this specific row
    var uniqueId = null;
    var existingItems = window.existingPurchaseItems || [];
    var matchingItem = existingItems.find(function(item) {
        return item.job_card_number === job_card_number_from_table &&
               item.product_type === product_type &&
               item.product_name === product_name &&
               item.supplier_name === supplier_name &&
               Math.abs(parseFloat(item.assigned_quantity || 0) - parseFloat(assigned_quantity)) < 0.001 &&
               Math.abs(parseFloat(item.price || 0) - parseFloat(price)) < 0.01;
    });
    
    if (matchingItem) {
        uniqueId = matchingItem.id;
    }

    // Get BOM quantity and row serial for unique identification
    var bom_quantity = targetRow.find('.bomQuantityInput').val() || '0';
    var row_serial = targetRow.find('td').eq(1).text().trim();
    
    // Create single item array with unique row identification
    var items_to_save = [{
        rowIndex: 0, // Single row index
        uniqueId: uniqueId,
        supplier_name: supplier_name,
        product_type: product_type,
        product_name: product_name,
        job_card_number: job_card_number_from_table,
        assigned_quantity: assigned_quantity,
        price: price,
        bom_quantity: bom_quantity, // Add BOM quantity for precise matching
        row_serial: row_serial, // Add row serial for identification
        total: (parseFloat(assigned_quantity) * parseFloat(price)).toFixed(2),
        invoice_number: invoice_number,
        builty_number: builty_number,
        existing_invoice_image: existing_invoice_image,
        existing_builty_image: existing_builty_image
    }];
    
    console.log('Individual save data:', items_to_save[0]);

    // Prepare FormData
    var formData = new FormData();
    formData.append('po_number', po_number);
    formData.append('jci_number', jci_number);
    formData.append('sell_order_number', sell_order_number);
    formData.append('bom_number', bom_number);
    formData.append('is_superadmin', isSuperAdmin);
    formData.append('items_json', JSON.stringify(items_to_save));

    // Add file uploads for this specific row
    var invoiceImageFile = targetRow.find('.invoiceImageInput')[0].files[0];
    var builtyImageFile = targetRow.find('.builtyImageInput')[0].files[0];
    
    if (invoiceImageFile) {
        formData.append('invoice_image_0', invoiceImageFile);
    }
    if (builtyImageFile) {
        formData.append('builty_image_0', builtyImageFile);
    }

    console.log('Saving individual row:', items_to_save[0]);

    // AJAX call
    $.ajax({
        url: 'ajax_save_purchase.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                toastr.success('Row saved successfully!');
                $('#jci_number_search').trigger('change'); // Reload table
            } else {
                toastr.error(response.error || 'Save failed');
            }
        },
        error: function(xhr, status, error) {
            toastr.error('AJAX error: ' + error);
        }
    });
}