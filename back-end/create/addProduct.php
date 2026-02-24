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

    // Handle new size-color configuration
    $sizeColorConfigJson = isset($_POST['sizeColorConfig']) ? $_POST['sizeColorConfig'] : '{}';
    error_log('DEBUG addProduct - sizeColorConfigJson: ' . $sizeColorConfigJson);
    $sizeColorConfig = json_decode($sizeColorConfigJson, true);
    error_log('DEBUG addProduct - sizeColorConfig: ' . print_r($sizeColorConfig, true));
    
    // Handle sizes
    $sizesInput = isset($_POST['productSizes']) ? trim($_POST['productSizes']) : '';
    $sizeData = [];
    if (!empty($sizesInput)) {
        $sizeData = array_map('trim', explode(',', $sizesInput));
        $sizeData = array_filter($sizeData, function($size) { return !empty($size); });
    }
    
    // Calculate total stock and build size-color-quantity matrix
    $stock = 0;
    $sizeColorQuantities = []; // Matrix structure: { "Size": { "Color": quantity } }
    
    if (!empty($sizeColorConfig) && is_array($sizeColorConfig)) {
        foreach ($sizeColorConfig as $size => $config) {
            // Initialize the size in the matrix (even if no colors yet)
            if (!isset($sizeColorQuantities[$size])) {
                $sizeColorQuantities[$size] = [];
            }
            
            // Process colors if they exist
            if (isset($config['colors']) && is_array($config['colors'])) {
                foreach ($config['colors'] as $color => $quantity) {
                    $qty = intval($quantity);
                    $stock += $qty;
                    $sizeColorQuantities[$size][$color] = $qty;
                }
            }
        }
    } else {
        // Simple product (no sizes) - check for simple quantity
        $simpleQty = isset($_POST['simpleQuantity']) ? intval($_POST['simpleQuantity']) : 0;
        $stock = $simpleQty;
    }
    
// Generate SKU using utility function - no size in base SKU
    $sku = generateSKU($name, $category, $price, $sizeData, []);
    
    // Check if SKU already exists
    $checkStmt = $conn->prepare("SELECT id FROM inventory WHERE sku = ?");
    $checkStmt->bind_param("s", $sku);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    // If SKU exists, generate a new one
    if ($checkResult->num_rows > 0) {
        $sku = generateSKU($name, $category, $price, $sizeData, []);
    }
    $checkStmt->close();
    
    // Determine product status
    if ($stock == 0) {
        $status = 'Out of Stock';
    } elseif ($stock <= 10) {
        $status = 'Low Stock';
    } else {
        $status = 'In Stock';
    }
    
    // Handle size string for database
    $sizeString = '';
    if (!empty($sizeData)) {
        $sizeString = implode(',', $sizeData);
    } elseif (isset($_POST['noSizeColorRequired']) && $_POST['noSizeColorRequired'] === 'on') {
        $sizeString = 'Simple Product';
    }
    
    // Handle images
    $images = [];
    if (isset($_FILES['productImages']) && !empty($_FILES['productImages']['name'][0])) {
        $uploadDir = '../../uploads/';
        
        foreach ($_FILES['productImages']['name'] as $key => $fileName) {
            if ($_FILES['productImages']['error'][$key] === UPLOAD_ERR_OK) {
                $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid() . '.' . $fileExt;
                $targetPath = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['productImages']['tmp_name'][$key], $targetPath)) {
                    $images[] = $newFileName;
                }
            }
        }
    }
    
$imagesJson = json_encode($images);
    $sizeColorQuantitiesJson = json_encode($sizeColorQuantities);
    
    // Build size_quantities for backward compatibility (sum of colors per size)
    $sizeQuantities = [];
    if (!empty($sizeColorQuantities) && is_array($sizeColorQuantities)) {
        foreach ($sizeColorQuantities as $size => $colors) {
            if (is_array($colors)) {
                $sizeQuantities[$size] = array_sum($colors);
            }
        }
    }
    $sizeQuantitiesJson = json_encode($sizeQuantities);
    
    error_log('DEBUG addProduct - Final sizeColorQuantitiesJson: ' . $sizeColorQuantitiesJson);
    
