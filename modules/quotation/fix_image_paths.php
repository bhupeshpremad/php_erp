<?php
/**
 * Image path fixer for quotation PDF generation
 * Ensures product images are properly fetched and resized to 100x100px
 */

function getQuotationProductImage($productImageName, $size = 100) {
    if (empty($productImageName)) {
        return [
            'src' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjBGMEYwIi8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZpbGw9IiM5OTk5OTkiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMnB4Ij5ObyBJbWFnZTwvdGV4dD4KPC9zdmc+',
            'width' => $size,
            'height' => $size,
            'alt' => 'No Image Available'
        ];
    }

    // Enhanced image location detection
    $imageLocations = [
        // Local file paths
        __DIR__ . '/../../uploads/quotation/' . $productImageName,
        __DIR__ . '/../../uploads/quotation/' . basename($productImageName),
        __DIR__ . '/../../assets/images/upload/quotation/' . $productImageName,
        __DIR__ . '/../../assets/images/upload/quotation/' . basename($productImageName),
        __DIR__ . '/../../assets/images/products/' . $productImageName,
        $_SERVER['DOCUMENT_ROOT'] . '/php_erp/uploads/quotation/' . $productImageName,
        $_SERVER['DOCUMENT_ROOT'] . '/php_erp/assets/images/upload/quotation/' . $productImageName,
        $_SERVER['DOCUMENT_ROOT'] . '/uploads/quotation/' . $productImageName,
        $_SERVER['DOCUMENT_ROOT'] . '/assets/images/upload/quotation/' . $productImageName,
    ];

    $imagePath = null;
    foreach ($imageLocations as $location) {
        if (file_exists($location)) {
            $imagePath = $location;
            break;
        }
    }

    // Enhanced web URL detection
    if (!$imagePath) {
        $webUrls = [
            BASE_URL . 'uploads/quotation/' . $productImageName,
            BASE_URL . 'assets/images/upload/quotation/' . $productImageName,
            'http://localhost/php_erp/uploads/quotation/' . $productImageName,
            'http://localhost/php_erp/assets/images/upload/quotation/' . $productImageName,
        ];
        
        foreach ($webUrls as $webUrl) {
            if (@getimagesize($webUrl)) {
                $imagePath = $webUrl;
                break;
            }
        }
    }

    // Try direct file access with mPDF compatible paths
    if (!$imagePath) {
        // Try to find image in uploads directory
        $uploadDir = __DIR__ . '/../../uploads/quotation/';
        if (is_dir($uploadDir)) {
            $files = scandir($uploadDir);
            foreach ($files as $file) {
                if ($file === $productImageName || strpos($file, $productImageName) !== false) {
                    $imagePath = $uploadDir . $file;
                    break;
                }
            }
        }
    }

    if ($imagePath) {
        // Create resized image
        $resizedImage = createResizedImage($imagePath, $size);
        return [
            'src' => $resizedImage,
            'width' => $size,
            'height' => $size,
            'alt' => htmlspecialchars($productImageName)
        ];
    }

    return [
        'src' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwIiBoZWlnaHQ9IjEwMCIgdmlld0JveD0iMCAwIDEwMCAxMDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIiBmaWxsPSIjRjBGMEYwIi8+Cjx0ZXh0IHg9IjUwIiB5PSI1MCIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iIGZpbGw9IiM5OTk5OTkiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxMnB4Ij5ObyBJbWFnZTwvdGV4dD4KPC9zdmc+',
        'width' => $size,
        'height' => $size,
        'alt' => 'No Image Available'
    ];
}

function createResizedImage($imagePath, $size = 100) {
    if (!extension_loaded('gd')) {
        return $imagePath; // Return original if GD not available
    }

    try {
        // Get image info
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) return $imagePath;

        $mime = $imageInfo['mime'];
        
        // Create image resource based on type
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($imagePath);
                break;
            case 'image/png':
                $source = imagecreatefrompng($imagePath);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($imagePath);
                break;
            case 'image/webp':
                $source = imagecreatefromwebp($imagePath);
                break;
            default:
                return $imagePath;
        }

        if (!$source) return $imagePath;

        // Get original dimensions
        $width = imagesx($source);
        $height = imagesy($source);

        // Calculate new dimensions maintaining aspect ratio
        $ratio = min($size / $width, $size / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);

        // Create new image
        $thumb = imagecreatetruecolor($size, $size);
        
        // Fill with white background
        $white = imagecolorallocate($thumb, 255, 255, 255);
        imagefill($thumb, 0, 0, $white);
        
        // Calculate centering
        $x = ($size - $newWidth) / 2;
        $y = ($size - $newHeight) / 2;
        
        // Resize image
        imagecopyresampled($thumb, $source, $x, $y, 0, 0, $newWidth, $newHeight, $width, $height);

        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'quotation_img_') . '.jpg';
        imagejpeg($thumb, $tempFile, 85);

        // Clean up
        imagedestroy($source);
        imagedestroy($thumb);

        return $tempFile;
    } catch (Exception $e) {
        return $imagePath;
    }
}

function getImageWebPath($productImageName) {
    if (empty($productImageName)) return null;
    
    // Check if it's a full URL
    if (strpos($productImageName, 'http') === 0) {
        return $productImageName;
    }
    
    // Check local files
    $localPaths = [
        __DIR__ . '/../../uploads/quotation/' . $productImageName,
        __DIR__ . '/../../assets/images/upload/quotation/' . $productImageName,
    ];
    
    foreach ($localPaths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // Return web URL
    return BASE_URL . 'uploads/quotation/' . $productImageName;
}
?>
