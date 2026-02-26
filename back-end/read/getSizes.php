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
    // Fetch the product by base SKU including variant_skus and stock
    $stmt = $conn->prepare("SELECT sku, name, size, size_quantities, size_color_quantities, variant_skus, stock FROM inventory WHERE sku = ?");
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
        // Use the stock column directly from the database
        $currentStock = (int)($row['stock'] ?? 0);
        
        // Initialize arrays if null
        if (!is_array($sizeQuantities)) $sizeQuantities = [];
        if (!is_array($sizeColorQuantities)) $sizeColorQuantities = [];
        if (!is_array($variantSkus)) $variantSkus = [];
        
        $hasSizes = !empty($sizeString) && $sizeString !== 'Simple Product';

        // If no sizes (simple product), return base SKU with stock
        if (!$hasSizes) {
            // Get colors from size_color_quantities for this size (empty string key for simple products)
            $colors = isset($sizeColorQuantities['']) ? $sizeColorQuantities[''] : [];
            
            // Create color variants using stored variant_skus
            // Variant SKU format: baseSku-COLORCODE (for simple products)
            $colorVariants = [];
            foreach ($colors as $color => $colorData) {
                // Handle both new format (with SKU) and old format (just quantity)
                if (is_array($colorData) && isset($colorData['quantity'])) {
                    $colorQty = intval($colorData['quantity']);
                    $variantSku = $colorData['sku'] ?? ($variantSkus[$color] ?? ($baseSku . '-' . strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color))));
                } else {
                    // Old format - just quantity
                    $colorQty = intval($colorData);
                    $variantSku = $variantSkus[$color] ?? ($baseSku . '-' . strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color)));
                }
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
                'stock' => $currentStock,
                'currentStock' => $currentStock,
                'size_quantities' => $colors,
                'color_variants' => $colorVariants,
                'isSimpleProduct' => true,
                'productName' => $productName
            ]];
            
            echo json_encode(['success' => true, 'sizes' => $sizes]);
            exit;
        }

        // Get all sizes to process - from size_color_quantities first, then from size column
        $allSizes = [];
        
        // First, get sizes from size_color_quantities keys
        $sizesFromColorConfig = array_keys($sizeColorQuantities);
        
        // Get sizes from size column (comma-separated string)
        $sizesFromSizeColumn = [];
        if (!empty($sizeString)) {
            if (strpos($sizeString, ',') !== false) {
                $sizesFromSizeColumn = array_map('trim', explode(',', $sizeString));
            } else {
                $sizesFromSizeColumn = [$sizeString];
            }
        }
        
        // Merge sizes from both sources, avoiding duplicates
        $allSizes = array_unique(array_merge($sizesFromColorConfig, $sizesFromSizeColumn));
        
        // Build the sizes array - ensure size is always a string
        $sizes = [];
        foreach ($allSizes as $size) {
            // Ensure size is a string
            $size = (string)$size;
            
            // Get colors for this size from size_color_quantities
            $colors = isset($sizeColorQuantities[$size]) ? $sizeColorQuantities[$size] : [];
            
            // Create a variant entry for each color with unique SKU
            // New format: { "Color": { "quantity": 10, "sku": "..." } }
            // Old format: { "Color": 10 }
            $colorVariants = [];
            $quantity = 0;
            if (is_array($colors)) {
                foreach ($colors as $color => $colorData) {
                    // Handle both new format (with SKU) and old format (just quantity)
                    if (is_array($colorData) && isset($colorData['quantity'])) {
                        $colorQty = intval($colorData['quantity']);
                        $variantSku = $colorData['sku'] ?? ($baseSku . '-' . $size . '-' . strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color)));
                    } else {
                        // Old format - just quantity
                        $colorQty = intval($colorData);
                        $variantSku = $baseSku . '-' . $size . '-' . strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color));
                    }
                    $quantity += $colorQty;
                    $colorVariants[] = [
                        'sku' => $variantSku,
                        'color' => $color,
                        'quantity' => $colorQty
                    ];
                }
            } else {
                $quantity = (int)($sizeQuantities[$size] ?? 0);
            }
            
            $sizes[] = [
                'sku' => $baseSku,
                'size' => $size,
                'stock' => $quantity,
                'currentStock' => $currentStock,
                'size_quantities' => $colors,
                'color_variants' => $colorVariants
            ];
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
