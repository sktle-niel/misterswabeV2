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
    // Fetch the product by base SKU
    $stmt = $conn->prepare("SELECT sku, size, size_quantities FROM inventory WHERE sku = ?");
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

        // If size is a comma-separated string, split it
        if (strpos($sizeString, ',') !== false) {
            $sizeArray = array_map('trim', explode(',', $sizeString));
            $sizes = [];
            foreach ($sizeArray as $size) {
                $quantity = (int)($sizeQuantities[$size] ?? 0);
                $sizes[] = [
                    'sku' => $baseSku . '-' . $size,
                    'size' => $size,
                    'stock' => $quantity
                ];
            }
        } else {
            // Single size
            $quantity = (int)($sizeQuantities[$sizeString] ?? 0);
            $sizes = [[
                'sku' => $baseSku . '-' . $sizeString,
                'size' => $sizeString,
                'stock' => $quantity
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
