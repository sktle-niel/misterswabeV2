<?php
// This script adds the variant_skus column to the inventory table

include '../../config/connection.php';

// Check if column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'variant_skus'");

if (!$checkColumn || $checkColumn->num_rows == 0) {
    // Create the column if it doesn't exist
    $result = $conn->query("ALTER TABLE inventory ADD COLUMN variant_skus JSON NULL");
    
    if ($result) {
        echo "Successfully added 'variant_skus' column to inventory table!";
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'variant_skus' already exists.";
}

$conn->close();
?>
