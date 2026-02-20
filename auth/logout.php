<?php
session_start();

// Prevent browser caching
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: ../auth/form.php");
exit();
?>
