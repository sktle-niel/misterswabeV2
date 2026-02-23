<?php
include '../../config/connection.php';

function fetchProducts() {
    global $conn;

    $sql = "SELECT i.id, i.name, i.sku, i.category, i.price, i.stock, i.size, i.images, i.status, i.size_color_quantities, i.color FROM inventory i ORDER BY i.id DESC";
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
                return '../../../' . $img; // From public/administrator/pages/ to uploads/
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
                // Also get color from the color column for simple products
                $colorData = json_decode($row['color'] ?? '[]', true);
                if (!empty($colorData) && is_array($colorData)) {
                    $colorDisplay = implode(', ', $colorData);
                }
            }

            // Format size with EUR prefix for numeric sizes
            $sizeValue = $row['size'] ?: 'N/A';
            if ($sizeValue !== 'N/A') {
                $sizeArray = array_map('trim', explode(',', $sizeValue));
                $formattedSizes = array_map(function($s) {
                    // Check if the size is numeric (like 39, 40, etc.)
                    if (is_numeric($s)) {
                        return 'EUR ' . $s;
                    }
                    return $s;
                }, $sizeArray);
                $sizeValue = implode(', ', $formattedSizes);
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
                'size_quantities' => $row['size_quantities'],
                'size_color_quantities' => $row['size_color_quantities'],

                'image' => $adjustedImages[0] ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90', // Use first image as main image
                'images' => $adjustedImages, // Keep all images with adjusted paths
                'status' => $row['status']
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
