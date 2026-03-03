<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/connection.php';

try {
    // Query to get all products from inventory including variant_skus
    $query = "SELECT id, sku, name, price, size, variant_skus, size_quantities, size_color_quantities, stock FROM inventory";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $products = array();
    
    while ($row = $result->fetch_assoc()) {
        $baseSku = trim($row['sku']);
        $variantSkus = json_decode($row['variant_skus'] ?? '[]', true);
        $sizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
        
        // Build a map of variant SKU to size/color/quantity
        $variantMap = [];
        
        // Handle variant_skus format: {"39-black": "SHO-SAP-3JOX-39-BLACK", "41-red": "SHO-SAP-3JOX-41-RED"}
        // OR for products without sizes: {"BLUE": "ACC-STI-7JX9-BLUE"}
        if (!empty($variantSkus) && is_array($variantSkus)) {
            $hasSizes = !empty($row['size']) && $row['size'] !== 'N/A' && $row['size'] !== '' && $row['size'] !== 'Simple Product';
            
            foreach ($variantSkus as $key => $variantSku) {
                if ($hasSizes) {
                    $lastDashPos = strrpos($key, '-');
                    if ($lastDashPos !== false) {
                        $size = substr($key, 0, $lastDashPos);
                        $color = substr($key, $lastDashPos + 1);
                    } else {
                        $size = $key;
                        $color = '';
                    }
                    
                    $quantity = 0;
                    if (!empty($sizeColorQuantities) && isset($sizeColorQuantities[$size][$color])) {
                        $colorData = $sizeColorQuantities[$size][$color];
                        if (is_array($colorData) && isset($colorData['quantity'])) {
                            $quantity = intval($colorData['quantity']);
                        } else {
                            $quantity = intval($colorData);
                        }
                    }
                } else {
                    $size = 'N/A';
                    $color = $key;
                    
                    // For products without sizes, check both direct key and empty string key
                    $quantity = 0;
                    if (!empty($sizeColorQuantities)) {
                        // Try direct key first (e.g., "BLUE")
                        if (isset($sizeColorQuantities[$color])) {
                            $colorData = $sizeColorQuantities[$color];
                            if (is_array($colorData) && isset($colorData['quantity'])) {
                                $quantity = intval($colorData['quantity']);
                            } else {
                                $quantity = intval($colorData);
                            }
                        }
                        // Also check empty string key '' (for products added via addQuantity)
                        if ($quantity === 0 && isset($sizeColorQuantities[''][$color])) {
                            $colorData = $sizeColorQuantities[''][$color];
                            if (is_array($colorData) && isset($colorData['quantity'])) {
                                $quantity = intval($colorData['quantity']);
                            } else {
                                $quantity = intval($colorData);
                            }
                        }
                    }
                }
                
                $variantMap[$variantSku] = [
                    'size' => $size,
                    'color' => $color,
                    'quantity' => $quantity
                ];
            }
        }
        
        // Also check size_color_quantities for variants not in variant_skus
        if (!empty($sizeColorQuantities)) {
            foreach ($sizeColorQuantities as $size => $colors) {
                if (is_array($colors)) {
                    foreach ($colors as $color => $colorData) {
                        // Generate variant SKU if not already in map
                        $variantSku = $baseSku . '-' . $size . '-' . strtoupper($color);
                        if (!isset($variantMap[$variantSku])) {
                            if (is_array($colorData) && isset($colorData['quantity'])) {
                                $quantity = intval($colorData['quantity']);
                            } else {
                                $quantity = intval($colorData);
                            }
                            $variantMap[$variantSku] = [
                                'size' => $size,
                                'color' => $color,
                                'quantity' => $quantity
                            ];
                        }
                    }
                }
            }
        }
        
        // Add base product
        $products[] = array(
            'id' => $row['id'],
            'sku' => $baseSku,
            'name' => $row['name'],
            'price' => floatval($row['price']),
            'size' => $row['size'] ?: 'N/A',
            'variant_skus' => $variantMap,
            'stock' => intval($row['stock'] ?? 0)
        );
    }
    
    echo json_encode($products);
    
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
}

$conn->close();
?>