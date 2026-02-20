<?php
include '../../config/connection.php';

function fetchProducts() {
    global $conn;

    $sql = "SELECT i.id, i.name, i.sku, i.category, i.price, i.stock, i.size, i.images, i.status, i.size_quantities, i.color FROM inventory i ORDER BY i.id DESC";
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

            // Calculate stock from size_quantities
            $size_quantities_array = json_decode($row['size_quantities'] ?? '{}', true);
            $calculated_stock = array_sum($size_quantities_array);

            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'sku' => $row['sku'],
                'category' => $row['category'],
                'price' => '₱' . number_format($row['price'], 2),
                'stock' => $calculated_stock,
                'size' => $row['size'] ?: 'N/A',
                'size_quantities' => $row['size_quantities'],
                'color' => $row['color'] ? str_replace(['"', '[', ']'], '', $row['color']) : 'N/A',

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
