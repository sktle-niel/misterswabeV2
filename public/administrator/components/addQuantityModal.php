<?php include __DIR__ . '/removeColorModal.php'; ?>

<!-- Add Quantity Modal -->
<div class="modal-overlay" id="addQuantityModalOverlay" onclick="closeAddQuantityModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 600px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="closeAddQuantityModal()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #111827;">Add Quantity</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                Add stock quantity for each size and color
            </p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 40px; max-height: 500px; overflow-y: auto;">
            <form id="addQuantityForm" onsubmit="event.preventDefault(); addQuantity();">
                <input type="hidden" id="addQuantityBaseSku" name="addQuantityBaseSku">
                
                <!-- Loading State -->
                <div id="sizesLoadingState" style="text-align: center; padding: 20px; display: none;">
                    <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f4f6; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="margin-top: 12px; color: #6b7280;">Loading sizes...</p>
                </div>

                <!-- Sizes Container -->
                <div id="sizesContainer" style="display: none;">
                    <!-- Sizes with colors will be dynamically inserted here -->
                </div>

                <!-- Error State -->
                <div id="sizesErrorState" style="text-align: center; padding: 20px; display: none;">
                    <p style="color: #ef4444; font-size: 15px;">Failed to load product sizes. Please try again.</p>
                </div>

                <!-- No Sizes State -->
                <div id="sizesEmptyState" style="text-align: center; padding: 20px; display: none;">
                    <p style="color: #6b7280; font-size: 15px;">This product has no sizes defined.</p>
                </div>

                <!-- Modal Footer -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                    <button type="button" onclick="closeAddQuantityModal()"
                        style="padding: 12px 28px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s;"
                        onmouseover="this.style.background='#e5e7eb'"
                        onmouseout="this.style.background='#f3f4f6'">
                        Cancel
                    </button>
                    <button type="submit" id="addQuantitySubmitBtn"
                        style="padding: 12px 32px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);"
                        onmouseover="this.style.background='#059669'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'"
                        onmouseout="this.style.background='#10b981'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'">
                        <span style="display: inline-flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            Add Quantity
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
// Add Quantity Modal Functions
function closeAddQuantityModal() {
  document.getElementById("addQuantityModalOverlay").style.display = "none";
  document.getElementById("addQuantityForm").reset();
  document.getElementById("sizesContainer").style.display = "none";
  document.getElementById("sizesLoadingState").style.display = "none";
  document.getElementById("sizesErrorState").style.display = "none";
  document.getElementById("sizesEmptyState").style.display = "none";
  document.getElementById("sizesContainer").innerHTML = "";
}

function closeAddQuantityModalOnOverlay(event) {
  if (event.target === document.getElementById("addQuantityModalOverlay")) {
    closeAddQuantityModal();
  }
}

function openAddQuantityModal(sku) {
  const product = products.find((p) => p.sku === sku);
  if (!product) return;

  const baseSku = sku;
  
  document.getElementById("addQuantityBaseSku").value = baseSku;
  document.getElementById("addQuantityModalOverlay").style.display = "flex";
  
  // Show loading state
  document.getElementById("sizesLoadingState").style.display = "block";
  document.getElementById("sizesContainer").style.display = "none";
  document.getElementById("sizesErrorState").style.display = "none";
  document.getElementById("sizesEmptyState").style.display = "none";
  
  // Store baseSku globally for use in addColorForSize
  window.currentBaseSku = baseSku;
  fetchProductSizes(baseSku);
}

function fetchProductSizes(baseSku) {
  fetch("../../back-end/read/getSizes.php?sku=" + encodeURIComponent(baseSku))
    .then((response) => {
      console.log("Response status:", response.status);
      return response.text();
    })
    .then((text) => {
      console.log("Raw response:", text);
      
      try {
        const data = JSON.parse(text);
        console.log("Parsed JSON:", data);
        
        if (data.success && data.sizes && data.sizes.length > 0) {
          renderSizeColorInputs(data.sizes);
          document.getElementById("sizesLoadingState").style.display = "none";
          document.getElementById("sizesContainer").style.display = "block";
        } else if (data.success && (!data.sizes || data.sizes.length === 0)) {
          document.getElementById("sizesLoadingState").style.display = "none";
          document.getElementById("sizesEmptyState").style.display = "block";
        } else {
          console.error("No sizes found or unsuccessful:", data.message);
          document.getElementById("sizesLoadingState").style.display = "none";
          document.getElementById("sizesErrorState").style.display = "block";
        }
      } catch (e) {
        console.error("JSON parse error:", e);
        console.error("Received text was:", text);
        document.getElementById("sizesLoadingState").style.display = "none";
        document.getElementById("sizesErrorState").style.display = "block";
      }
    })
    .catch((error) => {
      console.error("Fetch error:", error);
      document.getElementById("sizesLoadingState").style.display = "none";
      document.getElementById("sizesErrorState").style.display = "block";
    });
}

