<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/connection.php';

// Check if color column exists in sale_items table, if not create it
$checkColumn = $conn->query("SHOW COLUMNS FROM sale_items LIKE 'color'");
if (!$checkColumn || $checkColumn->num_rows == 0) {
    $conn->query("ALTER TABLE sale_items ADD COLUMN color VARCHAR(100) DEFAULT ''");
}

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
        $color = $product['color'] ?? '';

        if ($size !== 'N/A' && $quantity > 0 && !empty($color)) {
            // Fetch current size_color_quantities
            $stmt_check = $conn->prepare("SELECT size_color_quantities FROM inventory WHERE id = ?");
            $stmt_check->bind_param("s", $product_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $size_color_quantities = json_decode($row['size_color_quantities'] ?? '{}', true);

                // Check if requested quantity is available for this size and color
                // size_color_quantities stores object like {"sku": "...", "quantity": 2}
                $colorData = $size_color_quantities[$size][$color] ?? null;
                if (is_array($colorData) && isset($colorData['quantity'])) {
                    $available_stock = intval($colorData['quantity']);
                } else {
                    $available_stock = intval($colorData ?? 0);
                }
                
                if ($quantity > $available_stock) {
                    throw new Exception("Insufficient stock for size $size, color $color. Available: $available_stock, Requested: $quantity");
                }
            } else {
                throw new Exception("Product not found in inventory");
            }
            $stmt_check->close();
        } elseif ($size !== 'N/A' && $quantity > 0) {
            // Fallback to size_quantities if no color specified
            $stmt_check = $conn->prepare("SELECT size_quantities FROM inventory WHERE id = ?");
            $stmt_check->bind_param("s", $product_id);
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

    // Insert each product into sale_items table (now with color)
    $stmt_item = $conn->prepare("INSERT INTO sale_items (id, sale_id, product_id, quantity, price, size, color) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($products as $product) {
        $item_id = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
        $product_id = $product['id'] ?? 0;
        $quantity = $product['quantity'] ?? 0;
        $price = $product['price'] ?? 0;
        $size = $product['size'] ?? 'N/A';
        $color = $product['color'] ?? '';
        $stmt_item->bind_param("ssiidss", $item_id, $sale_id, $product_id, $quantity, $price, $size, $color);
        $stmt_item->execute();
    }

    // Update inventory stock for each sold product
    $stmt_update = $conn->prepare("UPDATE inventory SET size_quantities = ?, size_color_quantities = ?, stock = ? WHERE id = ?");
    foreach ($products as $product) {
        $product_id = $product['id'] ?? 0;
        $quantity = $product['quantity'] ?? 0;
        $size = $product['size'] ?? 'N/A';
        $color = $product['color'] ?? '';

        if ($size !== 'N/A' && $quantity > 0) {
            // Fetch current size_quantities and size_color_quantities
            $stmt_fetch = $conn->prepare("SELECT size_quantities, size_color_quantities FROM inventory WHERE id = ?");
            $stmt_fetch->bind_param("s", $product_id);
            $stmt_fetch->execute();
            $result = $stmt_fetch->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $size_quantities = json_decode($row['size_quantities'] ?? '{}', true);
                $size_color_quantities = json_decode($row['size_color_quantities'] ?? '{}', true);

                if (!empty($color) && isset($size_color_quantities[$size][$color])) {
                    // Handle object format {"sku": "...", "quantity": 2}
                    $colorData = $size_color_quantities[$size][$color];
                    if (is_array($colorData) && isset($colorData['quantity'])) {
                        $size_color_quantities[$size][$color]['quantity'] = max(0, intval($colorData['quantity']) - $quantity);
                    } else {
                        // Simple number format
                        $size_color_quantities[$size][$color] = max(0, intval($colorData) - $quantity);
                    }
                    
                    // Recalculate size_quantities from size_color_quantities
                    $sizeTotal = 0;
                    if (isset($size_color_quantities[$size]) && is_array($size_color_quantities[$size])) {
                        foreach ($size_color_quantities[$size] as $cData) {
                            if (is_array($cData) && isset($cData['quantity'])) {
                                $sizeTotal += intval($cData['quantity']);
                            } else {
                                $sizeTotal += intval($cData);
                            }
                        }
                    }
                    $size_quantities[$size] = $sizeTotal;
                } else {
                    // Fallback to size_quantities only
                    if (isset($size_quantities[$size])) {
                        $size_quantities[$size] = max(0, intval($size_quantities[$size]) - $quantity);
                    }
                }

                // Calculate total stock from size_quantities
                $total_stock = array_sum(array_map('intval', $size_quantities));

                // Update the size_quantities, size_color_quantities and stock in inventory
                $updated_quantities = json_encode($size_quantities);
                $updated_color_quantities = json_encode($size_color_quantities);
                $stmt_update->bind_param("sssi", $updated_quantities, $updated_color_quantities, $total_stock, $product_id);
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
