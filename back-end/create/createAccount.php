<?php
function createAccount($email, $password, $user_type) {
    include './../config/connection.php';
    
    // Validate user_type
    if (!in_array($user_type, ['staff', 'administrator'])) {
        return ['success' => false, 'message' => 'Invalid user type'];
    }
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Email already exists'];
    }
    $stmt->close();
    
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Get the next available ID
    $result = $conn->query("SELECT COALESCE(MAX(id), 0) + 1 AS next_id FROM users");
    $row = $result->fetch_assoc();
    $next_id = $row['next_id'];
    
    // Insert new user with explicit ID
    $stmt = $conn->prepare("INSERT INTO users (id, email, password, user_type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $next_id, $email, $hashed_password, $user_type);
    
    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        return ['success' => true, 'message' => 'Account created successfully'];
    } else {
        $error = $stmt->error;
        $stmt->close();
        $conn->close();
        return ['success' => false, 'message' => 'Error creating account: ' . $error];
    }
}
?>