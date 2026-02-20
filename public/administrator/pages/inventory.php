<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Inventory Management</h2>
            <p class="page-subtitle">Manage your product inventory and stock levels</p>
        </div>
    </div>

<?php
include __DIR__ . '/../components/editProductModal.php';
include __DIR__ . '/../components/deleteProductModal.php';
include __DIR__ . '/../components/addQuantityModal.php';
include __DIR__ . '/../components/skuModal.php';
include '../../back-end/create/addProduct.php';
include '../../back-end/read/fetchProduct.php';
include '../../back-end/update/editProduct.php';
include '../../back-end/delete/removeProduct.php';
include '../../auth/sessionCheck.php';


// Fetch products from database
$products = fetchProducts();
$recentProduct = !empty($products) ? $products[0] : null;
?>


<div id="successMessage" class="success-message" style="display: none;">
    <div class="success-content">
        <span class="success-icon">✓</span>
        <span class="success-text">Successfully Deleted!</span>
    </div>
</div>

<!-- Add Product Modal - Improved Layout -->
<div class="modal-overlay" id="addProductModalOverlay" onclick="closeAddProductModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 800px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="closeAddProductModal()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #111827;">Add New Product</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                Fill in the product details below to add it to your inventory
            </p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 40px;">
            <form id="addProductForm" enctype="multipart/form-data" onsubmit="event.preventDefault(); addProduct();">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <!-- Product Name -->
                    <div style="grid-column: span 2;">
                        <label for="productName" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Product Name <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="productName" name="productName" required 
                            placeholder="Enter product name" 
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s;" 
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';" 
                            onblur="this.style.borderColor='#e5e7eb';">
                    </div>

                    <!-- Category -->
                    <div style="grid-column: span 2;">
                        <label for="productCategory" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Category <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="productCategory" name="productCategory" required
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s; background: white; cursor: pointer;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                            onblur="this.style.borderColor='#e5e7eb';">
                            <option value="">Select Category</option>
                            <!-- Categories will be loaded dynamically -->
                        </select>
                    </div>

                    <!-- Colors -->
                    <div style="grid-column: span 2;">
                        <label for="productColors" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Available Colors
                        </label>
                        <input type="text" id="productColors" name="productColors" placeholder="Enter colors separated by commas (e.g., Red, Blue, Green)"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                            onblur="this.style.borderColor='#e5e7eb';">
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #6b7280;">Enter colors separated by commas</p>
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="productPrice" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Price <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="productPrice" name="productPrice" required
                            placeholder="₱0.00"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                            onblur="this.style.borderColor='#e5e7eb';">
                    </div>

                    <!-- Size -->
                    <div style="grid-column: span 2;">
                        <div style="background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; padding: 10px 12px; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                <line x1="12" y1="9" x2="12" y2="13"></line>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                            <span style="font-size: 13px; color: #92400e; font-weight: 500;">Important: If the product has no required size, put (Not Required)</span>
                        </div>
                        <label for="productSizes" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Available Sizes
                        </label>

                        <input type="text" id="productSizes" name="productSizes" placeholder="Enter sizes separated by commas (e.g., S,M,L,XL)"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                            onblur="this.style.borderColor='#e5e7eb';">
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #6b7280;">Enter sizes separated by commas</p>
                    </div>

                    <!-- Product Images -->
                    <div style="grid-column: span 2;">
                        <label for="productImages" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Product Images
                        </label>
                        <input type="file" id="productImages" name="productImages[]" multiple accept="image/*"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                            onblur="this.style.borderColor='#e5e7eb';">
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #6b7280;">Select multiple images (max 4MB each). Leave empty to use default product image</p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                    <button type="button" onclick="closeAddProductModal()" 
                        style="padding: 12px 28px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s;" 
                        onmouseover="this.style.background='#e5e7eb'" 
                        onmouseout="this.style.background='#f3f4f6'">
                        Cancel
                    </button>
                    <button type="submit" 
                        style="padding: 12px 32px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);" 
                        onmouseover="this.style.background='#059669'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'" 
                        onmouseout="this.style.background='#10b981'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'">
                        <span style="display: inline-flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Product
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="../../../src/css/modal.css">
<link rel="stylesheet" href="../../../src/css/successMessage.css">

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="../../../src/js/inventory.js?v=<?php echo time(); ?>"></script>
<script>
let products = <?php echo json_encode($products); ?>;

// Function to load categories into the select dropdown
async function loadCategories() {
    try {
        const response = await fetch('../../../back-end/read/fetchCategory.php');
        const result = await response.json();
        if (result.success) {
            const categorySelect = document.getElementById('productCategory');
            // Clear existing options except the first one
            categorySelect.innerHTML = '<option value="">Select Category</option>';

            // Add categories from database
            result.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name; // Use category name as value
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        } else {
            console.error('Failed to load categories:', result.message);
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

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
    loadCategories();
    loadFilterCategories();
});
</script>
    
    <div>
        <div class="card">
            <div class="products-header" style="margin-bottom: var(--spacing-lg); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1.125rem; font-weight: 600;">Products</h3>
                <div style="display: flex; gap: var(--spacing-md); align-items: center;">
                    <button style="width: 400px; height: 41px; background: black; color: white; padding: 0.625rem 1.5rem; border: none; border-radius: var(--radius-md); font-family: 'Outfit', sans-serif; font-weight: 500; font-size: 0.875rem; cursor: pointer; transition: all var(--transition-base); display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; position: relative; overflow: hidden; justify-content: center;" onclick="openProductModal('add')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Product
                    </button>

                </div>
            </div>

            <div class="products-filters" style="margin-bottom: var(--spacing-lg); display: flex; gap: var(--spacing-md);">
                <input type="text" id="search-filter" placeholder="Search by name or SKU..." class="filter-select" style="padding: 0.5rem; border: 1px solid var(--border-color); border-radius: var(--radius-md); font-size: 0.875rem;">

                <select class="filter-select" id="category-filter" >
                    <option value="">All Categories</option>
                </select>

                <select class="filter-select" id="status-filter">
                    <option value="">All Status</option>
                    <option value="in-stock">In Stock</option>
                    <option value="low-stock">Low Stock</option>
                    <option value="out-of-stock">Out of Stock</option>
                </select>

                <button class="btn btn-secondary" style="width: 400px; height: 43px;" onclick="clearFilters()">Clear Filters</button>
            </div>

            <div class="table-container" style="overflow: hidden;">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Product Name</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Size</th>
                            <th>Size Quantity</th>
                            <th>Color</th>
                            <th>Status</th>

                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody id="products-tbody">
                    </tbody>
                </table>
            </div>
            <div id="pagination-container"></div>
        </div>
    </div>
</div>
