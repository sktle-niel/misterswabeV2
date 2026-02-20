<?php
include '../utils/skuUtils.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$sku = $_POST['sku'] ?? '';
$amount = (int)($_POST['amount'] ?? 0);

if (empty($sku) || $amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid SKU or amount']);
    exit;
}

// Extract base SKU and size from the variant SKU (e.g., "BASE-S" -> base: "BASE", size: "S")
$skuParts = explode('-', $sku);
$size = array_pop($skuParts); // Last part is size
$baseSku = implode('-', $skuParts); // Rest is base SKU

try {
    // Include database connection
    include '../../config/connection.php';

    // Fetch current size_quantities for the base product
    $stmt = $conn->prepare("SELECT size_quantities FROM inventory WHERE sku = ?");
    $stmt->bind_param("s", $baseSku);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
        exit;
    }

    $row = $result->fetch_assoc();
    $sizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);

    // Set the quantity for the specific size to the new value
    $sizeQuantities[$size] = $amount;

    // Calculate new total stock
    $newStock = array_sum($sizeQuantities);

    // Determine new status based on stock
    if ($newStock == 0) {
        $newStatus = 'Out of Stock';
    } elseif ($newStock <= 10) {
        $newStatus = 'Low Stock';
    } else {
        $newStatus = 'In Stock';
    }

    // Encode back to JSON
    $updatedSizeQuantities = json_encode($sizeQuantities);

    // Update the database with size_quantities, stock, and status
    $updateStmt = $conn->prepare("UPDATE inventory SET size_quantities = ?, stock = ?, status = ? WHERE sku = ?");
    $updateStmt->bind_param("siss", $updatedSizeQuantities, $newStock, $newStatus, $baseSku);

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
