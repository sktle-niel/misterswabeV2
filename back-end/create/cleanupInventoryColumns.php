<?php
/**
 * This script removes redundant columns from the inventory table.
 * Run this file once to clean up the database.
 * 
 * Redundant columns being removed:
 * - size: comma-separated sizes (can be derived from size_color_quantities keys)
 * - size_quantities: total per size (can be calculated from size_color_quantities)
 * - color: JSON array of colors (can be derived from size_color_quantities)
 */

include '../../config/connection.php';

// Run the cleanup SQL
$sql = "ALTER TABLE inventory 
        DROP COLUMN size, 
        DROP COLUMN size_quantities, 
        DROP COLUMN color";

if ($conn->query($sql) === TRUE) {
    echo "Successfully removed redundant columns: size, size_quantities, color";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
