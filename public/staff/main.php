<?php include '../../auth/sessionCheck.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SWABE COLLECTION - Inventory Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../src/css/adminGlobal.css">
    <link rel="stylesheet" href="../../src/css/dashboard.css">
    <link rel="stylesheet" href="../../src/css/sidebar.css">
    <link rel="stylesheet" href="../../src/css/addSales.css">

<body>
    <?php //include 'components/topbar.php'; ?>
    <?php include 'components/sidebar.php'; ?>
    <?php include 'status/successStatus.php'; ?>
    <?php include 'status/invalidStatus.php'; ?>

    <?php
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

    // Staff can access dashboard, add sales, and sales pages
    switch ($currentPage) {
        case 'dashboard':
            include 'pages/dashboard.php';
            break;
        case 'addSales':
            include 'pages/addSales.php';
            break;
        case 'sales':
            include 'pages/sales.php';
            break;
        default:
            include 'pages/dashboard.php';
            break;
    }
    ?>

</body>
</html>