<?php
// Backend controller to fetch inventory alerts (Out of Stock and Low Stock products)

header('Content-Type: application/json');

include '../../config/connection.php';

// Check if minimum_stock column exists, if not add it
$checkColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'minimum_stock'");
if (!$checkColumn || $checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE inventory ADD COLUMN minimum_stock INT DEFAULT 10");
}

// Get out of stock products (stock = 0)
$outOfStockQuery = "
    SELECT id, name, sku, stock, minimum_stock, status 
    FROM inventory 
    WHERE stock = 0 
    ORDER BY name ASC
";
$outOfStockResult = $conn->query($outOfStockQuery);
$outOfStock = [];
if ($outOfStockResult && $outOfStockResult->num_rows > 0) {
    while ($row = $outOfStockResult->fetch_assoc()) {
        $outOfStock[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'sku' => $row['sku'],
            'current_quantity' => intval($row['stock']),
            'minimum_stock' => intval($row['minimum_stock'] ?? 10),
            'status' => $row['status']
        ];
    }
}

// Get low stock products (stock > 0 but <= minimum_stock)
$lowStockQuery = "
    SELECT id, name, sku, stock, minimum_stock, status 
    FROM inventory 
    WHERE stock > 0 AND stock <= COALESCE(minimum_stock, 10) 
    ORDER BY stock ASC, name ASC
";
$lowStockResult = $conn->query($lowStockQuery);
$lowStock = [];
if ($lowStockResult && $lowStockResult->num_rows > 0) {
    while ($row = $lowStockResult->fetch_assoc()) {
        $lowStock[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'sku' => $row['sku'],
            'current_quantity' => intval($row['stock']),
            'minimum_stock' => intval($row['minimum_stock'] ?? 10),
            'status' => $row['status']
        ];
    }
}

// Calculate totals
$totalOutOfStock = count($outOfStock);
$totalLowStock = count($lowStock);
$totalAlerts = $totalOutOfStock + $totalLowStock;

echo json_encode([
    'success' => true,
    'out_of_stock' => $outOfStock,
    'low_stock' => $lowStock,
    'totals' => [
        'out_of_stock' => $totalOutOfStock,
        'low_stock' => $totalLowStock,
        'total_alerts' => $totalAlerts
    ]
]);

$conn->close();
