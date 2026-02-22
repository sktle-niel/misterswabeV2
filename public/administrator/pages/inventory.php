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
include __DIR__ . '/../status/invalidStatus.php';
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

<div id="invalidMessage" class="invalid-message" style="display: none;">
    <div class="invalid-content">
        <span class="invalid-icon">✗</span>
        <span class="invalid-text">Invalid!</span>
    </div>
</div>

<!-- Add Product Modal - Improved Layout -->
<div class="modal-overlay" id="addProductModalOverlay" onclick="closeAddProductModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 600px; width: 95%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 95vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
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

                    <!-- Size Configuration Section -->
                    <div style="grid-column: span 2;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                            <label style="display: block; font-weight: 600; font-size: 14px; color: #374151;">
                                Product Sizes
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #6b7280;">
                                <input type="checkbox" id="noSizeColorRequired" onchange="toggleSizeColorRequired()" style="width: 16px; height: 16px; cursor: pointer;">
                                <span>No sizes (simple product)</span>
                            </label>
                        </div>
                        
                        <!-- Size Configuration Container -->
                        <div id="sizeConfigSection">
                            <!-- Size Type Selection -->
                            <div id="sizeTypeSelection" style="margin-bottom: 16px;">
                                <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px;">Select size type:</p>
                                <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                                    <button type="button" onclick="selectSizeType('alpha')" id="btnAlphaSize"
                                        style="flex: 1; padding: 16px; background: #f3f4f6; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="4,7 4,4 20,4 20,7"></polyline>
                                            <line x1="9" y1="20" x2="15" y2="20"></line>
                                            <line x1="12" y1="4" x2="12" y2="20"></line>
                                        </svg>
                                        <span>Alpha Based</span>
                                        <span style="font-size: 11px; color: #6b7280; font-weight: normal;">(XS, S, M, L, XL, XXL)</span>
                                    </button>
                                    <button type="button" onclick="selectSizeType('numeric')" id="btnNumericSize"
                                        style="flex: 1; padding: 16px; background: #f3f4f6; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="4" y1="6" x2="20" y2="6"></line>
                                            <line x1="4" y1="12" x2="20" y2="12"></line>
                                            <line x1="4" y1="18" x2="20" y2="18"></line>
                                        </svg>
                                        <span>Number Based</span>
                                        <span style="font-size: 11px; color: #6b7280; font-weight: normal;">(39, 40, 41, 42... 47)</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Size Selection (Hidden by default) -->
                            <div id="sizeSelectionArea" style="display: none;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <p style="font-size: 13px; color: #6b7280; margin: 0;">Select available sizes:</p>
                                    <button type="button" onclick="resetSizeType()" 
                                        style="padding: 6px 12px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; font-size: 12px; color: #6b7280;">
                                        Change Size Type
                                    </button>
                                </div>
                                
                                <!-- Alpha Sizes -->
                                <div id="alphaSizesSection" style="display: none;">
                                    <p style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 8px;">Clothing Sizes:</p>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;" id="alphaSizesContainer">
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="XS" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>XS</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="S" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>S</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="M" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>M</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="L" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>L</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="XL" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>XL</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="XXL" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>XXL</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Numeric Sizes -->
                                <div id="numericSizesSection" style="display: none;">
                                    <p style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 8px;">Shoe Sizes (EU):</p>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;" id="numericSizesContainer">
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="39" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>39</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="40" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>40</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="41" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>41</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="42" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>42</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="43" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>43</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="44" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>44</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="45" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>45</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="46" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>46</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="47" class="size-checkbox" onchange="toggleSize(this)" style="cursor: pointer;">
                                            <span>47</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Selected Sizes Display - Same as Edit Modal -->
                                <div id="selectedSizesDisplay" style="margin-top: 16px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                                    <p style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">Selected sizes:</p>
                                    <div id="selectedSizesList" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <!-- Selected sizes will appear here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Simple Product Quantity (when no sizes) -->
                        <div id="simpleQuantitySection" style="display: none; margin-top: 16px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                                Product Quantity
                            </label>
                            <input type="number" id="simpleQuantity" min="0" value="0" 
                                style="width: 200px; padding: 10px 14px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 14px;"
                                onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                                onblur="this.style.borderColor='#e5e7eb';">
                        </div>
                        
                        <input type="hidden" id="productSizes" name="productSizes">
                        <input type="hidden" id="sizeColorConfig" name="sizeColorConfig">
                    </div>

                    <!-- Product Images -->
                    <div style="grid-column: span 2;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Product Images
                        </label>
                        <div id="addImageUploadContainer" style="border: 2px dashed #ddd; border-radius: 8px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; background: #fafafa;"
                             onclick="document.getElementById('productImages').click();"
                             ondragover="handleAddDragOver(event)"
                             ondragleave="handleAddDragLeave(event)"
                             ondrop="handleAddDrop(event)">
                            <div style="font-size: 48px; color: #ccc; margin-bottom: 10px;">+</div>
                            <div style="color: #666; font-size: 16px;">Click to add images or drag & drop</div>
                            <div style="color: #999; font-size: 12px; margin-top: 5px;">PNG, JPG only (max 4MB each)</div>
                        </div>
                        <input type="file" id="productImages" name="productImages[]" multiple accept="image/png,image/jpeg" style="display: none;" onchange="handleAddImageSelection(event)">
                        <div id="addImagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
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
            categorySelect.innerHTML = '<option value="">Select Category</option>';
            result.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
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
            categoryFilter.innerHTML = '<option value="">All Categories</option>';
            result.categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.name.toLowerCase();
                option.textContent = category.name;
                categoryFilter.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

