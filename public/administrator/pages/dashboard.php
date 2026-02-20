<?php
include '../../auth/sessionCheck.php';

require_once '../../config/connection.php';

// Function to get total products
function getTotalProducts() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM inventory";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Function to get low stock items (stock < 10)
function getLowStockItems() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM inventory WHERE stock < 10";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Function to get total revenue
function getTotalRevenue() {
    global $conn;
    $query = "SELECT COALESCE(SUM(total_amount), 0) as total FROM sales";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Function to get best seller
function getBestSeller() {
    global $conn;
    $query = "SELECT i.name, SUM(si.quantity) as total_sold 
              FROM sale_items si 
              JOIN inventory i ON si.product_id = i.id 
              GROUP BY i.id, i.name 
              ORDER BY total_sold DESC 
              LIMIT 1";
    $result = $conn->query($query);
    return $result->fetch_assoc();
}

// Function to get total orders
function getTotalOrders() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM sales";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Function to get active customers
function getActiveCustomers() {
    global $conn;
    $query = "SELECT COUNT(*) as total FROM users WHERE user_type = 'customer'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

// Function to get sales data for chart (last 6 months)
function getSalesData() {
    global $conn;
    $query = "SELECT 
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(total_amount) as total
            FROM sales 
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month ASC";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'month' => date('M', strtotime($row['month'] . '-01')),
            'total' => (float)$row['total']
        ];
    }
    return $data;
}

// Function to get sales by category
function getCategoryData() {
    global $conn;
    $query = "SELECT 
                i.category,
                SUM(si.quantity * si.price) as total
            FROM sale_items si 
            JOIN inventory i ON si.product_id = i.id
            GROUP BY i.category
            ORDER BY total DESC";
    $result = $conn->query($query);
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            'category' => $row['category'],
            'total' => (float)$row['total']
        ];
    }
    return $data;
}

// Call all functions to get data
$totalProducts = getTotalProducts();
$lowStockItems = getLowStockItems();
$totalRevenue = getTotalRevenue();
$bestSeller = getBestSeller();
$totalOrders = getTotalOrders();
$activeCustomers = getActiveCustomers();
$salesData = getSalesData();
$categoryData = getCategoryData();

// Format sales data for JavaScript
$salesLabels = json_encode(array_column($salesData, 'month'));
$salesValues = json_encode(array_column($salesData, 'total'));

// Format category data for JavaScript
$categoryLabels = json_encode(array_column($categoryData, 'category'));
$categoryValues = json_encode(array_column($categoryData, 'total'));
?>

<div class="main-content">
    <div class="content-header">
        <div class="header-top">
            <div>
                <h1 class="brand-title">SWABE APPAREL AND COLLECTION</h1>
                <p class="brand-subtitle">Inventory Management</p>
            </div>
        </div>
        
        <div>
            <h2 class="page-title">Dashboard Overview</h2>
            <p class="page-subtitle">Welcome to your admin dashboard</p>
        </div>
    </div>
    
    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card" style="--stat-color: #6366f1;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Total Products</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($totalProducts); ?></div>
                <div class="stat-change" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">All products</div>
        </div>
        
        <div class="stat-card" style="--stat-color: #10b981;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Low Stock Items</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($lowStockItems); ?></div>
            <div class="stat-change" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Requires attention</div>
        </div>
        
        <div class="stat-card" style="--stat-color: #10b981;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <text x="10" y="14" text-anchor="middle" font-size="14" fill="currentColor">₱</text>
                    </svg>
                </div>
            </div>
            <div class="stat-value">₱<?php echo number_format($totalRevenue, 2); ?></div>
            <div class="stat-change" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">From last month</div>
        </div>
        
        <div class="stat-card" style="--stat-color: #ec4899;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Best Seller</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                    </svg>
                </div>
            </div>
            <div class="stat-value"><?php echo $bestSeller['name'] ?? 'N/A'; ?></div>
            <div class="stat-change" style="background: rgba(16, 185, 129, 0.1); color: #10b981;"><?php echo number_format($bestSeller['total_sold'] ?? 0); ?> unit sold</div>
        </div>
    </div>
    
    <!-- Charts Grid -->
    <div class="charts-grid">
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Sales Overview</h3>
                <div class="chart-actions">
                </div>
            </div>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        
        <div class="chart-card">
            <div class="chart-header">
                <h3 class="chart-title">Sales by Category</h3>
                <div class="chart-actions">
                </div>
            </div>
            <div class="chart-container">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Sales Overview Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'line',
    data: {
        labels: <?php echo $salesLabels; ?>,
        datasets: [{
            label: 'Sales',
            data: <?php echo $salesValues; ?>,
            borderColor: '#6366f1',
            backgroundColor: 'rgba(99, 102, 241, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#2d3548' },
                ticks: { color: '#94a3b8' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8' }
            }
        }
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: <?php echo $categoryLabels; ?>,
        datasets: [{
            data: <?php echo $categoryValues; ?>,
            backgroundColor: ['#6366f1', '#7c3aed', '#ec4899', '#f59e0b', '#10b981', '#3b82f6'],
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: '#2d3548' },
                ticks: { color: '#94a3b8' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#94a3b8' }
            }
        }
    }
});
</script>