<?php
require_once '../../config/connection.php';
require_once '../helpers/reportHelper.php';

ob_start();

$categoryId = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

if ($categoryId <= 0) {
    die('Invalid category ID');
}

$categoryQuery = "SELECT id, name FROM categories WHERE id = ?";
$stmt = $conn->prepare($categoryQuery);
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$categoryResult = $stmt->get_result();
$category = $categoryResult->fetch_assoc();
$stmt->close();

if (!$category) {
    die('Category not found');
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="category_report_' . urlencode($category['name']) . '_' . date('Y-m-d') . '.pdf"');

require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

$pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator('Swabe Collection');
$pdf->SetAuthor('Administrator');
$pdf->SetTitle('Category Report - ' . $category['name'] . ' - ' . date('Y-m-d'));
$pdf->SetSubject('Category Product Report');

$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

$pdf->AddPage();

$pdf->SetFont('dejavusans', 'B', 22);
$pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');

$pdf->SetFont('dejavusans', 'B', 16);
$pdf->Cell(0, 10, 'Category Report: ' . ucfirst($category['name']), 0, 1, 'C');
$pdf->Ln(5);

$categoryName = strtolower($category['name']);

$query = "SELECT id, name, sku, category, price, stock, size 
          FROM inventory 
          WHERE LOWER(category) = ?
          ORDER BY name";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $categoryName);
$stmt->execute();
$result = $stmt->get_result();
$inventory = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$totalItems = count($inventory);
$totalValue = 0;
$lowStockCount = 0;
$outOfStockCount = 0;
$inStockCount = 0;

foreach ($inventory as $item) {
    $totalValue += $item['price'] * $item['stock'];

    if ($item['stock'] == 0) {
        $outOfStockCount++;
    } elseif ($item['stock'] <= 10) {
        $lowStockCount++;
    } else {
        $inStockCount++;
    }
}

$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'Total Products: ' . $totalItems . ' | Total Value: ₱' . number_format($totalValue, 2), 0, 1, 'C');
$pdf->Cell(0, 8, 'In Stock: ' . $inStockCount . ' | Low Stock: ' . $lowStockCount . ' | Out of Stock: ' . $outOfStockCount, 0, 1, 'C');
$pdf->Ln(5);

$pdf->SetFont('dejavusans', 'B', 10);

$pdf->Cell(70, 10, 'Product Name', 1, 0, 'C');
$pdf->Cell(30, 10, 'Price', 1, 0, 'C');
$pdf->Cell(25, 10, 'Stock', 1, 0, 'C');
$pdf->Cell(60, 10, 'Size', 1, 0, 'C');
$pdf->Cell(45, 10, 'Total Value', 1, 0, 'C');
$pdf->Cell(40, 10, 'Status', 1, 1, 'C');

$pdf->SetFont('dejavusans', '', 9);

if (!empty($inventory)) {

    foreach ($inventory as $item) {

        $rowHeight = 8;
        $totalItemValue = $item['price'] * $item['stock'];

        if ($item['stock'] == 0) {
            $stockStatus = 'Out of Stock';
            $statusColor = [220, 38, 38];
        } elseif ($item['stock'] <= 10) {
            $stockStatus = 'Low Stock';
            $statusColor = [245, 158, 11];
        } else {
            $stockStatus = 'In Stock';
            $statusColor = [16, 185, 129];
        }

        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(70, $rowHeight, $item['name'], 1, 0, 'L');

        $pdf->Cell(30, $rowHeight, '₱' . number_format($item['price'], 2), 1, 0, 'R');

        $pdf->SetTextColor($statusColor[0], $statusColor[1], $statusColor[2]);
        $pdf->Cell(25, $rowHeight, $item['stock'], 1, 0, 'C');

        $pdf->SetTextColor(0, 0, 0);
        $pdf->MultiCell(60, $rowHeight, $item['size'], 1, 'C', false, 0);

        $pdf->Cell(45, $rowHeight, '₱' . number_format($totalItemValue, 2), 1, 0, 'R');

        $pdf->SetTextColor($statusColor[0], $statusColor[1], $statusColor[2]);
        $pdf->Cell(40, $rowHeight, $stockStatus, 1, 1, 'C');

        $pdf->SetTextColor(0, 0, 0);
    }

    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(230, 8, 'TOTAL INVENTORY VALUE', 1, 0, 'R');
    $pdf->Cell(40, 8, '₱' . number_format($totalValue, 2), 1, 1, 'R');

} else {

    $pdf->SetFont('dejavusans', 'I', 10);
    $pdf->Cell(270, 10, 'No products found in this category.', 1, 1, 'C');
}

$pdf->Ln(6);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(0, 6, 'Legend:', 0, 1, 'L');

$pdf->SetFont('dejavusans', '', 9);

$pdf->SetTextColor(16, 185, 129);
$pdf->Cell(70, 5, '● In Stock (> 10 units)', 0, 0, 'L');

$pdf->SetTextColor(245, 158, 11);
$pdf->Cell(70, 5, '● Low Stock (1-10 units)', 0, 0, 'L');

$pdf->SetTextColor(220, 38, 38);
$pdf->Cell(70, 5, '● Out of Stock (0 units)', 0, 1, 'L');

$pdf->SetTextColor(0, 0, 0);

$pdfContent = $pdf->Output('', 'S');

$reportName = 'Category Report - ' . ucfirst($category['name']) . ' - ' . date('F d, Y') . '.pdf';
$filePath = 'category_report_' . urlencode($category['name']) . '_' . date('Y-m-d') . '.pdf';
$fileSize = getReportFileSize($pdfContent);
$period = date('Y-m-d');

saveReportHistory($conn, 'category', $reportName, $filePath, $fileSize, $period, $pdfContent, $category['name']);

echo $pdfContent;
?>
