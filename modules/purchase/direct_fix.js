// Direct Fix - Disable all complex matching, show all saved rows
console.log('Direct Fix Loaded - Showing ALL saved rows');

$(document).ready(function() {
    setTimeout(function() {
        console.log('=== DIRECT FIX: Overriding ALL matching logic ===');
        
        // Override the main render function to show ALL saved items
        window.renderBOMTable = function(jobCards, bomItems, existingItems) {
            console.log('=== DIRECT RENDER START ===');
            console.log('Job Cards:', jobCards);
            console.log('BOM Items:', bomItems.length);
            console.log('Existing Items:', existingItems.length);
            
            var tableHtml = '';
            
            jobCards.forEach(function(jobCard) {
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
                
                var rowIndex = 0;
                
                bomItems.forEach(function(bomItem) {
                    if (bomItem.job_card_number === jobCard) {
                        // Find ALL matching saved items for this BOM item
                        var matchingItems = existingItems.filter(function(existingItem) {
                            return existingItem.product_name === bomItem.product_name && 
                                   existingItem.job_card_number === jobCard;
                        });
                        
                        console.log('BOM Item:', bomItem.product_name, 'Found matches:', matchingItems.length);
                        
                        if (matchingItems.length > 0) {
                            // Show ALL matching saved items
                            matchingItems.forEach(function(savedItem) {
                                tableHtml += createSavedRowHtml(savedItem, bomItem, rowIndex, jobCard);
                                rowIndex++;
                            });
                        } else {
                            // Show empty row for BOM item
                            tableHtml += createEmptyRowHtml(bomItem, rowIndex, jobCard);
                            rowIndex++;
                        }
                    }
                });
                
                tableHtml += '</tbody></table>';
            });
            
            $('#bomTableContainer').html(tableHtml);
            console.log('=== DIRECT RENDER COMPLETE ===');
        };
        
        // Helper function to create saved row HTML
        function createSavedRowHtml(savedItem, bomItem, index, jobCard) {
            var baseUrl = window.location.origin + '/modules/purchase/uploads/';
            var invoiceImg = savedItem.invoice_image ? 
                '<br><img src="' + baseUrl + 'invoice/' + savedItem.invoice_image + '?v=' + Date.now() + '" class="invoiceImageThumb" style="width:40px;height:40px;object-fit:cover;cursor:pointer;margin-top:3px;border:1px solid #ccc;" title="Click to change" onerror="this.style.display=\'none\'">' : '';
            var builtyImg = savedItem.builty_image ? 
                '<br><img src="' + baseUrl + 'Builty/' + savedItem.builty_image + '?v=' + Date.now() + '" class="builtyImageThumb" style="width:40px;height:40px;object-fit:cover;cursor:pointer;margin-top:3px;border:1px solid #ccc;" title="Click to change" onerror="this.style.display=\'none\'">' : '';
            
            return '<tr data-item-index="' + index + '" class="table-success">' +
                '<td><input type="checkbox" class="rowCheckbox" checked></td>' +
                '<td>' + (index + 1) + '</td>' +
                '<td><input type="text" class="form-control form-control-sm supplierNameInput" value="' + (savedItem.supplier_name || '') + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + bomItem.product_type + '" readonly></td>' +
                '<td><input type="text" class="form-control form-control-sm productNameInput" value="' + bomItem.product_name + '" readonly></td>' +
                '<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + bomItem.quantity + '" readonly></td>' +
                '<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + bomItem.price + '" readonly></td>' +
                '<td><input type="number" min="0" max="' + bomItem.quantity + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="' + (savedItem.quantity || 0) + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value="' + (savedItem.invoice_number || '') + '"></td>' +
                '<td><input type="file" class="form-control-file form-control-sm invoiceImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingInvoiceImage" value="' + (savedItem.invoice_image || '') + '">' + invoiceImg + '</td>' +
                '<td><input type="text" class="form-control form-control-sm builtyNumberInput" value="' + (savedItem.builty_number || '') + '"></td>' +
                '<td><input type="file" class="form-control-file form-control-sm builtyImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingBuiltyImage" value="' + (savedItem.builty_image || '') + '">' + builtyImg + '</td>' +
                '<td><span class="badge badge-success">Approved</span></td>' +
                '<td><button type="button" class="btn btn-primary btn-sm saveRowBtn">Save</button> <button type="button" class="btn btn-danger btn-sm deleteRowBtn" data-supplier="' + savedItem.supplier_name + '" data-product="' + bomItem.product_name + '" data-job-card="' + jobCard + '">Del</button></td>' +
                '</tr>';
        }
        
        // Helper function to create empty row HTML
        function createEmptyRowHtml(bomItem, index, jobCard) {
            return '<tr data-item-index="' + index + '">' +
                '<td><input type="checkbox" class="rowCheckbox"></td>' +
                '<td>' + (index + 1) + '</td>' +
                '<td><input type="text" class="form-control form-control-sm supplierNameInput" value=""></td>' +
                '<td><input type="text" class="form-control form-control-sm productTypeInput" value="' + bomItem.product_type + '" readonly></td>' +
                '<td><input type="text" class="form-control form-control-sm productNameInput" value="' + bomItem.product_name + '" readonly></td>' +
                '<td><input type="number" class="form-control form-control-sm bomQuantityInput" value="' + bomItem.quantity + '" readonly></td>' +
                '<td><input type="number" class="form-control form-control-sm bomPriceInput" value="' + bomItem.price + '" readonly></td>' +
                '<td><input type="number" min="0" max="' + bomItem.quantity + '" step="0.001" class="form-control form-control-sm assignQuantityInput" value="0"></td>' +
                '<td><input type="text" class="form-control form-control-sm invoiceNumberInput" value=""></td>' +
                '<td><input type="file" class="form-control-file form-control-sm invoiceImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingInvoiceImage" value=""></td>' +
                '<td><input type="text" class="form-control form-control-sm builtyNumberInput" value=""></td>' +
                '<td><input type="file" class="form-control-file form-control-sm builtyImageInput" accept="image/*,application/pdf"><input type="hidden" class="existingBuiltyImage" value=""></td>' +
                '<td><span class="badge badge-warning">Pending</span></td>' +
                '<td><button type="button" class="btn btn-primary btn-sm saveRowBtn">Save</button></td>' +
                '</tr>';
        }
        
        console.log('Direct fix installed - ALL saved rows will be shown');
    }, 1000);
});