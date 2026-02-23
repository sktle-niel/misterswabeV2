<?php
/**
 * SKU Utility Functions
 * Shared functions for SKU generation across the application
 */

if (!function_exists('generateSKU')) {
    /**
     * Generate SKU automatically based on product details
     * Format: CATEGORY-NAME-UNIQUEID (no size or color in base SKU)
     * 
     * @param string $name Product name
     * @param string $category Category name
     * @param mixed $price Product price
     * @param mixed $sizes Product sizes (string or array)
     * @return string Generated SKU
     */
    function generateSKU($name, $category, $price, $sizes, $colors = null) {
        // Category code (first 3 letters, uppercase)
        $categoryCode = strtoupper(substr($category, 0, 3));
        
        // Name code (first 3 letters of each word, uppercase)
        $nameWords = array_filter(explode(' ', $name));
        $nameCode = '';
        for ($i = 0; $i < min(count($nameWords), 2); $i++) {
            $nameCode .= strtoupper(substr($nameWords[$i], 0, 3));
        }
        
        // Generate a short unique ID (4 random alphanumeric characters for short barcode)
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $uniqueId = '';
        for ($i = 0; $i < 4; $i++) {
            $uniqueId .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        
        // Build SKU: CATEGORY-NAME-UNIQUEID (e.g., SHO-NIK-A1B2) - no size or color in base SKU
        $sku = $categoryCode . '-' . $nameCode . '-' . $uniqueId;
        
        return $sku;
    }
}
?>
