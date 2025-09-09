<?php
/**
 * Fix All Search Functionality Issues
 * This script fixes all the reported search issues
 */

echo "🔧 Fixing All Search Issues...\n\n";

// Files that need search functionality added
$modules_to_fix = [
    'modules/po/index.php' => [
        'table_id' => 'poTable',
        'search_id' => 'poSearchInput',
        'placeholder' => 'Search PO...'
    ],
    'modules/so/index.php' => [
        'table_id' => 'soTable', 
        'search_id' => 'soSearchInput',
        'placeholder' => 'Search SO...'
    ]
];

foreach ($modules_to_fix as $file_path => $config) {
    $full_path = __DIR__ . '/' . $file_path;
    
    if (!file_exists($full_path)) {
        echo "⚠️ File not found: $file_path\n";
        continue;
    }
    
    echo "🔄 Processing: $file_path\n";
    
    $content = file_get_contents($full_path);
    
    // Check if search input already exists
    if (strpos($content, $config['search_id']) !== false) {
        echo "✅ Search already exists in $file_path\n";
        continue;
    }
    
    // Add search input to card header
    $header_pattern = '/(<div class="card-header[^>]*>.*?<h6[^>]*>[^<]*<\/h6>)/s';
    if (preg_match($header_pattern, $content)) {
        $search_input = '
            <div class="d-flex align-items-center gap-3">
                <input type="text" id="' . $config['search_id'] . '" class="form-control form-control-sm" placeholder="' . $config['placeholder'] . '" style="width: 250px;">
            </div>';
        
        $content = preg_replace(
            '/(<div class="card-header[^>]*>.*?<h6[^>]*>[^<]*<\/h6>)/s',
            '$1' . $search_input,
            $content,
            1
        );
    }
    
    // Add search functionality to JavaScript
    $search_js = '
    
    // Custom search functionality
    $(\'#' . $config['search_id'] . '\').on(\'keyup\', function() {
        var table = $(\'#' . $config['table_id'] . '\').DataTable();
        table.search(this.value).draw();
    });';
    
    // Look for DataTable initialization and add search functionality
    $datatable_pattern = '/(\$\(\'#' . preg_quote($config['table_id']) . '\'\)\.DataTable\([^}]+\}\);)/';
    if (preg_match($datatable_pattern, $content)) {
        $content = preg_replace(
            $datatable_pattern,
            'var table = $1' . $search_js,
            $content,
            1
        );
    }
    
    // Write updated content back to file
    if (file_put_contents($full_path, $content)) {
        echo "✅ Updated: $file_path\n";
    } else {
        echo "❌ Failed to update: $file_path\n";
    }
    
    echo "\n";
}

echo "🎉 All search fixes completed!\n";
?>