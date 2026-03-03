<?php
require_once '../../config/connection.php';
require_once '../helpers/reportHelper.php';

ob_start();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="inventory_alerts_' . date('Y-m-d') . '.pdf"');

// Include TCPDF library
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Create new PDF document in PORTRAIT mode
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Swabe Collection');
$pdf->SetAuthor('Administrator');
$pdf->SetTitle('Inventory Alerts Report - ' . date('Y-m-d'));
$pdf->SetSubject('Low Stock and Out of Stock Products Report');

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Add a page
$pdf->AddPage();

// Company Name
$pdf->SetFont('dejavusans', 'B', 22);
$pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');

// Title
$pdf->SetFont('dejavusans', 'B', 16);
$pdf->Cell(0, 10, 'Inventory Alerts Report', 0, 1, 'C');
$pdf->SetFont('dejavusans', '', 12);
$pdf->Cell(0, 8, 'Generated: ' . date('Y-m-d H:i:s'), 0, 1, 'C');
$pdf->Ln(5);

// ============ OUT OF STOCK SECTION ============
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'Out of Stock Products', 0, 1, 'L');
$pdf->Ln(2);

// Query out of stock products
$outOfStockQuery = "
    SELECT id, name, sku, stock, minimum_stock, status 
    FROM inventory 
    WHERE stock = 0 
    ORDER BY name ASC
";
$outOfStockResult = $conn->query($outOfStockQuery);
$outOfStock = [];
if ($outOfStockResult && $outOfStockResult->num_rows > 0) {
    while ($row = $outOfStockResult->fetch_assoc()) {
        $outOfStock[] = $row;
    }
}

$outOfStockCount = count($outOfStock);

// Table header for Out of Stock
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('dejavusans', 'B', 9);

$pdf->Cell(90, 8, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'SKU', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Quantity', 1, 1, 'C', true);

// Table content for Out of Stock
$pdf->SetFont('dejavusans', '', 9);
$pdf->SetTextColor(0, 0, 0);

if ($outOfStockCount > 0) {
    foreach ($outOfStock as $item) {
        $pdf->Cell(90, 7, substr($item['name'], 0, 40), 1, 0, 'L', false);
        $pdf->Cell(50, 7, $item['sku'], 1, 0, 'C', false);
        $pdf->Cell(50, 7, '0', 1, 1, 'C', false);
    }
} else {
    $pdf->SetFont('dejavusans', 'I', 9);
    $pdf->Cell(190, 7, 'No out of stock products', 1, 1, 'C', false);
}

$pdf->Ln(5);

// ============ LOW STOCK SECTION ============
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, 'Low Stock Products', 0, 1, 'L');
$pdf->Ln(2);

// Query low stock products
$lowStockQuery = "
    SELECT id, name, sku, stock, minimum_stock, status 
    FROM inventory 
    WHERE stock > 0 AND stock <= COALESCE(minimum_stock, 10) 
    ORDER BY stock ASC, name ASC
";
$lowStockResult = $conn->query($lowStockQuery);
$lowStock = [];
if ($lowStockResult && $lowStockResult->num_rows > 0) {
    while ($row = $lowStockResult->fetch_assoc()) {
        $lowStock[] = $row;
    }
}

$lowStockCount = count($lowStock);

// Table header for Low Stock
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('dejavusans', 'B', 9);

$pdf->Cell(90, 8, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'SKU', 1, 0, 'C', true);
$pdf->Cell(50, 8, 'Quantity', 1, 1, 'C', true);

// Table content for Low Stock
$pdf->SetFont('dejavusans', '', 9);
$pdf->SetTextColor(0, 0, 0);

if ($lowStockCount > 0) {
    foreach ($lowStock as $item) {
        $pdf->Cell(90, 7, substr($item['name'], 0, 40), 1, 0, 'L', false);
        $pdf->Cell(50, 7, $item['sku'], 1, 0, 'C', false);
        $pdf->Cell(50, 7, strval($item['stock']), 1, 1, 'C', false);
    }
} else {
    $pdf->SetFont('dejavusans', 'I', 9);
    $pdf->Cell(190, 7, 'No low stock products', 1, 1, 'C', false);
}

$pdf->Ln(10);

// Summary
$totalAlerts = $outOfStockCount + $lowStockCount;

$pdf->SetFont('dejavusans', 'B', 11);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 8, 'Summary', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('dejavusans', '', 10);
$pdf->Cell(0, 7, 'Total Out of Stock: ' . $outOfStockCount, 0, 1, 'L');
$pdf->Cell(0, 7, 'Total Low Stock: ' . $lowStockCount, 0, 1, 'L');
$pdf->Cell(0, 7, 'Total Alerts: ' . $totalAlerts, 0, 1, 'L');

// Get PDF content
$pdfContent = $pdf->Output('inventory_alerts_report_' . date('Y-m-d') . '.pdf', 'S');

// Save to report history
$reportName = 'Inventory Alerts Report - ' . date('F d, Y') . '.pdf';
$filePath = 'inventory_alerts_report_' . date('Y-m-d') . '.pdf';
$fileSize = getReportFileSize($pdfContent);
$period = date('Y-m-d');

saveReportHistory($conn, 'inventory_alerts', $reportName, $filePath, $fileSize, $period);

// Output the PDF
echo $pdfContent;

$conn->close();
?>
