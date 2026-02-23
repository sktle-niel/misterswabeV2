<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$baseSku = $_POST['baseSku'] ?? '';
$size = $_POST['size'] ?? '';
$color = $_POST['color'] ?? '';

if (empty($baseSku) || empty($color)) {
    echo json_encode(['success' => false, 'message' => 'Base SKU and color are required']);
    exit;
}

try {
    include '../../config/connection.php';

    // Fetch current product data
    $stmt = $conn->prepare("SELECT size_quantities, size_color_quantities, color FROM inventory WHERE sku = ?");
    $stmt->bind_param("s", $baseSku);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    $row = $result->fetch_assoc();
    
    $sizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
    $sizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
    $existingColors = json_decode($row['color'] ?? '[]', true);
    
    if (!is_array($sizeColorQuantities)) $sizeColorQuantities = [];
    if (!is_array($sizeQuantities)) $sizeQuantities = [];
    if (!is_array($existingColors)) $existingColors = [];
    
    // Debug logging
    error_log("removeColor called with: baseSku=$baseSku, size=[$size], color=[$color]");
    error_log("Current sizeColorQuantities: " . json_encode($sizeColorQuantities));
    
    // Remove the color from size_color_quantities
    // For simple products (empty size), the structure is { "": { "ColorName": quantity } }
    // For products with sizes, the structure is { "Size": { "Color": quantity } }
    if ($size === '' || empty($size)) {
        // Simple product - remove the color from the empty string key
        // Structure: { "": { "ColorName": quantity } }
        
        // Check if there's data at the empty string key
        if (isset($sizeColorQuantities[''])) {
            error_log("Found empty string key in sizeColorQuantities");
            
            // Try to find and remove the color - try exact match first
            if (isset($sizeColorQuantities[''][$color])) {
                error_log("Found exact color match at empty key");
                unset($sizeColorQuantities[''][$color]);
            } else {
                // Try case-insensitive search
                foreach ($sizeColorQuantities[''] as $existingColor => $qty) {
                    if (strtolower($existingColor) === strtolower($color)) {
                        error_log("Found case-insensitive match: $existingColor");
                        unset($sizeColorQuantities[''][$existingColor]);
                        break;
                    }
                }
            }
            
            // If no more colors for simple product, remove the empty key
            if (empty($sizeColorQuantities[''])) {
                unset($sizeColorQuantities['']);
            }
        } else {
            error_log("No empty string key found, checking top-level");
            // Maybe the old format - check if color is at top level
            if (isset($sizeColorQuantities[$color])) {
                error_log("Found color at top level (old format)");
                unset($sizeColorQuantities[$color]);
            }
        }
        
        // Also remove from size_quantities (for simple products, size is empty string)
        if (isset($sizeQuantities[''])) {
            unset($sizeQuantities['']);
        }
    } else {
        // Product with sizes - remove color from specific size
        error_log("Processing product with size: $size");
        
        if (isset($sizeColorQuantities[$size])) {
            // Try exact match first
            if (isset($sizeColorQuantities[$size][$color])) {
                error_log("Found exact color match at size key");
                unset($sizeColorQuantities[$size][$color]);
            } else {
                // Try case-insensitive search
                foreach ($sizeColorQuantities[$size] as $existingColor => $qty) {
                    if (strtolower($existingColor) === strtolower($color)) {
                        error_log("Found case-insensitive match: $existingColor");
                        unset($sizeColorQuantities[$size][$existingColor]);
                        break;
                    }
                }
            }
            
            // If no more colors for this size, remove the size key
            if (empty($sizeColorQuantities[$size])) {
                unset($sizeColorQuantities[$size]);
            }
        }
        
        // Update size_quantities - recalculate total for this size
        if (isset($sizeColorQuantities[$size]) && is_array($sizeColorQuantities[$size])) {
            $sizeQuantities[$size] = array_sum($sizeColorQuantities[$size]);
        } else {
            unset($sizeQuantities[$size]);
        }
    }
    
    // Recalculate all unique colors
    $allColors = [];
    foreach ($sizeColorQuantities as $sizeKey => $colors) {
        if (is_array($colors)) {
            foreach (array_keys($colors) as $colorKey) {
                if (!in_array($colorKey, $allColors)) {
                    $allColors[] = $colorKey;
                }
            }
        }
    }

    // Calculate new total stock
    $newStock = 0;
    foreach ($sizeColorQuantities as $sizeKey => $colors) {
        if (is_array($colors)) {
            $newStock += array_sum($colors);
        }
    }

    // Determine new status based on stock
    if ($newStock == 0) {
        $newStatus = 'Out of Stock';
    } elseif ($newStock <= 10) {
        $newStatus = 'Low Stock';
    } else {
        $newStatus = 'In Stock';
    }

    $updatedSizeColorQuantities = json_encode($sizeColorQuantities);
    $updatedSizeQuantities = json_encode($sizeQuantities);
    $updatedColors = json_encode($allColors);

    error_log("Updated sizeColorQuantities: " . json_encode($sizeColorQuantities));

    // Update the database
    $updateStmt = $conn->prepare("UPDATE inventory SET size_quantities = ?, size_color_quantities = ?, color = ?, stock = ?, status = ? WHERE sku = ?");
    $updateStmt->bind_param("sssiss", $updatedSizeQuantities, $updatedSizeColorQuantities, $updatedColors, $newStock, $newStatus, $baseSku);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Color removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to remove color']);
    }

    $stmt->close();
    $updateStmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
