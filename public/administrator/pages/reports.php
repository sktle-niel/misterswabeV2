<?php
require_once '../../config/connection.php';
include '../../auth/sessionCheck.php';

// Query today's total sales
$todaySalesQuery = "SELECT SUM(total_amount) as total FROM sales WHERE DATE(created_at) = CURDATE()";
$todaySalesResult = $conn->query($todaySalesQuery);
$todayTotal = $todaySalesResult->fetch_assoc()['total'] ?? 0;

// Query this week's total sales
$weekSalesQuery = "SELECT SUM(total_amount) as total FROM sales WHERE YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
$weekSalesResult = $conn->query($weekSalesQuery);
$weekTotal = $weekSalesResult->fetch_assoc()['total'] ?? 0;

// Query this month's total sales
$monthSalesQuery = "SELECT SUM(total_amount) as total FROM sales WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
$monthSalesResult = $conn->query($monthSalesQuery);
$monthTotal = $monthSalesResult->fetch_assoc()['total'] ?? 0;

// Query this year's total sales
$yearSalesQuery = "SELECT SUM(total_amount) as total FROM sales WHERE YEAR(created_at) = YEAR(CURDATE())";
$yearSalesResult = $conn->query($yearSalesQuery);
$yearTotal = $yearSalesResult->fetch_assoc()['total'] ?? 0;

// Get available years from report history
$yearsQuery = "SELECT DISTINCT YEAR(generated_at) as year FROM report_history ORDER BY year DESC";
$yearsResult = $conn->query($yearsQuery);
$availableYears = [];
while ($row = $yearsResult->fetch_assoc()) {
    $availableYears[] = $row['year'];
}

// If no years in database, add current year
if (empty($availableYears)) {
    $availableYears[] = date('Y');
}

// Get selected year from URL or default to current year
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Query recent reports filtered by year
$recentReportsQuery = "SELECT * FROM report_history 
                       WHERE YEAR(generated_at) = ? 
                       ORDER BY generated_at DESC 
                       LIMIT 50";
$stmt = $conn->prepare($recentReportsQuery);
$stmt->bind_param("i", $selectedYear);
$stmt->execute();
$recentReportsResult = $stmt->get_result();

