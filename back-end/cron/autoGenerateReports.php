<?php
require_once '../../config/connection.php';
require_once '../helpers/reportHelper.php';
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Function to generate and save daily report
function generateDailyReport($conn, $date) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle('Sales Report - ' . $date);
    $pdf->SetSubject('Daily Sales Report');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    
    // Company Name
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');
    
    // Title
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Sales Report - ' . $date, 0, 1, 'C');
    $pdf->Ln(5);
    
    // Query sales for specific date
    $query = "SELECT s.id, s.total_amount, s.payment_method, s.created_at,
                     GROUP_CONCAT(CONCAT(COALESCE(i.name, 'Unknown Product'), ' (Qty: ', si.quantity, ')') SEPARATOR ', ') as products
              FROM sales s
              LEFT JOIN sale_items si ON s.id = si.sale_id
              LEFT JOIN inventory i ON si.product_id = i.id
              WHERE DATE(s.created_at) = ?
              GROUP BY s.id
              ORDER BY s.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $date);
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
    
    $pdf->Cell(25, 10, 'Time', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Payment', 1, 0, 'C', true);
    $pdf->Cell(100, 10, 'Products', 1, 0, 'C', true);
    $pdf->Cell(35, 10, 'Amount', 1, 1, 'C', true);
    
    // Reset text color for content
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('dejavusans', '', 9);
    
    // Table content
    if (count($sales) > 0) {
        foreach ($sales as $sale) {
            $time = date('H:i:s', strtotime($sale['created_at']));
            $payment = ucfirst($sale['payment_method']);
            $products = $sale['products'];
            $amount = '₱' . number_format($sale['total_amount'], 2);
            
            $productLines = $pdf->getNumLines($products, 100);
            $rowHeight = max(8, $productLines * 5);
            
            $pdf->MultiCell(25, $rowHeight, $time, 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
            $pdf->MultiCell(30, $rowHeight, $payment, 1, 'C', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
            $pdf->MultiCell(100, $rowHeight, $products, 1, 'L', true, 0, '', '', true, 0, false, true, $rowHeight, 'M');
            $pdf->MultiCell(35, $rowHeight, $amount, 1, 'R', true, 1, '', '', true, 0, false, true, $rowHeight, 'M');
        }
        
        // Total row
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(155, 10, 'TOTAL', 1, 0, 'R', true);
        $pdf->Cell(35, 10, '₱' . number_format($totalAmount, 2), 1, 1, 'R', true);
        
    } else {
        $pdf->SetFont('dejavusans', 'I', 10);
        $pdf->Cell(190, 10, 'No sales recorded for this date.', 1, 1, 'C');
    }
    
    // Get PDF content
    $pdfContent = $pdf->Output('sales_report_' . $date . '.pdf', 'S');
    
    // Save to report history
    $reportName = 'Daily Sales Report - ' . date('F d, Y', strtotime($date)) . '.pdf';
    $filePath = 'sales_report_' . $date . '.pdf';
    $fileSize = getReportFileSize($pdfContent);
    $period = $date;
    
    saveReportHistory($conn, 'daily_sales', $reportName, $filePath, $fileSize, $period);
    
    echo "Daily report generated for $date\n";
}

// Function to generate and save weekly report
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
    $period = $weekStart . ' to ' . $weekEnd;
    
    saveReportHistory($conn, 'weekly_sales', $reportName, $filePath, $fileSize, $period);
    
    echo "Weekly report generated for week starting $weekStart\n";
}

// Function to generate and save monthly report
function generateMonthlyReport($conn, $yearMonth) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle('Monthly Sales Report - ' . date('F Y', strtotime($yearMonth . '-01')));
    $pdf->SetSubject('Monthly Sales Report');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    
    // Company Name
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');
    
    // Title
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Monthly Sales Report - ' . date('F Y', strtotime($yearMonth . '-01')), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Query month's sales
    $year = substr($yearMonth, 0, 4);
    $month = substr($yearMonth, 5, 2);
    
    $query = "SELECT s.id, s.total_amount, s.payment_method, s.created_at,
                     GROUP_CONCAT(CONCAT(COALESCE(i.name, 'Unknown Product'), ' (Qty: ', si.quantity, ')') SEPARATOR ', ') as products
              FROM sales s
              LEFT JOIN sale_items si ON s.id = si.sale_id
              LEFT JOIN inventory i ON si.product_id = i.id
              WHERE YEAR(s.created_at) = ? AND MONTH(s.created_at) = ?
              GROUP BY s.id
              ORDER BY s.created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $year, $month);
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
        $pdf->Cell(190, 10, 'No sales recorded for this month.', 1, 1, 'C');
    }
    
    // Get PDF content
    $pdfContent = $pdf->Output('month_sales_report_' . $yearMonth . '.pdf', 'S');
    
    // Save to report history
    $reportName = date('F Y', strtotime($yearMonth . '-01')) . ' Sales Report.pdf';
    $filePath = 'month_sales_report_' . $yearMonth . '.pdf';
    $fileSize = getReportFileSize($pdfContent);
    $period = date('F Y', strtotime($yearMonth . '-01'));
    
    saveReportHistory($conn, 'monthly_sales', $reportName, $filePath, $fileSize, $period);
    
    echo "Monthly report generated for $yearMonth\n";
}

