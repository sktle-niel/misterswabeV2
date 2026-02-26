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
                </div>

                <!-- Error State -->
                <div id="sizesErrorState" style="text-align: center; padding: 20px; display: none;">
                    <p style="color: #ef4444; font-size: 15px;">Failed to load product sizes. Please try again.</p>
                </div>

                <!-- No Sizes State - With Toggle -->
                <div id="sizesEmptyState" style="text-align: center; padding: 20px; display: none;">
                    <!-- Toggle with Labels - Enter Stock | Toggle | Enter Colors -->
                    <div style="display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 20px;">
                        <span id="toggleLabelStock" style="font-size: 14px; color: #6b7280; font-weight: 500; display: none;">Enter Stock</span>
                        <label style="position: relative; display: inline-block; width: 56px; height: 30px; margin: 0;">
                            <input type="checkbox" id="noSizeColorToggle" onchange="toggleNoSizeMode()" style="opacity: 0; width: 0; height: 0;">
                            <span id="toggleSlider" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #9ca3af; transition: 0.4s; border-radius: 34px;"></span>
                            <span id="toggleDot" style="position: absolute; content: ''; height: 24px; width: 24px; left: 3px; bottom: 3px; background-color: white; transition: 0.4s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2);"></span>
                        </label>
                        <span id="toggleLabelColors" style="font-size: 14px; color: #6b7280; font-weight: 500;">Enter Colors</span>
                    </div>
                    
                    <!-- Direct Stock Input (for no colors) -->
                    <div id="simpleStockInput" style="display: none;">
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 12px;">This product has no sizes or colors defined.</p>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Enter Stock
                        </label>
                        <input type="number" id="simpleStockQuantity" min="0" value="0" 
                            style="width: 200px; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; text-align: center;">
                    </div>
                    
                    <!-- Add Colors Section -->
                    <div id="addColorsSection">
                        <p style="color: #6b7280; font-size: 14px; margin-bottom: 12px;">Add colors for this product (no sizes):</p>
                        <div id="colorsNoSizeContainer" style="text-align: left; max-width: 300px; margin: 0 auto;">
                        </div>
                        <div style="display: flex; gap: 8px; justify-content: center; margin-top: 12px;">
                            <input type="text" id="newColorNoSize" placeholder="Add new color" 
                                style="flex: 1; max-width: 200px; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px;">
                            <button type="button" onclick="addColorNoSize()" 
                                style="padding: 8px 16px; background: black; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                                Add Color
                            </button>
                        </div>
                    </div>
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
window.currentStock = 0;

function closeAddQuantityModal() {
  document.getElementById("addQuantityModalOverlay").style.display = "none";
  document.getElementById("addQuantityForm").reset();
  document.getElementById("sizesContainer").style.display = "none";
  document.getElementById("sizesLoadingState").style.display = "none";
  document.getElementById("sizesErrorState").style.display = "none";
  document.getElementById("sizesEmptyState").style.display = "none";
  document.getElementById("sizesContainer").innerHTML = "";
  document.getElementById('simpleStockQuantity').value = '0';
  document.getElementById('noSizeColorToggle').checked = false;
  document.getElementById('simpleStockInput').style.display = 'none';
  document.getElementById('addColorsSection').style.display = 'block';
  document.getElementById('colorsNoSizeContainer').innerHTML = '';
  document.getElementById('newColorNoSize').value = '';
  document.getElementById('toggleLabelColors').style.display = 'inline';
  document.getElementById('toggleLabelStock').style.display = 'none';
  window.currentStock = 0;
  window.noSizeMode = false;
  
  const toggle = document.getElementById('noSizeColorToggle');
  const toggleSlider = document.getElementById('toggleSlider');
  const toggleDot = document.getElementById('toggleDot');
  
  toggle.disabled = false;
  toggle.style.opacity = '1';
  toggle.style.pointerEvents = 'auto';
  
  if (toggleSlider) {
    toggleSlider.style.backgroundColor = '#9ca3af';
  }
  if (toggleDot) {
    toggleDot.style.transform = 'translateX(0)';
  }
}

