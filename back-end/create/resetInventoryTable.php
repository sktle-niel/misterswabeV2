<?php
// This script will drop and recreate the inventory table with all necessary columns
// WARNING: This will delete ALL existing data!

include '../../config/connection.php';

// First, get existing data to preserve it
$existingData = [];
$result = $conn->query("SELECT * FROM inventory");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $existingData[] = $row;
    }
}

// Drop the existing table
$conn->query("DROP TABLE IF EXISTS inventory");

// Create new table with all necessary columns
$sql = "CREATE TABLE inventory (
    id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) UNIQUE NOT NULL,
    category VARCHAR(100),
    price DECIMAL(10,2),
    stock INT DEFAULT 0,
    size VARCHAR(255),
    size_quantities JSON,
    size_color_quantities JSON,
    color JSON,
    images JSON,
    status VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($conn->query($sql) === TRUE) {
    echo "Table 'inventory' created successfully!<br>";
    
    // Restore existing data
    if (!empty($existingData)) {
        $restored = 0;
        foreach ($existingData as $row) {
            $id = $conn->real_escape_string($row['id']);
            $name = $conn->real_escape_string($row['name']);
            $sku = $conn->real_escape_string($row['sku']);
            $category = $conn->real_escape_string($row['category']);
            $price = floatval($row['price']);
            $stock = intval($row['stock']);
            $size = $conn->real_escape_string($row['size']);
            $sizeQuantities = $conn->real_escape_string($row['size_quantities'] ?? '{}');
            $sizeColorQuantities = $conn->real_escape_string($row['size_color_quantities'] ?? '{}');
            $color = $conn->real_escape_string($row['color'] ?? '[]');
            $images = $conn->real_escape_string($row['images'] ?? '[]');
            $status = $conn->real_escape_string($row['status']);
            
            $insertSql = "INSERT INTO inventory (id, name, sku, category, price, stock, size, size_quantities, size_color_quantities, color, images, status)
                          VALUES ('$id', '$name', '$sku', '$category', $price, $stock, '$size', '$sizeQuantities', '$sizeColorQuantities', '$color', '$images', '$status')";
            
            if ($conn->query($insertSql)) {
                $restored++;
            }
        }
        echo "Restored $restored products with their data!<br>";
    }
    
    echo "<br>Done! The inventory table has been reset with all necessary columns.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
