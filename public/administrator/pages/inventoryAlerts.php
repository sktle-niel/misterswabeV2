<?php
include __DIR__ . '/../components/editProductModal.php';
include __DIR__ . '/../components/deleteProductModal.php';
include __DIR__ . '/../components/addQuantityModal.php';
include __DIR__ . '/../components/skuModal.php';
include __DIR__ . '/../components/summaryModal.php';
include '../../back-end/create/addProduct.php';
include '../../back-end/read/fetchProduct.php';
include '../../back-end/update/editProduct.php';
include '../../back-end/delete/removeProduct.php';
include '../../auth/sessionCheck.php';

// Fetch products from database
$products = fetchProducts();
?>

<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Inventory Alerts</h2>
            <p class="page-subtitle">Monitor products that need restocking</p>
        </div>
        <button onclick="exportInventoryAlertsPDF()" style="display: flex; align-items: center; gap: 8px; padding: 10px 20px; background: black; color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-size: 14px; font-weight: 500;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
            Export PDF
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="stats-grid" style="margin-bottom: var(--spacing-2xl);">
        <!-- Total Alerts Card -->
        <div class="stat-card" style="--stat-color: #000000;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Total Alerts</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-value" id="totalAlerts">0</div>
            <div class="stat-change">Products need attention</div>
        </div>

        <!-- Out of Stock Card -->
        <div class="stat-card" style="--stat-color: #ef4444;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Out of Stock</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-value" id="outOfStockCount">0</div>
            <div class="stat-change" style="color: #ef4444;">Items unavailable</div>
        </div>

        <!-- Low Stock Card -->
        <div class="stat-card" style="--stat-color: #f97316;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Low Stock</div>
                </div>
                <div class="stat-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>
            <div class="stat-value" id="lowStockCount">0</div>
            <div class="stat-change" style="color: #f97316;">Items running low</div>
        </div>
    </div>

    <!-- Out of Stock Section -->
    <div class="card" style="margin-bottom: var(--spacing-2xl);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-md); border-bottom: 2px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                <div style="width: 12px; height: 12px; background: #ef4444; border-radius: 50%;"></div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Out of Stock</h3>
                <span id="outOfStockBadge" class="badge badge-danger">0 items</span>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Current Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="outOfStockTableBody">
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Section -->
    <div class="card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: var(--spacing-lg); padding-bottom: var(--spacing-md); border-bottom: 2px solid var(--border-color);">
            <div style="display: flex; align-items: center; gap: var(--spacing-md);">
                <div style="width: 12px; height: 12px; background: #f97316; border-radius: 50%;"></div>
                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Low Stock</h3>
                <span id="lowStockBadge" class="badge badge-warning">0 items</span>
            </div>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Current Quantity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="lowStockTableBody">
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">Loading...</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../../../src/css/modal.css">
<link rel="stylesheet" href="../../../src/css/successMessage.css">

<script src="../../../src/js/inventory.js?v=<?php echo time(); ?>"></script>

<script>
let products = <?php echo json_encode($products); ?>;

function loadInventoryAlerts() {
    fetch('../../back-end/read/fetchInventoryAlerts.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update summary cards
                document.getElementById('totalAlerts').textContent = data.totals.total_alerts;
                document.getElementById('outOfStockCount').textContent = data.totals.out_of_stock;
                document.getElementById('lowStockCount').textContent = data.totals.low_stock;

                // Update badges
                document.getElementById('outOfStockBadge').textContent = data.totals.out_of_stock + ' items';
                document.getElementById('lowStockBadge').textContent = data.totals.low_stock + ' items';

                // Render Out of Stock table
                renderOutOfStockTable(data.out_of_stock);
                
                // Render Low Stock table
                renderLowStockTable(data.low_stock);
            }
        })
        .catch(error => {
            console.error('Error loading inventory alerts:', error);
            document.getElementById('outOfStockTableBody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 40px; color: #ef4444;">Error loading data</td></tr>';
            document.getElementById('lowStockTableBody').innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 40px; color: #ef4444;">Error loading data</td></tr>';
        });
}

function renderOutOfStockTable(products) {
    const tbody = document.getElementById('outOfStockTableBody');
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No out of stock products</td></tr>';
        return;
    }
    
    let html = '';
    products.forEach(product => {
        html += `
            <tr>
                <td style="font-weight: 600;">${product.name}</td>
                <td><code style="background: var(--secondary-bg); padding: 4px 8px; border-radius: 4px; font-size: 12px;">${product.sku}</code></td>
                <td style="font-weight: 700; color: #ef4444;">${product.current_quantity}</td>
                <td>
                    <button onclick="openAddQuantityModal('${product.sku}')" style="padding: 8px 12px; background: black; color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-size: 13px; font-weight: 500;">
                        Add Stock
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

function renderLowStockTable(products) {
    const tbody = document.getElementById('lowStockTableBody');
    
    if (products.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--text-muted);">No low stock products</td></tr>';
        return;
    }
    
    let html = '';
    products.forEach(product => {
        html += `
            <tr>
                <td style="font-weight: 600;">${product.name}</td>
                <td><code style="background: var(--secondary-bg); padding: 4px 8px; border-radius: 4px; font-size: 12px;">${product.sku}</code></td>
                <td style="font-weight: 700; color: #f97316;">${product.current_quantity}</td>
                <td>
                    <button onclick="openAddQuantityModal('${product.sku}')" style="padding: 8px 12px; background: black; color: white; border: none; border-radius: var(--radius-md); cursor: pointer; font-size: 13px; font-weight: 500;">
                        Add Stock
                    </button>
                </td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
}

// Load data on page load
document.addEventListener('DOMContentLoaded', loadInventoryAlerts);

function exportInventoryAlertsPDF() {
    window.open('../../back-end/download/inventoryAlertsReport.php', '_blank');
}
</script>