// Check if size column exists, if not create it
    $checkSizeColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'size'");
    if (!$checkSizeColumn || $checkSizeColumn->num_rows == 0) {
        $conn->query("ALTER TABLE inventory ADD COLUMN size VARCHAR(255) DEFAULT NULL");
    }
    
    // Check if size_quantities column exists, if not create it
    $checkSizeQtyColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'size_quantities'");
    if (!$checkSizeQtyColumn || $checkSizeQtyColumn->num_rows == 0) {
        $conn->query("ALTER TABLE inventory ADD COLUMN size_quantities JSON DEFAULT NULL");
    }
    
    // Check if size_color_quantities column exists, if not create it
    $checkColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'size_color_quantities'");
    if (!$checkColumn || $checkColumn->num_rows == 0) {
        $conn->query("ALTER TABLE inventory ADD COLUMN size_color_quantities JSON NULL");
    }
    
    // Check if variant_skus column exists
    $checkVariantColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'variant_skus'");
    if (!$checkVariantColumn || $checkVariantColumn->num_rows == 0) {
        $conn->query("ALTER TABLE inventory ADD COLUMN variant_skus JSON NULL");
    }
    
    // Check if information column exists, if not create it
    $checkInfoColumn = $conn->query("SHOW COLUMNS FROM inventory LIKE 'information'");
    if (!$checkInfoColumn || $checkInfoColumn->num_rows == 0) {
        $conn->query("ALTER TABLE inventory ADD COLUMN information JSON DEFAULT NULL");
    }
    
    // Build information JSON (for simple products like bags)
    $productInformation = [];
    if (isset($_POST['productBrand']) && !empty($_POST['productBrand'])) {
        $productInformation['brand'] = mysqli_real_escape_string($conn, $_POST['productBrand']);
    }
    if (isset($_POST['productMaterial']) && !empty($_POST['productMaterial'])) {
        $productInformation['material'] = mysqli_real_escape_string($conn, $_POST['productMaterial']);
    }
    if (isset($_POST['productDimensions']) && !empty($_POST['productDimensions'])) {
        $productInformation['dimensions'] = mysqli_real_escape_string($conn, $_POST['productDimensions']);
    }
    if (isset($_POST['productInfo']) && !empty($_POST['productInfo'])) {
        $productInformation['product_info'] = mysqli_real_escape_string($conn, $_POST['productInfo']);
    }
    $informationJson = json_encode(!empty($productInformation) ? $productInformation : null);
    
    // Generate variant SKUs for each size and color combination
    // Variant SKU format: baseSku-SIZE-COLORCODE (e.g., SHO-NIK-A1B2-43-RED)
    $variantSkus = [];
    if (!empty($sizeColorQuantities) && is_array($sizeColorQuantities)) {
        foreach ($sizeColorQuantities as $size => $colors) {
            if (is_array($colors)) {
                foreach ($colors as $color => $qty) {
                    // Create variant SKU: baseSku-SIZE-COLORCODE
                    $colorCode = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $color));
                    $variantSku = $sku . '-' . $size . '-' . $colorCode;
                    $variantKey = $size . '-' . $color;
                    $variantSkus[$variantKey] = $variantSku;
                }
            }
        }
    }
    $variantSkusJson = json_encode($variantSkus);
    
$sql = "INSERT INTO inventory (id, name, sku, category, price, stock, size, size_quantities, size_color_quantities, images, status, variant_skus, information)
            VALUES ('$id', '$name', '$sku', '$category', $price, $stock, '$sizeString', '$sizeQuantitiesJson', '$sizeColorQuantitiesJson', '$imagesJson', '$status', '$variantSkusJson', '$informationJson')";

    if ($conn->query($sql) === TRUE) {
        return ['success' => true, 'message' => 'Product added successfully', 'id' => $id, 'sku' => $sku, 'images' => $images, 'stock' => $stock, 'size_color_quantities' => $sizeColorQuantities, 'variant_skus' => $variantSkus];
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
