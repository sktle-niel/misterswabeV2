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
        
        // Normalize: treat 'N/A' as empty for simple products
        if ($size === 'N/A') {
            $size = '';
        }
        if ($color === 'N/A') {
            $color = '';
        }

        if (!empty($size) && $quantity > 0 && !empty($color)) {
            // Product has both size and color
            $stmt_check = $conn->prepare("SELECT size_color_quantities FROM inventory WHERE id = ?");
            $stmt_check->bind_param("s", $product_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $size_color_quantities = json_decode($row['size_color_quantities'] ?? '{}', true);

                // Check if requested quantity is available for this size and color
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
        } elseif (!empty($size) && $quantity > 0 && empty($color)) {
            // Product has size but no color - use size_quantities
            $stmt_check = $conn->prepare("SELECT size_quantities FROM inventory WHERE id = ?");
            $stmt_check->bind_param("s", $product_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $size_quantities = json_decode($row['size_quantities'] ?? '{}', true);

                // Check if requested quantity is available
                $available_stock = intval($size_quantities[$size] ?? 0);
                if ($quantity > $available_stock) {
                    throw new Exception("Insufficient stock for size $size. Available: $available_stock, Requested: $quantity");
                }
            } else {
                throw new Exception("Product not found in inventory");
            }
            $stmt_check->close();
        } elseif (empty($size) && !empty($color)) {
            // Product has no size but has color (color-only product)
            $stmt_check = $conn->prepare("SELECT size_color_quantities FROM inventory WHERE id = ?");
            $stmt_check->bind_param("s", $product_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $size_color_quantities = json_decode($row['size_color_quantities'] ?? '{}', true);

                // Check stock in size_color_quantities with empty string key for color-only products
                $colorData = $size_color_quantities[''][$color] ?? null;
                if (is_array($colorData) && isset($colorData['quantity'])) {
                    $available_stock = intval($colorData['quantity']);
                } else {
                    $available_stock = intval($colorData ?? 0);
                }
                
                if ($quantity > $available_stock) {
                    throw new Exception("Insufficient stock for color $color. Available: $available_stock, Requested: $quantity");
                }
            } else {
                throw new Exception("Product not found in inventory");
            }
            $stmt_check->close();
        } elseif (empty($size) && empty($color)) {
            // Simple product - check main stock column
            $stmt_check = $conn->prepare("SELECT stock FROM inventory WHERE id = ?");
            $stmt_check->bind_param("s", $product_id);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $available_stock = intval($row['stock'] ?? 0);
                if ($quantity > $available_stock) {
                    throw new Exception("Insufficient stock. Available: $available_stock, Requested: $quantity");
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
    $stmt_update = $conn->prepare("UPDATE inventory SET size_quantities = ?, size_color_quantities = ?, variant_skus = ?, stock = ? WHERE id = ?");
    
    foreach ($products as $product) {
        $product_id = $product['id'] ?? 0;
        $quantity = $product['quantity'] ?? 0;
        $size = $product['size'] ?? 'N/A';
        $color = $product['color'] ?? '';
        
        // Normalize: treat 'N/A' as empty for simple products
        if ($size === 'N/A') {
            $size = '';
        }
        if ($color === 'N/A') {
            $color = '';
        }

        if ($quantity > 0) {
            // Fetch current inventory data
            $stmt_fetch = $conn->prepare("SELECT stock, size_quantities, size_color_quantities, variant_skus FROM inventory WHERE id = ?");
            $stmt_fetch->bind_param("s", $product_id);
            $stmt_fetch->execute();
            $result = $stmt_fetch->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $current_stock = intval($row['stock'] ?? 0);
                $size_quantities = json_decode($row['size_quantities'] ?? '{}', true);
                $size_color_quantities = json_decode($row['size_color_quantities'] ?? '{}', true);
                $variant_skus = json_decode($row['variant_skus'] ?? '{}', true);
                
                // Initialize arrays if null
                if (!is_array($size_quantities)) $size_quantities = [];
                if (!is_array($size_color_quantities)) $size_color_quantities = [];
                if (!is_array($variant_skus)) $variant_skus = [];

                if (!empty($size) && !empty($color)) {
                    // Product has both size and color
                    if (isset($size_color_quantities[$size][$color])) {
                        $colorData = $size_color_quantities[$size][$color];
                        if (is_array($colorData) && isset($colorData['quantity'])) {
                            $size_color_quantities[$size][$color]['quantity'] = max(0, intval($colorData['quantity']) - $quantity);
                        } else {
                            $size_color_quantities[$size][$color] = max(0, intval($colorData) - $quantity);
                        }
                        
                        // Update variant_skus for this size-color combination
                        $variantKey = $size . '-' . $color;
                        if (isset($variant_skus[$variantKey])) {
                            // Variant SKU exists - the quantity is already updated in size_color_quantities
                            // The variant_skus mapping stays the same, only quantity changes
                        }
                    }
                    
                    // Recalculate size_quantities from size_color_quantities for this size
                    $sizeTotal = 0;
                    if (isset($size_color_quantities[$size]) && is_array($size_color_quantities[$size])) {
                        foreach ($size_color_quantities[$size] as $cKey => $cData) {
                            if (is_array($cData) && isset($cData['quantity'])) {
                                $sizeTotal += intval($cData['quantity']);
                            } elseif (is_numeric($cData)) {
                                $sizeTotal += intval($cData);
                            }
                        }
                    }
                    $size_quantities[$size] = $sizeTotal;
                    
                } elseif (!empty($size) && empty($color)) {
                    // Product has size but no color - use size_quantities
                    if (isset($size_quantities[$size])) {
                        $size_quantities[$size] = max(0, intval($size_quantities[$size]) - $quantity);
                    }
                    
                    // Also update size_color_quantities if it exists
                    if (isset($size_color_quantities[$size])) {
                        $sizeTotal = 0;
                        foreach ($size_color_quantities[$size] as $cKey => $cData) {
                            if (is_array($cData) && isset($cData['quantity'])) {
                                $sizeTotal += intval($cData['quantity']);
                            } elseif (is_numeric($cData)) {
                                $sizeTotal += intval($cData);
                            }
                        }
                        $size_quantities[$size] = $sizeTotal;
                    }
                    
                } elseif (empty($size) && !empty($color)) {
                    // Color-only product (no size) - use empty string key
                    if (isset($size_color_quantities[''][$color])) {
                        $colorData = $size_color_quantities[''][$color];
                        if (is_array($colorData) && isset($colorData['quantity'])) {
                            $size_color_quantities[''][$color]['quantity'] = max(0, intval($colorData['quantity']) - $quantity);
                        } else {
                            $size_color_quantities[''][$color] = max(0, intval($colorData) - $quantity);
                        }
                    }
                    
                    // Update size_quantities for empty string key
                    $sizeTotal = 0;
                    if (isset($size_color_quantities['']) && is_array($size_color_quantities[''])) {
                        foreach ($size_color_quantities[''] as $cData) {
                            if (is_array($cData) && isset($cData['quantity'])) {
                                $sizeTotal += intval($cData['quantity']);
                            } elseif (is_numeric($cData)) {
                                $sizeTotal += intval($cData);
                            }
                        }
                    }
                    $size_quantities[''] = $sizeTotal;
                    
                } else {
                    // Simple product (no size, no color) - reduce main stock
                    $current_stock = max(0, $current_stock - $quantity);
                }

                // Calculate total stock from size_quantities
                $total_stock = 0;
                foreach ($size_quantities as $key => $value) {
                    if ($key !== '' && is_numeric($value)) {
                        $total_stock += intval($value);
                    }
                }
                
                // For color-only products (empty string key), also add to total
                if (isset($size_quantities['']) && is_numeric($size_quantities[''])) {
                    $total_stock += intval($size_quantities['']);
                }
                
                // If size_quantities is empty or all zero, use the current_stock (simple products)
                if ($total_stock === 0 && empty($size) && empty($color)) {
                    $total_stock = $current_stock;
                }

                // Update the inventory
                $updated_quantities = json_encode($size_quantities);
                $updated_color_quantities = json_encode($size_color_quantities);
                $updated_variant_skus = json_encode($variant_skus);
                
                $stmt_update->bind_param("ssssi", $updated_quantities, $updated_color_quantities, $updated_variant_skus, $total_stock, $product_id);
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