// Function to generate and save yearly report
function generateYearlyReport($conn, $year) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle('Yearly Sales Report - ' . $year);
    $pdf->SetSubject('Yearly Sales Report');
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    
    // Company Name
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');
    
    // Title
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Yearly Sales Report - ' . $year, 0, 1, 'C');
    $pdf->Ln(5);
    
    // Query year's sales
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
    $pdfContent = $pdf->Output('year_sales_report_' . $year . '.pdf', 'S');
    
    // Save to report history
    $reportName = $year . ' Sales Report.pdf';
    $filePath = 'year_sales_report_' . $year . '.pdf';
    $fileSize = getReportFileSize($pdfContent);
    $period = $year;
    
    saveReportHistory($conn, 'yearly_sales', $reportName, $filePath, $fileSize, $period);
    
    echo "Yearly report generated for $year\n";
}

// Main execution
echo "Starting automatic report generation...\n\n";

// Generate yesterday's daily report (if not already generated)
$yesterday = date('Y-m-d', strtotime('-1 day'));
$checkDaily = $conn->query("SELECT id FROM report_history WHERE report_type = 'daily_sales' AND period = '$yesterday'");
if ($checkDaily->num_rows == 0) {
    generateDailyReport($conn, $yesterday);
}

// Generate last week's report (if it's Monday and not already generated)
if (date('N') == 1) { // Monday
    $lastWeekStart = date('Y-m-d', strtotime('last monday -7 days'));
    $checkWeekly = $conn->query("SELECT id FROM report_history WHERE report_type = 'weekly_sales' AND period LIKE '$lastWeekStart%'");
    if ($checkWeekly->num_rows == 0) {
        generateWeeklyReport($conn, $lastWeekStart);
    }
}

// Generate last month's report (if it's the 1st of the month and not already generated)
if (date('d') == '01') {
    $lastMonth = date('Y-m', strtotime('first day of last month'));
    $checkMonthly = $conn->query("SELECT id FROM report_history WHERE report_type = 'monthly_sales' AND period = '" . date('F Y', strtotime($lastMonth . '-01')) . "'");
    if ($checkMonthly->num_rows == 0) {
        generateMonthlyReport($conn, $lastMonth);
    }
}

// Generate last year's report (if it's January 1st and not already generated)
if (date('m-d') == '01-01') {
    $lastYear = date('Y', strtotime('-1 year'));
    $checkYearly = $conn->query("SELECT id FROM report_history WHERE report_type = 'yearly_sales' AND period = '$lastYear'");
    if ($checkYearly->num_rows == 0) {
        generateYearlyReport($conn, $lastYear);
    }
}

echo "\nAutomatic report generation completed!\n";
?>