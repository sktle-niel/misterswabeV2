<?php
require_once '../../config/connection.php';
require_once '../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Get report ID from URL
$reportId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($reportId <= 0) {
    die('Invalid report ID');
}

// Get report details from database
$query = "SELECT * FROM report_history WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $reportId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Report not found in database');
}

$report = $result->fetch_assoc();
$stmt->close();

// Regenerate PDF based on report type
switch ($report['report_type']) {
    case 'daily_sales':
        generateDailySalesReport($conn, $report);
        break;
    case 'weekly_sales':
        generateWeeklySalesReport($conn, $report);
        break;
    case 'monthly_sales':
        generateMonthlySalesReport($conn, $report);
        break;
    case 'yearly_sales':
        generateYearlySalesReport($conn, $report);
        break;
    case 'inventory':
        generateInventoryReport($conn, $report);
        break;
    case 'category':
        generateCategoryReport($conn, $report);
        break;
    default:
        die('Unknown report type');
}

// ===== DAILY SALES REPORT =====
function generateDailySalesReport($conn, $report) {
    // Extract date from period
    $date = $report['period'];
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle($report['report_name']);
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
    
    // Query sales for this date
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
    
    $totalAmount = 0;
    foreach ($sales as $sale) {
        $totalAmount += $sale['total_amount'];
    }
    
    // Summary
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
    
    // Table content
    $pdf->SetFont('dejavusans', '', 9);
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
        
        $pdf->SetFont('dejavusans', 'B', 10);
        $pdf->Cell(155, 10, 'TOTAL', 1, 0, 'R', true);
        $pdf->Cell(35, 10, '₱' . number_format($totalAmount, 2), 1, 1, 'R', true);
    } else {
        $pdf->Cell(190, 10, 'No sales recorded.', 1, 1, 'C');
    }
    
    outputPDF($pdf, $report['report_name']);
}

// ===== WEEKLY SALES REPORT =====
function generateWeeklySalesReport($conn, $report) {
    // Extract dates from period (format: "2026-02-10 to 2026-02-16")
    list($weekStart, $weekEnd) = explode(' to ', $report['period']);
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle($report['report_name']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    
    // Company Name
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');
    
    // Title
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Weekly Sales Report', 0, 1, 'C');
    $pdf->SetFont('dejavusans', '', 12);
    $pdf->Cell(0, 8, 'Week of ' . date('M d, Y', strtotime($weekStart)) . ' - ' . date('M d, Y', strtotime($weekEnd)), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Query sales
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
    
    $totalAmount = 0;
    foreach ($sales as $sale) {
        $totalAmount += $sale['total_amount'];
    }
    
    // Summary
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
    
    // Table content
    $pdf->SetFont('dejavusans', '', 9);
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
        $pdf->Cell(190, 10, 'No sales recorded.', 1, 1, 'C');
    }
    
    outputPDF($pdf, $report['report_name']);
}

// ===== MONTHLY SALES REPORT =====
function generateMonthlySalesReport($conn, $report) {
    // Extract year and month from period (format: "February 2026")
    $dateObj = DateTime::createFromFormat('F Y', $report['period']);
    $year = $dateObj->format('Y');
    $month = $dateObj->format('m');
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle($report['report_name']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    
    // Company Name
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');
    
    // Title
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Monthly Sales Report - ' . $report['period'], 0, 1, 'C');
    $pdf->Ln(5);
    
    // Query sales
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
    
    $totalAmount = 0;
    foreach ($sales as $sale) {
        $totalAmount += $sale['total_amount'];
    }
    
    // Summary
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
    
    // Table content
    $pdf->SetFont('dejavusans', '', 9);
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
        $pdf->Cell(190, 10, 'No sales recorded.', 1, 1, 'C');
    }
    
    outputPDF($pdf, $report['report_name']);
}

// ===== YEARLY SALES REPORT =====
function generateYearlySalesReport($conn, $report) {
    // Extract year from period (format: "2026")
    $year = $report['period'];
    
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle($report['report_name']);
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
    
    // Query sales
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
    
    $totalAmount = 0;
    foreach ($sales as $sale) {
        $totalAmount += $sale['total_amount'];
    }
    
    // Summary
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
    
    // Table content
    $pdf->SetFont('dejavusans', '', 9);
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
        $pdf->Cell(190, 10, 'No sales recorded.', 1, 1, 'C');
    }
    
    outputPDF($pdf, $report['report_name']);
}

