// Quick fix for image path and duplicate rows
$(document).ready(function() {
    // Fix image paths after page load
    setTimeout(function() {
        $('.invoiceImageThumb, .builtyImageThumb').each(function() {
            var src = $(this).attr('src');
            if (src && src.includes('<?php')) {
                // Fix broken PHP tags in image src
                var imageName = src.split('/').pop().split('?')[0];
                var imageType = $(this).hasClass('invoiceImageThumb') ? 'invoice' : 'Builty';
                var newSrc = window.location.origin + '/modules/purchase/uploads/' + imageType + '/' + imageName + '?t=' + new Date().getTime();
                $(this).attr('src', newSrc);
            }
        });
    }, 1000);
    
    // Override renderBomTable to fix duplicate green rows
    window.originalRenderBomTable = window.renderBomTable;
    window.renderBomTable = function(jobCards, bomItemsData, existingItems) {
        // Track used items to prevent duplicates
        var usedItemIds = new Set();
        
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
                
                // Find matching existing item (only unused ones)
                var existingItem = null;
                if (existingItems && existingItems.length > 0) {
                    existingItem = existingItems.find(function(pItem) {
                        if (usedItemIds.has(pItem.id)) return false;
                        
                        var typeMatch = pItem.product_type === item.product_type;
                        var nameMatch = pItem.product_name === item.product_name;
                        var jobMatch = pItem.job_card_number === jobCard;
                        
                        if (item.product_type === 'Wood' && typeMatch && nameMatch && jobMatch) {
                            var lengthMatch = Math.abs(parseFloat(pItem.length_ft || 0) - parseFloat(item.length_ft || 0)) < 0.01;
                            var widthMatch = Math.abs(parseFloat(pItem.width_ft || 0) - parseFloat(item.width_ft || 0)) < 0.01;
                            var thicknessMatch = Math.abs(parseFloat(pItem.thickness_inch || 0) - parseFloat(item.thickness_inch || 0)) < 0.01;
                            return lengthMatch && widthMatch && thicknessMatch;
                        }
                        
                        return typeMatch && nameMatch && jobMatch;
                    });
                    
                    if (existingItem) {
                        usedItemIds.add(existingItem.id);
                    }
                }
                
                var supplierName = existingItem ? (existingItem.supplier_name || '') : '';
                var assignedQty = existingItem ? existingItem.assigned_quantity : '0';
                var isChecked = existingItem ? true : false;
                var invoiceNumber = existingItem ? (existingItem.invoice_number || '') : '';
                var builtyNumber = existingItem ? (existingItem.builty_number || '') : '';
                var invoiceImage = existingItem ? (existingItem.invoice_image || '') : '';
                var builtyImage = existingItem ? (existingItem.builty_image || '') : '';
                
                var isApproved = existingItem && invoiceNumber && invoiceImage;
                var inputReadonly = isApproved ? 'readonly' : '';
                var inputDisabled = isApproved ? 'disabled' : '';

                // Build row
                tr.append('<td><input type="checkbox" class="rowCheckbox" ' + (isChecked ? 'checked' : '') + ' ' + inputDisabled + '></td>');
                tr.append('<td>' + (itemIndex + 1) + '</td>');
                tr.append('<td><input type="text" class="form-control form-control-sm supplierNameInput" value="' + supplierName + '" ' + inputReadonly + '></td>');
                tr.append('<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + item.product_type + '" readonly></td>');
                tr.append('<td><input type="text" class="form-control form-control-sm productNameInput" value="' + item.product_name + '" readonly></td>');
                tr.append('<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + item.quantity + '" readonly></td>');
                tr.append('<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + item.price + '" readonly></td>');
                tr.append('<td><input type="number" min="0" max="' + item.quantity + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="' + assignedQty + '" ' + inputReadonly + '></td>');
                tr.append('<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value="' + invoiceNumber + '" ' + inputReadonly + '></td>');
                
                // Invoice image with fixed path
                var invoiceImageTd = '<input type="file" class="form-control-file form-control-sm invoiceImageInput" ' + inputDisabled + '>';
                invoiceImageTd += '<input type="hidden" class="existingInvoiceImage" value="' + invoiceImage + '">';
                if (invoiceImage) {
                    var invoiceImageUrl = window.location.origin + '/modules/purchase/uploads/invoice/' + invoiceImage + '?t=' + new Date().getTime();
                    invoiceImageTd += '<br><img src="' + invoiceImageUrl + '" class="invoiceImageThumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; margin-top: 5px;" title="Click to view/change">';
                }
                tr.append('<td>' + invoiceImageTd + '</td>');
                
                tr.append('<td><input type="text" class="form-control form-control-sm builtyNumberInput" value="' + builtyNumber + '" ' + inputReadonly + '></td>');
                
                // Builty image with fixed path
                var builtyImageTd = '<input type="file" class="form-control-file form-control-sm builtyImageInput" ' + inputDisabled + '>';
                builtyImageTd += '<input type="hidden" class="existingBuiltyImage" value="' + builtyImage + '">';
                if (builtyImage) {
                    var builtyImageUrl = window.location.origin + '/modules/purchase/uploads/Builty/' + builtyImage + '?t=' + new Date().getTime();
                    builtyImageTd += '<br><img src="' + builtyImageUrl + '" class="builtyImageThumb" style="width: 50px; height: 50px; object-fit: cover; cursor: pointer; margin-top: 5px;" title="Click to view/change">';
                }
                tr.append('<td>' + builtyImageTd + '</td>');
                
                // Status
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
                if (supplierName) {
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
        $('.selectAllRows').off('change').on('change', function() {
            var checked = $(this).is(':checked');
            $(this).closest('table').find('tbody .rowCheckbox:not(:disabled)').prop('checked', checked);
        });

        $('#bomTableContainer').off('click', '.saveRowBtn').on('click', '.saveRowBtn', function() {
            var row = $(this).closest('tr');
            var checkbox = row.find('.rowCheckbox');
            if (!checkbox.is(':checked')) {
                checkbox.prop('checked', true);
            }
            saveIndividualRow(row);
        });
        
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
    };
});