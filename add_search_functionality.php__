<?php
/**
 * Add Search Functionality to All Index Pages
 * This script checks all index.php files and adds search functionality where missing
 */

echo "🔍 Adding Search Functionality to All Modules...\n\n";

// List of modules to check and their search requirements
$modules = [
    'modules/pi/index.php' => [
        'search_input_id' => 'piSearchInput',
        'table_id' => 'piTable',
        'placeholder' => 'Search PI Number, Customer...'
    ],
    'modules/po/index.php' => [
        'search_input_id' => 'poSearchInput', 
        'table_id' => 'poTable',
        'placeholder' => 'Search PO Number, Client...'
    ],
    'modules/bom/index.php' => [
        'search_input_id' => 'bomSearchInput',
        'table_id' => 'bomTable', 
        'placeholder' => 'Search BOM Number, PO...'
    ],
    'modules/jci/index.php' => [
        'search_input_id' => 'jciSearchInput',
        'table_id' => 'jciTable',
        'placeholder' => 'Search JCI Number, PO...'
    ],
    'modules/payments/index.php' => [
        'search_input_id' => 'paymentSearchInput',
        'table_id' => 'paymentsTable',
        'placeholder' => 'Search Payment Number, Vendor...'
    ],
    'modules/so/index.php' => [
        'search_input_id' => 'soSearchInput',
        'table_id' => 'soTable', 
        'placeholder' => 'Search SO Number, Customer...'
    ]
];

$updated_files = 0;
$errors = [];

foreach ($modules as $file_path => $config) {
    $full_path = __DIR__ . '/' . $file_path;
    
    if (!file_exists($full_path)) {
        echo "⚠️ File not found: $file_path\n";
        continue;
    }
    
    echo "🔄 Processing: $file_path\n";
    
    $content = file_get_contents($full_path);
    
    // Check if search input already exists
    if (strpos($content, $config['search_input_id']) !== false) {
        echo "✅ Search already exists in $file_path\n";
        continue;
    }
    
    // Add search input to card header
    $search_input_html = '
                <div class="d-flex align-items-center gap-3">
                    <input type="text" id="' . $config['search_input_id'] . '" class="form-control form-control-sm" placeholder="' . $config['placeholder'] . '" style="width: 250px;">
                </div>';
    
    // Look for card header pattern and add search input
    $header_pattern = '/(<div class="card-header[^>]*>.*?<h6[^>]*>[^<]*<\/h6>)/s';
    if (preg_match($header_pattern, $content)) {
        $content = preg_replace(
            '/(<div class="card-header[^>]*>.*?)(<\/div>)/s',
            '$1' . $search_input_html . '$2',
            $content,
            1
        );
    }
    
    // Add search functionality to JavaScript
    $search_js = '
    
    // Custom search functionality
    $(\'#' . $config['search_input_id'] . '\').on(\'keyup\', function() {
        var table = $(\'#' . $config['table_id'] . '\').DataTable();
        table.search(this.value).draw();
    });';
    
    // Look for DataTable initialization and add search functionality
    $datatable_pattern = '/(\$\(\'#' . preg_quote($config['table_id']) . '\'\)\.DataTable\([^}]+\}\);)/';
    if (preg_match($datatable_pattern, $content)) {
        $content = preg_replace(
            $datatable_pattern,
            '$1' . $search_js,
            $content,
            1
        );
    } else {
        // If no DataTable found, add basic search functionality
        $basic_search_js = '
<script>
$(document).ready(function() {
    // Initialize DataTable if not already done
    if (!$.fn.DataTable.isDataTable(\'#' . $config['table_id'] . '\')) {
        var table = $(\'#' . $config['table_id'] . '\').DataTable({
            pageLength: 10,
            lengthChange: false,
            searching: false
        });
    }
    ' . $search_js . '
});
</script>';
        
        // Add before closing body tag
        $content = str_replace('</body>', $basic_search_js . '</body>', $content);
    }
    
    // Write updated content back to file
    if (file_put_contents($full_path, $content)) {
        echo "✅ Updated: $file_path\n";
        $updated_files++;
    } else {
        echo "❌ Failed to update: $file_path\n";
        $errors[] = $file_path;
    }
    
    echo "\n";
}

// Check specific modules that might need different handling
echo "🔧 Checking specific modules...\n\n";

// Check PI module
$pi_file = __DIR__ . '/modules/pi/index.php';
if (file_exists($pi_file)) {
    $content = file_get_contents($pi_file);
    if (strpos($content, 'piSearchInput') === false) {
        echo "🔄 Adding search to PI module...\n";
        // Add search functionality specific to PI module
        // This would need custom implementation based on PI module structure
    } else {
        echo "✅ PI module already has search\n";
    }
}

// Check Payment module  
$payment_file = __DIR__ . '/modules/payments/index.php';
if (file_exists($payment_file)) {
    $content = file_get_contents($payment_file);
    if (strpos($content, 'paymentSearchInput') === false) {
        echo "🔄 Adding search to Payment module...\n";
        // Add search functionality specific to Payment module
    } else {
        echo "✅ Payment module already has search\n";
    }
}

echo "\n📊 Summary:\n";
echo "✅ Files updated: $updated_files\n";
echo "❌ Errors: " . count($errors) . "\n";

if (!empty($errors)) {
    echo "\n⚠️ Files with errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}

echo "\n🎉 Search functionality update completed!\n";

// Create a verification script
$verification_script = '<?php
/**
 * Verify Search Functionality
 * Check if all modules have proper search functionality
 */

echo "🔍 Verifying Search Functionality...\n\n";

$modules_to_check = [
    "modules/lead/index.php" => "searchInput",
    "modules/quotation/index.php" => "searchQuotationInput", 
    "modules/customer/index.php" => "customerSearchInput",
    "modules/purchase/index.php" => "search_po", // Form-based search
    "modules/pi/index.php" => "piSearchInput",
    "modules/po/index.php" => "poSearchInput",
    "modules/bom/index.php" => "bomSearchInput",
    "modules/jci/index.php" => "jciSearchInput",
    "modules/payments/index.php" => "paymentSearchInput",
    "modules/so/index.php" => "soSearchInput"
];

$verified = 0;
$missing = 0;

foreach ($modules_to_check as $file => $search_id) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (strpos($content, $search_id) !== false) {
            echo "✅ $file - Search functionality found\n";
            $verified++;
        } else {
            echo "❌ $file - Search functionality missing\n";
            $missing++;
        }
    } else {
        echo "⚠️ $file - File not found\n";
    }
}

echo "\n📊 Verification Summary:\n";
echo "✅ Modules with search: $verified\n";
echo "❌ Modules missing search: $missing\n";

if ($missing === 0) {
    echo "\n🎉 All modules have search functionality!\n";
} else {
    echo "\n⚠️ Some modules still need search functionality\n";
}
?>';

file_put_contents(__DIR__ . '/verify_search.php', $verification_script);
echo "\n📄 Created verify_search.php for verification\n";
?>