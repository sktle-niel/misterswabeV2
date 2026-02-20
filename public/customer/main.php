<?php
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swabe Collection - <?php echo ucfirst($currentPage); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../src/css/global.css">
    <link rel="stylesheet" href="../../src/css/userCart.css">
    <link rel="stylesheet" href="../../src/css/modal.css">
    <link rel="stylesheet" href="../../src/css/home.css">
    <link rel="stylesheet" href="../../src/css/products.css">
    <link rel="stylesheet" href="../../src/css/successMessage.css">
</head>
<body>
    <?php include '../administrator/components/topbar.php'; ?>
    <?php include 'components/navigationBar.php'; ?>

    <?php
    switch ($currentPage) {
        case 'home':
            include 'pages/home.php';
            break;
        case 'products':
            include 'pages/products.php';
            break;
        default:
            include 'pages/home.php';
            break;
    }
    ?>

    <?php include 'components/footer.php'; ?>
    <?php include 'components/userCart.php'; ?>
    <?php include 'components/productModal.php'; ?>
    <?php include 'components/successStatus.php'; ?>

    <script src="../../../src/js/global.js"></script>
    <script src="../../../src/js/bootstrap.bundle.min.js"></script>
    
</body>
</html>
