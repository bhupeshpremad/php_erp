// Clean Solution - Remove all conflicts and show saved data properly
console.log('Clean Solution Loaded');

$(document).ready(function() {
    // Wait for page to fully load
    setTimeout(function() {
        console.log('=== CLEAN SOLUTION: Taking complete control ===');
        
        // Disable all other scripts
        window.renderBOMTable = null;
        window.findMatchingPurchaseItem = null;
        
        // Create our own clean render function
        window.cleanRenderBOMTable = function(jobCards, bomItems, existingItems) {
            console.log('=== CLEAN RENDER START ===');
            console.log('Job Cards:', jobCards);
            console.log('BOM Items count:', bomItems.length);
            console.log('Existing Items count:', existingItems.length);
            
            // Log all existing items for debugging
            existingItems.forEach(function(item, index) {
                console.log('Existing Item ' + index + ':', {
                    id: item.id,
                    supplier: item.supplier_name,
                    product: item.product_name,
                    type: item.product_type,
                    quantity: item.quantity
                });
            });
            
            var tableHtml = '';
            var globalRowIndex = 0;
            
            jobCards.forEach(function(jobCard) {
                console.log('Processing Job Card:', jobCard);
                
                tableHtml += '<table class="table table-bordered table-sm mb-4">';
                tableHtml += '<thead class="thead-light">';
                tableHtml += '<tr><th colspan="14">Job Card: ' + jobCard + '</th></tr>';
                tableHtml += '<tr>';
                tableHtml += '<th><input type="checkbox" class="selectAllRows"></th>';
                tableHtml += '<th>Sr.</th><th>Supplier</th><th>Type</th><th>Product</th>';
                tableHtml += '<th>BOM Qty</th><th>Price</th><th>Assign Qty</th>';
                tableHtml += '<th>Invoice No</th><th>Invoice</th><th>Builty No</th><th>Builty</th>';
                tableHtml += '<th>Status</th><th>Action</th>';
                tableHtml += '</tr></thead><tbody>';
                
                // Process each BOM item for this job card
                bomItems.forEach(function(bomItem) {
                    if (bomItem.job_card_number === jobCard) {
                        console.log('Processing BOM Item:', bomItem.product_name);
                        
                        // Find saved items for this BOM item (simple matching by product name only)
                        var savedItems = existingItems.filter(function(saved) {
                            var match = saved.product_name === bomItem.product_name && 
                                       saved.job_card_number === jobCard;
                            if (match) {
                                console.log('Found saved item for', bomItem.product_name, ':', saved);
                            }
                            return match;
                        });
                        
                        if (savedItems.length > 0) {
                            // Show each saved item as a separate row
                            savedItems.forEach(function(savedItem) {
                                tableHtml += createSavedRow(savedItem, bomItem, globalRowIndex, jobCard);
                                globalRowIndex++;
                            });
                        } else {
                            // Show empty row for BOM item
                            tableHtml += createEmptyRow(bomItem, globalRowIndex, jobCard);
                            globalRowIndex++;
                        }
                    }
                });
                
                tableHtml += '</tbody></table>';
            });
            
            $('#bomTableContainer').html(tableHtml);
            console.log('=== CLEAN RENDER COMPLETE ===');
        };
        
        function createSavedRow(savedItem, bomItem, index, jobCard) {
            var baseUrl = window.location.protocol + '//' + window.location.host + '/modules/purchase/uploads/';
            
            var invoiceImg = '';
            if (savedItem.invoice_image) {
                invoiceImg = '<br><img src="' + baseUrl + 'invoice/' + savedItem.invoice_image + '?v=' + Date.now() + '" class="invoiceImageThumb" style="width:40px;height:40px;object-fit:cover;cursor:pointer;margin-top:3px;border:1px solid #ccc;" title="Click to change" onerror="this.style.display=\'none\'">';
            }
            
            var builtyImg = '';
            if (savedItem.builty_image) {
                builtyImg = '<br><img src="' + baseUrl + 'Builty/' + savedItem.builty_image + '?v=' + Date.now() + '" class="builtyImageThumb" style="width:40px;height:40px;object-fit:cover;cursor:pointer;margin-top:3px;border:1px solid #ccc;" title="Click to change" onerror="this.style.display=\'none\'">';
            }
            
            return '<tr data-item-index="' + index + '" class="table-success">' +
                '<td><input type="checkbox" class="rowCheckbox" checked></td>' +
                '<td>' + (index + 1) + '</td>' +
                '<td><input type="text" class="form-control form-control-sm supplierNameInput" value="' + (savedItem.supplier_name || '') + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + (bomItem.product_type || savedItem.product_type) + '" readonly></td>' +
                '<td><input type="text" class="form-control form-control-sm productNameInput" value="' + bomItem.product_name + '" readonly></td>' +
                '<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + bomItem.quantity + '" readonly></td>' +
                '<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + (bomItem.price || '0') + '" readonly></td>' +
                '<td><input type="number" min="0" max="' + bomItem.quantity + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="' + (savedItem.quantity || 0) + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value="' + (savedItem.invoice_number || '') + '"></td>' +
                '<td><input type="file" class="form-control-file form-control-sm invoiceImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingInvoiceImage" value="' + (savedItem.invoice_image || '') + '">' + invoiceImg + '</td>' +
                '<td><input type="text" class="form-control form-control-sm builtyNumberInput" value="' + (savedItem.builty_number || '') + '"></td>' +
                '<td><input type="file" class="form-control-file form-control-sm builtyImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingBuiltyImage" value="' + (savedItem.builty_image || '') + '">' + builtyImg + '</td>' +
                '<td><span class="badge badge-success">Approved</span></td>' +
                '<td><button type="button" class="btn btn-primary btn-sm saveRowBtn">Save</button> <button type="button" class="btn btn-danger btn-sm deleteRowBtn" data-supplier="' + savedItem.supplier_name + '" data-product="' + bomItem.product_name + '" data-job-card="' + jobCard + '">Del</button></td>' +
                '</tr>';
        }
        
        function createEmptyRow(bomItem, index, jobCard) {
            return '<tr data-item-index="' + index + '">' +
                '<td><input type="checkbox" class="rowCheckbox"></td>' +
                '<td>' + (index + 1) + '</td>' +
                '<td><input type="text" class="form-control form-control-sm supplierNameInput" value=""></td>' +
                '<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + bomItem.product_type + '" readonly></td>' +
                '<td><input type="text" class="form-control form-control-sm productNameInput" value="' + bomItem.product_name + '" readonly></td>' +
                '<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + bomItem.quantity + '" readonly></td>' +
                '<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + (bomItem.price || '0') + '" readonly></td>' +
                '<td><input type="number" min="0" max="' + bomItem.quantity + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="0"></td>' +
                '<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value=""></td>' +
                '<td><input type="file" class="form-control-file form-control-sm invoiceImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingInvoiceImage" value=""></td>' +
                '<td><input type="text" class="form-control form-control-sm builtyNumberInput" value=""></td>' +
                '<td><input type="file" class="form-control-file form-control-sm builtyImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingBuiltyImage" value=""></td>' +
                '<td><span class="badge badge-warning">Pending</span></td>' +
                '<td><button type="button" class="btn btn-primary btn-sm saveRowBtn">Save</button></td>' +
                '</tr>';
        }
        
        // Override the main success function to use our clean render
        var originalSuccess = window.originalAjaxSuccess;
        
        // Find and override the AJAX success function
        $(document).ajaxSuccess(function(event, xhr, settings) {
            if (settings.url === 'ajax_fetch_saved_purchase.php') {
                setTimeout(function() {
                    var purchaseData = JSON.parse(xhr.responseText);
                    if (purchaseData && purchaseData.has_purchase && window.currentBomData) {
                        console.log('=== CLEAN SOLUTION: Overriding render ===');
                        window.cleanRenderBOMTable(['JOB-2025-0004-1'], window.currentBomData, purchaseData.purchase_items);
                    }
                }, 100);
            }
        });
        
        console.log('Clean solution installed');
    }, 2000);
});