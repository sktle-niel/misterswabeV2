<?php
include '../utils/skuUtils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$sku = $_POST['sku'] ?? '';
$amount = (int)($_POST['amount'] ?? 0);
$color = $_POST['color'] ?? '';
$size = $_POST['size'] ?? '';
$simpleStock = $_POST['simpleStock'] ?? '';
$noSizeProduct = $_POST['noSizeProduct'] ?? '';

if (empty($sku) || $amount < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid SKU or amount']);
    exit;
}

// Handle noSizeProduct mode - products with colors but no sizes
if ($noSizeProduct === 'true' || $noSizeProduct === true) {
    if (empty($color)) {
        echo json_encode(['success' => false, 'message' => 'Color is required']);
        exit;
    }
    
    try {
        include '../../config/connection.php';
        
        $baseSku = $sku;
        
        // Check if color column exists
        $checkColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'color'");
        if (!$checkColumn || $checkColumn->num_rows == 0) {
            $conn->query("ALTER TABLE inventory ADD COLUMN color JSON NULL");
        }

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
        
        $sizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
        $sizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
        $existingColors = json_decode($row['color'] ?? '[]', true);
        
        if (!is_array($sizeQuantities)) $sizeQuantities = [];
        if (!is_array($sizeColorQuantities)) $sizeColorQuantities = [];
        if (!is_array($existingColors)) $existingColors = [];
        
        // Generate variant SKU for no-size product
        $colorCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color));
        $variantSku = $baseSku . '-' . $colorCode;
        
        // Update size_color_quantities with empty size key
        if (!isset($sizeColorQuantities[''])) {
            $sizeColorQuantities[''] = [];
        }
        $sizeColorQuantities[''][$color] = [
            'quantity' => $amount,
            'sku' => $variantSku
        ];
        
        $sizeQuantities[''] = $amount;

        // Calculate new total stock
        $newStock = 0;
        foreach ($sizeColorQuantities as $sizeKey => $colors) {
            if (is_array($colors)) {
                foreach ($colors as $colorKey => $colorData) {
                    if (is_array($colorData) && isset($colorData['quantity'])) {
                        $newStock += intval($colorData['quantity']);
                    }
                }
            }
        }

        // Determine new status
        if ($newStock == 0) {
            $newStatus = 'Out of Stock';
        } elseif ($newStock <= 10) {
            $newStatus = 'Low Stock';
        } else {
            $newStatus = 'In Stock';
        }

        // Collect all unique colors
        $allColors = $existingColors;
        if (!in_array($color, $allColors)) {
            $allColors[] = $color;
        }
        
        $updatedColors = json_encode($allColors);
        $updatedSizeColorQuantities = json_encode($sizeColorQuantities);
        $updatedSizeQuantities = json_encode($sizeQuantities);

        $updateStmt = $conn->prepare("UPDATE inventory SET size_quantities = ?, size_color_quantities = ?, color = ?, stock = ?, status = ? WHERE sku = ?");
        $updateStmt->bind_param("sssiss", $updatedSizeQuantities, $updatedSizeColorQuantities, $updatedColors, $newStock, $newStatus, $baseSku);

        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Color added successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
        }

        $stmt->close();
        $updateStmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

// Handle simple stock update (for products with no sizes/colors)
if ($simpleStock === 'true' || $simpleStock === true) {
    try {
        include '../../config/connection.php';
        
        $baseSku = $sku;
        
        // Get current stock
        $stmt = $conn->prepare("SELECT stock FROM inventory WHERE sku = ?");
        $stmt->bind_param("s", $baseSku);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }
        
        $row = $result->fetch_assoc();
        $currentStock = intval($row['stock'] ?? 0);
        $newStock = $currentStock + $amount;
        
        // Determine new status based on stock
        if ($newStock == 0) {
            $newStatus = 'Out of Stock';
        } elseif ($newStock <= 10) {
            $newStatus = 'Low Stock';
        } else {
            $newStatus = 'In Stock';
        }
        
        // Update only the stock column
        $updateStmt = $conn->prepare("UPDATE inventory SET stock = ?, status = ? WHERE sku = ?");
        $updateStmt->bind_param("iss", $newStock, $newStatus, $baseSku);
        
        if ($updateStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Stock added successfully', 'newStock' => $newStock]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update stock']);
        }
        
        $stmt->close();
        $updateStmt->close();
        $conn->close();
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}

if (empty($color)) {
    echo json_encode(['success' => false, 'message' => 'Color is required']);
    exit;
}

