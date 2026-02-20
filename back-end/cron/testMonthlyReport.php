<?php
require_once '../../config/connection.php';

echo "<h2>Month Date Calculator</h2>";
echo "<hr>";

// Current month
$thisMonth = date('Y-m');
$thisMonthName = date('F Y');
echo "<h3>THIS MONTH</h3>";
echo "<p>Year-Month: <strong>$thisMonth</strong></p>";
echo "<p>Month Name: <strong>$thisMonthName</strong></p>";
echo "<p>First Day: <strong>" . date('Y-m-01') . "</strong></p>";
echo "<p>Last Day: <strong>" . date('Y-m-t') . "</strong></p>";
echo "<p>Today: <strong>" . date('Y-m-d (l)') . "</strong></p>";

echo "<hr>";

// Last month
$lastMonth = date('Y-m', strtotime('first day of last month'));
$lastMonthName = date('F Y', strtotime('first day of last month'));
echo "<h3>LAST MONTH</h3>";
echo "<p>Year-Month: <strong>$lastMonth</strong></p>";
echo "<p>Month Name: <strong>$lastMonthName</strong></p>";
echo "<p>First Day: <strong>" . date('Y-m-01', strtotime('first day of last month')) . "</strong></p>";
echo "<p>Last Day: <strong>" . date('Y-m-t', strtotime('first day of last month')) . "</strong></p>";

echo "<hr>";

// Check if today is the 1st
if (date('d') == '01') {
    echo "<p style='color: green; background: #e8f5e9; padding: 15px; border-radius: 5px;'>";
    echo "✓ <strong>Today IS the 1st of the month!</strong><br>";
    echo "The auto-report would trigger and generate a report for: <strong>$lastMonthName</strong>";
    echo "</p>";
} else {
    echo "<p style='color: orange; background: #fff3e0; padding: 15px; border-radius: 5px;'>";
    echo "⚠ <strong>Today is NOT the 1st of the month</strong><br>";
    echo "Today is: <strong>" . date('F d, Y (l)') . "</strong><br>";
    echo "Auto-report only runs on the 1st of each month.";
    echo "</p>";
}

echo "<hr>";

// Check sales data for this month
echo "<h3>Sales Data for This Month ($thisMonthName):</h3>";
$query = "SELECT 
            DATE(created_at) as sale_date, 
            COUNT(*) as count, 
            SUM(total_amount) as total
          FROM sales 
          WHERE YEAR(created_at) = YEAR(CURDATE()) 
          AND MONTH(created_at) = MONTH(CURDATE())
          GROUP BY sale_date
          ORDER BY sale_date";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $totalCount = 0;
    $totalAmount = 0;
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Date</th><th>Sales Count</th><th>Total Amount</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $totalCount += $row['count'];
        $totalAmount += $row['total'];
        
        echo "<tr>";
        echo "<td>" . date('M d, Y (l)', strtotime($row['sale_date'])) . "</td>";
        echo "<td>{$row['count']}</td>";
        echo "<td>₱" . number_format($row['total'], 2) . "</td>";
        echo "</tr>";
    }
    
    echo "<tr style='background: #e3f2fd; font-weight: bold;'>";
    echo "<td>TOTAL</td>";
    echo "<td>$totalCount</td>";
    echo "<td>₱" . number_format($totalAmount, 2) . "</td>";
    echo "</tr>";
    
    echo "</table>";
} else {
    echo "<p style='color: red;'>No sales data for this month yet.</p>";
}

echo "<hr>";

// Show all months with data
// Show months with sales data
echo "<h3>Months with Sales Data:</h3>";
$query = "SELECT 
            DATE_FORMAT(MIN(created_at), '%Y-%m') as `year_month`,
            DATE_FORMAT(MIN(created_at), '%M %Y') as `month_name`,
            COUNT(*) as sales_count,
            SUM(total_amount) as total_sales
          FROM sales 
          GROUP BY YEAR(created_at), MONTH(created_at)
          ORDER BY YEAR(created_at) DESC, MONTH(created_at) DESC
          LIMIT 12";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'>
            <th>Month</th>
            <th>Sales Count</th>
            <th>Total Sales</th>
            <th>Action</th>
          </tr>";
    
    while ($row = $result->fetch_assoc()) {
        $yearMonth = $row['year_month'];
        $monthName = $row['month_name'];
        $salesCount = $row['sales_count'];
        $totalSales = number_format($row['total_sales'], 2);
        
        echo "<tr>";
        echo "<td><strong>$monthName</strong> ($yearMonth)</td>";
        echo "<td>$salesCount</td>";
        echo "<td>₱$totalSales</td>";
        echo "<td><button onclick=\"generateMonth('$yearMonth')\">Generate Now</button></td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>No sales data found in database.</p>";
}

echo "<hr>";
echo "<p><a href='testMonthlyReport.php' style='background: #2196F3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>Generate Monthly Reports</a></p>";
?>