// Size Configuration Variables
let sizeColorConfig = {}; // Structure: { size: { colors: { colorName: quantity }, colorOrder: [] } }
let sizeOrder = [];
let currentSizeType = null;

function selectSizeType(type) {
    currentSizeType = type;
    document.getElementById('sizeTypeSelection').style.display = 'none';
    document.getElementById('sizeSelectionArea').style.display = 'block';
    
    if (type === 'alpha') {
        document.getElementById('alphaSizesSection').style.display = 'block';
        document.getElementById('numericSizesSection').style.display = 'none';
    } else {
        document.getElementById('alphaSizesSection').style.display = 'none';
        document.getElementById('numericSizesSection').style.display = 'block';
    }
}

function resetSizeType() {
    currentSizeType = null;
    sizeOrder = [];
    document.getElementById('sizeTypeSelection').style.display = 'block';
    document.getElementById('sizeSelectionArea').style.display = 'none';
    document.querySelectorAll('.size-checkbox').forEach(cb => cb.checked = false);
    renderSelectedSizes();
    updateHiddenInputs();
}

// Toggle size when checkbox is clicked - simplified for Add Product Modal (no color adding)
function toggleSize(checkbox) {
    const size = checkbox.value;
    
    if (checkbox.checked) {
        // Add size if not already in array
        if (!sizeOrder.includes(size)) {
            sizeOrder.push(size);
            // Initialize color configuration for this size
            sizeColorConfig[size] = {
                colors: {},
                colorOrder: []
            };
        }
    } else {
        // Remove size from array
        sizeOrder = sizeOrder.filter(s => s !== size);
        // Remove color configuration
        delete sizeColorConfig[size];
    }
    
    renderSelectedSizes();
    updateHiddenInputs();
}

function removeSize(size) {
    sizeOrder = sizeOrder.filter(s => s !== size);
    // Remove color configuration
    delete sizeColorConfig[size];
    // Uncheck the corresponding checkbox
    document.querySelectorAll('.size-checkbox').forEach(cb => {
        if (cb.value === size) {
            cb.checked = false;
        }
    });
    renderSelectedSizes();
    updateHiddenInputs();
}

function addColorToSize(size) {
    const input = document.getElementById(`colorInput-${size}`);
    if (!input) {
        console.error('Color input not found for size:', size);
        return;
    }
    
    const color = input.value.trim();
    
    if (!color) {
        alert('Please enter a color');
        return;
    }
    
    if (sizeColorConfig[size].colors[color]) {
        alert('This color already exists for this size');
        return;
    }
    
    sizeColorConfig[size].colors[color] = 0;
    sizeColorConfig[size].colorOrder.push(color);
    input.value = '';
    renderSelectedSizes();
    updateHiddenInputs();
}

function removeColorFromSize(size, color) {
    if (sizeColorConfig[size] && sizeColorConfig[size].colors[color]) {
        delete sizeColorConfig[size].colors[color];
        sizeColorConfig[size].colorOrder = sizeColorConfig[size].colorOrder.filter(c => c !== color);
        renderSelectedSizes();
        updateHiddenInputs();
    }
}

function updateSizeColorQuantity(size, color, value) {
    if (sizeColorConfig[size] && sizeColorConfig[size].colors[color] !== undefined) {
        sizeColorConfig[size].colors[color] = parseInt(value) || 0;
        updateHiddenInputs();
    }
}

function resetColorsForSize(size) {
    if (sizeColorConfig[size]) {
        sizeColorConfig[size].colors = {};
        sizeColorConfig[size].colorOrder = [];
        renderSelectedSizes();
        updateHiddenInputs();
    }
}

