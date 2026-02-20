<?php
include '../../config/connection.php';

$query = "ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'active'";

if ($conn->query($query) === TRUE) {
    echo "Column 'status' added successfully to users table";
} else {
    echo "Error adding column: " . $conn->error;
}

$conn->close();
?>
