<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/connection.php';

$total_amount = $_POST['totalAmount'] ?? 0;
$payment_method = $_POST['paymentMethod'] ?? '';
$products = $_POST['products'] ?? [];

$conn->begin_transaction();

try {
    // Validate stock availability for each product
    foreach ($products as $product) {
        $product_id = $product['id'] ?? 0;
        $quantity = $product['quantity'] ?? 0;
        $size = $product['size'] ?? 'N/A';

        if ($size !== 'N/A' && $quantity > 0) {
            // Fetch current size_quantities
            $stmt_check = $conn->prepare("SELECT size_quantities FROM inventory WHERE id = ?");
            $stmt_check->bind_param("i", $product_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $size_quantities = json_decode($row['size_quantities'] ?? '{}', true);

                // Check if requested quantity is available
                $available_stock = $size_quantities[$size] ?? 0;
                if ($quantity > $available_stock) {
                    throw new Exception("Insufficient stock for size $size. Available: $available_stock, Requested: $quantity");
                }
            } else {
                throw new Exception("Product not found in inventory");
            }
            $stmt_check->close();
        }
    }

    // Generate 7-digit sale id
    $sale_id = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);

    // Insert into sales table
    $stmt = $conn->prepare("INSERT INTO sales (id, total_amount, payment_method) VALUES (?, ?, ?)");
    $stmt->bind_param("sds", $sale_id, $total_amount, $payment_method);
    $stmt->execute();

    // Insert each product into sale_items table
    $stmt_item = $conn->prepare("INSERT INTO sale_items (id, sale_id, product_id, quantity, price, size) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $item_id = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
        $product_id = $product['id'] ?? 0;
        $quantity = $product['quantity'] ?? 0;
        $price = $product['price'] ?? 0;
        $size = $product['size'] ?? 'N/A';
        $stmt_item->bind_param("ssiids", $item_id, $sale_id, $product_id, $quantity, $price, $size);
        $stmt_item->execute();
    }

    // Update inventory stock for each sold product
    $stmt_update = $conn->prepare("UPDATE inventory SET size_quantities = ?, stock = ? WHERE id = ?");
    foreach ($products as $product) {
        $product_id = $product['id'] ?? 0;
        $quantity = $product['quantity'] ?? 0;
        $size = $product['size'] ?? 'N/A';

        if ($size !== 'N/A' && $quantity > 0) {
            // Fetch current size_quantities
            $stmt_fetch = $conn->prepare("SELECT size_quantities FROM inventory WHERE id = ?");
            $stmt_fetch->bind_param("i", $product_id);
            $stmt_fetch->execute();
            $result = $stmt_fetch->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $size_quantities = json_decode($row['size_quantities'] ?? '{}', true);

                // Deduct the sold quantity from the selected size
                if (isset($size_quantities[$size])) {
                    $size_quantities[$size] = max(0, $size_quantities[$size] - $quantity);
                }

                // Calculate total stock from size_quantities
                $total_stock = array_sum($size_quantities);

                // Update the size_quantities and stock in inventory
                $updated_quantities = json_encode($size_quantities);
                $stmt_update->bind_param("sii", $updated_quantities, $total_stock, $product_id);
                $stmt_update->execute();
            }
            $stmt_fetch->close();
        }
    }
    $stmt_update->close();

    $conn->commit();
    echo json_encode(['success' => true, 'sale_id' => $sale_id]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
