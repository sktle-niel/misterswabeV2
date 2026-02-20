<?php
header('Content-Type: application/json');
include_once '../../config/connection.php';

$response = [
    'totalProducts' => 0,
    'lowStockItems' => 0,
    'totalRevenue' => 0,
    'bestSeller' => ['name' => 'N/A', 'quantity' => 0],
    'totalOrders' => 0,
    'activeCustomers' => 0,
    'conversionRate' => 0,
    'salesData' => [],
    'categoryData' => []
];

// 1. Total Products
$sql = "SELECT COUNT(*) as total FROM inventory";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['totalProducts'] = (int)$row['total'];
}

// 2. Low Stock Items (stock < 10)
$sql = "SELECT COUNT(*) as total FROM inventory WHERE stock < 10";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['lowStockItems'] = (int)$row['total'];
}

// 3. Total Revenue
$sql = "SELECT COALESCE(SUM(total_amount), 0) as total FROM sales";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['totalRevenue'] = (float)$row['total'];
}

// 4. Best Seller (product with most sales)
$sql = "SELECT i.name, SUM(si.quantity) as total_sold 
        FROM sale_items si 
        JOIN inventory i ON si.product_id = i.id 
        GROUP BY i.id, i.name 
        ORDER BY total_sold DESC 
        LIMIT 1";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['bestSeller'] = [
        'name' => $row['name'],
        'quantity' => (int)$row['total_sold']
    ];
}

// 5. Total Orders
$sql = "SELECT COUNT(*) as total FROM sales";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['totalOrders'] = (int)$row['total'];
}

// 6. Active Customers
$sql = "SELECT COUNT(*) as total FROM users WHERE user_type = 'customer' AND status = 'active'";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['activeCustomers'] = (int)$row['total'];
}

// 7. Conversion Rate (placeholder - can be calculated based on actual data)
$response['conversionRate'] = 3.2;

// 8. Sales Data for Chart (last 6 months)
$sql = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            SUM(total_amount) as total
        FROM sales 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(created_at, '%Y-%m')
        ORDER BY month ASC";
$result = $conn->query($sql);
$salesData = [];
while ($row = $result->fetch_assoc()) {
    $salesData[] = [
        'month' => date('M', strtotime($row['month'] . '-01')),
        'total' => (float)$row['total']
    ];
}
$response['salesData'] = $salesData;

// 9. Sales by Category
$sql = "SELECT 
            i.category,
            SUM(si.quantity * si.price) as total
        FROM sale_items si 
        JOIN inventory i ON si.product_id = i.id
        WHERE MONTH(si.created_at) = MONTH(CURDATE()) AND YEAR(si.created_at) = YEAR(CURDATE())
        GROUP BY i.category
        ORDER BY total DESC";
$result = $conn->query($sql);
$categoryData = [];
while ($row = $result->fetch_assoc()) {
    $categoryData[] = [
        'category' => $row['category'],
        'total' => (float)$row['total']
    ];
}
$response['categoryData'] = $categoryData;

// If no data for current month, get all-time data
if (empty($categoryData)) {
    $sql = "SELECT 
                i.category,
                SUM(si.quantity * si.price) as total
            FROM sale_items si 
            JOIN inventory i ON si.product_id = i.id
            GROUP BY i.category
            ORDER BY total DESC";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $categoryData[] = [
            'category' => $row['category'],
            'total' => (float)$row['total']
        ];
    }
    $response['categoryData'] = $categoryData;
}

echo json_encode($response);
$conn->close();
