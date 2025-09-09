// Final Fix for Wood Dimensions Matching Issue
console.log('Final Fix Loaded');

// Override the matching logic in ultimate_fix.js
$(document).ready(function() {
    // Wait for ultimate_fix to load first
    setTimeout(function() {
        console.log('=== FINAL FIX: Overriding matching logic ===');
        
        // Store original function
        window.originalFindMatchingPurchaseItem = window.findMatchingPurchaseItem;
        
        // Override with enhanced matching
        window.findMatchingPurchaseItem = function(bomItem, purchaseItems) {
            console.log('=== ENHANCED MATCHING START ===');
            console.log('Looking for BOM item:', bomItem);
            console.log('Available purchase items:', purchaseItems);
            
            for (var i = 0; i < purchaseItems.length; i++) {
                var purchaseItem = purchaseItems[i];
                console.log('Checking purchase item ' + i + ':', purchaseItem);
                
                // Basic matching
                var productMatch = bomItem.product_name === purchaseItem.product_name;
                var typeMatch = bomItem.product_type === purchaseItem.product_type || 
                               (bomItem.product_type === 'Wood' && purchaseItem.product_type === 'Wood Type');
                
                console.log('Product match:', productMatch, bomItem.product_name, '===', purchaseItem.product_name);
                console.log('Type match:', typeMatch, bomItem.product_type, '===', purchaseItem.product_type);
                
                if (productMatch && typeMatch) {
                    // For Wood items, check dimensions if available
                    if (bomItem.product_type === 'Wood' || purchaseItem.product_type === 'Wood' || purchaseItem.product_type === 'Wood Type') {
                        var dimensionsMatch = true;
                        
                        // Only check dimensions if both have them
                        if (bomItem.length_ft && bomItem.width_ft && bomItem.thickness_inch &&
                            purchaseItem.length_ft && purchaseItem.width_ft && purchaseItem.thickness_inch) {
                            
                            dimensionsMatch = (
                                parseFloat(bomItem.length_ft) === parseFloat(purchaseItem.length_ft) &&
                                parseFloat(bomItem.width_ft) === parseFloat(purchaseItem.width_ft) &&
                                parseFloat(bomItem.thickness_inch) === parseFloat(purchaseItem.thickness_inch)
                            );
                            
                            console.log('Dimensions check:', {
                                bom: {l: bomItem.length_ft, w: bomItem.width_ft, t: bomItem.thickness_inch},
                                purchase: {l: purchaseItem.length_ft, w: purchaseItem.width_ft, t: purchaseItem.thickness_inch},
                                match: dimensionsMatch
                            });
                        } else {
                            console.log('Dimensions not available for comparison, using basic match');
                        }
                        
                        if (dimensionsMatch) {
                            console.log('=== MATCH FOUND ===', purchaseItem);
                            return purchaseItem;
                        }
                    } else {
                        // Non-Wood items - basic match is enough
                        console.log('=== NON-WOOD MATCH FOUND ===', purchaseItem);
                        return purchaseItem;
                    }
                }
            }
            
            console.log('=== NO MATCH FOUND ===');
            return null;
        };
        
        console.log('Enhanced matching logic installed');
    }, 500);
});