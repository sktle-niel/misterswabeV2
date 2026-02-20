<?php
ini_set('display_errors', 0);
include '../../config/connection.php';

function fetchCategoriesWithImages($conn) {
    // Fetch all categories
    $stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categoryName = $row['name'];
        
        // Fetch the first product image for this category
        $imgStmt = $conn->prepare("SELECT images FROM inventory WHERE category = ? LIMIT 1");
        $imgStmt->bind_param("s", $categoryName);
        $imgStmt->execute();
        $imgResult = $imgStmt->get_result();
        
        $imageUrl = 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=700&h=350&fit=crop&q=90'; // Default image
        
        if ($imgRow = $imgResult->fetch_assoc()) {
            $images = json_decode($imgRow['images'], true);
            if ($images && !empty($images) && isset($images[0])) {
                $imageUrl = '../../../' . $images[0];
            }
        }
        
        $imgStmt->close();

        $categories[] = [
            'id' => $row['id'],
            'name' => $categoryName,
            'image' => $imageUrl
        ];
    }

    return $categories;
}

// Only output JSON if this file is accessed directly, not when included
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $categories = fetchCategoriesWithImages($conn);
        echo json_encode(['success' => true, 'categories' => $categories]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
}
?>
