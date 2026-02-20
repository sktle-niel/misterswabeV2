<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/connection.php';

$sale_id = $_GET['sale_id'] ?? '';

if (empty($sale_id)) {
    echo json_encode(['success' => false, 'error' => 'Sale ID is required']);
    exit;
}

try {
    // Fetch sale details
    $stmt_sale = $conn->prepare("SELECT id, total_amount, payment_method, created_at FROM sales WHERE id = ?");
    $stmt_sale->bind_param("s", $sale_id);
    $stmt_sale->execute();
    $result_sale = $stmt_sale->get_result();

    if ($result_sale->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Sale not found']);
        exit;
    }

    $sale = $result_sale->fetch_assoc();
    $stmt_sale->close();

    // Fetch sale items with product details
    $stmt_items = $conn->prepare("
        SELECT si.quantity, si.price, si.size, i.name, i.sku
        FROM sale_items si
        JOIN inventory i ON si.product_id = i.id
        WHERE si.sale_id = ?
    ");
    $stmt_items->bind_param("s", $sale_id);
    $stmt_items->execute();
    $result_items = $stmt_items->get_result();

    $items = [];
    while ($row = $result_items->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt_items->close();

    $sale['items'] = $items;

    echo json_encode(['success' => true, 'sale' => $sale]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
