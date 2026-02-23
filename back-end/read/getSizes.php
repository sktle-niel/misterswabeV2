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
    // Fetch the product by base SKU including size_color_quantities and name
    $stmt = $conn->prepare("SELECT sku, name, size, size_quantities, size_color_quantities FROM inventory WHERE sku = ?");
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
        
        // Check if product has sizes (not a simple product)
        // "Simple Product" is stored for simple products, treat it as no size
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
            
            // Create color variants
            $colorVariants = [];
            foreach ($colors as $color => $colorQty) {
                $variantSku = $baseSku . '-' . strtoupper(substr($color, 0, 3));
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
                
                // Create a variant entry for each color with unique SKU
                $colorVariants = [];
                foreach ($colors as $color => $colorQty) {
                    // Generate unique SKU with color first, then size
                    $variantSku = $baseSku . '-' . strtoupper(substr($color, 0, 3)) . '-' . $size;
                    $colorVariants[] = [
                        'sku' => $variantSku,
                        'color' => $color,
                        'quantity' => $colorQty
                    ];
                }
                
                $sizes[] = [
                    'sku' => $baseSku . '-' . $size,
                    'size' => $size,
                    'stock' => $quantity,
                    'size_quantities' => $colors,
                    'color_variants' => $colorVariants
                ];
            }
        } else {
            // Single size
            $quantity = (int)($sizeQuantities[$sizeString] ?? 0);
            
            // Get colors for this size from size_color_quantities
            $colors = isset($sizeColorQuantities[$sizeString]) ? $sizeColorQuantities[$sizeString] : [];
            
            // Create a variant entry for each color with unique SKU
            $colorVariants = [];
            foreach ($colors as $color => $colorQty) {
                // Generate unique SKU with color first, then size
                $variantSku = $baseSku . '-' . strtoupper(substr($color, 0, 3)) . '-' . $sizeString;
                $colorVariants[] = [
                    'sku' => $variantSku,
                    'color' => $color,
                    'quantity' => $colorQty
                ];
            }
            
            $sizes = [[
                'sku' => $baseSku . '-' . $sizeString,
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
