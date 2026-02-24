<?php
include '../../config/connection.php';

function fetchProducts() {
    global $conn;

// Check if information column exists
    $informationColumnExists = $conn->query("SHOW COLUMNS FROM inventory LIKE 'information'")->num_rows > 0;
    
// Build query - only select columns that exist
$sql = "SELECT i.id, i.name, i.sku, i.category, i.price, i.stock, i.images, i.status, i.size, i.size_quantities, i.size_color_quantities";
    if ($informationColumnExists) {
        $sql .= ", i.information";
    }
    $sql .= ", i.variant_skus FROM inventory i ORDER BY i.id DESC";
    
    $result = $conn->query($sql);

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Decode images JSON
            $images = json_decode($row['images'], true);
            if (!$images || empty($images)) {
                // $images = ["https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90"];
            }

            $adjustedImages = array_map(function($img) {
                return '../../uploads/' . $img; // From public/administrator/pages/ to uploads/
            }, $images);

            // Calculate stock from size_color_quantities and format color display
            $size_color_quantities = json_decode($row['size_color_quantities'] ?? '{}', true);
            $dbStock = intval($row['stock'] ?? 0);
            $calculated_stock = 0;
            $colorDisplay = 'N/A';
            
            // Check if product has sizes (size_color_quantities has data)
            $hasSizes = !empty($size_color_quantities) && is_array($size_color_quantities);
            
            if ($hasSizes) {
                // Product has sizes - calculate stock from size_color_quantities
                $colorParts = [];
                foreach ($size_color_quantities as $size => $colors) {
                    if (is_array($colors)) {
                        $calculated_stock += array_sum($colors);
                        foreach ($colors as $color => $qty) {
                            if ($qty > 0) {
                                $colorParts[] = $color . ' (' . $qty . ')';
                            }
                        }
                    }
                }
                if (!empty($colorParts)) {
                    $colorDisplay = implode(', ', $colorParts);
                }
            } else {
                // Simple product (no sizes) - use the stock from database
                $calculated_stock = $dbStock;
            }

            // Get sizes - first try size_color_quantities keys, then fall back to size column
            $sizesFromConfig = array_keys($size_color_quantities);
            $sizeValue = '';
            
            if (!empty($sizesFromConfig)) {
                $sizeValue = implode(', ', $sizesFromConfig);
            } elseif (!empty($row['size'])) {
                // Fall back to size column
                $sizeValue = $row['size'];
            }
            
            // Format size with EUR prefix for numeric sizes
            if (!empty($sizeValue)) {
                $sizeArray = array_map('trim', explode(',', $sizeValue));
                $formattedSizes = array_map(function($s) {
                    if (is_numeric($s)) {
                        return 'EUR ' . $s;
                    }
                    return $s;
                }, $sizeArray);
                $sizeValue = implode(', ', $formattedSizes);
            } else {
                $sizeValue = 'N/A';
            }
            
            // Get quantities from size_quantities if size_color_quantities is empty
            $sizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
            
            // If size_color_quantities is empty but we have sizes in size_quantities, show quantities
            if (empty($size_color_quantities) && !empty($sizeQuantities) && is_array($sizeQuantities)) {
                $colorParts = [];
                foreach ($sizeQuantities as $size => $qty) {
                    if ($qty > 0) {
                        $colorParts[] = $size . ' (' . $qty . ')';
                    }
                }
                if (!empty($colorParts)) {
                    $colorDisplay = implode(', ', $colorParts);
                }
            }

            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'sku' => $row['sku'],
                'category' => $row['category'],
                'price' => '₱' . number_format($row['price'], 2),
                'stock' => $dbStock,
                'size' => $sizeValue,
                'color' => $colorDisplay,
                'size_color_quantities' => $row['size_color_quantities'] ?? null,

'image' => $adjustedImages[0] ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90', // Use first image as main image
                'images' => $adjustedImages, // Keep all images with adjusted paths
                'status' => $row['status'],
                'information' => $informationColumnExists ? $row['information'] ?? null : null
            ];

        }
    }

    return $products;
}

// If called directly, return JSON
if (basename($_SERVER['PHP_SELF']) === 'fetchProduct.php') {
    header('Content-Type: application/json');
    echo json_encode(fetchProducts());
}
?>
