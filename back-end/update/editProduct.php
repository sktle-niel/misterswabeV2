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

// Check if information column exists
    $informationColumnExists = $conn->query("SHOW COLUMNS FROM inventory LIKE 'information'")->num_rows > 0;

    // If information column doesn't exist, create it
    if (!$informationColumnExists) {
        $conn->query("ALTER TABLE inventory ADD COLUMN information JSON NULL");
    }
    
    // Check if color column exists
    $colorColumnExists = $conn->query("SHOW COLUMNS FROM inventory LIKE 'color'")->num_rows > 0;

    // If color column doesn't exist, create it
    if (!$colorColumnExists) {
        $conn->query("ALTER TABLE inventory ADD COLUMN color JSON DEFAULT NULL");
    }

    // Sanitize inputs
    $originalSku = mysqli_real_escape_string($conn, $data['originalSku']);
    $name = mysqli_real_escape_string($conn, $data['name']);
    $categoryName = mysqli_real_escape_string($conn, $data['category']);

    // Clean price value
    $priceStr = str_replace(['₱', ','], '', $data['price']);
    $price = floatval($priceStr);

    // Check if simple product (no sizes)
    $isSimpleProduct = isset($data['isSimpleProduct']) && $data['isSimpleProduct'] == '1';

    // Handle sizes
    $newSizes = [];
    $simpleQuantity = 0;

    if ($isSimpleProduct) {
        // Simple product - no sizes
        $newSizes = [];
        $simpleQuantity = isset($data['simpleQuantity']) ? intval($data['simpleQuantity']) : 0;
    } else {
        // Product with sizes
        $sizesInput = isset($data['productSizes']) ? trim($data['productSizes']) : '';
        if (!empty($sizesInput)) {
            $newSizes = array_map('trim', explode(',', $sizesInput));
            $newSizes = array_filter($newSizes, function($size) { return !empty($size); });
            $newSizes = array_values($newSizes);
        }
    }
    
    // Fetch current product data
    $fetchSql = "SELECT * FROM inventory WHERE sku = '$originalSku'";
    $fetchResult = $conn->query($fetchSql);
    
    if ($fetchResult && $fetchResult->num_rows > 0) {
        $row = $fetchResult->fetch_assoc();
        
        // Get existing data
        $currentSizeColorQuantities = json_decode($row['size_color_quantities'] ?? '{}', true);
        $existingInformation = $informationColumnExists ? json_decode($row['information'] ?? '{}', true) : [];
        
        // Initialize arrays if null
        if (!is_array($currentSizeColorQuantities)) $currentSizeColorQuantities = [];
        if (!is_array($existingInformation)) $existingInformation = [];
        
        // Check if name or category changed - if so, generate new SKU
        $currentName = $row['name'] ?? '';
        $currentCategory = $row['category'] ?? '';
        $sku = $originalSku;
        
        if ($name !== $currentName || $categoryName !== $currentCategory) {
            // Generate new SKU based on new name and category
            $newSku = generateSKU($name, $categoryName, $price, $newSizes);
            
            // Update all variant SKUs in size_color_quantities that use the old base SKU
            $updatedSizeColorQuantities = [];
            foreach ($currentSizeColorQuantities as $size => $colors) {
                $newColors = [];
                if (is_array($colors)) {
                    foreach ($colors as $color => $colorData) {
                        // Check if already in new format (array with 'quantity' key)
                        if (is_array($colorData) && isset($colorData['quantity'])) {
                            // Already in new format - just update the SKU
                            $colorQty = $colorData['quantity'];
                            $colorCode = strtoupper(substr($color, 0, 3));
                            $newVariantSku = $newSku . '-' . $size . '-' . $colorCode;
                            $newColors[$color] = [
                                'quantity' => $colorQty,
                                'sku' => $newVariantSku
                            ];
                        } else {
                            // Old format (just quantity number) - convert to new format
                            $colorQty = is_array($colorData) ? 0 : intval($colorData);
                            $colorCode = strtoupper(substr($color, 0, 3));
                            $newVariantSku = $newSku . '-' . $size . '-' . $colorCode;
                            $newColors[$color] = [
                                'quantity' => $colorQty,
                                'sku' => $newVariantSku
                            ];
                        }
                    }
                }
                $updatedSizeColorQuantities[$size] = $newColors;
            }
            
            // Also update simple product variant SKUs if it's a simple product
            if ($isSimpleProduct && !empty($currentSizeColorQuantities)) {
                $updatedSizeColorQuantities = [];
                foreach ($currentSizeColorQuantities as $sizeKey => $colors) {
                    if (is_array($colors)) {
                        $newColors = [];
                        foreach ($colors as $color => $colorData) {
                            // Check if already in new format
                            if (is_array($colorData) && isset($colorData['quantity'])) {
                                $colorQty = $colorData['quantity'];
                            } else {
                                $colorQty = is_array($colorData) ? 0 : intval($colorData);
                            }
                            // For simple products, variant SKU format: NEWBASE-COLORCODE
                            $colorCode = strtoupper(substr($color, 0, 3));
                            $newVariantSku = $newSku . '-' . $colorCode;
                            $newColors[$color] = [
                                'quantity' => $colorQty,
                                'sku' => $newVariantSku
                            ];
                        }
                        $updatedSizeColorQuantities[$sizeKey] = $newColors;
                    }
                }
            }
            
            $sku = $newSku;
        } else {
            // SKU remains unchanged
            $sku = $originalSku;
            $updatedSizeColorQuantities = $currentSizeColorQuantities;
        }
        
        // Build product information (for simple products)
        $productInformation = $existingInformation;
        
        // Override with new values if provided
        if ($isSimpleProduct && isset($data['productBrand'])) {
            $productInformation['brand'] = mysqli_real_escape_string($conn, $data['productBrand']);
        }
        if ($isSimpleProduct && isset($data['productMaterial'])) {
            $productInformation['material'] = mysqli_real_escape_string($conn, $data['productMaterial']);
        }
        if ($isSimpleProduct && isset($data['productDimensions'])) {
            $productInformation['dimensions'] = mysqli_real_escape_string($conn, $data['productDimensions']);
        }
        if ($isSimpleProduct && isset($data['productInfo'])) {
            $productInformation['product_info'] = mysqli_real_escape_string($conn, $data['productInfo']);
        }
        
        $informationJson = json_encode($productInformation);
        
// Build updated size quantities from size_color_quantities
        $finalSizeColorQuantities = [];
        
        // Extract unique colors from size_color_quantities for the color column
        $allColors = [];
        foreach ($updatedSizeColorQuantities as $sizeKey => $colors) {
            if (is_array($colors)) {
                foreach (array_keys($colors) as $colorKey) {
                    if (!in_array($colorKey, $allColors)) {
                        $allColors[] = $colorKey;
                    }
                }
            }
        }
        
        if ($isSimpleProduct) {
            // Simple product - preserve existing size data and just set the quantity
            $finalSizeColorQuantities = $updatedSizeColorQuantities;
            // Only update stock if a valid quantity is provided (> 0), otherwise preserve existing stock
            if ($simpleQuantity > 0) {
                $stock = $simpleQuantity;
            } else {
                // Preserve existing stock from database
                $stock = intval($row['stock'] ?? 0);
            }
        } else {
            // Product with sizes - keep existing quantities from size_color_quantities
            foreach ($newSizes as $size) {
                // Keep existing color quantities for this size if available
                if (isset($updatedSizeColorQuantities[$size])) {
                    $finalSizeColorQuantities[$size] = $updatedSizeColorQuantities[$size];
                } else {
                    $finalSizeColorQuantities[$size] = [];
                }
            }
            
            // Calculate stock from size_color_quantities
            $stock = 0;
            foreach ($finalSizeColorQuantities as $size => $colors) {
                if (is_array($colors)) {
                    foreach ($colors as $color => $colorData) {
                        if (is_array($colorData) && isset($colorData['quantity'])) {
                            $stock += intval($colorData['quantity']);
                        } else {
                            // Handle old format where colorData is just the quantity
                            $stock += intval($colorData);
                        }
                    }
                }
            }
            
            // If stock is still 0 after calculation, preserve existing stock from database
            if ($stock == 0) {
                $stock = intval($row['stock'] ?? 0);
            }
        }
        
        // Determine status
        if ($stock == 0) {
            $status = 'Out of Stock';
        } elseif ($stock <= 10) {
            $status = 'Low Stock';
        } else {
            $status = 'In Stock';
        }
        
        $sizeString = implode(',', $newSizes);
        $sizeColorQuantitiesJson = json_encode($finalSizeColorQuantities);
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

    // Check if SKU changed - if so, we need to update the product with the new SKU
    $skuChanged = ($sku !== $originalSku);
    
    if ($skuChanged) {
// Get the existing ID from the product
        $existingId = $row['id'] ?? '';
        
        // Use the extracted colors from size_color_quantities
        $colorJson = json_encode($allColors);
        $colorEscaped = mysqli_real_escape_string($conn, $colorJson);
        
        // Get existing size_quantities from the row
        $existingSizeQuantities = $row['size_quantities'] ?? '{}';
        $sizeQuantitiesEscaped = mysqli_real_escape_string($conn, $existingSizeQuantities);
        
        // Get existing images from the row
        $existingImages = $row['images'] ?? '[]';
        $imagesEscaped = mysqli_real_escape_string($conn, $existingImages);
        
        // First, insert a new product with the new SKU (keeping the same ID)
        $insertFields = [
            "id = '$existingId'",
            "name = '$name'",
            "sku = '$sku'",
            "category = '$categoryName'",
            "price = $price",
            "stock = $stock",
            "size = '$sizeString'",
            "size_quantities = '$sizeQuantitiesEscaped'",
            "size_color_quantities = '$sizeColorQuantitiesJson'",
            "color = '$colorEscaped'",
            "images = '$imagesEscaped'",
            "status = '$status'"
        ];
        
        // Add information field only if column exists
        if ($informationColumnExists) {
            $insertFields[] = "information = '$informationJson'";
        }

        if (!empty($images)) {
            $imagesJson = json_encode($images);
            $insertFields[] = "images = '$imagesJson'";
        }

        $insertFieldsStr = implode(', ', $insertFields);
        
        // First delete the old product
        $conn->query("DELETE FROM inventory WHERE sku = '$originalSku'");
        
        // Then insert the new product with updated SKU
        $copySql = "INSERT INTO inventory SET $insertFieldsStr";
        
        if ($conn->query($copySql) === TRUE) {
            
            return [
                'success' => true, 
                'message' => 'Product updated successfully', 
                'sku' => $sku, 
                'images' => $images,
                'stock' => $stock,
                'status' => $status,
                'size_color_quantities' => $finalSizeColorQuantities,
                'skuChanged' => true,
                'oldSku' => $originalSku
            ];
        } else {
            error_log('Database error: ' . $conn->error);
            error_log('SQL: ' . $copySql);
            return ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
    } else {
        // SKU unchanged - regular update
        // Prepare color JSON from extracted colors
        $colorJson = json_encode($allColors);
        $colorEscaped = mysqli_real_escape_string($conn, $colorJson);
        
        $updateFields = [
            "name = '$name'",
            "category = '$categoryName'",
            "price = $price",
            "stock = $stock",
            "size_color_quantities = '$sizeColorQuantitiesJson'",
            "color = '$colorEscaped'",
            "status = '$status'"
        ];
        
        // Add information field only if column exists
        if ($informationColumnExists) {
            $updateFields[] = "information = '$informationJson'";
        }

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
                'size_color_quantities' => $finalSizeColorQuantities
            ];
        } else {
            error_log('Database error: ' . $conn->error);
            error_log('SQL: ' . $sql);
            return ['success' => false, 'message' => 'Database error: ' . $conn->error];
        }
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
                'productSizes' => $_POST['productSizes'] ?? '',
                'isSimpleProduct' => $_POST['isSimpleProduct'] ?? '0',
                'simpleQuantity' => $_POST['simpleQuantity'] ?? 0,
                'productBrand' => $_POST['productBrand'] ?? '',
                'productMaterial' => $_POST['productMaterial'] ?? '',
                'productDimensions' => $_POST['productDimensions'] ?? '',
                'productInfo' => $_POST['productInfo'] ?? ''
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
