// Temporary file to check syntax around line 1098
// The issue is likely in the matching debug section

if (existingItems && existingItems.length > 0) {
    existingItem = existingItems.find(function(pItem) {
        // ... matching logic ...
        
        console.log('Checking match with saved item:', {
            id: pItem.id,
            product_type: pItemProductType,
            product_name: pItemProductName,
            job_card: pItem.job_card_number,
            typeMatch: typeMatch,
            nameMatch: nameMatch,
            jobCardMatch: jobCardMatch
        });
        
        return typeMatch && nameMatch && jobCardMatch;
    });
    
    console.log('Match result:', existingItem ? 'FOUND ID: ' + existingItem.id : 'NOT FOUND');
}
console.log('=== END MATCHING DEBUG ===');

// The issue is likely missing closing brace after this section