function renderSelectedSizes() {
    const container = document.getElementById('selectedSizesList');
    if (!container) return;
    
    if (sizeOrder.length === 0) {
        container.innerHTML = '<span style="color: #9ca3af; font-size: 13px;">No sizes selected</span>';
        return;
    }
    
    let html = '';
    for (let i = 0; i < sizeOrder.length; i++) {
        const size = sizeOrder[i];
        const sizeEscaped = size.replace(/'/g, "\\'");
        html += `
            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; background: #dbeafe; color: #1e40af; border-radius: 16px; font-size: 13px; font-weight: 500;">
                ${size}
                <button type="button" onclick="removeSize('${sizeEscaped}')" 
                    style="background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; color: #1e40af;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </span>
        `;
    }
    
    container.innerHTML = html;
}

function toggleSizeColorRequired() {
    const checkbox = document.getElementById('noSizeColorRequired');
    const configSection = document.getElementById('sizeConfigSection');
    const simpleSection = document.getElementById('simpleQuantitySection');
    
    if (checkbox.checked) {
        configSection.style.display = 'none';
        simpleSection.style.display = 'block';
    } else {
        configSection.style.display = 'block';
        simpleSection.style.display = 'none';
    }
    updateHiddenInputs();
}

function updateHiddenInputs() {
    document.getElementById('productSizes').value = sizeOrder.join(',');
    // Update hidden input with full size-color configuration for backend
    const sizeColorConfigInput = document.getElementById('sizeColorConfig');
    if (sizeColorConfigInput) {
        sizeColorConfigInput.value = JSON.stringify(sizeColorConfig);
    }
}

function resetAddProductForm() {
    sizeOrder = [];
    sizeColorConfig = {};
    currentSizeType = null;
    document.getElementById('noSizeColorRequired').checked = false;
    document.getElementById('sizeConfigSection').style.display = 'block';
    document.getElementById('sizeTypeSelection').style.display = 'block';
    document.getElementById('sizeSelectionArea').style.display = 'none';
    document.getElementById('alphaSizesSection').style.display = 'none';
    document.getElementById('numericSizesSection').style.display = 'none';
    document.getElementById('simpleQuantitySection').style.display = 'none';
    document.getElementById('simpleQuantity').value = '0';
    document.querySelectorAll('.size-checkbox').forEach(cb => cb.checked = false);
    renderSelectedSizes();
    updateHiddenInputs();
}

const originalCloseAddProductModal = closeAddProductModal;
closeAddProductModal = function() {
    document.getElementById('addProductModalOverlay').style.display = 'none';
    document.getElementById('addProductForm').reset();
    resetAddProductForm();
};

document.addEventListener("DOMContentLoaded", function () {
    loadCategories();
    loadFilterCategories();
    renderSelectedSizes();
    updateHiddenInputs();
});

// Add Product Modal - Image Upload Functions
function handleAddDragOver(event) {
    event.preventDefault();
    event.stopPropagation();
    const container = document.getElementById("addImageUploadContainer");
    container.style.borderColor = "#3b82f6";
    container.style.backgroundColor = "#eff6ff";
}

function handleAddDragLeave(event) {
    event.preventDefault();
    event.stopPropagation();
    const container = document.getElementById("addImageUploadContainer");
    container.style.borderColor = "#ddd";
    container.style.backgroundColor = "#fafafa";
}

function handleAddDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    const container = document.getElementById("addImageUploadContainer");
    container.style.borderColor = "#ddd";
    container.style.backgroundColor = "#fafafa";

    const files = event.dataTransfer.files;
    const fileInput = document.getElementById("productImages");
    fileInput.files = files;
    handleAddImageSelection({ target: { files: files } });
}

function handleAddImageSelection(event) {
    const files = event.target.files;
    const previewContainer = document.getElementById("addImagePreview");
    previewContainer.innerHTML = "";

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type !== "image/png" && file.type !== "image/jpeg") {
            showInvalidMessage("Only PNG and JPG files are allowed.");
            event.target.value = "";
            return;
        }
        if (file.size > 4 * 1024 * 1024) {
            showInvalidMessage("File size exceeds 4MB limit.");
            event.target.value = "";
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            const imgContainer = document.createElement("div");
            imgContainer.style.position = "relative";
            imgContainer.style.display = "inline-block";

            const img = document.createElement("img");
            img.src = e.target.result;
            img.style.width = "80px";
            img.style.height = "80px";
            img.style.objectFit = "cover";
            img.style.borderRadius = "4px";
            img.style.border = "1px solid #ddd";

            const removeBtn = document.createElement("button");
            removeBtn.innerHTML = "×";
            removeBtn.style.position = "absolute";
            removeBtn.style.top = "-5px";
            removeBtn.style.right = "-5px";
            removeBtn.style.background = "red";
            removeBtn.style.color = "white";
            removeBtn.style.border = "none";
            removeBtn.style.borderRadius = "50%";
            removeBtn.style.width = "20px";
            removeBtn.style.height = "20px";
            removeBtn.style.cursor = "pointer";
            removeBtn.style.fontSize = "12px";
            removeBtn.onclick = function () {
                imgContainer.remove();
            };

            imgContainer.appendChild(img);
            imgContainer.appendChild(removeBtn);
            previewContainer.appendChild(imgContainer);
        };
        reader.readAsDataURL(file);
    }
}

// Show invalid message function - Fixed to use invalidMessage
function showInvalidMessage(message) {
    const invalidMessageDiv = document.getElementById("invalidMessage");
    if (invalidMessageDiv) {
        const invalidTextSpan = invalidMessageDiv.querySelector(".invalid-text");
        if (invalidTextSpan) {
            invalidTextSpan.textContent = message;
        }
        invalidMessageDiv.style.display = "block";
        
        setTimeout(() => {
            invalidMessageDiv.style.display = "none";
        }, 3000);
    }
}
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
