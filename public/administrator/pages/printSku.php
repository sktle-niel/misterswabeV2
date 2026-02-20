<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Print SKU Barcode</h2>
            <p class="page-subtitle">Generate SKU to Barcode</p>
        </div>
    </div>

<?php
include '../../back-end/read/fetchProduct.php';
include '../../auth/sessionCheck.php';

// Fetch products from database
$products = fetchProducts();
$recentProduct = !empty($products) ? $products[0] : null;
?>






<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
let products = <?php echo json_encode($products); ?>;

// Function to load categories into the filter dropdown
async function loadFilterCategories() {
    try {
        const response = await fetch('../../../back-end/read/fetchCategory.php');
        const result = await response.json();
        if (result.success) {
            const categoryFilter = document.getElementById('category-filter');
            // Clear existing options except the first one
            categoryFilter.innerHTML = '<option value="">All Categories</option>';

            // Add categories from database
            result.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name.toLowerCase(); // Use category name as value for filtering
                option.textContent = category.name;
                categoryFilter.appendChild(option);
            });
        } else {
            console.error('Failed to load categories:', result.message);
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Load categories when the page loads
document.addEventListener('DOMContentLoaded', function() {
    loadFilterCategories();
});
</script>
    
    <div style="display: grid; grid-template-columns: 1fr 320px; gap: var(--spacing-lg);">
        <!-- Main Content -->
        <div>
            <div class="card">
                <div class="products-header" style="margin-bottom: var(--spacing-lg); display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="font-size: 1.125rem; font-weight: 600;">Products</h3>
                </div>

                <div class="products-filters" style="margin-bottom: var(--spacing-lg); display: flex; gap: var(--spacing-md);">
                    <input type="text" id="search-filter" placeholder="Search by name or SKU..." class="filter-select" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.875rem;">

                    <select class="filter-select" id="category-filter" >
                        <option value="">All Categories</option>
                    </select>

                    <button class="btn btn-secondary" style="width: 400px; height: 43px;" onclick="clearFilters()">Clear Filters</button>
                </div>

                <div class="table-container" style="max-height: 530px; overflow-y: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Category</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="products-tbody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div style="display: flex; flex-direction: column; gap: var(--spacing-lg);">
            <!-- Print Barcode -->
            <div class="card">
                <h3 style="font-size: 1rem; font-weight: 600; margin-bottom: var(--spacing-md);">Print Barcode</h3>
                <div id="print-barcode-container" style="max-height: 400px; overflow-y: auto;">
                    <p style="color: var(--text-muted); font-size: 0.875rem;">No barcodes selected</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../../../src/js/printSku.js"></script>