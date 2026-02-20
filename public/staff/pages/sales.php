<?php
include 'components/skuScanner.php';
include '../../auth/sessionCheck.php';
?>
<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Sales</h2>
            <p class="page-subtitle">View all sales transactions</p>
        </div>
        <div>
            <a href="?page=addSales" class="btn btn-success">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Add New Sale
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Sales Transactions</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Products Information</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody">
                        <!-- Sales data will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    'use strict';

    let currentPage = 1;

    // Load sales data
    async function loadSales(page = 1) {
        try {
            const response = await fetch(`../../back-end/read/fetchSales.php?page=${page}`);
            const data = await response.json();

            if (data.error) {
                console.error('Error loading sales:', data.error);
                return;
            }

            currentPage = page;
            displaySales(data.sales);
            displayPagination(data.pagination);
        } catch (error) {
            console.error('Error fetching sales:', error);
        }
    }

    // Display sales in table
    function displaySales(sales) {
        const tbody = document.getElementById('salesTableBody');
        tbody.innerHTML = '';

        if (sales.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="text-center">No sales found</td></tr>';
            return;
        }

        // Group sales by sale_id
        const groupedSales = {};
        sales.forEach(sale => {
            if (!groupedSales[sale.sale_id]) {
                groupedSales[sale.sale_id] = {
                    sale_id: sale.sale_id,
                    total_amount: sale.total_amount,
                    payment_method: sale.payment_method,
                    created_at: sale.created_at,
                    products: []
                };
            }
            groupedSales[sale.sale_id].products.push({
                name: sale.product_name,
                quantity: sale.quantity,
                size: sale.size,
                price: sale.price
            });
        });

        // Display each sale
        Object.values(groupedSales).forEach(sale => {
            const productsList = sale.products.map(product =>
                `${product.name} (Qty: ${product.quantity}, Size: ${product.size})`
            ).join('<br>');

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${sale.sale_id}</td>
                <td>${productsList}</td>
                <td style="color: black;">₱${parseFloat(sale.total_amount).toFixed(2)}</td>
                <td>${sale.payment_method}</td>
                <td>${new Date(sale.created_at).toLocaleDateString()}</td>
            `;
            tbody.appendChild(row);
        });
    }

    // Display pagination controls
    function displayPagination(pagination) {
        const container = document.getElementById('paginationContainer');
        container.innerHTML = '';

        if (pagination.total_pages <= 1) {
            return;
        }

        const paginationDiv = document.createElement('div');
        paginationDiv.className = 'pagination';
        const prevDisabled = pagination.current_page <= 1 ? 'disabled' : '';
        const nextDisabled = pagination.current_page >= pagination.total_pages ? 'disabled' : '';
        paginationDiv.innerHTML = `
            <button class="btn btn-sm btn-secondary" ${prevDisabled} onclick="changePage(${pagination.current_page - 1})">
                Previous
            </button>
            <span class="pagination-info">
                Page ${pagination.current_page} of ${pagination.total_pages} (${pagination.total_sales} total sales)
            </span>
            <button class="btn btn-sm btn-secondary" ${nextDisabled} onclick="changePage(${pagination.current_page + 1})">
                Next
            </button>
        `;

        container.appendChild(paginationDiv);
    }

    // Change page
    window.changePage = function(page) {
        loadSales(page);
    };

    // View sale details
    window.viewSaleDetails = function(saleId) {
        // This could open a modal or redirect to a details page
        alert('View details for sale: ' + saleId);
    };

    // Load sales on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadSales();
    });
})();
</script>

<style>
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 1rem;
}

.table th,
.table td {
    padding: 0.75rem;
    vertical-align: top;
    border-top: 1px solid #dee2e6;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6;
    background-color: #f8f9fa;
    font-weight: 600;
}

.table tbody tr:hover {
    background-color: #f8f9fa;
}

.table-responsive {
    overflow-x: auto;
}

.text-center {
    text-align: center;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
    border-radius: 0.2rem;
}

.btn-primary {
    color: #fff;
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    color: #fff;
    background-color: #0056b3;
    border-color: #004085;
}
</style>
