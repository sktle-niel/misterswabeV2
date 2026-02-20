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
        
        // Name code (first 3 letters of each word, uppercase, max 6 chars)
        $nameWords = array_filter(explode(' ', $name));
        $nameCode = '';
        for ($i = 0; $i < min(count($nameWords), 2); $i++) {
            $nameCode .= strtoupper(substr($nameWords[$i], 0, 3));
        }
        
        // Size code
        $sizeCode = 'NS'; // Default: No Size
        if (!empty($sizes)) {
            $sizeArray = is_array($sizes) ? $sizes : explode(',', $sizes);
            $sizeArray = array_map('trim', $sizeArray);
            $sizeArray = array_filter($sizeArray);
            
            if (count($sizeArray) === 1) {
                // Single size
                $sizeCode = strtoupper($sizeArray[0]);
            } elseif (count($sizeArray) > 1) {
                // Multiple sizes - use range (min-max)
                // Sort sizes appropriately
                usort($sizeArray, function($a, $b) {
                    // Numeric comparison if both are numbers
                    if (is_numeric($a) && is_numeric($b)) {
                        return intval($a) - intval($b);
                    }
                    // String comparison otherwise
                    return strcmp($a, $b);
                });
                $sizeCode = strtoupper($sizeArray[0]) . '-' . strtoupper($sizeArray[count($sizeArray) - 1]);
            }
        }
        
        // Price code (remove currency symbols and decimals, take last 4 digits)
        $priceCode = '';
        $numericPrice = preg_replace('/[^\d]/', '', $price);
        if (!empty($numericPrice)) {
            $priceCode = str_pad(substr($numericPrice, -4), 4, '0', STR_PAD_LEFT);
        }
        
        // Timestamp code (last 4 digits of current timestamp)
        $timestamp = substr(time(), -4);
        
        // Build SKU
        $sku = $categoryCode . '-' . $nameCode;
        
        if ($sizeCode !== 'NS') {
            $sku .= '-' . $sizeCode;
        }
        
        if (!empty($priceCode)) {
            $sku .= '-' . $priceCode;
        }
        
        $sku .= '-' . $timestamp;
        
        return $sku;
    }
}
?>