// Query all categories for category report
$categoriesQuery = "SELECT id, name FROM categories ORDER BY name";
$categoriesResult = $conn->query($categoriesQuery);
$categories = [];
while ($row = $categoriesResult->fetch_assoc()) {
    $categories[] = $row;
}
?>
<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Reports</h2>
            <p class="page-subtitle">Generate and download business reports</p>
        </div>
    </div>
    
    
    <!-- Quick Generate -->
    <div class="card" style="margin-bottom: var(--spacing-2xl);">
        <div style="margin-bottom: var(--spacing-lg);">
            <h3 style="font-size: 1.125rem; font-weight: 600;">Quick Generate</h3>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: var(--spacing-lg);">
            <!-- Today's Sales Report -->
            <div style="padding: var(--spacing-lg); background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: var(--radius-lg); transition: all var(--transition-base);" onmouseover="this.style.borderColor='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--spacing-md);">
                    <div style="width: 48px; height: 48px; background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--accent-primary);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <text x="10" y="14" text-anchor="middle" font-size="14" fill="currentColor">₱</text>
                    </svg>
                    </div>
                </div>
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: var(--spacing-xs);">Today's Sales Report</h4>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--spacing-md);">Today's sales: <span style="color: black; font-weight: bold;">₱<?php echo number_format($todayTotal, 2); ?></span></p>
                <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('Y-m-d'); ?></p>
                <button class="btn" style="width: 100%; margin-top: var(--spacing-md); background-color: black !important; border-color: black; color: #fff;" onclick="window.location.href='../../../back-end/download/salesReport.php'">Download</button>
            </div>
            
            <!-- This Week's Sales Report -->
            <div style="padding: var(--spacing-lg); background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: var(--radius-lg); transition: all var(--transition-base);" onmouseover="this.style.borderColor='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--spacing-md);">
                    <div style="width: 48px; height: 48px; background: rgba(16, 185, 129, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--accent-success);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <text x="10" y="14" text-anchor="middle" font-size="14" fill="currentColor">₱</text>
                    </svg>
                    </div>
                </div>
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: var(--spacing-xs);">This Week's Sales Report</h4>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--spacing-md);">Week's sales: <span style="color: black; font-weight: bold;">₱<?php echo number_format($weekTotal, 2); ?></span></p>
                <p style="font-size: 0.75rem; color: var(--text-muted);">Week of <?php echo date('M d, Y', strtotime('monday this week')); ?></p>
                <button class="btn" style="width: 100%; margin-top: var(--spacing-md); background-color: black !important; border-color: black; color: #fff;" onclick="window.location.href='../../../back-end/download/weekSalesReport.php'">Download</button>
            </div>
            
            <!-- This Month's Sales Report -->
            <div style="padding: var(--spacing-lg); background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: var(--radius-lg); transition: all var(--transition-base);" onmouseover="this.style.borderColor='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--spacing-md);">
                    <div style="width: 48px; height: 48px; background: rgba(139, 92, 246, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: #8b5cf6;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <text x="10" y="14" text-anchor="middle" font-size="14" fill="currentColor">₱</text>
                    </svg>
                    </div>
                </div>
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: var(--spacing-xs);">This Month's Sales Report</h4>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--spacing-md);">Month's sales: <span style="color: black; font-weight: bold;">₱<?php echo number_format($monthTotal, 2); ?></span></p>
                <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('F Y'); ?></p>
                <button class="btn" style="width: 100%; margin-top: var(--spacing-md); background-color: black !important; border-color: black; color: #fff;" onclick="window.location.href='../../../back-end/download/monthSalesReport.php'">Download</button>
            </div>
            
            <!-- This Year's Sales Report -->
            <div style="padding: var(--spacing-lg); background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: var(--radius-lg); transition: all var(--transition-base);" onmouseover="this.style.borderColor='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--spacing-md);">
                    <div style="width: 48px; height: 48px; background: rgba(245, 158, 11, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--accent-warning);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <text x="10" y="14" text-anchor="middle" font-size="14" fill="currentColor">₱</text>
                    </svg>
                    </div>
                </div>
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: var(--spacing-xs);">This Year's Sales Report</h4>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--spacing-md);">Year's sales: <span style="color: black; font-weight: bold;">₱<?php echo number_format($yearTotal, 2); ?></span></p>
                <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('Y'); ?></p>
                <button class="btn" style="width: 100%; margin-top: var(--spacing-md); background-color: black !important; border-color: black; color: #fff;" onclick="window.location.href='../../../back-end/download/yearSalesReport.php'">Download</button>
            </div>
            
