<?php
/**
 * SKU Utility Functions
 * Shared functions for SKU generation across the application
 */

if (!function_exists('generateSKU')) {
    /**
     * Generate SKU automatically based on product details
     * Format: CATEGORY-NAME-SIZE-PRICE-TIMESTAMP
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
        
        // Add size info if available (first size only for brevity)
        $sizeCode = '';
        if (!empty($sizes)) {
            if (is_array($sizes)) {
                $firstSize = reset($sizes); // Get first size
                $sizeCode = '-' . strtoupper($firstSize);
            } else {
                $sizeParts = explode(',', $sizes);
                $firstSize = trim($sizeParts[0]);
                $sizeCode = '-' . strtoupper($firstSize);
            }
        }
        
        // Add color info if available (first color only for brevity)
        $colorCode = '';
        if (!empty($colors)) {
            if (is_array($colors)) {
                $firstColor = reset($colors); // Get first color
                $colorCode = '-' . strtoupper(substr($firstColor, 0, 3));
            } else {
                $colorParts = explode(',', $colors);
                $firstColor = trim($colorParts[0]);
                $colorCode = '-' . strtoupper(substr($firstColor, 0, 3));
            }
        }
        
        // Build SKU: CATEGORY-NAME-SIZE-COLOR-UNIQUEID (e.g., SHO-NIK-S-RED-A1B2)
        $sku = $categoryCode . '-' . $nameCode . $sizeCode . $colorCode . '-' . $uniqueId;
        
        return $sku;
    }
}
?>