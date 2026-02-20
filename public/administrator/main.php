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
    <link rel="stylesheet" href="../../src/css/adminSuccessMessage.css">
    <link rel="stylesheet" href="../../src/css/adminProducts.css">
    <link rel="stylesheet" href="../../src/css/modal.css">
    <link rel="stylesheet" href="../../src/css/successMessage.css">
</head>
<body>
    <?php include 'components/topbar.php'; ?>
    <?php include 'components/sidebar.php'; ?>
    <?php include 'status/successStatus.php'; ?>
    <?php include 'status/invalidStatus.php'; ?>
    <?php require_once '../../back-end/helpers/autoReportTrigger.php'; ?>

    <?php
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    
    switch ($currentPage) {
        case 'dashboard':
            include 'pages/dashboard.php';
            break;
        case 'inventory':
            include 'pages/inventory.php';
            break;
        case 'orders':
            include 'pages/orders.php';
            break;
        case 'categories':
            include 'pages/categories.php';
            break;
        case 'accounts':
            include 'pages/customers.php';
            break;
        case 'printSku':
            include 'pages/printSku.php';
            break;
        case 'reports':
            include 'pages/reports.php';
            break;
        case 'settings':
            include 'pages/settings.php';
            break;
        default:
            include 'pages/dashboard.php';
            break;
    }
    ?>

</body>
</html>