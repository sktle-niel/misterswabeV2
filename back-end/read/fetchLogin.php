<?php
function handleLogin($email, $password) {
    include '../config/connection.php';

    $stmt = $conn->prepare("SELECT id, password, user_type FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = $user['user_type'];
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            return ['success' => false, 'message' => 'Invalid password'];
        }
    } else {
        return ['success' => false, 'message' => 'Email not found'];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $result = handleLogin($email, $password);
    if ($result['success']) {
        // Redirect based on user type
        if ($_SESSION['user_type'] == 'administrator') {
            header('Location: ../public/administrator/main.php');
        } else {
            header('Location: ../public/staff/main.php');
        }
        exit();
    } else {
        $_SESSION['login_error'] = $result['message'];
        header('Location: ?view=login');
        exit();
    }
}
?>
