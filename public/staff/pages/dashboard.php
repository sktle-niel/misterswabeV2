 <?php
require_once '../../config/connection.php';
include '../../auth/sessionCheck.php';

// Function to get total sales for a period
function getTotalSales($period) {
    global $conn;
    $query = "";
    switch ($period) {
        case 'today':
            $query = "SELECT SUM(total_amount) as total FROM sales WHERE DATE(created_at) = CURDATE()";
            break;
        case 'week':
            $query = "SELECT SUM(total_amount) as total FROM sales WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";
            break;
        case 'month':
            $query = "SELECT SUM(total_amount) as total FROM sales WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
            break;
    }
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Get recent sales
function getRecentSales() {
    global $conn;
    $query = "SELECT s.id, s.total_amount, s.payment_method, s.created_at,
                     GROUP_CONCAT(CONCAT(i.name, ' (Qty: ', si.quantity, ')') SEPARATOR ', ') as products
              FROM sales s
              LEFT JOIN sale_items si ON s.id = si.sale_id
              LEFT JOIN inventory i ON si.product_id = i.id
              GROUP BY s.id
              ORDER BY s.created_at DESC
              LIMIT 5";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get top product by total quantity sold
function getTopProducts() {
    global $conn;
    $query = "SELECT i.name, SUM(si.quantity) as total_quantity
              FROM sale_items si
              LEFT JOIN inventory i ON si.product_id = i.id
              GROUP BY si.product_id
              ORDER BY total_quantity DESC
              LIMIT 1";
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}



$totalToday = getTotalSales('today');
$totalWeek = getTotalSales('week');
$totalMonth = getTotalSales('month');
$recentSales = getRecentSales();
$topProducts = getTopProducts();
?>
<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Staff Dashboard</h2>
            <p class="page-subtitle">Sales overview and recent transactions</p>
        </div>
    </div>

    <!-- Important Note -->
    <div class="alert alert-warning" style="margin-bottom: 20px; padding: 15px; border: 1px solid #f39c12; background-color: #fff3cd; color: #856404; border-radius: 5px;">
        <strong>Important Note:</strong> Staff cannot delete added sales. To void or delete a sale, please request it from the administrator.
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card" style="--stat-color: #6366f1;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Today's Sales</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <text x="10" y="14" text-anchor="middle" font-size="14" fill="currentColor">₱</text>
                    </svg>
                </div>
            </div>
            <div class="stat-value">₱<?php echo number_format($totalToday, 2); ?></div>
            <div class="stat-change positive" style="color: black;">Sales today</div>
        </div>

        <div class="stat-card" style="--stat-color: #10b981;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">This Week</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-value">₱<?php echo number_format($totalWeek, 2); ?></div>
            <div class="stat-change positive" style="color: black;">This week</div>
        </div>

        <div class="stat-card" style="--stat-color: #ec4899;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">This Month</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                        <path d="M8 14l2 2 4-4"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-value">₱<?php echo number_format($totalMonth, 2); ?></div>
            <div class="stat-change positive" style="color: black;">This month</div>
        </div>

        <div class="stat-card" style="--stat-color: #f59e0b;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Top Product</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?php echo $topProducts[0]['name'] ?? 'N/A'; ?></div>
            <div class="stat-change positive" style="color: black;"><?php echo $topProducts[0]['total_quantity'] ?? 0; ?> units sold</div>
        </div>
    </div>



    <!-- Recent Sales -->
    <div class="card">
        <div class="card-header">
            <h3>Recent Sales Transactions</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sale ID</th>

                            <th>Products</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentSales as $sale): ?>
                        <tr>
                            <td><?php echo $sale['id']; ?></td>
                            <td><?php echo htmlspecialchars($sale['products']); ?></td>
                            <td>₱<?php echo number_format($sale['total_amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($sale['payment_method']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($sale['created_at'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


</div>


