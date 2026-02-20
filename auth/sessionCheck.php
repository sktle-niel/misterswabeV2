<?php
ob_start();

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

ob_end_flush();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    // User is not logged in, redirect to login page
    header('Location: ../../auth/form.php');
    exit();
}
?>
