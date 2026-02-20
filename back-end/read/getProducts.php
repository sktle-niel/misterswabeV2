<?php
function getProducts($category = 'all', $search = '') {
    include '../../config/connection.php';

    $query = "SELECT id, name, sku, category, price, stock, size, images, status, color, size_quantities FROM inventory WHERE 1";

    $result = $conn->query($query);
    $products = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Decode images JSON
            $images = json_decode($row['images'], true);
            if (!$images || empty($images)) {
                $images = ["https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90"];
            }
            $adjustedImages = array_map(function($img) {
                return '../../' . $img; // From public/customer/pages/ to uploads/
            }, $images);

            // Decode colors JSON
            $colors = $row['color'] ?: '';
            if ($colors) {
                $colorsArray = json_decode($colors, true);
                if (is_array($colorsArray)) {
                    $colors = implode(', ', $colorsArray);
                }
            }

            $products[] = [
                'image' => $adjustedImages[0] ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90',
                'brand' => ucfirst($row['category']),
                'name' => $row['name'],
                'price' => '₱' . number_format($row['price'], 0, '.', ','),
                'sizes' => $row['size'],
                'category' => $row['category'],
                'colors' => $colors,
                'size_quantities' => $row['size_quantities'] ?: '{}'
            ];

        }
    }

    // Filter products based on category and search
    $filteredProducts = [];
    foreach ($products as $product) {
        $matchesCategory = $category === 'all' || strtolower($product['category']) === strtolower($category);
        $matchesSearch = !$search || stripos($product['name'], $search) !== false || stripos($product['brand'], $search) !== false;
        if ($matchesCategory && $matchesSearch) {
            $filteredProducts[] = $product;
        }
    }

    return $filteredProducts;
}
?>