// Toggle between Enter Colors and Enter Stock
function toggleNoSizeMode() {
  const toggle = document.getElementById('noSizeColorToggle');
  const toggleSlider = document.getElementById('toggleSlider');
  const toggleDot = document.getElementById('toggleDot');
  const toggleLabelColors = document.getElementById('toggleLabelColors');
  const toggleLabelStock = document.getElementById('toggleLabelStock');
  const simpleStockInput = document.getElementById('simpleStockInput');
  const addColorsSection = document.getElementById('addColorsSection');
  
  if (toggle.disabled) {
    toggle.checked = !toggle.checked;
    return;
  }
  
  window.noSizeMode = toggle.checked;
  
  if (toggle.checked) {
    // Toggle ON (green) - Show Enter Stock on left
    if (toggleSlider) toggleSlider.style.backgroundColor = '#10b981';
    if (toggleDot) toggleDot.style.transform = 'translateX(26px)';
    if (toggleLabelStock) toggleLabelStock.style.display = 'inline';
    if (toggleLabelColors) toggleLabelColors.style.display = 'none';
    simpleStockInput.style.display = 'block';
    addColorsSection.style.display = 'none';
  } else {
    // Toggle OFF (gray) - Show Enter Colors on right
    if (toggleSlider) toggleSlider.style.backgroundColor = '#9ca3af';
    if (toggleDot) toggleDot.style.transform = 'translateX(0)';
    if (toggleLabelStock) toggleLabelStock.style.display = 'none';
    if (toggleLabelColors) toggleLabelColors.style.display = 'inline';
    simpleStockInput.style.display = 'none';
    addColorsSection.style.display = 'block';
  }
}

// Disable the toggle if product already has colors or stock entered
function disableNoSizeToggle(reason) {
  const toggle = document.getElementById('noSizeColorToggle');
  const toggleSlider = document.getElementById('toggleSlider');
  const toggleDot = document.getElementById('toggleDot');
  const toggleLabelColors = document.getElementById('toggleLabelColors');
  const toggleLabelStock = document.getElementById('toggleLabelStock');
  
  toggle.disabled = true;
  toggle.style.opacity = '1';
  toggle.style.pointerEvents = 'none';
  
  if (toggleSlider) {
    toggleSlider.style.backgroundColor = '#d1d5db';
    toggleSlider.style.cursor = 'not-allowed';
  }
  if (toggleDot) {
    toggleDot.style.cursor = 'not-allowed';
  }
  
  if (reason === 'hasColors') {
    toggleLabelColors.textContent = 'Colors already set';
    toggleLabelStock.textContent = 'Colors already set';
  } else if (reason === 'hasStock') {
    toggleLabelColors.textContent = 'Stock already entered';
    toggleLabelStock.textContent = 'Stock already entered';
  }
}

function addColorNoSize() {
  const input = document.getElementById('newColorNoSize');
  const colorName = input.value.trim();
  
  if (!colorName) {
    showInvalidMessage('Please enter a color name');
    return;
  }
  
  const container = document.getElementById('colorsNoSizeContainer');
  const existingColors = container.querySelectorAll('.color-no-size-input');
  for (let div of existingColors) {
    if (div.dataset.color.toLowerCase() === colorName.toLowerCase()) {
      showInvalidMessage('This color already exists');
      return;
    }
  }
  
  const colorDiv = document.createElement('div');
  colorDiv.style.display = 'flex';
  colorDiv.style.alignItems = 'center';
  colorDiv.style.gap = '8px';
  colorDiv.style.marginBottom = '8px';
  colorDiv.style.padding = '8px';
  colorDiv.style.background = '#f8fafc';
  colorDiv.style.borderRadius = '6px';
  
  colorDiv.innerHTML = `
    <span style="flex: 1; font-size: 14px; color: #374151; font-weight: 500;">${colorName}</span>
    <input type="number" 
           class="color-no-size-input" 
           data-color="${colorName}"
           min="0" 
           value="0"
           placeholder="Qty"
           style="width: 80px; padding: 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; text-align: center;">
    <button type="button" 
            onclick="this.parentElement.remove()"
            style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;">
      ✕
    </button>
  `;
  
  container.appendChild(colorDiv);
  input.value = '';
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
  window.currentStock = parseInt(product.stock) || 0;
  
  document.getElementById("addQuantityBaseSku").value = baseSku;
  document.getElementById("addQuantityModalOverlay").style.display = "flex";
  
  document.getElementById("sizesLoadingState").style.display = "block";
  document.getElementById("sizesContainer").style.display = "none";
  document.getElementById("sizesErrorState").style.display = "none";
  document.getElementById("sizesEmptyState").style.display = "none";
  
  // Reset toggle to default
  const toggle = document.getElementById('noSizeColorToggle');
  const toggleSlider = document.getElementById('toggleSlider');
  const toggleDot = document.getElementById('toggleDot');
  const toggleLabelColors = document.getElementById('toggleLabelColors');
  const toggleLabelStock = document.getElementById('toggleLabelStock');
  
  toggle.checked = false;
  toggle.disabled = false;
  if (toggleSlider) toggleSlider.style.backgroundColor = '#9ca3af';
  if (toggleDot) toggleDot.style.transform = 'translateX(0)';
  if (toggleLabelColors) {
    toggleLabelColors.style.display = 'inline';
    toggleLabelColors.textContent = 'Enter Colors';
  }
  if (toggleLabelStock) {
    toggleLabelStock.style.display = 'none';
    toggleLabelStock.textContent = 'Enter Stock';
  }
  
  window.currentBaseSku = baseSku;
  fetchProductSizes(baseSku);
}

