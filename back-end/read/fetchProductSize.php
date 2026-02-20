<?php
include '../../config/connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$sku = $_GET['sku'] ?? '';

if (empty($sku)) {
    echo json_encode(['success' => false, 'message' => 'SKU is required']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT size FROM inventory WHERE sku = ?");
    $stmt->bind_param("s", $sku);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['success' => true, 'size' => $row['size']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
