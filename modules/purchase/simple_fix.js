// Simple fix for data not showing after save
console.log('Loading simple fix...');

// Override saveIndividualRow to force refresh
function saveIndividualRowFixed(targetRow) {
    var po_number = $('#po_number').val();
    var jci_number = $('#jci_number').val();
    var sell_order_number = $('#sell_order_number').val();
    var bom_number = $('#bom_number_display').val();
    var isSuperAdmin = window.isSuperAdmin || false;

    var currentTable = targetRow.closest('table');
    var job_card_number_from_table = currentTable.find('thead th').first().text().replace('Job Card: ', '').trim();

    var supplier_name = (targetRow.find('.supplierNameInput').val() || '').trim();
    var product_type = (targetRow.find('.productTypeInput').val() || '').trim();
    var product_name = (targetRow.find('.productNameInput').val() || '').trim();
    var assigned_quantity = targetRow.find('.assignQuantityInput').val() || '0';
    var price = targetRow.find('.bomPriceInput').val() || '0';
    var invoice_number = (targetRow.find('.invoiceNumberInput').val() || '').trim();
    var builty_number = (targetRow.find('.builtyNumberInput').val() || '').trim();
    var existing_invoice_image = targetRow.find('.existingInvoiceImage').val() || '';
    var existing_builty_image = targetRow.find('.existingBuiltyImage').val() || '';

    if (supplier_name === '') {
        toastr.error('Supplier name is required.');
        return;
    }
    
    if (isNaN(parseFloat(assigned_quantity)) || parseFloat(assigned_quantity) <= 0) {
        toastr.error('Assigned quantity must be greater than zero.');
        return;
    }

    var bom_quantity = targetRow.find('.bomQuantityInput').val() || '0';
    var length_ft = '0', width_ft = '0', thickness_inch = '0';
    
    if (product_type === 'Wood' && window.currentBomData) {
        var matchingBomItem = window.currentBomData.find(function(item) {
            return item.product_type === 'Wood' && 
                   item.product_name === product_name &&
                   Math.abs(parseFloat(item.quantity || 0) - parseFloat(bom_quantity)) < 0.001;
        });
        
        if (matchingBomItem) {
            length_ft = matchingBomItem.length_ft || '0';
            width_ft = matchingBomItem.width_ft || '0';
            thickness_inch = matchingBomItem.thickness_inch || '0';
        }
    }

    var items_to_save = [{
        rowIndex: 0,
        supplier_name: supplier_name,
        product_type: product_type,
        product_name: product_name,
        job_card_number: job_card_number_from_table,
        assigned_quantity: assigned_quantity,
        price: price,
        bom_quantity: bom_quantity,
        length_ft: length_ft,
        width_ft: width_ft,
        thickness_inch: thickness_inch,
        date: new Date().toISOString().split('T')[0],
        total: (parseFloat(assigned_quantity) * parseFloat(price)).toFixed(2),
        invoice_number: invoice_number,
        builty_number: builty_number,
        existing_invoice_image: existing_invoice_image,
        existing_builty_image: existing_builty_image
    }];

    var formData = new FormData();
    formData.append('po_number', po_number);
    formData.append('jci_number', jci_number);
    formData.append('sell_order_number', sell_order_number);
    formData.append('bom_number', bom_number);
    formData.append('is_superadmin', isSuperAdmin);
    formData.append('items_json', JSON.stringify(items_to_save));

    var invoiceImageFile = targetRow.find('.invoiceImageInput')[0].files[0];
    var builtyImageFile = targetRow.find('.builtyImageInput')[0].files[0];
    
    if (invoiceImageFile) {
        formData.append('invoice_image_0', invoiceImageFile);
    }
    if (builtyImageFile) {
        formData.append('builty_image_0', builtyImageFile);
    }

    var saveBtn = targetRow.find('.saveRowBtn');
    saveBtn.prop('disabled', true).text('Saving...');

    $.ajax({
        url: 'ajax_save_individual_row.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            console.log('Save response:', response);
            if (response.success) {
                toastr.success('Row saved successfully!');
                
                // FORCE COMPLETE REFRESH
                setTimeout(function() {
                    console.log('Forcing complete refresh...');
                    // Clear all trackers
                    if (window.PURCHASE_MODULE) {
                        window.PURCHASE_MODULE.usedItems.clear();
                    }
                    
                    // Trigger JCI change to reload everything
                    var currentJci = $('#jci_number_search').val();
                    $('#jci_number_search').val('').trigger('change');
                    setTimeout(function() {
                        $('#jci_number_search').val(currentJci).trigger('change');
                    }, 100);
                }, 1000);
                
            } else {
                toastr.error(response.error || 'Save failed');
            }
        },
        error: function(xhr, status, error) {
            console.log('Save error:', error);
            toastr.error('Save failed: ' + error);
        },
        complete: function() {
            saveBtn.prop('disabled', false).text('Save');
        }
    });
}

// Override the global function
$(document).ready(function() {
    window.saveIndividualRow = saveIndividualRowFixed;
    console.log('Simple fix loaded - saveIndividualRow overridden');
});