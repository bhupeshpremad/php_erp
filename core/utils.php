<?php
class Utils {
    public static function showToast($message, $type = 'info') {
        // This is a placeholder for toast notification logic.
        // You can integrate with your frontend JS toast library or implement server-side notifications.
        echo "<script>showToast('$message', '$type');</script>";
    }

    public static function sanitizeFilename($filename) {
        // Remove any characters that are not alphanumeric, underscores, hyphens, or dots
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);
        // Ensure no multiple dots or leading/trailing dots
        $filename = preg_replace('/\.{2,}/', '.', $filename);
        $filename = trim($filename, '.');
        return $filename;
    }
}
?>