function fetchProductSizes(baseSku) {
  fetch("../../back-end/read/getSizes.php?sku=" + encodeURIComponent(baseSku))
    .then((response) => { return response.text(); })
    .then((text) => {
      try {
        const data = JSON.parse(text);
        
        if (data.success && data.sizes && data.sizes.length > 0 && data.sizes[0].currentStock !== undefined) {
          window.currentStock = data.sizes[0].currentStock;
        }
        
        document.getElementById("simpleStockQuantity").placeholder = "Current stock: " + window.currentStock;
        
        if (data.success && data.sizes && data.sizes.length > 0) {
          const hasSizes = data.sizes.some(s => !s.isSimpleProduct && s.size && s.size.trim() !== '');
          
          if (hasSizes) {
            renderSizeColorInputs(data.sizes);
            document.getElementById("sizesLoadingState").style.display = "none";
            document.getElementById("sizesContainer").style.display = "block";
          } else {
            document.getElementById("sizesLoadingState").style.display = "none";
            document.getElementById("sizesEmptyState").style.display = "block";
            
            const simpleProduct = data.sizes[0];
            if (simpleProduct && simpleProduct.color_variants && simpleProduct.color_variants.length > 0) {
              renderExistingNoSizeColors(simpleProduct.color_variants);
              disableNoSizeToggle('hasColors');
              document.getElementById('simpleStockInput').style.display = 'none';
              document.getElementById('addColorsSection').style.display = 'block';
            } else {
              if (window.currentStock > 0) {
                disableNoSizeToggle('hasStock');
                document.getElementById('simpleStockInput').style.display = 'block';
                document.getElementById('addColorsSection').style.display = 'none';
                document.getElementById('noSizeColorToggle').checked = true;
                document.getElementById('simpleStockQuantity').value = window.currentStock;
                const toggleSlider = document.getElementById('toggleSlider');
                const toggleDot = document.getElementById('toggleDot');
                const toggleLabelColors = document.getElementById('toggleLabelColors');
                const toggleLabelStock = document.getElementById('toggleLabelStock');
                if (toggleSlider) toggleSlider.style.backgroundColor = '#10b981';
                if (toggleDot) toggleDot.style.transform = 'translateX(26px)';
                if (toggleLabelColors) toggleLabelColors.style.display = 'none';
                if (toggleLabelStock) toggleLabelStock.style.display = 'inline';
                window.noSizeMode = true;
              }
            }
          }
        } else if (data.success && (!data.sizes || data.sizes.length === 0)) {
          document.getElementById("sizesLoadingState").style.display = "none";
          document.getElementById("sizesEmptyState").style.display = "block";
        } else {
          document.getElementById("sizesLoadingState").style.display = "none";
          document.getElementById("sizesErrorState").style.display = "block";
        }
      } catch (e) {
        document.getElementById("sizesLoadingState").style.display = "none";
        document.getElementById("sizesErrorState").style.display = "block";
      }
    })
    .catch((error) => {
      document.getElementById("sizesLoadingState").style.display = "none";
      document.getElementById("sizesErrorState").style.display = "block";
    });
}

