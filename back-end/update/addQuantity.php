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

// Extract base SKU and size from the variant SKU (e.g., "BASE-S" -> base: "BASE", size: "S")
$skuParts = explode('-', $sku);
$size = array_pop($skuParts); // Last part is size
$baseSku = implode('-', $skuParts); // Rest is base SKU

try {
    // Include database connection
    include '../../config/connection.php';

    // Fetch current size_quantities, size_color_quantities and color for the base product
    $stmt = $conn->prepare("SELECT size_quantities, size_color_quantities, color FROM inventory WHERE sku = ?");
    $stmt->bind_param("s", $baseSku);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    $row = $result->fetch_assoc();
    
    // Get existing data
    $sizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
    $sizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
    $existingColors = json_decode($row['color'] ?? '[]', true);
    
    // Initialize arrays if null
    if (!is_array($sizeColorQuantities)) $sizeColorQuantities = [];
    if (!is_array($sizeQuantities)) $sizeQuantities = [];
    if (!is_array($existingColors)) $existingColors = [];
    
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

    // Update the database with size_quantities, size_color_quantities, color, stock, and status
    $updateStmt = $conn->prepare("UPDATE inventory SET size_quantities = ?, size_color_quantities = ?, color = ?, stock = ?, status = ? WHERE sku = ?");
    $updateStmt->bind_param("sssiss", $updatedSizeQuantities, $updatedSizeColorQuantities, $updatedColors, $newStock, $newStatus, $baseSku);

    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantity added successfully']);
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
