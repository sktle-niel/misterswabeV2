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
    function generateSKU($name, $category, $price, $sizes) {
        // Category code (first 3 letters, uppercase)
        $categoryCode = strtoupper(substr($category, 0, 3));
        
        // Name code (first 3 letters of each word, uppercase)
        $nameWords = array_filter(explode(' ', $name));
        $nameCode = '';
        for ($i = 0; $i < min(count($nameWords), 2); $i++) {
            $nameCode .= strtoupper(substr($nameWords[$i], 0, 3));
        }
        
        // Build shortest SKU: CATEGORY-NAME
        $sku = $categoryCode . '-' . $nameCode;
        
        return $sku;
    }
}
?>