function renderExistingNoSizeColors(colorVariants) {
  const container = document.getElementById('colorsNoSizeContainer');
  if (!container) return;
  
  container.innerHTML = '';
  
  colorVariants.forEach(variant => {
    const colorDiv = document.createElement('div');
    colorDiv.style.display = 'flex';
    colorDiv.style.alignItems = 'center';
    colorDiv.style.gap = '8px';
    colorDiv.style.marginBottom = '8px';
    colorDiv.style.padding = '8px';
    colorDiv.style.background = '#f8fafc';
    colorDiv.style.borderRadius = '6px';
    
    const qty = variant.quantity || 0;
    
    colorDiv.innerHTML = `
      <span style="flex: 1; font-size: 14px; color: #374151; font-weight: 500;">${variant.color}</span>
      <input type="number" 
             class="color-no-size-input" 
             data-color="${variant.color}"
             data-sku="${variant.sku || ''}"
             min="0" 
             value="${qty}"
             placeholder="Qty"
             style="width: 80px; padding: 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; text-align: center;">
      <button type="button" 
              onclick="this.parentElement.remove()"
              style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;">
        ✕
      </button>
    `;
    
    container.appendChild(colorDiv);
  });
}

function renderSizeColorInputs(sizes) {
  const container = document.getElementById("sizesContainer");
  container.innerHTML = "";
  
  sizes.forEach((sizeData) => {
    if (sizeData.isSimpleProduct || !sizeData.size || sizeData.size.trim() === '') {
      return;
    }
    
    const sizeDiv = document.createElement("div");
    sizeDiv.style.marginBottom = "24px";
    sizeDiv.style.padding = "16px";
    sizeDiv.style.background = "#f8fafc";
    sizeDiv.style.borderRadius = "8px";
    sizeDiv.style.border = "1px solid #e2e8f0";
    
    let existingColors = {};
    if (sizeData.size_quantities && typeof sizeData.size_quantities === 'object') {
      existingColors = sizeData.size_quantities;
    }
    
    let colorsHtml = '';
    const colorVariants = sizeData.color_variants || [];
    
    if (colorVariants.length > 0) {
      colorVariants.forEach(variant => {
        const qty = variant.quantity || 0;
        colorsHtml += `
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <span style="flex: 1; font-size: 14px; color: #374151; font-weight: 500;">${variant.color}</span>
            <input type="number" 
                   class="color-quantity-input" 
                   data-size="${sizeData.size}" 
                   data-color="${variant.color}"
                   data-sku="${variant.sku}"
                   min="0" 
                   value="${qty}"
                   placeholder="Qty"
                   style="width: 80px; padding: 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; text-align: center;">
            <button type="button" 
                    onclick="removeColor('${sizeData.size}', '${variant.color.replace(/'/g, "\\'")}', '${variant.sku}')"
                    style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
                    title="Remove color">
              ✕
            </button>
          </div>
        `;
      });
    } else if (Object.keys(existingColors).length > 0) {
      Object.keys(existingColors).forEach(color => {
        const qty = existingColors[color] || 0;
        const colorCode = color.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '');
        const variantSku = sizeData.sku + '-' + sizeData.size + '-' + colorCode;
        colorsHtml += `
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <span style="flex: 1; font-size: 14px; color: #374151; font-weight: 500;">${color}</span>
            <input type="number" 
                   class="color-quantity-input" 
                   data-size="${sizeData.size}" 
                   data-color="${color}"
                   data-sku="${variantSku}"
                   min="0" 
                   value="${qty}"
                   placeholder="Qty"
                   style="width: 80px; padding: 8px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; text-align: center;">
            <button type="button" 
                    onclick="removeColor('${sizeData.size}', '${color.replace(/'/g, "\\'")}', '${variantSku}')"
                    style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
                    title="Remove color">
              ✕
            </button>
          </div>
        `;
      });
    }
    
    sizeDiv.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
        <label style="font-weight: 600; font-size: 14px; color: #374151;">
          Size: ${sizeData.size}
        </label>
        <span style="font-size: 12px; color: #6b7280;">
          Total: <span id="total-${sizeData.size.replace(/\s/g, '-')}">${Object.values(existingColors).reduce((a, b) => a + b, 0)}</span>
        </span>
      </div>
      <div id="colors-${sizeData.size.replace(/\s/g, '-')}" style="margin-bottom: 12px;">
        ${colorsHtml}
      </div>
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
    
    container.appendChild(sizeDiv);
  });
}

