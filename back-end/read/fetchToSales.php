<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../../config/connection.php';

try {
    // Check if information column exists, if not create it
    $checkInfoColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'information'");
    if (!$checkInfoColumn || $checkInfoColumn->num_rows == 0) {
        $conn->query("ALTER TABLE inventory ADD COLUMN information JSON DEFAULT NULL");
    }
    
    // Query to get all products from inventory including variant_skus and information
    $query = "SELECT id, sku, name, price, size, variant_skus, size_quantities, size_color_quantities, stock, information, images FROM inventory";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception($conn->error);
    }
    
    $products = array();
    
    while ($row = $result->fetch_assoc()) {
        $baseSku = trim($row['sku']);
        $variantSkus = json_decode($row['variant_skus'] ?? '[]', true);
        $sizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
        
        $variantMap = [];
        
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
        
        if (!empty($sizeColorQuantities)) {
            $hasSizes = !empty($row['size']) && $row['size'] !== 'N/A' && $row['size'] !== '' && $row['size'] !== 'Simple Product';
            
            foreach ($sizeColorQuantities as $size => $colors) {
                if (is_array($colors)) {
                    foreach ($colors as $color => $colorData) {
                        if ($hasSizes) {
                            $variantSku = $baseSku . '-' . $size . '-' . strtoupper($color);
                            $displaySize = $size;
                        } else {
                            $variantSku = $baseSku . '-' . strtoupper($color);
                            $displaySize = 'N/A';
                        }
                        
                        if (!isset($variantMap[$variantSku])) {
                            if (is_array($colorData) && isset($colorData['quantity'])) {
                                $quantity = intval($colorData['quantity']);
                            } else {
                                $quantity = intval($colorData);
                            }
                            $variantMap[$variantSku] = [
                                'size' => $displaySize,
                                'color' => $color,
                                'quantity' => $quantity
                            ];
                        }
                    }
                }
            }
        }
        
        // Decode information column
        $information = json_decode($row['information'] ?? '{}', true);
        if (!is_array($information)) {
            $information = [];
        }
        
        // Decode images column
        $images = json_decode($row['images'] ?? '[]', true);
        $imageUrl = '';
        if (!empty($images) && is_array($images)) {
            $imageUrl = $images[0] ?? '';
        }
        // Add uploads/ prefix if image exists
        if (!empty($imageUrl)) {
            $imageUrl = '../../uploads/' . $imageUrl;
        }
        
        // Add base product
        $products[] = array(
            'id' => $row['id'],
            'sku' => $baseSku,
            'name' => $row['name'],
            'price' => floatval($row['price']),
            'size' => $row['size'] ?: 'N/A',
            'variant_skus' => $variantMap,
            'size_color_quantities' => $sizeColorQuantities,
            'stock' => intval($row['stock'] ?? 0),
            'information' => $information,
            'image' => $imageUrl
        );
    }
    
    echo json_encode($products);
    
} catch (Exception $e) {
    echo json_encode(array('error' => $e->getMessage()));
}

$conn->close();
?>