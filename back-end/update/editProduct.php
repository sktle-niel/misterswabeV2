<?php
// Prevent any output before JSON
ob_start();

// Disable all error display
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Include files
include '../../config/connection.php';
require_once __DIR__ . '/../utils/skuUtils.php';


function editProduct($data) {
    global $conn;

    // Sanitize inputs
    $originalSku = mysqli_real_escape_string($conn, $data['originalSku']);
    $name = mysqli_real_escape_string($conn, $data['name']);

    // Handle category
    $category = $data['category'];
    $categoryName = '';
    
    if (is_numeric($category)) {
        $category = intval($category);
        $categorySql = "SELECT name FROM categories WHERE id = $category LIMIT 1";
        $categoryResult = $conn->query($categorySql);
        if ($categoryResult && $categoryResult->num_rows > 0) {
            $categoryRow = $categoryResult->fetch_assoc();
            $categoryName = $categoryRow['name'];
        } else {
            return ['success' => false, 'message' => 'Invalid category'];
        }
    } else {
        $categoryName = $category;
    }

    // Clean price value
    $priceStr = str_replace(['₱', ','], '', $data['price']);
    $price = floatval($priceStr);

    // SKU remains unchanged
    $sku = $originalSku;

    // Handle sizes from form
    $sizesInput = isset($data['productSizes']) ? trim($data['productSizes']) : '';
    $newSizes = [];
    if (!empty($sizesInput)) {
        $newSizes = array_map('trim', explode(',', $sizesInput));
        $newSizes = array_filter($newSizes, function($size) { return !empty($size); });
        $newSizes = array_values($newSizes);
    }
    
    // Fetch current product data
    $fetchSql = "SELECT * FROM inventory WHERE sku = '$originalSku'";
    $fetchResult = $conn->query($fetchSql);
    
    if ($fetchResult && $fetchResult->num_rows > 0) {
        $row = $fetchResult->fetch_assoc();
        
        // Get existing size data
        $currentSizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
        $currentSizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
        
        // Remove sizes that are no longer selected
        $updatedSizeQuantities = [];
        $updatedSizeColorQuantities = [];
        
        foreach ($newSizes as $size) {
            if (isset($currentSizeQuantities[$size])) {
                $updatedSizeQuantities[$size] = $currentSizeQuantities[$size];
            }
            if (isset($currentSizeColorQuantities[$size])) {
                $updatedSizeColorQuantities[$size] = $currentSizeColorQuantities[$size];
            }
        }
        
        // Calculate new stock
        $stock = array_sum($updatedSizeQuantities);
        
        // Determine status
        if ($stock == 0) {
            $status = 'Out of Stock';
        } elseif ($stock <= 10) {
            $status = 'Low Stock';
        } else {
            $status = 'In Stock';
        }
        
        $sizeString = implode(',', $newSizes);
        $sizeQuantitiesJson = json_encode($updatedSizeQuantities);
        $sizeColorQuantitiesJson = json_encode($updatedSizeColorQuantities);
    } else {
        return ['success' => false, 'message' => 'Product not found'];
    }

    // Handle image uploads if provided
    $images = [];
    if (isset($_FILES['editProductImages']) && is_array($_FILES['editProductImages']['name'])) {
        $fileCount = count($_FILES['editProductImages']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $error = $_FILES['editProductImages']['error'][$i];
            if ($error === UPLOAD_ERR_OK) {
                $fileSize = $_FILES['editProductImages']['size'][$i];
                if ($fileSize > 4 * 1024 * 1024) {
                    return ['success' => false, 'message' => 'Image file size exceeds 4MB limit'];
                }

                $type = $_FILES['editProductImages']['type'][$i];
                $allowedTypes = ['image/jpeg', 'image/png'];
                if (!in_array($type, $allowedTypes)) {
                    return ['success' => false, 'message' => 'Invalid image file type. Only PNG and JPG are allowed.'];
                }

                $originalName = $_FILES['editProductImages']['name'][$i];
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $uploadPath = '../../uploads/' . $filename;

                $uploadDir = dirname($uploadPath);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $tmpName = $_FILES['editProductImages']['tmp_name'][$i];
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

    // Build update query
    $updateFields = [
        "name = '$name'",
        "category = '$categoryName'",
        "price = $price",
        "stock = $stock",
        "size = '$sizeString'",
        "size_quantities = '$sizeQuantitiesJson'",
        "size_color_quantities = '$sizeColorQuantitiesJson'",
        "status = '$status'"
    ];

    if (!empty($images)) {
        $imagesJson = json_encode($images);
        $updateFields[] = "images = '$imagesJson'";
    }

    $updateFieldsStr = implode(', ', $updateFields);
    $sql = "UPDATE inventory SET $updateFieldsStr WHERE sku = '$originalSku'";

    if ($conn->query($sql) === TRUE) {
        return [
            'success' => true, 
            'message' => 'Product updated successfully', 
            'sku' => $sku, 
            'images' => $images,
            'stock' => $stock,
            'status' => $status,
            'size_quantities' => $updatedSizeQuantities
        ];
    } else {
        error_log('Database error: ' . $conn->error);
        error_log('SQL: ' . $sql);
        return ['success' => false, 'message' => 'Database error: ' . $conn->error];
    }
}


// If called directly via POST, handle it
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $response = ['success' => false, 'message' => 'Unknown error'];
        
        if (empty($_POST['originalSku'])) {
            $response = ['success' => false, 'message' => 'Original SKU is required'];
        } elseif (empty($_POST['name'])) {
            $response = ['success' => false, 'message' => 'Product name is required'];
        } elseif (empty($_POST['category'])) {
            $response = ['success' => false, 'message' => 'Category is required'];
        } elseif (empty($_POST['price'])) {
            $response = ['success' => false, 'message' => 'Price is required'];
        } else {
            $data = [
                'originalSku' => $_POST['originalSku'],
                'name' => $_POST['name'],
                'category' => $_POST['category'],
                'price' => $_POST['price'],
                'productSizes' => $_POST['productSizes'] ?? ''
            ];
            
            $response = editProduct($data);
        }
    } catch (Exception $e) {
        error_log('Edit Product Error: ' . $e->getMessage());
        $response = ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    }
    
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}
?>
