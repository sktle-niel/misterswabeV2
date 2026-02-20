<?php
require_once '../../config/connection.php';
require_once '../helpers/reportHelper.php';

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="year_sales_report_' . date('Y') . '.pdf"');

// Include TCPDF library
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator('Swabe Collection');
$pdf->SetAuthor('Administrator');
$pdf->SetTitle('Yearly Sales Report - ' . date('Y'));
$pdf->SetSubject('Yearly Sales Report');

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
$pdf->Cell(0, 10, 'Yearly Sales Report - ' . date('Y'), 0, 1, 'C');
$pdf->Ln(5);

// Query this year's sales
$year = date('Y');

$query = "SELECT s.id, s.total_amount, s.payment_method, s.created_at,
                 GROUP_CONCAT(CONCAT(COALESCE(i.name, 'Unknown Product'), ' (Qty: ', si.quantity, ')') SEPARATOR ', ') as products
          FROM sales s
          LEFT JOIN sale_items si ON s.id = si.sale_id
          LEFT JOIN inventory i ON si.product_id = i.id
          WHERE YEAR(s.created_at) = ?
          GROUP BY s.id
          ORDER BY s.created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $year);
$stmt->execute();
$result = $stmt->get_result();
$sales = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate total
$totalAmount = 0;
foreach ($sales as $sale) {
    $totalAmount += $sale['total_amount'];
}

// Summary section
$pdf->SetFont('dejavusans', 'B', 12);
$pdf->Cell(0, 8, 'Total Sales: ' . count($sales) . ' | Total Revenue: ₱' . number_format($totalAmount, 2), 0, 1, 'C');
$pdf->Ln(5);

// Table header
$pdf->SetFillColor(255, 255, 255);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('dejavusans', 'B', 10);

$pdf->Cell(30, 10, 'Date', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Time', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Payment', 1, 0, 'C', true);
$pdf->Cell(70, 10, 'Products', 1, 0, 'C', true);
$pdf->Cell(35, 10, 'Amount', 1, 1, 'C', true);

// Reset text color for content
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('dejavusans', '', 9);

// Table content
if (count($sales) > 0) {
    foreach ($sales as $sale) {
        $date = date('M d, Y', strtotime($sale['created_at']));
        $time = date('H:i:s', strtotime($sale['created_at']));
        $payment = ucfirst($sale['payment_method']);
        $products = $sale['products'];
        $amount = '₱' . number_format($sale['total_amount'], 2);
        
        $productLines = $pdf->getNumLines($products, 70);
        $rowHeight = max(8, $productLines * 5);
        
        $pdf->MultiCell(30, $rowHeight, $date, 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
        $pdf->MultiCell(25, $rowHeight, $time, 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
        $pdf->MultiCell(30, $rowHeight, $payment, 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
        $pdf->MultiCell(70, $rowHeight, $products, 1, 'L', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
        $pdf->MultiCell(35, $rowHeight, $amount, 1, 'R', true, 1, '', '', true, 0, false, true, $rowHeight, 'M');
    }
    
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(155, 10, 'TOTAL', 1, 0, 'R', true);
    $pdf->Cell(35, 10, '₱' . number_format($totalAmount, 2), 1, 1, 'R', true);
    
} else {
    $pdf->SetFont('dejavusans', 'I', 10);
    $pdf->Cell(190, 10, 'No sales recorded for this year.', 1, 1, 'C');
}

// Get PDF content
$pdfContent = $pdf->Output('year_sales_report_' . date('Y') . '.pdf', 'S');

// Save to report history
$reportName = date('Y') . ' Sales Report.pdf';
$filePath = 'year_sales_report_' . date('Y') . '.pdf';
$fileSize = getReportFileSize($pdfContent);
$period = date('Y');

saveReportHistory($conn, 'yearly_sales', $reportName, $filePath, $fileSize, $period, $pdfContent);
// Output the PDF
echo $pdfContent;
?>