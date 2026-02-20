<?php
include '../../auth/sessionCheck.php';

include '../../back-end/read/fetchCustomers.php';
$customers = fetchCustomers();
?>

<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Accounts</h2>
            <p class="page-subtitle">Swabe Apprel & Collection Account List</p>
        </div>
    </div>
    
    <!-- Customer List -->
    <div class="card">
        <div style="margin-bottom: var(--spacing-lg);">
            <h3 style="font-size: 1.125rem; font-weight: 600; margin-bottom: var(--spacing-xs);">Account List</h3>
        </div>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>User Type</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td><?php echo htmlspecialchars($customer['user_type']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>