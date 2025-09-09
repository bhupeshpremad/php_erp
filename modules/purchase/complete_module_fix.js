// Complete module fix - replaces all existing functions
console.log('Loading complete module fix...');

// Global variables
window.usedItemTracker = new Set();
window.BASE_URL = window.location.origin + '/';

// Complete override of renderBomTable function
function renderBomTableFixed(jobCards, bomItemsData, existingItems) {
    console.log('=== COMPLETE MODULE FIX RENDER ===');
    console.log('Job Cards:', jobCards);
    console.log('BOM Items:', bomItemsData.length);
    console.log('Existing Items:', existingItems.length);
    
    // Clear container and reset tracker
    $('#bomTableContainer').empty();
    window.usedItemTracker.clear();
    
    jobCards.forEach(function(jobCard) {
        var table = $('<table class="table table-bordered table-sm mb-4"></table>');
        var thead = $('<thead class="thead-light"></thead>');
        var tbody = $('<tbody></tbody>');

        // Header
        var headerRow = $('<tr><th colspan="14">Job Card: ' + jobCard + '</th></tr>');
        thead.append(headerRow);

        // Column headers
        var colHeaderRow = $('<tr></tr>');
        colHeaderRow.append('<th><input type="checkbox" class="selectAllRows"></th>');
        colHeaderRow.append('<th>Sr. No.</th>'); 
        colHeaderRow.append('<th>Supplier Name</th>');
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

        // Process each BOM item
        bomItemsData.forEach(function(item, itemIndex) {
            var tr = $('<tr></tr>');
            
            // Find matching existing item (prevent duplicates)
            var existingItem = findUniqueMatch(item, existingItems, jobCard);
            
            // Extract data
            var supplierName = existingItem ? (existingItem.supplier_name || '').replace(/"/g, '&quot;') : '';
            var assignedQty = existingItem ? existingItem.assigned_quantity : '0';
            var isChecked = existingItem ? true : false;
            var invoiceNumber = existingItem ? (existingItem.invoice_number || '') : '';
            var builtyNumber = existingItem ? (existingItem.builty_number || '') : '';
            var invoiceImage = existingItem ? (existingItem.invoice_image || '') : '';
            var builtyImage = existingItem ? (existingItem.builty_image || '') : '';
            
            var isApproved = existingItem && invoiceNumber && invoiceImage;
            var isSuperAdmin = window.isSuperAdmin || false;
            var inputReadonly = (isApproved && !isSuperAdmin) ? 'readonly' : '';
            var inputDisabled = (isApproved && !isSuperAdmin) ? 'disabled' : '';

            // Build table row
            tr.append('<td><input type="checkbox" class="rowCheckbox" ' + (isChecked ? 'checked' : '') + ' ' + inputDisabled + '></td>');
            tr.append('<td>' + (itemIndex + 1) + '</td>');
            tr.append('<td><input type="text" class="form-control form-control-sm supplierNameInput" value="' + supplierName + '" ' + inputReadonly + '></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + item.product_type + '" readonly></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm productNameInput" value="' + item.product_name + '" readonly></td>');
            tr.append('<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + item.quantity + '" readonly></td>');
            tr.append('<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + item.price + '" readonly></td>');
            tr.append('<td><input type="number" min="0" max="' + item.quantity + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="' + assignedQty + '" ' + inputReadonly + '></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value="' + invoiceNumber + '" ' + inputReadonly + '></td>');
            
            // Invoice image with FIXED path
            var invoiceImageTd = '<input type="file" class="form-control-file form-control-sm invoiceImageInput" accept="image/*,application/pdf" ' + inputDisabled + '>';
            invoiceImageTd += '<input type="hidden" class="existingInvoiceImage" value="' + invoiceImage + '">';
            if (invoiceImage && invoiceImage.trim() !== '') {
                var invoiceUrl = window.BASE_URL + 'modules/purchase/uploads/invoice/' + invoiceImage + '?t=' + Date.now();
                invoiceImageTd += '<br><img src="' + invoiceUrl + '" class="invoiceImageThumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; margin-top: 5px; border: 1px solid #ddd;" title="Click to view/change" onerror="this.style.display=\'none\'">';
            }
            tr.append('<td>' + invoiceImageTd + '</td>');
            
            tr.append('<td><input type="text" class="form-control form-control-sm builtyNumberInput" value="' + builtyNumber + '" ' + inputReadonly + '></td>');
            
            // Builty image with FIXED path
            var builtyImageTd = '<input type="file" class="form-control-file form-control-sm builtyImageInput" accept="image/*,application/pdf" ' + inputDisabled + '>';
            builtyImageTd += '<input type="hidden" class="existingBuiltyImage" value="' + builtyImage + '">';
            if (builtyImage && builtyImage.trim() !== '') {
                var builtyUrl = window.BASE_URL + 'modules/purchase/uploads/Builty/' + builtyImage + '?t=' + Date.now();
                builtyImageTd += '<br><img src="' + builtyUrl + '" class="builtyImageThumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; margin-top: 5px; border: 1px solid #ddd;" title="Click to view/change" onerror="this.style.display=\'none\'">';
            }
            tr.append('<td>' + builtyImageTd + '</td>');
            
            // Status - ONLY ONE ROW SHOULD BE GREEN
            if (isApproved) {
                tr.addClass('table-success');
                tr.append('<td><span class="badge badge-success">Invoice Uploaded</span></td>');
            } else if (existingItem) {
                tr.addClass('table-success');
                tr.append('<td><span class="badge badge-info">Saved</span></td>');
            } else {
                tr.append('<td><span class="badge badge-warning">Pending</span></td>');
            }
            
            // Action buttons
            var actionTd = '<td>';
            actionTd += '<button type="button" class="btn btn-primary btn-sm saveRowBtn" ' + inputDisabled + '>Save</button>';
            if (supplierName && supplierName.trim() !== '') {
                actionTd += ' <button type="button" class="btn btn-danger btn-sm deleteRowBtn" data-supplier="' + supplierName + '" data-product="' + item.product_name + '" data-job-card="' + jobCard + '">Del</button>';
            }
            actionTd += '</td>';
            tr.append(actionTd);

            tbody.append(tr);
        });

        table.append(thead);
        table.append(tbody);
        $('#bomTableContainer').append(table);
    });
    
    // Attach all event handlers
    attachFixedEventHandlers();
    
    console.log('=== RENDER COMPLETE ===');
}

// Unique matching function to prevent duplicate green rows
function findUniqueMatch(bomItem, existingItems, jobCard) {
    if (!existingItems || existingItems.length === 0) return null;
    
    var match = existingItems.find(function(pItem) {
        // Skip already used items
        if (window.usedItemTracker.has(pItem.id)) return false;
        
        var typeMatch = String(pItem.product_type || '').trim() === String(bomItem.product_type || '').trim();
        var nameMatch = String(pItem.product_name || '').trim() === String(bomItem.product_name || '').trim();
        var jobMatch = String(pItem.job_card_number || '').trim() === String(jobCard || '').trim();
        
        if (!typeMatch || !nameMatch || !jobMatch) return false;
        
        // For Wood items, match dimensions
        if (bomItem.product_type === 'Wood') {
            var lengthMatch = Math.abs(parseFloat(pItem.length_ft || 0) - parseFloat(bomItem.length_ft || 0)) < 0.01;
            var widthMatch = Math.abs(parseFloat(pItem.width_ft || 0) - parseFloat(bomItem.width_ft || 0)) < 0.01;
            var thicknessMatch = Math.abs(parseFloat(pItem.thickness_inch || 0) - parseFloat(bomItem.thickness_inch || 0)) < 0.01;
            var quantityMatch = Math.abs(parseFloat(pItem.assigned_quantity || 0) - parseFloat(bomItem.quantity || 0)) < 0.001;
            var priceMatch = Math.abs(parseFloat(pItem.price || 0) - parseFloat(bomItem.price || 0)) < 0.01;
            
            return lengthMatch && widthMatch && thicknessMatch && quantityMatch && priceMatch;
        }
        
        // For non-Wood items
        var quantityMatch = Math.abs(parseFloat(pItem.assigned_quantity || 0) - parseFloat(bomItem.quantity || 0)) < 0.001;
        var priceMatch = Math.abs(parseFloat(pItem.price || 0) - parseFloat(bomItem.price || 0)) < 0.01;
        var hasSupplier = String(pItem.supplier_name || '').trim() !== '';
        
        return quantityMatch && priceMatch && hasSupplier;
    });
    
    // Mark as used if found
    if (match) {
        window.usedItemTracker.add(match.id);
        console.log('Matched item ID:', match.id, 'for', bomItem.product_name);
    }
    
    return match;
}

// Fixed event handlers
function attachFixedEventHandlers() {
    // Select all checkbox
    $('.selectAllRows').off('change').on('change', function() {
        var checked = $(this).is(':checked');
        $(this).closest('table').find('tbody .rowCheckbox:not(:disabled)').prop('checked', checked);
    });

    // Image click handlers
    $('#bomTableContainer').off('click', '.invoiceImageThumb').on('click', '.invoiceImageThumb', function() {
        if (window.isSuperAdmin) {
            $(this).siblings('.invoiceImageInput').trigger('click');
        }
    });

    $('#bomTableContainer').off('click', '.builtyImageThumb').on('click', '.builtyImageThumb', function() {
        if (window.isSuperAdmin) {
            $(this).siblings('.builtyImageInput').trigger('click');
        }
    });
    
    // Save button handler
    $('#bomTableContainer').off('click', '.saveRowBtn').on('click', '.saveRowBtn', function() {
        var row = $(this).closest('tr');
        var checkbox = row.find('.rowCheckbox');
        
        if (!checkbox.is(':checked')) {
            checkbox.prop('checked', true);
        }
        
        saveIndividualRow(row);
    });
    
    // Delete button handler
    $('#bomTableContainer').off('click', '.deleteRowBtn').on('click', '.deleteRowBtn', function() {
        var supplier = $(this).data('supplier');
        var product = $(this).data('product');
        var jobCard = $(this).data('job-card');
        var jciNumber = $('#jci_number').val();
        
        if (confirm('Delete saved data for: ' + supplier + ' - ' + product + '?')) {
            $.ajax({
                url: 'ajax_delete_row_by_details.php',
                method: 'POST',
                data: { 
                    supplier_name: supplier,
                    product_name: product,
                    job_card_number: jobCard,
                    jci_number: jciNumber
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        toastr.success('Deleted successfully!');
                        $('#jci_number_search').trigger('change');
                    } else {
                        toastr.error(response.error);
                    }
                },
                error: function() {
                    toastr.error('Error deleting data');
                }
            });
        }
    });
}

// Override the global function
$(document).ready(function() {
    // Replace renderBomTable globally
    window.renderBomTable = renderBomTableFixed;
    
    // Also replace enhanced version if it exists
    if (typeof window.renderEnhancedBomTable !== 'undefined') {
        window.renderEnhancedBomTable = renderBomTableFixed;
    }
    
    console.log('Complete module fix loaded successfully');
});