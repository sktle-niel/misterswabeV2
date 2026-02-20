<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/connection.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Get total count for pagination
    $countSql = "SELECT COUNT(DISTINCT s.id) as total FROM sales s";
    $countResult = $conn->query($countSql);
    $totalSales = $countResult->fetch_assoc()['total'];
    $totalPages = ceil($totalSales / $limit);

    // Query to fetch sales with their items and product details with pagination
    $sql = "SELECT s.id as sale_id, s.total_amount, s.payment_method, s.created_at,
                   si.quantity, si.price, si.size,
                   i.name as product_name
            FROM sales s
            LEFT JOIN sale_items si ON s.id = si.sale_id
            LEFT JOIN inventory i ON si.product_id = i.id
            ORDER BY s.created_at DESC, s.id DESC
            LIMIT ? OFFSET ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        throw new Exception("Database query failed: " . $conn->error);
    }

    $sales = [];
    while ($row = $result->fetch_assoc()) {
        $sales[] = [
            'sale_id' => $row['sale_id'],
            'total_amount' => (float)$row['total_amount'],
            'payment_method' => $row['payment_method'],
            'created_at' => $row['created_at'],
            'product_name' => $row['product_name'] ?: 'Unknown Product',
            'quantity' => (int)$row['quantity'],
            'size' => $row['size'] ?: 'N/A',
            'price' => (float)$row['price']
        ];
    }

    echo json_encode([
        'sales' => $sales,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $totalPages,
            'total_sales' => $totalSales,
            'per_page' => $limit
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