try {
    include '../../config/connection.php';

    // Check if color column exists, if not create it
    $checkColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'color'");
    if (!$checkColumn || $checkColumn->num_rows == 0) {
        $conn->query("ALTER TABLE inventory ADD COLUMN color JSON NULL");
    }

    $baseSku = $sku;
    
    // First check if exact SKU exists
    $checkStmt = $conn->prepare("SELECT sku FROM inventory WHERE sku = ?");
    $checkStmt->bind_param("s", $sku);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $baseSku = $sku;
    } else {
        // SKU not found exactly - use size parameter to determine extraction method
        if (empty($size)) {
            // Simple product - remove only last part (color code)
            $skuParts = explode('-', $sku);
            if (count($skuParts) >= 2) {
                $potentialBase = implode('-', array_slice($skuParts, 0, -1));
            } else {
                $potentialBase = $sku;
            }
        } else {
            // Product with sizes - remove last 2 parts (size and color code)
            $skuParts = explode('-', $sku);
            if (count($skuParts) >= 3) {
                $potentialBase = implode('-', array_slice($skuParts, 0, -2));
            } else {
                $potentialBase = $sku;
            }
        }
        
        // Check if potential base SKU exists
        $checkStmt2 = $conn->prepare("SELECT sku FROM inventory WHERE sku = ?");
        $checkStmt2->bind_param("s", $potentialBase);
        $checkStmt2->execute();
        $checkResult2 = $checkStmt2->get_result();
        
        if ($checkResult2->num_rows > 0) {
            $baseSku = $potentialBase;
        } else if (empty($size)) {
            // Try fallback for simple products
            $skuParts = explode('-', $sku);
            $potentialBase2 = implode('-', array_slice($skuParts, 0, -2));
            $checkStmt3 = $conn->prepare("SELECT sku FROM inventory WHERE sku = ?");
            $checkStmt3->bind_param("s", $potentialBase2);
            $checkStmt3->execute();
            $checkResult3 = $checkStmt3->get_result();
            
            if ($checkResult3->num_rows > 0) {
                $baseSku = $potentialBase2;
            }
            $checkStmt3->close();
        }
        $checkStmt2->close();
    }
    $checkStmt->close();

    // Fetch current product data including size_quantities and color
    $stmt = $conn->prepare("SELECT size, size_quantities, size_color_quantities, color, variant_skus FROM inventory WHERE sku = ?");
    $stmt->bind_param("s", $baseSku);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found: ' . $baseSku]);
        exit;
    }

    $row = $result->fetch_assoc();
    
    $sizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
    $sizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
    $existingColors = json_decode($row['color'] ?? '[]', true);
    $variantSkus = json_decode($row['variant_skus'] ?? '{}', true);
    
    if (!is_array($sizeQuantities)) $sizeQuantities = [];
    if (!is_array($sizeColorQuantities)) $sizeColorQuantities = [];
    if (!is_array($existingColors)) $existingColors = [];
    if (!is_array($variantSkus)) $variantSkus = [];
    
    // Generate variant SKU first
    $colorCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color));
    // For simple products (empty size), use baseSku-COLORCODE format
    if (empty($size)) {
        $variantSku = $baseSku . '-' . $colorCode;
    } else {
        $variantSku = $baseSku . '-' . $size . '-' . $colorCode;
    }
    
    // Update size_color_quantities with color-specific quantity in new format
    // Structure: { "Size": { "Color": { "quantity": 10, "sku": "BASE-S-WHI" } } }
    if (!isset($sizeColorQuantities[$size])) {
        $sizeColorQuantities[$size] = [];
    }
    $sizeColorQuantities[$size][$color] = [
        'quantity' => $amount,
        'sku' => $variantSku
    ];
    
    // Also update size_quantities for backward compatibility
    // Calculate total for this size (sum of all colors)
    $sizeTotal = 0;
    foreach ($sizeColorQuantities[$size] as $colorKey => $colorData) {
        if (is_array($colorData) && isset($colorData['quantity'])) {
            $sizeTotal += intval($colorData['quantity']);
        }
    }
    $sizeQuantities[$size] = $sizeTotal;

    // Calculate new total stock from size_color_quantities
    $newStock = 0;
    foreach ($sizeColorQuantities as $sizeKey => $colors) {
        if (is_array($colors)) {
            foreach ($colors as $colorKey => $colorData) {
                if (is_array($colorData) && isset($colorData['quantity'])) {
                    $newStock += intval($colorData['quantity']);
                }
            }
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

    // Collect all unique colors from size_color_quantities for the color column
    $allColors = $existingColors;
    foreach ($sizeColorQuantities as $sizeKey => $colors) {
        if (is_array($colors)) {
            foreach (array_keys($colors) as $colorKey) {
                if (!in_array($colorKey, $allColors)) {
                    $allColors[] = $colorKey;
                }
            }
        }
    }
    $updatedColors = json_encode($allColors);

    $updatedSizeColorQuantities = json_encode($sizeColorQuantities);
    $updatedSizeQuantities = json_encode($sizeQuantities);

    // Generate variant SKUs for all colors
    $variantSkus = [];
    foreach ($sizeColorQuantities as $sizeKey => $colors) {
        if (is_array($colors)) {
            foreach ($colors as $colorKey => $colorData) {
                $colorCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $colorKey));
                // For simple products (empty sizeKey), use baseSku-COLORCODE format
                // For products with sizes, use baseSku-SIZE-COLORCODE format
                if (empty($sizeKey)) {
                    $variantSkuKey = $colorKey;
                    $variantSkus[$variantSkuKey] = $baseSku . '-' . $colorCode;
                } else {
                    $variantSkuKey = $sizeKey . '-' . $colorKey;
                    $variantSkus[$variantSkuKey] = $baseSku . '-' . $sizeKey . '-' . $colorCode;
                }
            }
        }
    }
    $updatedVariantSkus = json_encode($variantSkus);

    // Update query - now includes color column
    $updateStmt = $conn->prepare("UPDATE inventory SET size_quantities = ?, variant_skus = ?, size_color_quantities = ?, color = ?, stock = ?, status = ? WHERE sku = ?");
    $updateStmt->bind_param("ssssiss", $updatedSizeQuantities, $updatedVariantSkus, $updatedSizeColorQuantities, $updatedColors, $newStock, $newStatus, $baseSku);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantity added successfully', 'variantSku' => $variantSku, 'baseSku' => $baseSku]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update quantity']);
    }

    $stmt->close();
    $updateStmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
