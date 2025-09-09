// ULTIMATE FIX - Final solution for all purchase module issues
console.log('Loading ULTIMATE FIX...');

// Global state management
window.PURCHASE_MODULE = {
    usedItems: new Set(),
    baseUrl: window.location.protocol + '//' + window.location.host + '/',
    initialized: false
};

// COMPLETE OVERRIDE - Replace all existing functions
function ultimateRenderBomTable(jobCards, bomItemsData, existingItems) {
    console.log('=== ULTIMATE RENDER START ===');
    console.log('Job Cards:', jobCards);
    console.log('BOM Items:', bomItemsData ? bomItemsData.length : 0);
    console.log('Existing Items:', existingItems ? existingItems.length : 0);
    
    // Reset state
    $('#bomTableContainer').empty();
    window.PURCHASE_MODULE.usedItems.clear();
    
    if (!jobCards || !bomItemsData) {
        console.log('No data to render');
        return;
    }
    
    jobCards.forEach(function(jobCard) {
        var table = $('<table class="table table-bordered table-sm mb-4"></table>');
        var thead = $('<thead class="thead-light"></thead>');
        var tbody = $('<tbody></tbody>');

        // Table header
        thead.append('<tr><th colspan="14">Job Card: ' + jobCard + '</th></tr>');
        
        // Column headers
        var headerRow = $('<tr></tr>');
        headerRow.append('<th><input type="checkbox" class="selectAllRows"></th>');
        headerRow.append('<th>Sr.</th>');
        headerRow.append('<th>Supplier</th>');
        headerRow.append('<th>Type</th>');
        headerRow.append('<th>Product</th>');
        headerRow.append('<th>BOM Qty</th>');
        headerRow.append('<th>Price</th>');
        headerRow.append('<th>Assign Qty</th>');
        headerRow.append('<th>Invoice No</th>');
        headerRow.append('<th>Invoice</th>');
        headerRow.append('<th>Builty No</th>');
        headerRow.append('<th>Builty</th>');
        headerRow.append('<th>Status</th>');
        headerRow.append('<th>Action</th>');
        thead.append(headerRow);

        // Process BOM items
        bomItemsData.forEach(function(item, index) {
            var tr = $('<tr data-item-index="' + index + '"></tr>');
            
            // Find unique matching item
            var existingItem = findUniqueExistingItem(item, existingItems, jobCard);
            
            var supplierName = existingItem ? (existingItem.supplier_name || '') : '';
            var assignedQty = existingItem ? (existingItem.assigned_quantity || '0') : '0';
            var invoiceNumber = existingItem ? (existingItem.invoice_number || '') : '';
            var builtyNumber = existingItem ? (existingItem.builty_number || '') : '';
            var invoiceImage = existingItem ? (existingItem.invoice_image || '') : '';
            var builtyImage = existingItem ? (existingItem.builty_image || '') : '';
            
            var isChecked = existingItem ? true : false;
            var isApproved = existingItem && invoiceNumber && invoiceImage;
            var isSuperAdmin = window.isSuperAdmin || false;
            
            var readonly = (isApproved && !isSuperAdmin) ? 'readonly' : '';
            var disabled = (isApproved && !isSuperAdmin) ? 'disabled' : '';

            // Build row cells
            tr.append('<td><input type="checkbox" class="rowCheckbox" ' + (isChecked ? 'checked' : '') + ' ' + disabled + '></td>');
            tr.append('<td>' + (index + 1) + '</td>');
            tr.append('<td><input type="text" class="form-control form-control-sm supplierNameInput" value="' + escapeHtml(supplierName) + '" ' + readonly + '></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + escapeHtml(item.product_type || '') + '" readonly></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm productNameInput" value="' + escapeHtml(item.product_name || '') + '" readonly></td>');
            tr.append('<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + (item.quantity || 0) + '" readonly></td>');
            tr.append('<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + (item.price || 0) + '" readonly></td>');
            tr.append('<td><input type="number" min="0" max="' + (item.quantity || 0) + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="' + assignedQty + '" ' + readonly + '></td>');
            tr.append('<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value="' + escapeHtml(invoiceNumber) + '" ' + readonly + '></td>');
            
            // Invoice image cell with FIXED path
            var invoiceCell = '<input type="file" class="form-control-file form-control-sm invoiceImageInput" accept="image/*,application/pdf" ' + disabled + '>';
            invoiceCell += '<input type="hidden" class="existingInvoiceImage" value="' + escapeHtml(invoiceImage) + '">';
            if (invoiceImage) {
                var invoiceUrl = window.PURCHASE_MODULE.baseUrl + 'modules/purchase/uploads/invoice/' + invoiceImage + '?v=' + Date.now();
                invoiceCell += '<br><img src="' + invoiceUrl + '" class="invoiceImageThumb" style="width:40px;height:40px;object-fit:cover;cursor:pointer;margin-top:3px;border:1px solid #ccc;" title="Click to change" onerror="this.style.display=\'none\'">';
            }
            tr.append('<td>' + invoiceCell + '</td>');
            
            tr.append('<td><input type="text" class="form-control form-control-sm builtyNumberInput" value="' + escapeHtml(builtyNumber) + '" ' + readonly + '></td>');
            
            // Builty image cell with FIXED path
            var builtyCell = '<input type="file" class="form-control-file form-control-sm builtyImageInput" accept="image/*,application/pdf" ' + disabled + '>';
            builtyCell += '<input type="hidden" class="existingBuiltyImage" value="' + escapeHtml(builtyImage) + '">';
            if (builtyImage) {
                var builtyUrl = window.PURCHASE_MODULE.baseUrl + 'modules/purchase/uploads/Builty/' + builtyImage + '?v=' + Date.now();
                builtyCell += '<br><img src="' + builtyUrl + '" class="builtyImageThumb" style="width:40px;height:40px;object-fit:cover;cursor:pointer;margin-top:3px;border:1px solid #ccc;" title="Click to change" onerror="this.style.display=\'none\'">';
            }
            tr.append('<td>' + builtyCell + '</td>');
            
            // Status - ONLY ONE ROW GREEN PER ITEM
            if (isApproved) {
                tr.addClass('table-success');
                tr.append('<td><span class="badge badge-success">Approved</span></td>');
            } else if (existingItem) {
                tr.addClass('table-success');
                tr.append('<td><span class="badge badge-info">Saved</span></td>');
            } else {
                tr.append('<td><span class="badge badge-warning">Pending</span></td>');
            }
            
            // Action buttons
            var actionCell = '<button type="button" class="btn btn-primary btn-sm saveRowBtn" ' + disabled + '>Save</button>';
            if (supplierName) {
                actionCell += ' <button type="button" class="btn btn-danger btn-sm deleteRowBtn" data-supplier="' + escapeHtml(supplierName) + '" data-product="' + escapeHtml(item.product_name || '') + '" data-job-card="' + escapeHtml(jobCard) + '">Del</button>';
            }
            tr.append('<td>' + actionCell + '</td>');

            tbody.append(tr);
        });

        table.append(thead).append(tbody);
        $('#bomTableContainer').append(table);
    });
    
    // Attach event handlers
    attachUltimateEventHandlers();
    
    console.log('=== ULTIMATE RENDER COMPLETE ===');
}

// Find unique existing item to prevent duplicate green rows
function findUniqueExistingItem(bomItem, existingItems, jobCard) {
    if (!existingItems || existingItems.length === 0) return null;
    
    var match = existingItems.find(function(pItem) {
        // Skip already used items
        if (window.PURCHASE_MODULE.usedItems.has(pItem.id)) return false;
        
        var typeMatch = String(pItem.product_type || '').trim() === String(bomItem.product_type || '').trim();
        var nameMatch = String(pItem.product_name || '').trim() === String(bomItem.product_name || '').trim();
        var jobMatch = String(pItem.job_card_number || '').trim() === String(jobCard || '').trim();
        
        if (!typeMatch || !nameMatch || !jobMatch) return false;
        
        // For Wood items - match dimensions
        if (bomItem.product_type === 'Wood') {
            var lengthMatch = Math.abs(parseFloat(pItem.length_ft || 0) - parseFloat(bomItem.length_ft || 0)) < 0.01;
            var widthMatch = Math.abs(parseFloat(pItem.width_ft || 0) - parseFloat(bomItem.width_ft || 0)) < 0.01;
            var thicknessMatch = Math.abs(parseFloat(pItem.thickness_inch || 0) - parseFloat(bomItem.thickness_inch || 0)) < 0.01;
            return lengthMatch && widthMatch && thicknessMatch;
        }
        
        // For other items - match quantity and price
        var qtyMatch = Math.abs(parseFloat(pItem.assigned_quantity || 0) - parseFloat(bomItem.quantity || 0)) < 0.001;
        var priceMatch = Math.abs(parseFloat(pItem.price || 0) - parseFloat(bomItem.price || 0)) < 0.01;
        return qtyMatch && priceMatch;
    });
    
    if (match) {
        window.PURCHASE_MODULE.usedItems.add(match.id);
        console.log('Matched and marked as used:', match.id);
    }
    
    return match;
}

// Attach all event handlers
function attachUltimateEventHandlers() {
    // Select all checkboxes
    $('.selectAllRows').off('change').on('change', function() {
        var checked = $(this).is(':checked');
        $(this).closest('table').find('.rowCheckbox:not(:disabled)').prop('checked', checked);
    });

    // Image click handlers
    $('#bomTableContainer').off('click', '.invoiceImageThumb').on('click', '.invoiceImageThumb', function() {
        if (window.isSuperAdmin) {
            $(this).siblings('.invoiceImageInput').click();
        }
    });

    $('#bomTableContainer').off('click', '.builtyImageThumb').on('click', '.builtyImageThumb', function() {
        if (window.isSuperAdmin) {
            $(this).siblings('.builtyImageInput').click();
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
        
        if (confirm('Delete: ' + supplier + ' - ' + product + '?')) {
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
                        toastr.success('Deleted!');
                        // Clear the used items tracker and reload
                        window.PURCHASE_MODULE.usedItems.clear();
                        $('#jci_number_search').trigger('change');
                    } else {
                        toastr.error(response.error);
                    }
                },
                error: function() {
                    toastr.error('Delete failed');
                }
            });
        }
    });
}

// Utility function
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text || '').replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Function to refresh purchase data after save
function refreshPurchaseData() {
    var jciNumber = $('#jci_number').val();
    if (!jciNumber) return;
    
    $.ajax({
        url: 'ajax_fetch_saved_purchase.php',
        method: 'POST',
        data: { jci_number: jciNumber },
        dataType: 'json',
        success: function(purchaseData) {
            if (purchaseData.has_purchase && purchaseData.purchase_items) {
                window.existingPurchaseItems = purchaseData.purchase_items;
                console.log('Refreshed existingPurchaseItems:', window.existingPurchaseItems);
            }
        },
        error: function() {
            console.log('Error refreshing purchase data');
        }
    });
}

// Initialize on document ready
$(document).ready(function() {
    if (!window.PURCHASE_MODULE.initialized) {
        // Override all render functions
        window.renderBomTable = ultimateRenderBomTable;
        window.renderEnhancedBomTable = ultimateRenderBomTable;
        
        // Add global refresh function
        window.refreshPurchaseData = refreshPurchaseData;
        
        window.PURCHASE_MODULE.initialized = true;
        console.log('ULTIMATE FIX initialized successfully');
    }
});