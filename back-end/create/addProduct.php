<?php
// Start output buffering to prevent header issues
ob_start();

// Enable error logging for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);
ini_set('log_errors', 1);
ini_set('error_log', '../../logs/php_errors.log');

include '../../config/connection.php';
require_once __DIR__ . '/../utils/skuUtils.php';

function addProduct($data) {
    global $conn;

    // Generate random 7-digit ID
    $id = str_pad(mt_rand(0, 9999999), 7, '0', STR_PAD_LEFT);

    // Sanitize inputs
    $name = mysqli_real_escape_string($conn, $data['name']);
    
    // Handle category - store the name directly
    $category = mysqli_real_escape_string($conn, $data['category']);
    
    $price = floatval($data['price']);
    $stock = 0; // Default stock to 0 since no stock field in modal

    // Handle size - create size_quantities with 0 for each selected size
    $sizesInput = isset($_POST['productSizes']) ? trim($_POST['productSizes']) : '';
    $sizeData = [];
    if (!empty($sizesInput)) {
        $sizeData = array_map('trim', explode(',', $sizesInput));
        $sizeData = array_filter($sizeData, function($size) { return !empty($size); });
    }
    $sizeQuantities = [];
    $sizeString = '';
    if (!empty($sizeData)) {
        foreach ($sizeData as $size) {
            $sizeQuantities[$size] = 0; // Initialize with 0 quantity
        }
        $sizeString = implode(', ', $sizeData); // Create comma-separated string
    }
    $sizeQuantitiesJson = json_encode($sizeQuantities);
    
    // Handle color - create JSON array from comma-separated input
    $colorInput = isset($_POST['productColors']) ? trim($_POST['productColors']) : '';
    $colorData = [];
    if (!empty($colorInput)) {
        $colorData = array_map('trim', explode(',', $colorInput));
        $colorData = array_filter($colorData, function($color) { return !empty($color); });
    }
    $colorJson = json_encode($colorData);
    
    // Generate SKU automatically
    $sku = generateSKU($name, $category, $data['price'], $sizeData);
    
    // Check if SKU already exists (very unlikely with timestamp, but just in case)
    $checkSql = "SELECT id FROM inventory WHERE sku = '$sku'";
    $result = $conn->query($checkSql);
    if ($result && $result->num_rows > 0) {
        // If somehow SKU exists, add additional random digits
        $sku .= '-' . mt_rand(10, 99);
    }

    // Handle image uploads
    $images = [];
    if (isset($_FILES['productImages']) && is_array($_FILES['productImages']['name'])) {
        $fileCount = count($_FILES['productImages']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $error = $_FILES['productImages']['error'][$i];
            if ($error === UPLOAD_ERR_OK) {
                // Validate file size (4MB max)
                $fileSize = $_FILES['productImages']['size'][$i];
                if ($fileSize > 4 * 1024 * 1024) {
                    return ['success' => false, 'message' => 'Image file size exceeds 4MB limit'];
                }

                // Validate file type
                $type = $_FILES['productImages']['type'][$i];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($type, $allowedTypes)) {
                    return ['success' => false, 'message' => 'Invalid image file type. Only PNG and JPG are allowed.'];
                }

                // Generate unique filename
                $filename_original = $_FILES['productImages']['name'][$i];
                $extension = pathinfo($filename_original, PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $uploadPath = '../../uploads/' . $filename;

                // Ensure uploads directory exists and is writable
                $uploadDir = dirname($uploadPath);
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        return ['success' => false, 'message' => 'Failed to create upload directory'];
                    }
                }

                // Move uploaded file
                $tmpName = $_FILES['productImages']['tmp_name'][$i];
                if (move_uploaded_file($tmpName, $uploadPath)) {
                    $images[] = 'uploads/' . $filename;
                } else {
                    return ['success' => false, 'message' => 'Failed to upload image'];
                }
            } elseif ($error !== UPLOAD_ERR_NO_FILE) {
                return ['success' => false, 'message' => 'Upload error: ' . $error];
            }
        }
    }

    // If no images uploaded, use default or empty array
    if (empty($images)) {
        $images[] = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90';
    }

    // Determine status based on stock
    if ($stock == 0) {
        $status = 'Out of Stock';
    } elseif ($stock <= 10) {
        $status = 'Low Stock';
    } else {
        $status = 'In Stock';
    }

    // Insert query
    $imagesJson = json_encode($images);
    $sql = "INSERT INTO inventory (id, name, sku, category, price, stock, size, size_quantities, color, images, status)
            VALUES ('$id', '$name', '$sku', '$category', $price, $stock, '$sizeString', '$sizeQuantitiesJson', '$colorJson', '$imagesJson', '$status')";

    if ($conn->query($sql) === TRUE) {
        return ['success' => true, 'message' => 'Product added successfully', 'id' => $id, 'sku' => $sku, 'images' => $images, 'stock' => $stock, 'size_quantities' => $sizeQuantities];
    } else {
        error_log('Database error: ' . $conn->error);
        error_log('SQL: ' . $sql);
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Clear any previous output
    ob_clean();
    
    // Set JSON header
    header('Content-Type: application/json');
    
    try {
        // Handle size data
        $size = '';
        if (isset($_POST['productSize']) && is_array($_POST['productSize'])) {
            $size = $_POST['productSize'];
        } elseif (isset($_POST['productSize'])) {
            $size = $_POST['productSize'];
        }
        
        // Handle size data from array
        $sizeData = isset($_POST['productSize']) && is_array($_POST['productSize']) ? $_POST['productSize'] : [];

        $data = array(
            'name' => $_POST['productName'] ?? '',
            'category' => $_POST['productCategory'] ?? '',
            'price' => $_POST['productPrice'] ?? '',
            'size' => json_encode($sizeData)
        );

        $response = addProduct($data);
        echo json_encode($response);
    } catch (Exception $e) {
        error_log('Exception in addProduct: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    }
    
    // End output buffering and send
    ob_end_flush();
    exit;
}

// If not POST, clear buffer and exit
ob_end_clean();
?>