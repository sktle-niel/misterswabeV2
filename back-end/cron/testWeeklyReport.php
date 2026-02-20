<?php
require_once '../../config/connection.php';
require_once '../helpers/reportHelper.php';
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Copy the generateWeeklyReport function from autoGenerateReports.php
function generateWeeklyReport($conn, $weekStart) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle('Weekly Sales Report');
    $pdf->SetSubject('Weekly Sales Report');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    
    // Company Name
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');
    
    // Title
    $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Weekly Sales Report', 0, 1, 'C');
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 8, 'Week of ' . date('M d, Y', strtotime($weekStart)) . ' - ' . date('M d, Y', strtotime($weekEnd)), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Query week's sales
    $query = "SELECT s.id, s.total_amount, s.payment_method, s.created_at,
                     GROUP_CONCAT(CONCAT(COALESCE(i.name, 'Unknown Product'), ' (Qty: ', si.quantity, ')') SEPARATOR ', ') as products
              FROM sales s
              LEFT JOIN sale_items si ON s.id = si.sale_id
              LEFT JOIN inventory i ON si.product_id = i.id
              WHERE DATE(s.created_at) BETWEEN ? AND ?
              GROUP BY s.id
              ORDER BY s.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $weekStart, $weekEnd);
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
        $pdf->Cell(190, 10, 'No sales recorded for this week.', 1, 1, 'C');
    }
    
    // Get PDF content
    $pdfContent = $pdf->Output('week_sales_report_' . $weekStart . '.pdf', 'S');
    
    // Save to report history
    $reportName = 'Weekly Sales Report - Week of ' . date('M d, Y', strtotime($weekStart)) . '.pdf';
    $filePath = 'week_sales_report_' . $weekStart . '.pdf';
    $fileSize = getReportFileSize($pdfContent);
    $weekEnd = date('Y-m-d', strtotime($weekStart . ' +6 days'));
    $period = $weekStart . ' to ' . $weekEnd;
    
    saveReportHistory($conn, 'weekly_sales', $reportName, $filePath, $fileSize, $period);
    
    echo "Weekly report generated for week starting $weekStart<br>";
}

echo "<h2>Testing Weekly Report Generation</h2>";
echo "<hr>";

// Test 1: This week (current week)
echo "<h3>Test 1: THIS WEEK (Current Week)</h3>";
$thisWeekStart = date('Y-m-d', strtotime('monday this week'));
$thisWeekEnd = date('Y-m-d', strtotime('sunday this week'));
echo "<p>Week Start: <strong>$thisWeekStart</strong> (Monday)</p>";
echo "<p>Week End: <strong>$thisWeekEnd</strong> (Sunday)</p>";

generateWeeklyReport($conn, $thisWeekStart);
echo "<p style='color: green; font-weight: bold;'>✓ Current week report generated!</p>";

echo "<hr>";

// Test 2: Last week
echo "<h3>Test 2: LAST WEEK</h3>";
$lastWeekStart = date('Y-m-d', strtotime('monday last week'));
$lastWeekEnd = date('Y-m-d', strtotime('sunday last week'));
echo "<p>Week Start: <strong>$lastWeekStart</strong> (Monday)</p>";
echo "<p>Week End: <strong>$lastWeekEnd</strong> (Sunday)</p>";

generateWeeklyReport($conn, $lastWeekStart);
echo "<p style='color: green; font-weight: bold;'>✓ Last week report generated!</p>";

echo "<hr>";

// Test 3: Two weeks ago
echo "<h3>Test 3: TWO WEEKS AGO</h3>";
$twoWeeksAgoStart = date('Y-m-d', strtotime('monday -2 weeks'));
$twoWeeksAgoEnd = date('Y-m-d', strtotime('sunday -2 weeks'));
echo "<p>Week Start: <strong>$twoWeeksAgoStart</strong> (Monday)</p>";
echo "<p>Week End: <strong>$twoWeeksAgoEnd</strong> (Sunday)</p>";

generateWeeklyReport($conn, $twoWeeksAgoStart);
echo "<p style='color: green; font-weight: bold;'>✓ Two weeks ago report generated!</p>";

echo "<hr>";

// Show recent reports from database
echo "<h3>Recent Weekly Reports in Database:</h3>";
$result = $conn->query("SELECT * FROM report_history WHERE report_type = 'weekly_sales' ORDER BY generated_at DESC LIMIT 5");
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'>
        <th>ID</th>
        <th>Report Name</th>
        <th>Period</th>
        <th>File Size</th>
        <th>Generated At</th>
      </tr>";

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['id']}</td>";
        echo "<td>{$row['report_name']}</td>";
        echo "<td>{$row['period']}</td>";
        echo "<td>{$row['file_size']}</td>";
        echo "<td>" . date('M d, Y h:i A', strtotime($row['generated_at'])) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5' style='text-align: center; color: #999;'>No weekly reports found</td></tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3 style='color: green;'>✓ All tests completed! Check your Reports page to see the results.</h3>";
echo "<p><a href='../../public/administrator/?page=reports' style='background: black; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Go to Reports Page</a></p>";
?>
```

Then visit:
```
http://localhost/misterswabe/back-end/cron/testWeeklyReport.php