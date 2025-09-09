// Complete fix for purchase data display issues
// This script addresses all matching and display problems

// Enhanced matching function for precise row identification
function findExactMatch(bomItem, existingItems, jobCard) {
    console.log('=== ENHANCED MATCHING DEBUG ===');
    console.log('BOM Item:', bomItem);
    console.log('Job Card:', jobCard);
    console.log('Existing Items Count:', existingItems.length);
    
    if (!existingItems || existingItems.length === 0) {
        console.log('No existing items to match');
        return null;
    }
    
    // Try multiple matching strategies in order of precision
    var matchingStrategies = [
        // Strategy 1: Exact match with all criteria including dimensions for Wood
        function(bomItem, existingItems, jobCard) {
            return existingItems.find(function(pItem) {
                var typeMatch = String(pItem.product_type || '').trim() === String(bomItem.product_type || '').trim();
                var nameMatch = String(pItem.product_name || '').trim() === String(bomItem.product_name || '').trim();
                var jobMatch = String(pItem.job_card_number || '').trim() === String(jobCard || '').trim();
                
                if (!typeMatch || !nameMatch || !jobMatch) {
                    return false;
                }
                
                // For Wood items, match dimensions
                if (bomItem.product_type === 'Wood') {
                    var lengthMatch = Math.abs(parseFloat(pItem.length_ft || 0) - parseFloat(bomItem.length_ft || 0)) < 0.01;
                    var widthMatch = Math.abs(parseFloat(pItem.width_ft || 0) - parseFloat(bomItem.width_ft || 0)) < 0.01;
                    var thicknessMatch = Math.abs(parseFloat(pItem.thickness_inch || 0) - parseFloat(bomItem.thickness_inch || 0)) < 0.01;
                    var quantityMatch = Math.abs(parseFloat(pItem.assigned_quantity || 0) - parseFloat(bomItem.quantity || 0)) < 0.001;
                    var priceMatch = Math.abs(parseFloat(pItem.price || 0) - parseFloat(bomItem.price || 0)) < 0.01;
                    
                    console.log('Wood matching details:', {
                        lengthMatch: lengthMatch,
                        widthMatch: widthMatch,
                        thicknessMatch: thicknessMatch,
                        quantityMatch: quantityMatch,
                        priceMatch: priceMatch,
                        pItem_dims: [pItem.length_ft, pItem.width_ft, pItem.thickness_inch],
                        bomItem_dims: [bomItem.length_ft, bomItem.width_ft, bomItem.thickness_inch]
                    });
                    
                    return lengthMatch && widthMatch && thicknessMatch && quantityMatch && priceMatch;
                }
                
                // For non-Wood items, match quantity and price
                var quantityMatch = Math.abs(parseFloat(pItem.assigned_quantity || 0) - parseFloat(bomItem.quantity || 0)) < 0.001;
                var priceMatch = Math.abs(parseFloat(pItem.price || 0) - parseFloat(bomItem.price || 0)) < 0.01;
                
                return quantityMatch && priceMatch;
            });
        },
        
        // Strategy 2: Match by supplier name (for items with supplier assigned)
        function(bomItem, existingItems, jobCard) {
            return existingItems.find(function(pItem) {
                var typeMatch = String(pItem.product_type || '').trim() === String(bomItem.product_type || '').trim();
                var nameMatch = String(pItem.product_name || '').trim() === String(bomItem.product_name || '').trim();
                var jobMatch = String(pItem.job_card_number || '').trim() === String(jobCard || '').trim();
                var hasSupplier = String(pItem.supplier_name || '').trim() !== '';
                
                return typeMatch && nameMatch && jobMatch && hasSupplier;
            });
        },
        
        // Strategy 3: Basic type and name match
        function(bomItem, existingItems, jobCard) {
            return existingItems.find(function(pItem) {
                var typeMatch = String(pItem.product_type || '').trim() === String(bomItem.product_type || '').trim();
                var nameMatch = String(pItem.product_name || '').trim() === String(bomItem.product_name || '').trim();
                var jobMatch = String(pItem.job_card_number || '').trim() === String(jobCard || '').trim();
                
                return typeMatch && nameMatch && jobMatch;
            });
        }
    ];
    
    // Try each strategy until we find a match
    for (var i = 0; i < matchingStrategies.length; i++) {
        var match = matchingStrategies[i](bomItem, existingItems, jobCard);
        if (match) {
            console.log('Match found using strategy', i + 1, ':', match);
            return match;
        }
    }
    
    console.log('No match found for BOM item');
    return null;
}

