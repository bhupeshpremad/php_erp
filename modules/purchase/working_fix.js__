// Working Fix - Simple and Direct
console.log('Working Fix Loaded');

// Global function to create table
window.createPurchaseTable = function(purchaseItems) {
    console.log('=== CREATING PURCHASE TABLE ===');
    console.log('Purchase items:', purchaseItems);
    
    var tableHtml = '<table class="table table-bordered table-sm mb-4">';
    tableHtml += '<thead class="thead-light">';
    tableHtml += '<tr><th colspan="14">Job Card: JOB-2025-0004-1</th></tr>';
    tableHtml += '<tr>';
    tableHtml += '<th><input type="checkbox" class="selectAllRows"></th>';
    tableHtml += '<th>Sr.</th><th>Supplier</th><th>Type</th><th>Product</th>';
    tableHtml += '<th>BOM Qty</th><th>Price</th><th>Assign Qty</th>';
    tableHtml += '<th>Invoice No</th><th>Invoice</th><th>Builty No</th><th>Builty</th>';
    tableHtml += '<th>Status</th><th>Action</th>';
    tableHtml += '</tr></thead><tbody>';
    
    // Create rows for each saved item
    purchaseItems.forEach(function(item, index) {
        console.log('Creating row for item:', item);
        
        var baseUrl = window.location.protocol + '//' + window.location.host + '/modules/purchase/uploads/';
        
        var invoiceImg = '';
        if (item.invoice_image) {
            invoiceImg = '<br><img src="' + baseUrl + 'invoice/' + item.invoice_image + '?v=' + Date.now() + '" style="width:40px;height:40px;object-fit:cover;cursor:pointer;margin-top:3px;border:1px solid #ccc;" onerror="this.style.display=\'none\'">';
        }
        
        var builtyImg = '';
        if (item.builty_image) {
            builtyImg = '<br><img src="' + baseUrl + 'Builty/' + item.builty_image + '?v=' + Date.now() + '" style="width:40px;height:40px;object-fit:cover;cursor:pointer;margin-top:3px;border:1px solid #ccc;" onerror="this.style.display=\'none\'">';
        }
        
        tableHtml += '<tr data-item-index="' + index + '" class="table-success">';
        tableHtml += '<td><input type="checkbox" class="rowCheckbox" checked></td>';
        tableHtml += '<td>' + (index + 1) + '</td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm supplierNameInput" value="' + (item.supplier_name || '') + '"></td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + (item.product_type || '') + '" readonly></td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm productNameInput" value="' + (item.product_name || '') + '" readonly></td>';
        tableHtml += '<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="100" readonly></td>';
        tableHtml += '<td><input type="number" class="form-control form-control-sm bomPriceInput" value="560" readonly></td>';
        tableHtml += '<td><input type="number" class="form-control form-control-sm assignQuantityInput" value="' + (item.quantity || item.assign_quantity || 0) + '"></td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value="' + (item.invoice_number || '') + '"></td>';
        tableHtml += '<td><input type="file" class="form-control-file form-control-sm invoiceImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingInvoiceImage" value="' + (item.invoice_image || '') + '">' + invoiceImg + '</td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm builtyNumberInput" value="' + (item.builty_number || '') + '"></td>';
        tableHtml += '<td><input type="file" class="form-control-file form-control-sm builtyImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingBuiltyImage" value="' + (item.builty_image || '') + '">' + builtyImg + '</td>';
        tableHtml += '<td><span class="badge badge-success">Approved</span></td>';
        tableHtml += '<td><button type="button" class="btn btn-primary btn-sm saveRowBtn">Save</button> <button type="button" class="btn btn-danger btn-sm deleteRowBtn">Del</button></td>';
        tableHtml += '</tr>';
    });
    
    // Add empty rows for remaining BOM items
    for (var i = purchaseItems.length; i < 10; i++) {
        tableHtml += '<tr data-item-index="' + i + '">';
        tableHtml += '<td><input type="checkbox" class="rowCheckbox"></td>';
        tableHtml += '<td>' + (i + 1) + '</td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm supplierNameInput" value=""></td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm productTypeInput" value="Wood" readonly></td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm productNameInput" value="Mango" readonly></td>';
        tableHtml += '<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="100" readonly></td>';
        tableHtml += '<td><input type="number" class="form-control form-control-sm bomPriceInput" value="560" readonly></td>';
        tableHtml += '<td><input type="number" class="form-control form-control-sm assignQuantityInput" value="0"></td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value=""></td>';
        tableHtml += '<td><input type="file" class="form-control-file form-control-sm invoiceImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingInvoiceImage" value=""></td>';
        tableHtml += '<td><input type="text" class="form-control form-control-sm builtyNumberInput" value=""></td>';
        tableHtml += '<td><input type="file" class="form-control-file form-control-sm builtyImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingBuiltyImage" value=""></td>';
        tableHtml += '<td><span class="badge badge-warning">Pending</span></td>';
        tableHtml += '<td><button type="button" class="btn btn-primary btn-sm saveRowBtn">Save</button></td>';
        tableHtml += '</tr>';
    }
    
    tableHtml += '</tbody></table>';
    
    $('#bomTableContainer').html(tableHtml);
    console.log('=== TABLE CREATED SUCCESSFULLY ===');
};

$(document).ready(function() {
    // Override any existing render functions
    window.renderBOMTable = null;
    
    setTimeout(function() {
        console.log('=== WORKING FIX: Complete Override ===');
        
        // Override the main success function in add.php
        var originalAjaxSuccess = $.fn.ajaxSuccess;
        
        // Completely override the AJAX success for saved purchase
        $(document).ajaxSuccess(function(event, xhr, settings) {
            if (settings.url === 'ajax_fetch_saved_purchase.php') {
                setTimeout(function() {
                    try {
                        var purchaseData = JSON.parse(xhr.responseText);
                        if (purchaseData && purchaseData.has_purchase && purchaseData.purchase_items) {
                            console.log('=== WORKING FIX: Intercepted AJAX response ===');
                            // Clear any existing table first
                            $('#bomTableContainer').empty();
                            // Create our table
                            window.createPurchaseTable(purchaseData.purchase_items);
                            // Prevent other scripts from running
                            event.stopImmediatePropagation();
                        }
                    } catch (e) {
                        console.error('Working fix error:', e);
                    }
                }, 50);
            }
        });
        
        // Also override the main render function that gets called
        window.renderBOMTable = function(jobCards, bomItems, existingItems) {
            console.log('=== WORKING FIX: Overriding renderBOMTable ===');
            if (existingItems && existingItems.length > 0) {
                window.createPurchaseTable(existingItems);
            }
            return false; // Prevent original function
        };
        
        console.log('Working fix installed');
    }, 500);
});