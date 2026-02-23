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

if (empty($sku) || $amount < 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid SKU or amount']);
    exit;
}

if (empty($color)) {
    echo json_encode(['success' => false, 'message' => 'Color is required']);
    exit;
}

// Extract size from the POST data (the frontend sends it)
// For simple products, size can be empty string
$size = $_POST['size'] ?? '';

try {
    // Include database connection
    include '../../config/connection.php';

    // Find the base SKU - try exact match first, then try to find by pattern
    $baseSku = $sku;
    
    // First check if the exact SKU exists
    $checkStmt = $conn->prepare("SELECT sku FROM inventory WHERE sku = ?");
    $checkStmt->bind_param("s", $sku);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        // Exact SKU found - this is the base SKU
        $baseSku = $sku;
    } else {
        // SKU not found exactly, try to find base SKU by pattern
        // Try removing size and color suffix (last 2 parts after splitting by -)
        $skuParts = explode('-', $sku);
        if (count($skuParts) >= 3) {
            // Try different combinations to find base SKU
            $foundBase = false;
            for ($i = count($skuParts) - 1; $i >= 1; $i--) {
                $potentialBase = implode('-', array_slice($skuParts, 0, $i));
                $checkStmt2 = $conn->prepare("SELECT sku FROM inventory WHERE sku = ?");
                $checkStmt2->bind_param("s", $potentialBase);
                $checkStmt2->execute();
                $checkResult2 = $checkStmt2->get_result();
                
                if ($checkResult2->num_rows > 0) {
                    $baseSku = $potentialBase;
                    $foundBase = true;
                    $checkStmt2->close();
                    break;
                }
                $checkStmt2->close();
            }
            
            if (!$foundBase) {
                // Use the first part as fallback (for very short SKUs)
                $baseSku = $skuParts[0];
            }
        } else {
            // Single part SKU - use as base
            $baseSku = $sku;
        }
    }
    $checkStmt->close();

    // Fetch current product data
    $stmt = $conn->prepare("SELECT name, category, size, size_quantities, size_color_quantities, color FROM inventory WHERE sku = ?");
    $stmt->bind_param("s", $baseSku);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found: ' . $baseSku]);
        exit;
    }

    $row = $result->fetch_assoc();
    
    // Get product details
    $productName = $row['name'];
    $productCategory = $row['category'];
    $productSize = $row['size'];
    
    // Get existing data
    $sizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
    $sizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
    $existingColors = json_decode($row['color'] ?? '[]', true);
    
    // Initialize arrays if null
    if (!is_array($sizeColorQuantities)) $sizeColorQuantities = [];
    if (!is_array($sizeQuantities)) $sizeQuantities = [];
    if (!is_array($existingColors)) $existingColors = [];
    
    // Check if this is a new color for this size
    $isNewColor = !isset($sizeColorQuantities[$size]) || !isset($sizeColorQuantities[$size][$color]);
    
    // Update size_color_quantities with color-specific quantity
    // Structure: { "Size": { "Color": quantity } }
    if (!isset($sizeColorQuantities[$size])) {
        $sizeColorQuantities[$size] = [];
    }
    $sizeColorQuantities[$size][$color] = $amount;
    
    // Update size_quantities (sum of all colors per size)
    $sizeQuantities[$size] = array_sum($sizeColorQuantities[$size]);
    
    // Collect all unique colors
    $allColors = $existingColors;
    if (!in_array($color, $allColors)) {
        $allColors[] = $color;
    }
    
    // Add any new colors from size_color_quantities
    foreach ($sizeColorQuantities as $sizeKey => $colors) {
        foreach (array_keys($colors) as $colorKey) {
            if (!in_array($colorKey, $allColors)) {
                $allColors[] = $colorKey;
            }
        }
    }

    // Calculate new total stock from size_color_quantities
    $newStock = 0;
    foreach ($sizeColorQuantities as $sizeKey => $colors) {
        $newStock += array_sum($colors);
    }

    // Determine new status based on stock
    if ($newStock == 0) {
        $newStatus = 'Out of Stock';
    } elseif ($newStock <= 10) {
        $newStatus = 'Low Stock';
    } else {
        $newStatus = 'In Stock';
    }

    // Encode back to JSON
    $updatedSizeColorQuantities = json_encode($sizeColorQuantities);
    $updatedSizeQuantities = json_encode($sizeQuantities);
    $updatedColors = json_encode($allColors);

    // Generate the variant SKU for this size-color combination (color first, then size)
    // For simple products (empty size), just use base SKU with color
    if (!empty($size)) {
        $variantSku = $baseSku . '-' . strtoupper(substr($color, 0, 3)) . '-' . $size;
    } else {
        $variantSku = $baseSku . '-' . strtoupper(substr($color, 0, 3));
    }

    // Update the database with size_quantities, size_color_quantities, color, stock, and status
    $updateStmt = $conn->prepare("UPDATE inventory SET size_quantities = ?, size_color_quantities = ?, color = ?, stock = ?, status = ? WHERE sku = ?");
    $updateStmt->bind_param("sssiss", $updatedSizeQuantities, $updatedSizeColorQuantities, $updatedColors, $newStock, $newStatus, $baseSku);

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