<!-- Inventory Report -->
            <div style="padding: var(--spacing-lg); background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: var(--radius-lg); transition: all var(--transition-base);" onmouseover="this.style.borderColor='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--spacing-md);">
                    <div style="width: 48px; height: 48px; background: rgba(220, 38, 38, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: #dc2626;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                </div>
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: var(--spacing-xs);">Inventory Report</h4>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--spacing-md);">Current stock levels and reorder requirements</p>
                <p style="font-size: 0.75rem; color: var(--text-muted);"><?php echo date('Y-m-d'); ?></p>
                <button class="btn" style="width: 100%; margin-top: var(--spacing-md); background-color: black !important; border-color: black; color: #fff;" onclick="window.location.href='../../../back-end/download/inventoryReport.php'">Download</button>
            </div>
            
            <!-- Category Report -->
            <div style="padding: var(--spacing-lg); background: var(--secondary-bg); border: 1px solid var(--border-color); border-radius: var(--radius-lg); transition: all var(--transition-base);" onmouseover="this.style.borderColor='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-color)'">
                <div style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: var(--spacing-md);">
                    <div style="width: 48px; height: 48px; background: rgba(59, 130, 246, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: #3b82f6;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                </div>
                <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: var(--spacing-xs);">Category Report</h4>
                <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: var(--spacing-md);">Generate report for specific product category</p>
                <select id="categorySelect" class="form-input" style="width: 100%; margin-bottom: var(--spacing-sm); padding: 0.5rem;">
                    <option value="">Select a category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars(ucfirst($cat['name'])); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn" style="width: 100%; background-color: black !important; border-color: black; color: #fff;" onclick="downloadCategoryReport()">Download</button>
            </div>
        </div>
    </div>
    
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--spacing-lg);">
        <h3 style="font-size: 1.125rem; font-weight: 600;">Recent Reports</h3>
        
        <!-- Year Filter -->
        <div style="display: flex; gap: var(--spacing-sm); align-items: center;">
            <label style="font-size: 0.875rem; color: var(--text-muted);">Filter by Year:</label>
            <select id="yearFilter" class="form-input" style="width: 120px; padding: 0.5rem;" onchange="filterByYear(this.value)">
                <?php foreach ($availableYears as $year): ?>
                    <option value="<?php echo $year; ?>" <?php echo $year == $selectedYear ? 'selected' : ''; ?>>
                        <?php echo $year; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    
    <!-- Scrollable Container -->
    <div style="max-height: 600px; overflow-y: auto; padding-right: 8px;">
        <div style="display: flex; flex-direction: column; gap: var(--spacing-md);">
            <?php if ($recentReportsResult->num_rows > 0): ?>
                <?php while ($report = $recentReportsResult->fetch_assoc()): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: var(--spacing-md); background: var(--secondary-bg); border-radius: var(--radius-md); transition: all var(--transition-base);" onmouseover="this.style.background='var(--hover-bg)'" onmouseout="this.style.background='var(--secondary-bg)'">
                        <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                            <div style="width: 40px; height: 40px; background: rgba(99, 102, 241, 0.1); border-radius: var(--radius-md); display: flex; align-items: center; justify-content: center; color: var(--accent-primary);">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div style="font-weight: 600; margin-bottom: 0.25rem;"><?php echo htmlspecialchars($report['report_name']); ?></div>
                                <div style="font-size: 0.875rem; color: var(--text-muted);"><?php echo date('M d, Y h:i A', strtotime($report['generated_at'])); ?> • <?php echo $report['file_size']; ?></div>
                            </div>
                        </div>
                        <button class="btn btn-icon btn-secondary" title="Download" onclick="downloadReport(<?php echo $report['id']; ?>, '<?php echo htmlspecialchars($report['report_name'], ENT_QUOTES); ?>')">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="7 10 12 15 17 10"></polyline>
                                <line x1="12" y1="15" x2="12" y2="3"></line>
                            </svg>
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="text-align: center; padding: var(--spacing-xl); color: var(--text-muted);">
                    <p>No reports found for <?php echo $selectedYear; ?>. Generate your first report above!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Show count -->
    <div style="margin-top: var(--spacing-md); padding-top: var(--spacing-md); border-top: 1px solid var(--border-color); text-align: center; color: var(--text-muted); font-size: 0.875rem;">
        Showing <?php echo $recentReportsResult->num_rows; ?> report<?php echo $recentReportsResult->num_rows != 1 ? 's' : ''; ?> for <?php echo $selectedYear; ?>
    </div>
</div>
</div>

<style>
/* Toast Notification */
.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #333;
    color: white;
    padding: 16px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 9999;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
    max-width: 400px;
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
}

.toast.success {
    background: #10b981;
}

.toast.error {
    background: #ef4444;
}

.toast.info {
    background: #3b82f6;
}

.toast.warning {
    background: #f59e0b;
}

/* Custom scrollbar for Recent Reports */
.card > div[style*="max-height"] {
    scrollbar-width: thin;
    scrollbar-color: #e0e0e0 transparent;
}

.card > div[style*="max-height"]::-webkit-scrollbar {
    width: 8px;
}

.card > div[style*="max-height"]::-webkit-scrollbar-track {
    background: transparent;
}

.card > div[style*="max-height"]::-webkit-scrollbar-thumb {
    background-color: #e0e0e0;
    border-radius: 4px;
}

.card > div[style*="max-height"]::-webkit-scrollbar-thumb:hover {
    background-color: #bdbdbd;
}
</style>

<script>
function showToast(type, message) {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            ${type === 'success' ? '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline>' : 
              type === 'error' ? '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>' :
              type === 'warning' ? '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>' :
              '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>'}
        </svg>
        <span>${message}</span>
    `;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function downloadReport(reportId, reportName) {
    showToast('info', 'Downloading ' + reportName + '...');
    window.location.href = '../../../back-end/download/downloadReport.php?id=' + reportId;
}

function filterByYear(year) {
    window.location.href = '?page=reports&year=' + year;
}

function downloadCategoryReport() {
    const categorySelect = document.getElementById('categorySelect');
    const categoryId = categorySelect.value;
    
    if (!categoryId) {
        showToast('warning', 'Please select a category first');
        return;
    }
    
    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
    const categoryName = selectedOption.text;
    
    showToast('info', 'Downloading ' + categoryName + ' report...');
    window.location.href = '../../../back-end/download/categoryReport.php?category_id=' + categoryId;
}
</script>
