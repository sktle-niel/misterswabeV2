<?php
ini_set('display_errors', 0);
error_reporting(0);
header('Content-Type: application/json');

try {
    include '../../config/connection.php';
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $conn->prepare("SELECT c.id, c.name, COUNT(i.id) as productCount FROM categories c LEFT JOIN inventory i ON c.name COLLATE utf8mb4_unicode_ci = i.category COLLATE utf8mb4_unicode_ci GROUP BY c.id, c.name ORDER BY c.name ASC");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $result = $stmt->get_result();

        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'productCount' => (int)$row['productCount']
            ];
        }

        echo json_encode(['success' => true, 'categories' => $categories]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
