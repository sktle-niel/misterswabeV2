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

    // Handle category - can be either ID or name
    $category = $data['category'];
    $categoryName = '';
    
    if (is_numeric($category)) {
        // It's a category ID - get the name for SKU generation
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
        // It's a category name - look up the ID
        $categoryName = $category;
        $categoryNameEscaped = mysqli_real_escape_string($conn, $category);
        $categorySql = "SELECT id FROM categories WHERE name = '$categoryNameEscaped' LIMIT 1";
        $categoryResult = $conn->query($categorySql);
        if ($categoryResult && $categoryResult->num_rows > 0) {
            $categoryRow = $categoryResult->fetch_assoc();
            $category = intval($categoryRow['id']);
        } else {
            return ['success' => false, 'message' => 'Invalid category'];
        }
    }

    // Clean price value - remove currency symbol and commas
    $priceStr = str_replace(['₱', ','], '', $data['price']);
    $price = floatval($priceStr);

    $stock = intval($data['stock']);
    $size = mysqli_real_escape_string($conn, $data['size']);
    
    // Handle color - convert to JSON array
    $colorInput = $data['color'] ?? '';
    if (!empty($colorInput)) {
        // Split by comma and trim each color
        $colorArray = array_map('trim', explode(',', $colorInput));
        $colorArray = array_filter($colorArray); // Remove empty values
        $colorJson = json_encode(array_values($colorArray));
    } else {
        $colorJson = json_encode([]);
    }
    $color = mysqli_real_escape_string($conn, $colorJson);


    // SKU remains unchanged - it's readonly and cannot be edited
    // We keep the original SKU
    $sku = $originalSku;

    // Handle image uploads if provided
    $images = [];
    if (isset($_FILES['editProductImages']) && is_array($_FILES['editProductImages']['name'])) {
        $fileCount = count($_FILES['editProductImages']['name']);
        for ($i = 0; $i < $fileCount; $i++) {
            $error = $_FILES['editProductImages']['error'][$i];
            if ($error === UPLOAD_ERR_OK) {
                // Validate file size (4MB max)
                $fileSize = $_FILES['editProductImages']['size'][$i];
                if ($fileSize > 4 * 1024 * 1024) {
                    return ['success' => false, 'message' => 'Image file size exceeds 4MB limit'];
                }

                // Validate file type
                $type = $_FILES['editProductImages']['type'][$i];
                $allowedTypes = ['image/jpeg', 'image/png'];
                if (!in_array($type, $allowedTypes)) {
                    return ['success' => false, 'message' => 'Invalid image file type. Only PNG and JPG are allowed.'];
                }

                // Generate unique filename
                $originalName = $_FILES['editProductImages']['name'][$i];
                $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $extension;
                $uploadPath = '../../uploads/' . $filename;

                // Ensure uploads directory exists and is writable
                $uploadDir = dirname($uploadPath);
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Move uploaded file
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

    // Handle size_quantities - remove sizes that are no longer in the new size list
    $fetchSql = "SELECT size_quantities FROM inventory WHERE sku = '$originalSku'";
    $fetchResult = $conn->query($fetchSql);
    
    $updatedSizeQuantities = [];
    if ($fetchResult && $fetchResult->num_rows > 0) {
        $row = $fetchResult->fetch_assoc();
        $currentSizeQuantities = json_decode($row['size_quantities'] ?? '{}', true);
        
        // Parse new sizes from form
        $newSizes = array_map('trim', explode(',', $size));
        $newSizes = array_filter($newSizes);
        
        // Remove sizes from size_quantities that are not in the new size list
        foreach ($currentSizeQuantities as $sizeKey => $qty) {
            if (in_array($sizeKey, $newSizes)) {
                $updatedSizeQuantities[$sizeKey] = $qty;
            }
        }
    }
    
    $sizeQuantitiesJson = json_encode($updatedSizeQuantities);

    // Calculate stock from size_quantities
    $calculatedStock = array_sum($updatedSizeQuantities);

    // Determine status based on calculated stock
    if ($calculatedStock == 0) {
        $status = 'Out of Stock';
    } elseif ($calculatedStock <= 10) {
        $status = 'Low Stock';
    } else {
        $status = 'In Stock';
    }

    // Build update query - SKU is NOT updated, it remains the same
    $updateFields = [
        "name = '$name'",
        "category = '$categoryName'",
        "price = $price",
        "stock = $calculatedStock",
        "size = '$size'",
        "color = '$color'",
        "status = '$status'",
        "size_quantities = '$sizeQuantitiesJson'"
    ];


    // Only update images if new ones were uploaded
    if (!empty($images)) {
        $imagesJson = json_encode($images);
        $updateFields[] = "images = '$imagesJson'";
    }

    $updateFieldsStr = implode(', ', $updateFields);

    // Update query - WHERE clause uses original SKU, and SKU field is not updated
    $sql = "UPDATE inventory SET $updateFieldsStr WHERE sku = '$originalSku'";

    if ($conn->query($sql) === TRUE) {
        // Return the original SKU (unchanged)
        return ['success' => true, 'message' => 'Product updated successfully', 'sku' => $sku, 'images' => $images];
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
        
        // Check if required fields are present
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
                'stock' => '0', // Stock is calculated from size_quantities
                'size' => $_POST['size'] ?? '',
                'color' => $_POST['color'] ?? ''
            ];
            
            $response = editProduct($data);
        }
    } catch (Exception $e) {
        error_log('Edit Product Error: ' . $e->getMessage());
        $response = ['success' => false, 'message' => 'Server error: ' . $e->getMessage()];
    }
    
    // Clear buffer and output JSON
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($response);
    exit;
}


?>