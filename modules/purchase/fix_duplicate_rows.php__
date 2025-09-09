<?php
// Fix for duplicate rows in Job Card BOM display
// The issue is in the renderBomTable function where same invoice/builty data 
// is being repeated across multiple BOM items

// Problem Analysis:
// 1. BOM-2025-0004 has 10 items (1 Glow, 1 Hardware, 8 Wood items)
// 2. Purchase system creates separate entries for each BOM item
// 3. Same supplier "ASHAPURNA ENTERPRISES" with same invoice "AE/2526/196" 
//    and builty "733" is being shown for multiple wood items
// 4. This creates confusion as it looks like duplicate data

// Solution: Update the display logic to properly handle unique BOM items
// and avoid showing same invoice/builty for multiple items unless they are 
// actually separate purchases

echo "Issue identified in add.php renderBomTable function\n";
echo "Multiple BOM items from same supplier showing same invoice/builty numbers\n";
echo "This is causing visual duplication in the Job Card display\n";
echo "\nRecommended fixes:\n";
echo "1. Group BOM items by supplier when displaying\n";
echo "2. Show invoice/builty only once per supplier group\n";
echo "3. Or clearly indicate when same invoice covers multiple items\n";
?>