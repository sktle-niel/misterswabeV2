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

if (empty($sku) || $amount < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid SKU or amount']);
    exit;
}

if (empty($color)) {
    echo json_encode(['success' => false, 'message' => 'Color is required']);
    exit;
}

try {
    include '../../config/connection.php';

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

    // Fetch current product data including size_quantities
    $stmt = $conn->prepare("SELECT size, size_quantities, size_color_quantities, variant_skus FROM inventory WHERE sku = ?");
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
    $variantSkus = json_decode($row['variant_skus'] ?? '{}', true);
    
    if (!is_array($sizeQuantities)) $sizeQuantities = [];
    if (!is_array($sizeColorQuantities)) $sizeColorQuantities = [];
    if (!is_array($variantSkus)) $variantSkus = [];
    
    // Update size_color_quantities with color-specific quantity
    // Structure: { "Size": { "Color": quantity } }
    if (!isset($sizeColorQuantities[$size])) {
        $sizeColorQuantities[$size] = [];
    }
    $sizeColorQuantities[$size][$color] = $amount;
    
    // Also update size_quantities for backward compatibility
    // Calculate total for this size (sum of all colors)
    $sizeTotal = array_sum($sizeColorQuantities[$size]);
    $sizeQuantities[$size] = $sizeTotal;

    // Calculate new total stock from size_color_quantities
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

    // Generate variant SKUs
    $variantSkus = [];
    foreach ($sizeColorQuantities as $sizeKey => $colors) {
        if (is_array($colors)) {
            foreach ($colors as $colorKey => $qty) {
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

    $colorCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color));
    // For simple products (empty size), use baseSku-COLORCODE format
    if (empty($size)) {
        $variantSku = $baseSku . '-' . $colorCode;
    } else {
        $variantSku = $baseSku . '-' . $size . '-' . $colorCode;
    }

    // Update query - uses size_quantities, size_color_quantities and variant_skus
    $updateStmt = $conn->prepare("UPDATE inventory SET size_quantities = ?, variant_skus = ?, size_color_quantities = ?, stock = ?, status = ? WHERE sku = ?");
    $updateStmt->bind_param("sssiss", $updatedSizeQuantities, $updatedVariantSkus, $updatedSizeColorQuantities, $newStock, $newStatus, $baseSku);

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
