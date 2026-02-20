<?php
include '../../config/connection.php';

function fetchHomeProducts() {
    global $conn;

    $sql = "SELECT `id`, `name`, `sku`, `category`, `price`, `stock`, `size`, `images`, `status`, `size_quantities`, `color` FROM `inventory` WHERE 1";

    $result = $conn->query($sql);

    $products = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Decode images JSON
            $images = json_decode($row['images'], true);
            if (!$images || empty($images)) {
                $images = ["https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90"];
            }

            $adjustedImages = array_map(function($img) {
                return '../../../' . $img; // From public/customer/pages/ to uploads/
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
                'image' => $adjustedImages[0] ?? 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90', // Use first image as main image
                'brand' => $row['category'],
                'name' => $row['name'],
                'price' => '₱' . number_format($row['price'], 2),
                'sizes' => $row['size'] ?: '',
                'colors' => $colors,
                'size_quantities' => $row['size_quantities'] ?: '{}'
            ];



        }
    }

    return $products;
}
?>
