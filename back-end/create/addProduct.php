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
    
    // Check if product with same name already exists
    $checkNameSql = "SELECT id, name FROM inventory WHERE LOWER(name) = LOWER('$name')";
    $nameResult = $conn->query($checkNameSql);
    if ($nameResult && $nameResult->num_rows > 0) {
        return ['success' => false, 'message' => 'A product with this name already exists. Please use a different name or edit the existing product.'];
    }
    
    // Handle category - store the name directly
    $category = mysqli_real_escape_string($conn, $data['category']);
    
    $price = floatval($data['price']);

    // Handle new size-color configuration
    $sizeColorConfigJson = isset($_POST['sizeColorConfig']) ? $_POST['sizeColorConfig'] : '{}';
    $sizeColorConfig = json_decode($sizeColorConfigJson, true);
    
    // Handle sizes
    $sizesInput = isset($_POST['productSizes']) ? trim($_POST['productSizes']) : '';
    $sizeData = [];
    if (!empty($sizesInput)) {
        $sizeData = array_map('trim', explode(',', $sizesInput));
        $sizeData = array_filter($sizeData, function($size) { return !empty($size); });
    }
    
    // Calculate total stock and build size-color-quantity matrix
    $stock = 0;
    $sizeQuantities = []; // For backward compatibility - sum quantities per size
    $allColors = []; // Collect all unique colors
    $sizeColorQuantities = []; // New matrix structure
    
    if (!empty($sizeColorConfig) && is_array($sizeColorConfig)) {
        foreach ($sizeColorConfig as $size => $config) {
            $sizeTotal = 0;
            if (isset($config['colors']) && is_array($config['colors'])) {
                foreach ($config['colors'] as $color => $quantity) {
                    $qty = intval($quantity);
                    $stock += $qty;
                    $sizeTotal += $qty;
                    
                    // Build size-color matrix
                    if (!isset($sizeColorQuantities[$size])) {
                        $sizeColorQuantities[$size] = [];
                    }
                    $sizeColorQuantities[$size][$color] = $qty;
                    
                    // Collect unique colors
                    if (!in_array($color, $allColors)) {
                        $allColors[] = $color;
                    }
                }
            }
            $sizeQuantities[$size] = $sizeTotal;
        }
    }
    
    // Handle simple product quantity (no sizes)
    $simpleProductQuantity = isset($_POST['simpleProductQuantity']) ? intval($_POST['simpleProductQuantity']) : 0;
    if (empty($sizeData) && $simpleProductQuantity > 0) {
        $stock = $simpleProductQuantity;
        $sizeQuantities[''] = $simpleProductQuantity;
        $sizeString = 'Simple Product';
    }
    
    // Create size string for display
    if (!isset($sizeString)) {
        $sizeString = !empty($sizeData) ? implode(', ', $sizeData) : 'N/A';
    }
    
    // Create color JSON for storage
    $colorJson = json_encode(!empty($allColors) ? $allColors : []);
    
    // Store the size-color-quantity matrix as JSON
    $sizeColorQuantitiesJson = json_encode($sizeColorQuantities);
    
    // Generate SKU automatically
    $sku = generateSKU($name, $category, $data['price'], $sizeData, $allColors);
    
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

    // Insert query - now includes size_color_quantities field
    $imagesJson = json_encode($images);
    $sizeQuantitiesJson = json_encode($sizeQuantities); // For backward compatibility
    
    // Check if size_color_quantities column exists, if not create it
    $checkColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'size_color_quantities'");
    if (!$checkColumn || $checkColumn->num_rows == 0) {
        // Create the column if it doesn't exist
        $conn->query("ALTER TABLE inventory ADD COLUMN size_color_quantities JSON NULL");
    }
    
    // Check if size_quantities column exists, if not create it
    $checkSizeQtyColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'size_quantities'");
    if (!$checkSizeQtyColumn || $checkSizeQtyColumn->num_rows == 0) {
        // Create the column if it doesn't exist
        $conn->query("ALTER TABLE inventory ADD COLUMN size_quantities JSON NULL");
    }
    
    // Check if color column exists, if not create it
    $checkColorColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'color'");
    if (!$checkColorColumn || $checkColorColumn->num_rows == 0) {
        // Create the column if it doesn't exist
        $conn->query("ALTER TABLE inventory ADD COLUMN color JSON NULL");
    }
    
    $sql = "INSERT INTO inventory (id, name, sku, category, price, stock, size, size_quantities, size_color_quantities, color, images, status)
            VALUES ('$id', '$name', '$sku', '$category', $price, $stock, '$sizeString', '$sizeQuantitiesJson', '$sizeColorQuantitiesJson', '$colorJson', '$imagesJson', '$status')";

    if ($conn->query($sql) === TRUE) {
        return ['success' => true, 'message' => 'Product added successfully', 'id' => $id, 'sku' => $sku, 'images' => $images, 'stock' => $stock, 'size_quantities' => $sizeQuantities, 'size_color_quantities' => $sizeColorQuantities, 'all_colors' => $allColors];
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
