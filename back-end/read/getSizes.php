<?php
// Prevent any output before JSON
ob_start();

include '../../config/connection.php';

// Clear any output that might have come from connection.php
ob_end_clean();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$baseSku = $_GET['sku'] ?? '';

if (empty($baseSku)) {
    echo json_encode(['success' => false, 'message' => 'SKU is required']);
    exit;
}

try {
    // Fetch the product by base SKU including variant_skus
    $stmt = $conn->prepare("SELECT sku, name, size, size_quantities, size_color_quantities, variant_skus FROM inventory WHERE sku = ?");
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Query preparation failed: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("s", $baseSku);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Query execution failed: ' . $stmt->error]);
        exit;
    }

    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sizeString = $row['size'];
        $sizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
        $sizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
        $variantSkus = json_decode($row['variant_skus'] ?? '{}', true);
        
        // Initialize variant_skus array if null
        if (!is_array($variantSkus)) {
            $variantSkus = [];
        }
        
        $hasSizes = !empty($sizeString) && $sizeString !== 'Simple Product';

        // If no sizes (simple product), return base SKU with stock
        if (!$hasSizes) {
            // Calculate stock from size_color_quantities (sum of all color quantities)
            $baseStock = 0;
            if (!empty($sizeColorQuantities) && is_array($sizeColorQuantities)) {
                foreach ($sizeColorQuantities as $sizeKey => $colors) {
                    if (is_array($colors)) {
                        $baseStock += array_sum($colors);
                    }
                }
            }
            
            // If still 0, fallback to the stock field
            if ($baseStock === 0) {
                $baseStock = (int)($row['stock'] ?? 0);
            }
            
            // Get colors from size_color_quantities for this size (empty string key for simple products)
            $colors = isset($sizeColorQuantities['']) ? $sizeColorQuantities[''] : [];
            
            // Create color variants using stored variant_skus
            // Variant SKU format: baseSku-COLORCODE (for simple products)
            $colorVariants = [];
            foreach ($colors as $color => $colorQty) {
                // Use stored variant SKU if available, otherwise generate (baseSku-COLORCODE)
                $variantSku = $variantSkus[$color] ?? ($baseSku . '-' . strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color)));
                $colorVariants[] = [
                    'sku' => $variantSku,
                    'color' => $color,
                    'quantity' => $colorQty
                ];
            }
            
            // Use the name from the query
            $productName = $row['name'] ?? '';
            
            $sizes = [[
                'sku' => $baseSku,
                'size' => '',
                'stock' => $baseStock,
                'size_quantities' => $colors,
                'color_variants' => $colorVariants,
                'isSimpleProduct' => true,
                'productName' => $productName
            ]];
            
            echo json_encode(['success' => true, 'sizes' => $sizes]);
            exit;
        }

        // If size is a comma-separated string, split it
        if (strpos($sizeString, ',') !== false) {
            $sizeArray = array_map('trim', explode(',', $sizeString));
            $sizes = [];
            foreach ($sizeArray as $size) {
                $quantity = (int)($sizeQuantities[$size] ?? 0);
                
                // Get colors for this size from size_color_quantities
                $colors = isset($sizeColorQuantities[$size]) ? $sizeColorQuantities[$size] : [];
                
                // Create a variant entry for each color with unique SKU using stored variant_skus
                // Variant SKU format: baseSku-SIZE-COLORCODE (includes size)
                $colorVariants = [];
                foreach ($colors as $color => $colorQty) {
                    // Use stored variant SKU if available, otherwise generate (baseSku-SIZE-COLORCODE)
                    $variantKey = $size . '-' . $color;
                    $variantSku = $variantSkus[$variantKey] ?? ($baseSku . '-' . $size . '-' . strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color)));
                    $colorVariants[] = [
                        'sku' => $variantSku,
                        'color' => $color,
                        'quantity' => $colorQty
                    ];
                }
                
                $sizes[] = [
                    'sku' => $baseSku,
                    'size' => $size,
                    'stock' => $quantity,
                    'size_quantities' => $colors,
                    'color_variants' => $colorVariants
                ];
            }
        } else {
            // Single size
            $quantity = (int)($sizeQuantities[$sizeString] ?? 0);
            
            $colors = isset($sizeColorQuantities[$sizeString]) ? $sizeColorQuantities[$sizeString] : [];
            
            $colorVariants = [];
            foreach ($colors as $color => $colorQty) {
                // Use stored variant SKU if available, otherwise generate (baseSku-SIZE-COLORCODE)
                $variantKey = $sizeString . '-' . $color;
                $variantSku = $variantSkus[$variantKey] ?? ($baseSku . '-' . $sizeString . '-' . strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color)));
                $colorVariants[] = [
                    'sku' => $variantSku,
                    'color' => $color,
                    'quantity' => $colorQty
                ];
            }
            
            $sizes = [[
                'sku' => $baseSku,
                'size' => $sizeString,
                'stock' => $quantity,
                'size_quantities' => $colors,
                'color_variants' => $colorVariants
            ]];
        }

        echo json_encode(['success' => true, 'sizes' => $sizes]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No product found with this SKU']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