// Enhanced render function with better data handling
function renderEnhancedBomTable(jobCards, bomItemsData, existingItems) {
    console.log('=== ENHANCED RENDER START ===');
    console.log('Job Cards:', jobCards);
    console.log('BOM Items:', bomItemsData.length);
    console.log('Existing Items:', existingItems.length);
    
    $('#bomTableContainer').empty();
    
    jobCards.forEach(function(jobCard) {
        var table = $('<table class="table table-bordered table-sm mb-4"></table>');
        var thead = $('<thead class="thead-light"></thead>');
        var tbody = $('<tbody></tbody>');

        var headerRow = $('<tr><th colspan="14">Job Card: ' + jobCard + '</th></tr>');
        thead.append(headerRow);

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

        bomItemsData.forEach(function(item, itemIndex) {
            var tr = $('<tr></tr>');
            
            // Use enhanced matching
            var existingItem = findExactMatch(item, existingItems, jobCard);
            
            var supplierName = existingItem ? (existingItem.supplier_name || '').toString().replace(/"/g, '&quot;') : '';
            var assignedQty = existingItem ? existingItem.assigned_quantity : '0';
            var isChecked = existingItem ? true : false;
            
            // Handle invoice/builty data with uniqueness check
            var invoiceNumber = '';
            var builtyNumber = '';
            var invoiceImage = '';
            var builtyImage = '';
            
            if (existingItem) {
                // Check for duplicate invoice display
                var sameInvoiceCount = existingItems.filter(function(pItem) {
                    return pItem.invoice_number === existingItem.invoice_number && 
                           pItem.invoice_number !== '' && 
                           pItem.job_card_number === jobCard;
                }).length;
                
                // Only show invoice/builty if unique or first occurrence
                if (sameInvoiceCount === 1 || existingItems.indexOf(existingItem) === existingItems.findIndex(function(pItem) {
                    return pItem.invoice_number === existingItem.invoice_number && 
                           pItem.invoice_number !== '' && 
                           pItem.job_card_number === jobCard;
                })) {
                    invoiceNumber = (existingItem.invoice_number || '').toString().trim();
                    builtyNumber = (existingItem.builty_number || '').toString().trim();
                    invoiceImage = (existingItem.invoice_image || '').toString().trim();
                    builtyImage = (existingItem.builty_image || '').toString().trim();
                }
            }
            
            var isApproved = existingItem && invoiceNumber && invoiceImage ? true : false;
            var isSuperAdmin = window.isSuperAdmin || false;

            var inputReadonly = (isApproved && !isSuperAdmin) ? 'readonly' : '';
            var inputDisabled = (isApproved && !isSuperAdmin) ? 'disabled' : '';

            // Build row with proper data
            tr.append('<td><input type="checkbox" class="rowCheckbox" ' + (isChecked ? 'checked' : '') + ' ' + inputDisabled + '></td>');
            tr.append('<td>' + (itemIndex + 1) + '</td>');
            tr.append('<td><input type="text" class="form-control form-control-sm supplierNameInput" value="' + supplierName + '" ' + inputReadonly + '></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + item.product_type + '" readonly></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm productNameInput" value="' + item.product_name + '" readonly></td>');
            tr.append('<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + item.quantity + '" readonly></td>');
            tr.append('<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + item.price + '" readonly></td>');
            tr.append('<td><input type="number" min="0" max="' + item.quantity + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="' + assignedQty + '" ' + inputReadonly + '></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value="' + invoiceNumber + '" ' + inputReadonly + '></td>');
            
            // Invoice image with proper display
            var invoiceImageTd = '<input type="file" class="form-control-file form-control-sm invoiceImageInput" ' + inputDisabled + '>';
            invoiceImageTd += '<input type="hidden" class="existingInvoiceImage" value="' + invoiceImage + '">';
            if (invoiceImage && invoiceImage.trim() !== '') {
                invoiceImageTd += '<br><img src="<?php echo BASE_URL; ?>modules/purchase/uploads/invoice/' + invoiceImage + '?t=' + new Date().getTime() + '" class="invoiceImageThumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; margin-top: 5px; border: 1px solid #ddd;" title="Click to view/change">';
            }
            tr.append('<td>' + invoiceImageTd + '</td>');
            
            tr.append('<td><input type="text" class="form-control form-control-sm builtyNumberInput" value="' + builtyNumber + '" ' + inputReadonly + '></td>');
            
            // Builty image with proper display
            var builtyImageTd = '<input type="file" class="form-control-file form-control-sm builtyImageInput" ' + inputDisabled + '>';
            builtyImageTd += '<input type="hidden" class="existingBuiltyImage" value="' + builtyImage + '">';
            if (builtyImage && builtyImage.trim() !== '') {
                builtyImageTd += '<br><img src="<?php echo BASE_URL; ?>modules/purchase/uploads/Builty/' + builtyImage + '?t=' + new Date().getTime() + '" class="builtyImageThumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; margin-top: 5px; border: 1px solid #ddd;" title="Click to view/change">';
            }
            tr.append('<td>' + builtyImageTd + '</td>');
            
            // Status
            if (isApproved) {
                tr.addClass('table-success');
                tr.append('<td><span class="badge badge-success">Invoice Uploaded</span></td>');
            } else if (existingItem) {
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
    
    // Attach event handlers
    attachEnhancedEventHandlers();
    
    console.log('=== ENHANCED RENDER COMPLETE ===');
}

// Enhanced event handlers
function attachEnhancedEventHandlers() {
    // Select all checkbox handler
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
        
        var confirmMsg = 'Delete saved data for:\nSupplier: ' + supplier + '\nProduct: ' + product + '\n\nThis will clear the row data.';
        
        if (confirm(confirmMsg)) {
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
                        toastr.success('Saved data deleted successfully!');
                        $('#jci_number_search').trigger('change');
                    } else {
                        toastr.error(response.error);
                    }
                },
                error: function() {
                    toastr.error('Error deleting saved data');
                }
            });
        }
    });
}

// Replace the original renderBomTable function
if (typeof window.renderBomTable === 'undefined') {
    window.renderBomTable = renderEnhancedBomTable;
}