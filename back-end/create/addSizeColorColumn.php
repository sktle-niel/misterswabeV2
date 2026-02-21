<?php
// This script adds the size_color_quantities column to the inventory table

include '../../config/connection.php';

// Check if column exists
$checkColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'size_color_quantities'");

if (!$checkColumn || $checkColumn->num_rows == 0) {
    // Create the column if it doesn't exist
    $result = $conn->query("ALTER TABLE inventory ADD COLUMN size_color_quantities JSON NULL");
    
    if ($result) {
        echo "Column 'size_color_quantities' created successfully!";
    } else {
        echo "Error creating column: " . $conn->error;
    }
} else {
    echo "Column 'size_color_quantities' already exists.";
}

$conn->close();
