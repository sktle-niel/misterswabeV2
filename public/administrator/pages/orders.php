to<?php
require_once '../../config/connection.php';
include '../../auth/sessionCheck.php';


// Query total orders
$totalOrdersQuery = "SELECT COUNT(*) as total FROM sales";
$totalOrdersResult = $conn->query($totalOrdersQuery);
$totalOrders = $totalOrdersResult->fetch_assoc()['total'];

// Query today's sales
$todaySalesQuery = "SELECT COUNT(*) as today FROM sales WHERE DATE(created_at) = CURDATE()";
$todaySalesResult = $conn->query($todaySalesQuery);
$todaySales = $todaySalesResult->fetch_assoc()['today'];

// Query this week's sales
$thisWeekSalesQuery = "SELECT COUNT(*) as this_week FROM sales WHERE YEARWEEK(created_at) = YEARWEEK(CURDATE())";
$thisWeekSalesResult = $conn->query($thisWeekSalesQuery);
$thisWeekSales = $thisWeekSalesResult->fetch_assoc()['this_week'];

// Query this month's sales
$thisMonthSalesQuery = "SELECT COUNT(*) as this_month FROM sales WHERE YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
$thisMonthSalesResult = $conn->query($thisMonthSalesQuery);
$thisMonthSales = $thisMonthSalesResult->fetch_assoc()['this_month'];

// Query recent orders (last 5)
$recentOrdersQuery = "SELECT s.id, s.total_amount, s.payment_method, s.created_at, GROUP_CONCAT(CONCAT(COALESCE(i.name, 'Unknown Product'), ' (Qty: ', si.quantity, ', Size: ', si.size, ')') SEPARATOR ', ') as products
                      FROM sales s
                      LEFT JOIN sale_items si ON s.id = si.sale_id
                      LEFT JOIN inventory i ON si.product_id = i.id
                      GROUP BY s.id
                      ORDER BY s.created_at DESC
                      LIMIT 5";
$recentOrdersResult = $conn->query($recentOrdersQuery);
$recentOrders = $recentOrdersResult->fetch_all(MYSQLI_ASSOC);
?>

<!-- Void Confirmation Modal -->
<div class="modal-overlay" id="voidModalOverlay" onclick="closeVoidModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 500px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="closeVoidModal()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #111827;">Void Sale</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                Are you sure you want to void this sale? This action cannot be undone.
            </p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 40px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 48px; color: #ef4444; margin-bottom: 15px;">⚠️</div>
            </div>

            <!-- Void Reason Input -->
            <div style="margin-bottom: 20px;">
                <label for="voidReason" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                    Void Reason <span style="color: #ef4444;">*</span>
                </label>
                <textarea id="voidReason" name="voidReason" required
                    placeholder="Enter the reason for voiding this sale..."
                    style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s; resize: vertical; min-height: 80px;"
                    onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                    onblur="this.style.borderColor='#e5e7eb';"></textarea>
            </div>

            <!-- Modal Footer -->
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="button" onclick="closeVoidModal()" style="padding: 12px 28px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">Cancel</button>
                <button type="button" onclick="confirmVoid()" style="padding: 12px 32px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);" onmouseover="this.style.background='#dc2626'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.background='#ef4444'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'">Void Sale</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentSaleId = null;

function openVoidModal(saleId) {
    currentSaleId = saleId;
    document.getElementById('voidReason').value = '';
    document.getElementById('voidModalOverlay').style.display = 'flex';
}

function closeVoidModal() {
    document.getElementById('voidModalOverlay').style.display = 'none';
    currentSaleId = null;
}

function closeVoidModalOnOverlay(event) {
    if (event.target === document.getElementById('voidModalOverlay')) {
        closeVoidModal();
    }
}

function confirmVoid() {
    const voidReason = document.getElementById('voidReason').value.trim();
    if (!voidReason) {
        alert('Please enter a void reason.');
        return;
    }

    if (!currentSaleId) {
        alert('No sale selected.');
        return;
    }

    const formData = new FormData();
    formData.append('sale_id', currentSaleId);
    formData.append('void_reason', voidReason);

    fetch('../../back-end/create/voidSale.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(text => {
        try {
            const data = JSON.parse(text);
            if (data.success) {
                alert('Sale voided successfully.');
                closeVoidModal();
                location.reload();
            } else {
                alert('Failed to void sale: ' + data.message);
            }
        } catch (e) {
            alert('Server error: ' + text);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while voiding the sale.');
    });
}
</script>

<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Orders</h2>
            <p class="page-subtitle">Manage and track customer orders</p>
        </div>
    </div>
    
    <!-- Order Stats -->
    <div class="stats-grid" style="margin-bottom: var(--spacing-2xl);">
        <div class="stat-card" style="--stat-color: #6366f1;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($totalOrders); ?></div>
        </div>
        
        <div class="stat-card" style="--stat-color: #10b981;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">Today's Sales</div>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($todaySales); ?></div>
        </div>
        
        <div class="stat-card" style="--stat-color: #3b82f6;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">This Week's Sales</div>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($thisWeekSales); ?></div>
        </div>
        
        <div class="stat-card" style="--stat-color: #10b981;">
            <div class="stat-header">
                <div>
                    <div class="stat-label">This Month's Sales</div>
                </div>
            </div>
            <div class="stat-value"><?php echo number_format($thisMonthSales); ?></div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="card">
        <div style="margin-bottom: var(--spacing-lg);">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: var(--spacing-xs);">Recent Orders</h3>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Product Information</th>
                        <th>Total Amount</th>
                        <th>Payment Method</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td style="font-weight: 600; color: black;"><?php echo $order['id']; ?></td>
                        <td><?php echo $order['products']; ?></td>
                        <td style="font-weight: 600; color: black;">₱<?php echo number_format($order['total_amount'], 0); ?></td>
                        <td><?php echo $order['payment_method']; ?></td>
                        <td><?php echo date('m/d/Y', strtotime($order['created_at'])); ?></td>
                        <td>
                            <div style="display: flex; gap: var(--spacing-sm);">
                                <button class="btn btn-icon btn-danger" title="Void" onclick="openVoidModal(<?php echo $order['id']; ?>)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M3 6h18"></path>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        <line x1="10" y1="11" x2="10" y2="17"></line>
                                        <line x1="14" y1="11" x2="14" y2="17"></line>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>