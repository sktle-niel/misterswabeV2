<?php
require_once '../../config/connection.php';
require_once '../helpers/reportHelper.php';

ob_start();

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="inventory_report_' . date('Y-m-d') . '.pdf"');

// Include TCPDF library
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Create new PDF document in PORTRAIT mode
$pdf = new TCPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); // 'P' = Portrait

// Set document information
$pdf->SetCreator('Swabe Collection');
$pdf->SetAuthor('Administrator');
$pdf->SetTitle('Inventory Report - ' . date('Y-m-d'));
$pdf->SetSubject('Inventory Status Report');

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
$pdf->Cell(0, 10, 'Inventory Report - ' . date('Y-m-d'), 0, 1, 'C');
$pdf->Ln(5);

// Query inventory
$query = "SELECT id, name, sku, category, price, stock, size, status 
          FROM inventory 
          ORDER BY category, name";
$result = $conn->query($query);
$inventory = $result->fetch_all(MYSQLI_ASSOC);

// Calculate statistics
$totalItems = count($inventory);
$totalValue = 0;
$lowStockCount = 0;
$outOfStockCount = 0;

foreach ($inventory as $item) {
    $totalValue += $item['price'] * $item['stock'];
    if ($item['stock'] <= 10 && $item['stock'] > 0) {
        $lowStockCount++;
    }
    if ($item['stock'] == 0) {
        $outOfStockCount++;
    }
}

// Summary section
$pdf->SetFont('dejavusans', 'B', 11);
$pdf->Cell(0, 8, 'Total Items: ' . $totalItems . ' | Total Value: ₱' . number_format($totalValue, 2) . ' | Low Stock: ' . $lowStockCount . ' | Out of Stock: ' . $outOfStockCount, 0, 1, 'C');
$pdf->Ln(5);

// Table header (without ID and SKU columns)
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('dejavusans', 'B', 10);

$pdf->Cell(60, 10, 'Product Name', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Category', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Price', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Stock', 1, 0, 'C', true);
$pdf->Cell(45, 10, 'Status', 1, 1, 'C', true);

// Table content
$pdf->SetFont('dejavusans', '', 9);

if (count($inventory) > 0) {
    foreach ($inventory as $item) {
        $rowHeight = 8;
        
        $stockStatus = '';
        if ($item['stock'] == 0) {
            $stockStatus = 'Out of Stock';
        } elseif ($item['stock'] <= 10) {
            $stockStatus = 'Low Stock';
        } else {
            $stockStatus = 'In Stock';
        }
        
        // Product Name
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, $rowHeight, $item['name'], 1, 0, 'L', true);
        
        // Category
        $pdf->Cell(35, $rowHeight, ucfirst($item['category']), 1, 0, 'C', true);
        
        // Price
        $pdf->Cell(30, $rowHeight, '₱' . number_format($item['price'], 2), 1, 0, 'R', true);
        
        // Stock (with color)
        if ($item['stock'] == 0) {
            $pdf->SetTextColor(220, 38, 38); // Red
        } elseif ($item['stock'] <= 10) {
            $pdf->SetTextColor(245, 158, 11); // Orange
        } else {
            $pdf->SetTextColor(16, 185, 129); // Green
        }
        $pdf->Cell(20, $rowHeight, $item['stock'], 1, 0, 'C', true);
        
        // Status (with color)
        if ($item['stock'] == 0) {
            $pdf->SetTextColor(220, 38, 38);
        } elseif ($item['stock'] <= 10) {
            $pdf->SetTextColor(245, 158, 11);
        } else {
            $pdf->SetTextColor(0, 0, 0);
        }
        $pdf->Cell(45, $rowHeight, $stockStatus, 1, 1, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0); // Reset to black
    }
    
    // Total row
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(125, 8, 'TOTAL INVENTORY VALUE', 1, 0, 'R', true);
    $pdf->Cell(65, 8, '₱' . number_format($totalValue, 2), 1, 1, 'R', true);
    
} else {
    $pdf->SetFont('dejavusans', 'I', 10);
    $pdf->Cell(190, 10, 'No inventory items found.', 1, 1, 'C');
}

// Add legend
$pdf->Ln(5);
$pdf->SetFont('dejavusans', 'B', 10);
$pdf->Cell(0, 6, 'Legend:', 0, 1, 'L');
$pdf->SetFont('dejavusans', '', 9);

$pdf->SetTextColor(16, 185, 129);
$pdf->Cell(60, 5, '● In Stock (> 10 units)', 0, 0, 'L');

$pdf->SetTextColor(245, 158, 11);
$pdf->Cell(60, 5, '● Low Stock (1-10 units)', 0, 0, 'L');

$pdf->SetTextColor(220, 38, 38);
$pdf->Cell(60, 5, '● Out of Stock (0 units)', 0, 1, 'L');

$pdf->SetTextColor(0, 0, 0);

// Get PDF content
$pdfContent = $pdf->Output('inventory_report_' . date('Y-m-d') . '.pdf', 'S');

// Save to report history
$reportName = 'Inventory Report - ' . date('F d, Y') . '.pdf';
$filePath = 'inventory_report_' . date('Y-m-d') . '.pdf';
$fileSize = getReportFileSize($pdfContent);
$period = date('Y-m-d');

saveReportHistory($conn, 'inventory', $reportName, $filePath, $fileSize, $period);

// Output the PDF
echo $pdfContent;
?>