function renderSizeColorInputs(sizes) {
  const container = document.getElementById("sizesContainer");
  container.innerHTML = "";
  
  sizes.forEach((sizeData, index) => {
    const sizeDiv = document.createElement("div");
    sizeDiv.style.marginBottom = "24px";
    sizeDiv.style.padding = "16px";
    sizeDiv.style.background = "#f8fafc";
    sizeDiv.style.borderRadius = "8px";
    sizeDiv.style.border = "1px solid #e2e8f0";
    
    // Get existing colors for this size from size_quantities
    let existingColors = {};
    if (sizeData.size_quantities && typeof sizeData.size_quantities === 'object') {
      existingColors = sizeData.size_quantities;
    }
    
    // Check if color_variants exist (new format with variant SKUs)
    let colorsHtml = '';
    const colorVariants = sizeData.color_variants || [];
    const isSimple = sizeData.isSimpleProduct || sizeData.size === '';
    const containerId = isSimple ? 'colors-simple' : 'colors-' + sizeData.size.replace(/\s/g, '-');
    
    if (colorVariants.length > 0) {
      // Use color variants with their own SKUs
      colorVariants.forEach(variant => {
        const qty = variant.quantity || 0;
        colorsHtml += `
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <span style="flex: 1; font-size: 14px; color: #374151; font-weight: 500;">${variant.color}</span>
            <input type="number" 
                   class="color-quantity-input" 
                   data-size="${isSimple ? '' : sizeData.size}" 
                   data-color="${variant.color}"
                   data-sku="${variant.sku}"
                   min="0" 
                   value="${qty}"
                   placeholder="Qty"
                   style="width: 80px; padding: 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; text-align: center;">
            <button type="button" 
                    onclick="removeColor('${isSimple ? '' : sizeData.size}', '${variant.color.replace(/'/g, "\\'")}', '${variant.sku}', ${isSimple})"
                    style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
                    title="Remove color">
              ✕
            </button>
          </div>
        `;
      });
    } else if (Object.keys(existingColors).length > 0) {
      // Fallback to old format without color variants
      Object.keys(existingColors).forEach(color => {
        const qty = existingColors[color] || 0;
        // Generate variant SKU for old format
        const colorCode = color.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '');
        const variantSku = isSimple 
          ? sizeData.sku + '-' + colorCode 
          : sizeData.sku + '-' + sizeData.size + '-' + colorCode;
        colorsHtml += `
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <span style="flex: 1; font-size: 14px; color: #374151; font-weight: 500;">${color}</span>
            <input type="number" 
                   class="color-quantity-input" 
                   data-size="${isSimple ? '' : sizeData.size}" 
                   data-color="${color}"
                   data-sku="${variantSku}"
                   min="0" 
                   value="${qty}"
                   placeholder="Qty"
                   style="width: 80px; padding: 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; text-align: center;">
            <button type="button" 
                    onclick="removeColor('${isSimple ? '' : sizeData.size}', '${color.replace(/'/g, "\\'")}', '${variantSku}', ${isSimple})"
                    style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
                    title="Remove color">
              ✕
            </button>
          </div>
        `;
      });
    }
    
    // Determine label based on whether it's a simple product
    const sizeLabel = sizeData.isSimpleProduct ? sizeData.productName : `Size: ${sizeData.size}`;
    
    let sizeContent = '';
    
    if (sizeData.isSimpleProduct) {
      // Simple product - show color inputs (same as products with sizes)
      const simpleSizeKey = '';
      
      sizeContent = `
        <!-- Existing Colors -->
        <div id="colors-simple" style="margin-bottom: 12px;">
          ${colorsHtml}
        </div>
        
        <!-- Add New Color -->
        <div style="display: flex; gap: 8px;">
          <input type="text" 
                 id="newColor-simple"
                 placeholder="Add new color" 
                 style="flex: 1; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
          <button type="button" 
                  onclick="addColorForSimpleProduct()"
                  style="padding: 8px 16px; background: black; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
            Add Color
          </button>
        </div>
      `;
    } else {
      // Product with sizes - show size/color inputs
      sizeContent = `
        <!-- Existing Colors -->
        <div id="colors-${sizeData.size.replace(/\s/g, '-')}" style="margin-bottom: 12px;">
          ${colorsHtml}
        </div>
        
        <!-- Add New Color -->
        <div style="display: flex; gap: 8px;">
          <input type="text" 
                 id="newColor-${sizeData.size.replace(/\s/g, '-')}"
                 placeholder="Add new color" 
                 style="flex: 1; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
          <button type="button" 
                  onclick="addColorForSize('${sizeData.size}', '${sizeData.sku}')"
                  style="padding: 8px 16px; background: black; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
            Add Color
          </button>
        </div>
      `;
    }
    
    sizeDiv.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <label style="font-weight: 600; font-size: 14px; color: #374151;">
          ${sizeLabel}
        </label>
        <span style="font-size: 12px; color: #6b7280;">
          ${sizeData.isSimpleProduct ? 'Total: <span id="total-simple">' + Object.values(existingColors).reduce((a, b) => a + b, 0) + '</span>' : 'Total: <span id="total-' + sizeData.size.replace(/\s/g, '-') + '">' + Object.values(existingColors).reduce((a, b) => a + b, 0) + '</span>'}
        </span>
      </div>
      ${sizeContent}
    `;
    
    container.appendChild(sizeDiv);
  });
}

function addColorForSimpleProduct() {
  const inputId = 'newColor-simple';
  const colorInput = document.getElementById(inputId);
  const colorName = colorInput.value.trim();
  
  if (!colorName) {
    showInvalidMessage('Please enter a color name');
    return;
  }
  
  // Check if color already exists
  const colorsContainer = document.getElementById('colors-simple');
  const existingInputs = colorsContainer.querySelectorAll('.color-quantity-input');
  for (let input of existingInputs) {
    if (input.dataset.color.toLowerCase() === colorName.toLowerCase()) {
      showInvalidMessage('This color already exists');
      return;
    }
  }
  
  // Use base SKU and create proper variant SKU: baseSku-COLORCODE (for simple products)
  const baseSku = window.currentBaseSku;
  const colorCode = colorName.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '');
  const variantSku = baseSku + '-' + colorCode;
  
  // Add new color input with empty size key for simple products
  const newColorDiv = document.createElement('div');
  newColorDiv.style.display = 'flex';
  newColorDiv.style.alignItems = 'center';
  newColorDiv.style.gap = '8px';
  newColorDiv.style.marginBottom = '8px';
  
  newColorDiv.innerHTML = `
    <span style="flex: 1; font-size: 14px; color: #374151; font-weight: 500;">${colorName}</span>
    <input type="number" 
           class="color-quantity-input" 
           data-size="" 
           data-color="${colorName}"
           data-sku="${variantSku}"
           data-new="true"
           min="0" 
           value="0"
           placeholder="Qty"
           style="width: 80px; padding: 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; text-align: center;">
    <button type="button" 
            onclick="removeColor('', '${colorName.replace(/'/g, "\\'")}', '${variantSku}', true)"
            style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
            title="Remove color">
      ✕
    </button>
  `;
  
  colorsContainer.appendChild(newColorDiv);
  colorInput.value = '';
  
  // Add event listener to update total
  const qtyInput = newColorDiv.querySelector('.color-quantity-input');
  qtyInput.addEventListener('input', function() {
    updateSimpleProductTotal();
  });
}

function updateSimpleProductTotal() {
  const container = document.getElementById('colors-simple');
  const inputs = container.querySelectorAll('.color-quantity-input');
  let total = 0;
  inputs.forEach(input => {
    total += parseInt(input.value) || 0;
  });
  
  const totalSpan = document.getElementById('total-simple');
  if (totalSpan) {
    totalSpan.textContent = total;
  }
}

function addColorForSize(size, sku) {
  const inputId = 'newColor-' + size.replace(/\s/g, '-');
  const colorInput = document.getElementById(inputId);
  const colorName = colorInput.value.trim();
  
  if (!colorName) {
    showInvalidMessage('Please enter a color name');
    return;
  }
  
  // Check if color already exists
  const colorsContainer = document.getElementById('colors-' + size.replace(/\s/g, '-'));
  const existingInputs = colorsContainer.querySelectorAll('.color-quantity-input');
  for (let input of existingInputs) {
    if (input.dataset.color.toLowerCase() === colorName.toLowerCase()) {
      showInvalidMessage('This color already exists for size ' + size);
      return;
    }
  }
  
  // Use the base SKU stored globally and create proper variant SKU: baseSku-SIZE-COLORCODE
  const baseSku = window.currentBaseSku || sku;
  const colorCode = colorName.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '');
  const variantSku = baseSku + '-' + size + '-' + colorCode;
  
  // Add new color input
  const newColorDiv = document.createElement('div');
  newColorDiv.style.display = 'flex';
  newColorDiv.style.alignItems = 'center';
  newColorDiv.style.gap = '8px';
  newColorDiv.style.marginBottom = '8px';
  
  newColorDiv.innerHTML = `
    <span style="flex: 1; font-size: 14px; color: #374151; font-weight: 500;">${colorName}</span>
    <input type="number" 
           class="color-quantity-input" 
           data-size="${size}" 
           data-color="${colorName}"
           data-sku="${variantSku}"
           data-new="true"
           min="0" 
           value="0"
           placeholder="Qty"
           style="width: 80px; padding: 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; text-align: center;">
    <button type="button" 
            onclick="removeColor('${size}', '${colorName.replace(/'/g, "\\'")}', '${variantSku}', false)"
            style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
            title="Remove color">
      ✕
    </button>
  `;
  
  colorsContainer.appendChild(newColorDiv);
  colorInput.value = '';
  
  // Add event listener to update total
  const qtyInput = newColorDiv.querySelector('.color-quantity-input');
  qtyInput.addEventListener('input', function() {
    updateSizeTotal(size);
  });
}

// Store pending color removal info
let pendingColorRemoval = null;

function removeColor(size, color, sku, isSimple) {
  // Store the pending removal info
  pendingColorRemoval = { size, color, sku, isSimple };
  
  // Show custom modal
  const modal = document.getElementById('removeColorModalOverlay');
  const colorNameSpan = document.getElementById('removeColorName');
  colorNameSpan.textContent = color;
  modal.style.display = 'flex';
}

function closeRemoveColorModal() {
  const modal = document.getElementById('removeColorModalOverlay');
  modal.style.display = 'none';
  pendingColorRemoval = null;
}

function confirmRemoveColor() {
  if (!pendingColorRemoval) return;
  
  const { size, color, sku, isSimple } = pendingColorRemoval;
  const baseSku = window.currentBaseSku;
  
  // Close modal first
  closeRemoveColorModal();
  
  fetch("../../back-end/update/removeColor.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "baseSku=" + encodeURIComponent(baseSku) + 
          "&size=" + encodeURIComponent(size) + 
          "&color=" + encodeURIComponent(color),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Show success message
        const successMessage = document.getElementById("successMessage");
        const successText = successMessage.querySelector(".success-text");
        successText.textContent = "Color Removed Successfully!";
        successMessage.style.display = "block";

        setTimeout(() => {
          successMessage.style.display = "none";
        }, 3000);
        
        // Reload the modal to show updated colors
        fetchProductSizes(baseSku);
      } else {
        showInvalidMessage("Error: " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showInvalidMessage("Error removing color");
    });
}

function updateSizeTotal(size) {
  const container = document.getElementById('colors-' + size.replace(/\s/g, '-'));
  const inputs = container.querySelectorAll('.color-quantity-input');
  let total = 0;
  inputs.forEach(input => {
    total += parseInt(input.value) || 0;
  });
  
  const totalSpan = document.getElementById('total-' + size.replace(/\s/g, '-'));
  if (totalSpan) {
    totalSpan.textContent = total;
  }
}

function addQuantity() {
  const form = document.getElementById("addQuantityForm");
  
  // Check if sizes are loaded
  const sizesContainer = document.getElementById("sizesContainer");
  const sizesEmptyState = document.getElementById("sizesEmptyState");
  const sizesErrorState = document.getElementById("sizesErrorState");
  
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  
  // Check if there are sizes defined
  if (sizesEmptyState.style.display === "block") {
    showInvalidMessage("This product has no sizes defined. Please add first.");
    return;
  }
  
  // Check if sizes to the product there was an error loading sizes
  if (sizesErrorState.style.display === "block") {
    showInvalidMessage("Failed to load product sizes. Please try again.");
    return;
  }
  
  // Check if sizes container is empty
  if (!sizesContainer.innerHTML || sizesContainer.innerHTML.trim() === "") {
    showInvalidMessage("No sizes available for this product.");
    return;
  }

  // Get both color-quantity inputs and simple-quantity inputs
  const quantityInputs = document.querySelectorAll(".color-quantity-input, .simple-quantity-input");
  const updates = [];
  
  quantityInputs.forEach((input) => {
    const amount = parseInt(input.value) || 0;
    if (amount >= 0) {
      updates.push({
        sku: input.dataset.sku,
        size: input.dataset.size,
        color: input.dataset.color,
        amount: amount,
        isNew: input.dataset.new === "true"
      });
    }
  });
  
  if (updates.length === 0) {
    showInvalidMessage("Please enter at least one quantity");
    return;
  }
  
  // Disable submit button
  const submitBtn = document.getElementById("addQuantitySubmitBtn");
  submitBtn.disabled = true;
  submitBtn.style.opacity = "0.6";
  submitBtn.style.cursor = "not-allowed";
  
  // Process all updates
  processQuantityUpdates(updates, 0, submitBtn);
}

function processQuantityUpdates(updates, index, submitBtn) {
  if (index >= updates.length) {
    // All updates completed
    submitBtn.disabled = false;
    submitBtn.style.opacity = "1";
    submitBtn.style.cursor = "pointer";
    
    // Update the local products array with new color and stock values
    updates.forEach(update => {
      const productIndex = products.findIndex(p => p.sku === update.sku || p.sku === window.currentBaseSku);
      if (productIndex !== -1) {
        const product = products[productIndex];
        
        // Update stock based on the amount added
        product.stock = (parseInt(product.stock) || 0) + update.amount;
        
        // Update status based on new stock
        if (product.stock === 0) {
          product.status = 'Out of Stock';
        } else if (product.stock <= 10) {
          product.status = 'Low Stock';
        } else {
          product.status = 'In Stock';
        }
        
        // Update color field if new color was added
        if (update.isNew || !product.color.includes(update.color)) {
          if (product.color && product.color !== "N/A") {
            product.color = product.color + ", " + update.color;
          } else {
            product.color = update.color;
          }
        }
        
        // Update size_quantities if it exists
        if (product.size_quantities) {
          try {
            const sizeQuantities = typeof product.size_quantities === 'string' 
              ? JSON.parse(product.size_quantities) 
              : product.size_quantities;
            
            if (!sizeQuantities[update.size]) {
              sizeQuantities[update.size] = 0;
            }
            sizeQuantities[update.size] = (sizeQuantities[update.size] || 0) + update.amount;
            product.size_quantities = sizeQuantities;
          } catch (e) {
            console.log("Error updating size_quantities:", e);
          }
        }
        
        products[productIndex] = product;
      }
    });
    
    // Save to localStorage and re-render the table
    localStorage.setItem("inventoryProducts", JSON.stringify(products));
    
    // Show success message
    const successMessage = document.getElementById("successMessage");
    const successText = successMessage.querySelector(".success-text");
    successText.textContent = "Quantities Added Successfully!";
    successMessage.style.display = "block";

    setTimeout(() => {
      successMessage.style.display = "none";
    }, 3000);

    closeAddQuantityModal();
    
    // Reload the page to get fresh data from the server
    window.location.reload();
    return;
  }
  
  const update = updates[index];
  
  fetch("../../back-end/update/addQuantity.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "sku=" + encodeURIComponent(update.sku) + 
          "&amount=" + encodeURIComponent(update.amount) + 
          "&size=" + encodeURIComponent(update.size) + 
          "&color=" + encodeURIComponent(update.color),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Process next update
        processQuantityUpdates(updates, index + 1, submitBtn);
      } else {
        submitBtn.disabled = false;
        submitBtn.style.opacity = "1";
        submitBtn.style.cursor = "pointer";
        showInvalidMessage("Error: " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      submitBtn.disabled = false;
      submitBtn.style.opacity = "1";
      submitBtn.style.cursor = "pointer";
      showInvalidMessage("Error adding quantity");
    });
}
</script>