function addColorForSize(size, sku) {
  const inputId = 'newColor-' + size.replace(/\s/g, '-');
  const colorInput = document.getElementById(inputId);
  const colorName = colorInput.value.trim();
  
  if (!colorName) {
    showInvalidMessage('Please enter a color name');
    return;
  }
  
  const colorsContainer = document.getElementById('colors-' + size.replace(/\s/g, '-'));
  const existingInputs = colorsContainer.querySelectorAll('.color-quantity-input');
  for (let input of existingInputs) {
    if (input.dataset.color.toLowerCase() === colorName.toLowerCase()) {
      showInvalidMessage('This color already exists for size ' + size);
      return;
    }
  }
  
  const baseSku = window.currentBaseSku || sku;
  const colorCode = colorName.substring(0, 3).toUpperCase().replace(/[^A-Z0-9]/g, '');
  const variantSku = baseSku + '-' + size + '-' + colorCode;
  
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
            onclick="removeColor('${size}', '${colorName.replace(/'/g, "\\'")}', '${variantSku}')"
            style="padding: 4px 8px; background: #fee2e2; color: #dc2626; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 500;"
            title="Remove color">
      ✕
    </button>
  `;
  
  colorsContainer.appendChild(newColorDiv);
  colorInput.value = '';
  
  const qtyInput = newColorDiv.querySelector('.color-quantity-input');
  qtyInput.addEventListener('input', function() {
    updateSizeTotal(size);
  });
}

function removeColor(size, color, sku) {
  const colorsContainer = document.getElementById('colors-' + size.replace(/\s/g, '-'));
  const inputs = colorsContainer.querySelectorAll('.color-quantity-input');
  
  for (let input of inputs) {
    if (input.dataset.color === color) {
      input.parentElement.remove();
      break;
    }
  }
  
  updateSizeTotal(size);
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

function handleSimpleStockUpdate(quantity) {
  const baseSku = window.currentBaseSku;
  const submitBtn = document.getElementById("addQuantitySubmitBtn");
  
  submitBtn.disabled = true;
  submitBtn.style.opacity = "0.6";
  submitBtn.style.cursor = "not-allowed";
  
  // Check if this is an existing stock (replace mode) or new stock (add mode)
  // If current stock > 0, we are replacing the stock value
  const replaceStock = window.currentStock > 0 ? "true" : "false";
  
  fetch("../../back-end/update/addQuantity.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "sku=" + encodeURIComponent(baseSku) + 
          "&amount=" + encodeURIComponent(quantity) + 
          "&size=" + encodeURIComponent('') + 
          "&color=" + encodeURIComponent('') + 
          "&simpleStock=true" +
          "&replaceStock=" + replaceStock,
  })
    .then((response) => response.json())
    .then((data) => {
      submitBtn.disabled = false;
      submitBtn.style.opacity = "1";
      submitBtn.style.cursor = "pointer";
      
      if (data.success) {
        const productIndex = products.findIndex(p => p.sku === baseSku);
        if (productIndex !== -1) {
          products[productIndex].stock = data.newStock;
          
          if (products[productIndex].stock === 0) {
            products[productIndex].status = 'Out of Stock';
          } else if (products[productIndex].stock <= 10) {
            products[productIndex].status = 'Low Stock';
          } else {
            products[productIndex].status = 'In Stock';
          }
          
          localStorage.setItem("inventoryProducts", JSON.stringify(products));
        }
        
        const successMessage = document.getElementById("successMessage");
        const successText = successMessage.querySelector(".success-text");
        successText.textContent = "Stock Updated Successfully!";
        successMessage.style.display = "block";

        setTimeout(() => { successMessage.style.display = "none"; }, 3000);

        closeAddQuantityModal();
        window.location.reload();
      } else {
        showInvalidMessage("Error: " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      submitBtn.disabled = false;
      submitBtn.style.opacity = "1";
      submitBtn.style.cursor = "pointer";
      showInvalidMessage("Error adding stock");
    });
}

function addQuantity() {
  const form = document.getElementById("addQuantityForm");
  
  const sizesContainer = document.getElementById("sizesContainer");
  const sizesEmptyState = document.getElementById("sizesEmptyState");
  const sizesErrorState = document.getElementById("sizesErrorState");
  const simpleStockQuantity = document.getElementById("simpleStockQuantity");
  const noSizeColorToggle = document.getElementById("noSizeColorToggle");
  
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }
  
  if (sizesEmptyState.style.display === "block") {
    if (noSizeColorToggle.checked) {
      const quantity = parseInt(simpleStockQuantity.value) || 0;
      if (quantity < 0) {
        showInvalidMessage("Please enter a valid quantity");
        return;
      }
      handleSimpleStockUpdate(quantity);
      return;
    } else {
      const colorInputs = document.querySelectorAll('.color-no-size-input');
      const updates = [];
      
      colorInputs.forEach((input) => {
        const amount = parseInt(input.value) || 0;
        if (amount >= 0) {
          updates.push({ color: input.dataset.color, amount: amount });
        }
      });
      
      if (updates.length === 0) {
        showInvalidMessage("Please add at least one color and quantity");
        return;
      }
      
      const submitBtn = document.getElementById("addQuantitySubmitBtn");
      submitBtn.disabled = true;
      submitBtn.style.opacity = "0.6";
      submitBtn.style.cursor = "not-allowed";
      
      processNoSizeColorUpdates(updates, 0, submitBtn);
      return;
    }
  }
  
  if (sizesErrorState.style.display === "block") {
    showInvalidMessage("Failed to load product sizes. Please try again.");
    return;
  }
  
  if (!sizesContainer.innerHTML || sizesContainer.innerHTML.trim() === "") {
    showInvalidMessage("No sizes available for this product.");
    return;
  }

  const quantityInputs = document.querySelectorAll(".color-quantity-input");
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
  
  const submitBtn = document.getElementById("addQuantitySubmitBtn");
  submitBtn.disabled = true;
  submitBtn.style.opacity = "0.6";
  submitBtn.style.cursor = "not-allowed";
  
  processQuantityUpdates(updates, 0, submitBtn);
}

function processQuantityUpdates(updates, index, submitBtn) {
  if (index >= updates.length) {
    submitBtn.disabled = false;
    submitBtn.style.opacity = "1";
    submitBtn.style.cursor = "pointer";
    
    const successMessage = document.getElementById("successMessage");
    const successText = successMessage.querySelector(".success-text");
    successText.textContent = "Quantities Added Successfully!";
    successMessage.style.display = "block";

    setTimeout(() => { successMessage.style.display = "none"; }, 3000);

    closeAddQuantityModal();
    window.location.reload();
    return;
  }
  
  const update = updates[index];
  
  fetch("../../back-end/update/addQuantity.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "sku=" + encodeURIComponent(update.sku) + 
          "&amount=" + encodeURIComponent(update.amount) + 
          "&size=" + encodeURIComponent(update.size) + 
          "&color=" + encodeURIComponent(update.color),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        processQuantityUpdates(updates, index + 1, submitBtn);
      } else {
        submitBtn.disabled = false;
        submitBtn.style.opacity = "1";
        submitBtn.style.cursor = "pointer";
        showInvalidMessage("Error: " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      submitBtn.disabled = false;
      submitBtn.style.opacity = "1";
      submitBtn.style.cursor = "pointer";
      showInvalidMessage("Error adding quantity");
    });
}

function processNoSizeColorUpdates(updates, index, submitBtn) {
  const baseSku = window.currentBaseSku;
  
  if (index >= updates.length) {
    submitBtn.disabled = false;
    submitBtn.style.opacity = "1";
    submitBtn.style.cursor = "pointer";
    
    const successMessage = document.getElementById("successMessage");
    const successText = successMessage.querySelector(".success-text");
    successText.textContent = "Colors Added Successfully!";
    successMessage.style.display = "block";

    setTimeout(() => { successMessage.style.display = "none"; }, 3000);

    closeAddQuantityModal();
    window.location.reload();
    return;
  }
  
  const update = updates[index];
  
  fetch("../../back-end/update/addQuantity.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "sku=" + encodeURIComponent(baseSku) + 
          "&amount=" + encodeURIComponent(update.amount) + 
          "&size=" + encodeURIComponent('') + 
          "&color=" + encodeURIComponent(update.color) +
          "&noSizeProduct=true",
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        processNoSizeColorUpdates(updates, index + 1, submitBtn);
      } else {
        submitBtn.disabled = false;
        submitBtn.style.opacity = "1";
        submitBtn.style.cursor = "pointer";
        showInvalidMessage("Error: " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      submitBtn.disabled = false;
      submitBtn.style.opacity = "1";
      submitBtn.style.cursor = "pointer";
      showInvalidMessage("Error adding color");
    });
}
</script>