// ===== INVENTORY REPORT =====
function generateInventoryReport($conn, $report) {
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle($report['report_name']);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->AddPage();
    
    // Company Name
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');
    
    // Title
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Inventory Report - ' . $report['period'], 0, 1, 'C');
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
    
    // Summary
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
        
        if ($item['stock'] == 0) {
            $stockStatus = 'Out of Stock';
        } elseif ($item['stock'] <= 10) {
            $stockStatus = 'Low Stock';
        } else {
            $stockStatus = 'In Stock';
        }
        
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(60, $rowHeight, $item['name'], 1, 0, 'L', true);
        $pdf->Cell(35, $rowHeight, ucfirst($item['category']), 1, 0, 'C', true);
        $pdf->Cell(30, $rowHeight, '₱' . number_format($item['price'], 2), 1, 0, 'R', true);
        
        if ($item['stock'] == 0) {
            $pdf->SetTextColor(220, 38, 38);
        } elseif ($item['stock'] <= 10) {
            $pdf->SetTextColor(245, 158, 11);
        } else {
            $pdf->SetTextColor(16, 185, 129);
        }
        $pdf->Cell(20, $rowHeight, $item['stock'], 1, 0, 'C', true);
        
        if ($item['stock'] == 0) {
            $pdf->SetTextColor(220, 38, 38);
        } elseif ($item['stock'] <= 10) {
            $pdf->SetTextColor(245, 158, 11);
        } else {
            $pdf->SetTextColor(0, 0, 0);
        }
        $pdf->Cell(45, $rowHeight, $stockStatus, 1, 1, 'C', true);
        
        $pdf->SetTextColor(0, 0, 0);
    }
    
    $pdf->SetFont('dejavusans', 'B', 10);
    $pdf->Cell(125, 8, 'TOTAL INVENTORY VALUE', 1, 0, 'R', true);
    $pdf->Cell(65, 8, '₱' . number_format($totalValue, 2), 1, 1, 'R', true);
} else {
    $pdf->Cell(190, 10, 'No inventory items found.', 1, 1, 'C');
}
    
    outputPDF($pdf, $report['report_name']);
}

// ===== CATEGORY REPORT =====
function generateCategoryReport($conn, $report) {
    // Extract category name from report_name (format: "Category Report - CategoryName - February 14, 2026.pdf")
    // We need to get the category from the database using the period
    
    $period = $report['period']; // This should be the date when the report was generated
    
    // Get the category from the report_name
    $reportName = $report['report_name'];
    // Extract category name from "Category Report - {categoryName} - {date}.pdf"
    if (preg_match('/Category Report - (.+?) -/', $reportName, $matches)) {
        $categoryName = strtolower(trim($matches[1]));
    } else {
        $categoryName = '';
    }
    
    $pdf = new TCPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    $pdf->SetCreator('Swabe Collection');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle($report['report_name']);
    $pdf->SetSubject('Category Product Report');
    
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    $pdf->AddPage();
    
    $pdf->SetFont('dejavusans', 'B', 22);
    $pdf->Cell(0, 12, 'SWABE APPAREL AND COLLECTION', 0, 1, 'C');
    
    $pdf->SetFont('dejavusans', 'B', 16);
    $pdf->Cell(0, 10, 'Category Report: ' . ucfirst($categoryName), 0, 1, 'C');
    $pdf->Ln(5);
    
    // Query inventory for this category
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
    
    outputPDF($pdf, $report['report_name']);
}

// ===== HELPER FUNCTION TO OUTPUT PDF =====
function outputPDF($pdf, $filename) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    echo $pdf->Output($filename, 'S');
    exit;
}
?>