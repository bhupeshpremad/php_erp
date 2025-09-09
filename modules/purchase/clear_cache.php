<?php
// Clear server cache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPCache cleared<br>";
}

if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    echo "APC cache cleared<br>";
}

// Force file modification time update
$file = __DIR__ . '/add.php';
if (file_exists($file)) {
    touch($file);
    echo "File timestamp updated: " . date('Y-m-d H:i:s', filemtime($file)) . "<br>";
}

echo "Cache clearing completed. Try refreshing the page now.";
?>