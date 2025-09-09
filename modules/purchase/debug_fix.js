// Debug Fix for Second Row Display Issue
console.log('Debug Fix Loaded');

// Override the saveIndividualRow function with enhanced debugging
window.saveIndividualRow = function(button) {
    console.log('=== SAVE INDIVIDUAL ROW DEBUG START ===');
    
    var row = $(button).closest('tr');
    var rowIndex = row.index();
    console.log('Row Index:', rowIndex);
    
    // Get all form data
    var formData = {
        jci_number: $('#jci_number').val(),
        po_number: $('#po_number').val(),
        sell_order_number: $('#sell_order_number').val()
    };
    
    console.log('Form Data:', formData);
    
    // Get row data
    var rowData = {
        supplier_name: row.find('select[name="supplier_name[]"]').val(),
        product_type: row.find('input[name="product_type[]"]').val(),
        product_name: row.find('input[name="product_name[]"]').val(),
        quantity: row.find('input[name="quantity[]"]').val(),
        rate: row.find('input[name="rate[]"]').val(),
        amount: row.find('input[name="amount[]"]').val()
    };
    
    console.log('Row Data:', rowData);
    
    // Add Wood dimensions if product type is Wood
    if (rowData.product_type === 'Wood' && window.currentBomData) {
        var bomItem = window.currentBomData.find(item => 
            item.product_type === 'Wood' && 
            item.product_name === rowData.product_name
        );
        
        if (bomItem) {
            rowData.length_ft = bomItem.length_ft;
            rowData.width_ft = bomItem.width_ft;
            rowData.thickness_inch = bomItem.thickness_inch;
            console.log('Added Wood dimensions:', {
                length_ft: rowData.length_ft,
                width_ft: rowData.width_ft,
                thickness_inch: rowData.thickness_inch
            });
        }
    }
    
    // Validation
    if (!rowData.supplier_name || !rowData.quantity || !rowData.rate) {
        toastr.error('Please fill all required fields');
        return;
    }
    
    // Show loading
    $(button).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
    
    // Combine form and row data
    var saveData = Object.assign({}, formData, rowData);
    console.log('Final Save Data:', saveData);
    
    $.ajax({
        url: 'ajax_save_individual_row.php',
        method: 'POST',
        data: saveData,
        dataType: 'json',
        success: function(response) {
            console.log('Save Response:', response);
            
            $(button).prop('disabled', false).html('Save');
            
            if (response.success) {
                toastr.success('Row saved successfully!');
                
                // Force complete refresh after 1 second
                setTimeout(function() {
                    console.log('=== FORCING COMPLETE REFRESH ===');
                    
                    // Clear all global variables
                    window.currentBomData = null;
                    window.existingPurchaseItems = [];
                    
                    // Get current JCI value
                    var currentJci = $('#jci_number_search').val();
                    console.log('Current JCI for refresh:', currentJci);
                    
                    if (currentJci) {
                        // Clear container first
                        $('#bomTableContainer').empty();
                        
                        // Trigger change event to reload everything
                        $('#jci_number_search').trigger('change');
                        
                        console.log('Complete refresh triggered');
                    }
                }, 1000);
                
            } else {
                toastr.error(response.message || 'Failed to save row');
            }
        },
        error: function(xhr, status, error) {
            console.error('Save Error:', error);
            $(button).prop('disabled', false).html('Save');
            toastr.error('Error saving row: ' + error);
        }
    });
    
    console.log('=== SAVE INDIVIDUAL ROW DEBUG END ===');
};