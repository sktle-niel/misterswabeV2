<?php
ini_set('display_errors', 0);
error_reporting(0);

include '../../config/connection.php';

function removeProduct($sku) {
    global $conn;

    // Sanitize input
    $sku = mysqli_real_escape_string($conn, $sku);

    // First, get the product to check if images need to be deleted (optional)
    $selectSql = "SELECT images FROM inventory WHERE sku = '$sku'";
    $result = $conn->query($selectSql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $images = json_decode($row['images'], true);
        // Optionally delete image files from uploads directory
        if ($images && is_array($images)) {
            foreach ($images as $image) {
                $filePath = '../../' . $image; // Adjust path as needed
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    // Delete the product from database
    $deleteSql = "DELETE FROM inventory WHERE sku = '$sku'";

    if ($conn->query($deleteSql) === TRUE) {
        return ['success' => true, 'message' => 'Product deleted successfully'];
    } else {
        error_log('Database error: ' . $conn->error);
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
}

// If called directly via POST, handle it
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $sku = $_POST['sku'] ?? '';

    if (empty($sku)) {
        $response = ['success' => false, 'message' => 'SKU is required'];
    } else {
        $response = removeProduct($sku);
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
