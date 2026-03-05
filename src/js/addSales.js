(function () {
  "use strict";

  // Prevent double initialization
  if (window.salesFormInitialized) {
    return;
  }
  window.salesFormInitialized = true;

  // Global variables
  var products = [];
  var codeReader;
  var currentRow;

  // Show success message
  function showSuccessMessage() {
    const successMessage = document.getElementById("successMessage");
    if (successMessage) {
      successMessage.style.display = "block";
      setTimeout(() => {
        successMessage.style.display = "none";
      }, 3000);
    }
  }

  // Show invalid message
  function showInvalidMessage() {
    const invalidMessage = document.getElementById("invalidMessage");
    if (invalidMessage) {
      invalidMessage.style.display = "block";
      setTimeout(() => {
        invalidMessage.style.display = "none";
      }, 3000);
    }
  }

  // Load products from database
  async function loadProducts() {
    try {
      const response = await fetch("../../back-end/read/fetchToSales.php");
      products = await response.json();
      if (products.error) {
        console.error("Error loading products:", products.error);
        products = [];
        return;
      }
    } catch (error) {
      console.error("Error fetching products:", error);
      products = [];
    }
  }

  function lookupProductBySKU(sku, row) {
    if (!sku || sku.trim() === "") {
      return;
    }

    const trimmedSku = sku.trim();
    
    let product = null;
    let variantInfo = null;
    
    // First, check if this is a variant SKU (contains size/color info)
    // Try to find in variant_skus of any product
    for (const p of products) {
      if (p.variant_skus) {
        // First, try exact match in KEYS (the full SKU like "SHO-SAP-3JOX-39-BLACK")
        if (p.variant_skus[trimmedSku]) {
          product = p;
          variantInfo = p.variant_skus[trimmedSku];
          break;
        }
        
        // Try case-insensitive match in keys
        const variantKeys = Object.keys(p.variant_skus);
        for (const key of variantKeys) {
          if (key.toLowerCase() === trimmedSku.toLowerCase()) {
            product = p;
            variantInfo = p.variant_skus[key];
            break;
          }
        }
        if (product) break;
        
        // Also search in VALUES (some products may have the SKU in values)
        // When found in values, extract size/color from the KEY (e.g., "39-black" -> size: 39, color: black)
        const variantValues = Object.values(p.variant_skus);
        for (let i = 0; i < variantValues.length; i++) {
          const val = variantValues[i];
          if (typeof val === 'string' && val.toLowerCase() === trimmedSku.toLowerCase()) {
            product = p;
            // Extract size and color from the key (e.g., "39-black" or "41-red")
            const key = variantKeys[i];
            const keyParts = key.split('-');
            const size = keyParts[0];
            const color = keyParts.slice(1).join('-'); // Join rest in case color has hyphen (e.g., "light-blue")
            
            // Get the quantity from size_color_quantities
            let quantity = 0;
            if (product.size_color_quantities && product.size_color_quantities[size]) {
              quantity = product.size_color_quantities[size][color]?.quantity || 0;
            }
            
            variantInfo = {
              size: size,
              color: color,
              quantity: quantity
            };
            break;
          }
        }
        if (product) break;
      }
    }
    
    // If not found as variant, check if it's a base SKU
    if (!product) {
      product = products.find((p) => p.sku === trimmedSku);
    }

    if (product) {
      // Fill in the product details
      row.querySelector(".product-id").value = product.id;
      row.querySelector('input[name*="[price]"]').value = product.price;

      // Hide dropdowns and auto-populate from variant SKU
      const sizeSelect = row.querySelector(".product-size");
      const colorSelect = row.querySelector(".product-color");
      const sizeGroup = sizeSelect.closest(".form-group");
      const colorGroup = colorSelect.closest(".form-group");
      
      // Hide size and color dropdowns
      sizeGroup.style.display = "none";
      colorGroup.style.display = "none";
      sizeSelect.removeAttribute("required");
      colorSelect.removeAttribute("required");

      // Store variant info in row for form submission
      row.variantInfo = variantInfo;

      if (variantInfo) {
        // This is a variant SKU - auto-fill size and color
        // Set hidden fields for size and color
        sizeSelect.innerHTML = `<option value="${variantInfo.size}">${variantInfo.size}</option>`;
        colorSelect.innerHTML = `<option value="${variantInfo.color}">${variantInfo.color}</option>`;
        
        // Also set the value property explicitly for form submission
        sizeSelect.value = variantInfo.size;
        colorSelect.value = variantInfo.color;
        
        // Set quantity max to available stock (only if stock > 0 to avoid HTML5 validation issues)
        const qtyInput = row.querySelector('input[name*="[quantity]"]');
        if (variantInfo.quantity > 0) {
          qtyInput.max = variantInfo.quantity;
        } else {
          // Remove max attribute when out of stock to prevent HTML5 default message
          qtyInput.removeAttribute('max');
        }
        
        // If quantity entered exceeds available, cap it
        if (parseInt(qtyInput.value) > variantInfo.quantity && variantInfo.quantity > 0) {
          qtyInput.value = variantInfo.quantity;
        }
        
        // Store extracted size and color for form submission
        row.extractedSize = variantInfo.size;
        row.extractedColor = variantInfo.color;
      } else {
        // Base SKU - fetch sizes to get variant information
        fetch(`../../back-end/read/getSizes.php?sku=${encodeURIComponent(trimmedSku)}`)
          .then((response) => response.json())
          .then((data) => {
            row.sizeColorData = data.sizes || [];
            
            // Check if product has sizes with stock
            const hasSizesWithStock = data.sizes && data.sizes.some(s => s.stock > 0);
            
            if (!hasSizesWithStock) {
              // Simple product - no sizes, just use base SKU
              sizeSelect.innerHTML = '<option value="N/A">N/A</option>';
              colorSelect.innerHTML = '<option value="N/A">N/A</option>';
              row.extractedSize = 'N/A';
              row.extractedColor = 'N/A';
            }
          })
          .catch((error) => {
            console.error("Error fetching sizes:", error);
          });
      }
      // Update inline product info display
      updateInlineProductInfo(row, product, variantInfo);

      // Update total
      updateTotal();

      // Visual feedback
      const skuInput = row.querySelector(".product-sku");
      skuInput.style.borderColor = "green";
      setTimeout(() => {
        skuInput.style.borderColor = "";
      }, 2000);
    } else {
      // Product not found

      // Clear fields
      row.querySelector(".product-id").value = "";
      row.querySelector('input[name*="[price]"]').value = "";

      // Reset inline product info
      resetInlineProductInfo(row);

      // Visual feedback
      const skuInput = row.querySelector(".product-sku");
      skuInput.style.borderColor = "red";
      setTimeout(() => {
        skuInput.style.borderColor = "";
      }, 2000);
    }
  }
  
  // Update inline product info for each row
  function updateInlineProductInfo(row, product, variantInfo) {
    const infoImage = row.querySelector(".info-image");
    const infoName = row.querySelector(".info-name");
    const infoPrice = row.querySelector(".info-price");
    const infoDetails = row.querySelector(".info-details");
    const nameDisplay = row.querySelector(".product-name-display");
    
    // Set product image
    if (infoImage) {
      const imageUrl = product.image || '';
      if (imageUrl) {
        infoImage.src = imageUrl;
        infoImage.style.display = "block";
      } else {
        infoImage.src = "";
        infoImage.style.display = "none";
      }
    }
    if (infoName) {
      infoName.textContent = product.name || "N/A";
    }
    if (infoPrice) {
      infoPrice.textContent = "₱" + (parseFloat(product.price) || 0).toFixed(2);
    }
    
    // Build details text
    let detailsText = "";
    if (variantInfo) {
      if (variantInfo.size && variantInfo.size !== 'N/A') {
        detailsText += `Size: ${variantInfo.size} | `;
      }
      if (variantInfo.color) {
        detailsText += `Color: ${variantInfo.color} | `;
      }
      detailsText += `Stock: ${variantInfo.quantity || 0}`;
    } else {
      detailsText += `Stock: ${product.stock || 0}`;
    }
    
    // Add additional info if available
    if (product.information) {
      const info = product.information;
      if (info.brand && typeof info.brand === 'string' && info.brand.trim() !== '') {
        detailsText += ` | Brand: ${info.brand}`;
      }
      if (info.material && typeof info.material === 'string' && info.material.trim() !== '') {
        detailsText += ` | Material: ${info.material}`;
      }
    }
    
    if (infoDetails) {
      infoDetails.textContent = detailsText;
    }
    
    // Also update the name display if it exists
    if (nameDisplay) {
      nameDisplay.textContent = product.name;
      nameDisplay.style.color = "green";
    }
    
    // Update row total
    updateRowTotal(row);
  }
  
  // Reset inline product info
  function resetInlineProductInfo(row) {
    const infoImage = row.querySelector(".info-image");
    const infoName = row.querySelector(".info-name");
    const infoPrice = row.querySelector(".info-price");
    const infoDetails = row.querySelector(".info-details");
    const nameDisplay = row.querySelector(".product-name-display");
    
    // Reset product image
    if (infoImage) {
      infoImage.src = "";
      infoImage.style.display = "none";
    }
    if (infoName) infoName.textContent = "-";
    if (infoPrice) infoPrice.textContent = "₱0.00";
    if (infoDetails) infoDetails.textContent = "";
    if (nameDisplay) {
      nameDisplay.textContent = "Product not found";
      nameDisplay.style.color = "red";
    }
    
    updateRowTotal(row);
  }
  
  // Update row total (price * quantity)
  function updateRowTotal(row) {
    const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
    const quantity = parseInt(row.querySelector('input[name*="[quantity]"]').value) || 0;
    const rowTotal = row.querySelector(".row-total");
    if (rowTotal) {
      rowTotal.textContent = "₱" + (price * quantity).toFixed(2);
    }
  }

  // Helper function to check if sizeColorData has any sizes with stock
  function hasSizeVariantProducts(sizeColorData) {
    if (!sizeColorData || !Array.isArray(sizeColorData)) {
      return false;
    }
    return sizeColorData.some(s => s.stock > 0);
  }

  // Open barcode scanner
  window.openScanner = function (row) {
    currentRow = row;
    document.getElementById("scannerModal").style.display = "block";

    // Initialize ZXing barcode reader
    codeReader = new ZXing.BrowserMultiFormatReader();

    // Start scanning
    codeReader
      .decodeFromVideoDevice(null, "scanner-video", (result, err) => {
        if (result) {
          const code = result.text;

          // Fill the SKU input
          const skuInput = currentRow.querySelector(".product-sku");
          skuInput.value = code;

          // Lookup product
          lookupProductBySKU(code, currentRow);

          // Close scanner
          window.closeScanner();
        }
        if (err && !(err instanceof ZXing.NotFoundException)) {
          console.error("Scanner error:", err);
        }
      })
      .catch((err) => {
        console.error("Error starting scanner:", err);
        alert(
          "Camera access denied or not available. Please enter SKU manually.",
        );
        window.closeScanner();
      });
  };

  // Close scanner
  window.closeScanner = function () {
    document.getElementById("scannerModal").style.display = "none";
    if (codeReader) {
      codeReader.reset();
      codeReader = null;
    }
  };

  // Update total amount
  function updateTotal() {
    const rows = document.querySelectorAll(".product-row");
    let total = 0;

    rows.forEach((row) => {
      const price =
        parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
      const quantity =
        parseInt(row.querySelector('input[name*="[quantity]"]').value) || 0;
      total += price * quantity;
    });

    document.getElementById("totalAmount").textContent = total.toFixed(2);
    document.getElementById("totalAmountInput").value = total.toFixed(2);
  }

  // Update the right-side product info panel
  function updateProductInfoPanel(product, variantInfo) {
    const selectedProductInfo = document.getElementById("selectedProductInfo");
    const noProductSelected = document.getElementById("noProductSelected");
    const infoProductName = document.getElementById("infoProductName");
    const infoProductPrice = document.getElementById("infoProductPrice");
    const infoVariantDetails = document.getElementById("infoVariantDetails");
    const infoProductExtra = document.getElementById("infoProductExtra");
    const infoExtraDetails = document.getElementById("infoExtraDetails");
    
    if (!selectedProductInfo || !noProductSelected) {
      return;
    }
    
    // Show selected product info, hide "no product" message
    selectedProductInfo.style.display = "block";
    noProductSelected.style.display = "none";
    
    // Set product name
    if (infoProductName) {
      infoProductName.textContent = product.name || "N/A";
    }
    
    // Set price
    if (infoProductPrice) {
      infoProductPrice.textContent = "₱" + (parseFloat(product.price) || 0).toFixed(2);
    }
    
    // Set variant details
    if (infoVariantDetails) {
      let variantHtml = "";
      if (variantInfo) {
        if (variantInfo.size && variantInfo.size !== 'N/A') {
          variantHtml += `<p style="margin: 0 0 8px 0;"><strong>Size:</strong> ${variantInfo.size}</p>`;
        }
        if (variantInfo.color) {
          variantHtml += `<p style="margin: 0 0 8px 0;"><strong>Color:</strong> ${variantInfo.color}</p>`;
        }
        variantHtml += `<p style="margin: 0;"><strong>Stock:</strong> ${variantInfo.quantity || 0}</p>`;
      } else {
        variantHtml += `<p style="margin: 0;"><strong>Stock:</strong> ${product.stock || 0}</p>`;
      }
      infoVariantDetails.innerHTML = variantHtml;
    }
    
    // Set additional product information (brand, material, dimensions, etc.)
    let extraHtml = "";
    let hasExtra = false;
    
    if (product.information) {
      const info = product.information;
      if (info.brand && typeof info.brand === 'string' && info.brand.trim() !== '') {
        extraHtml += `<p style="margin: 0 0 6px 0;"><strong>Brand:</strong> ${info.brand}</p>`;
        hasExtra = true;
      }
      if (info.material && typeof info.material === 'string' && info.material.trim() !== '') {
        extraHtml += `<p style="margin: 0 0 6px 0;"><strong>Material:</strong> ${info.material}</p>`;
        hasExtra = true;
      }
      if (info.dimensions && typeof info.dimensions === 'string' && info.dimensions.trim() !== '') {
        extraHtml += `<p style="margin: 0 0 6px 0;"><strong>Dimensions:</strong> ${info.dimensions}</p>`;
        hasExtra = true;
      }
      if (info.product_info && typeof info.product_info === 'string' && info.product_info.trim() !== '') {
        extraHtml += `<p style="margin: 0;"><strong>Info:</strong> ${info.product_info}</p>`;
        hasExtra = true;
      }
    }
    
    if (infoProductExtra && infoExtraDetails) {
      if (hasExtra) {
        infoExtraDetails.innerHTML = extraHtml;
        infoProductExtra.style.display = "block";
      } else {
        infoProductExtra.style.display = "none";
      }
    }
  }

  // Reset the product info panel to default state
  function resetProductInfoPanel() {
    const selectedProductInfo = document.getElementById("selectedProductInfo");
    const noProductSelected = document.getElementById("noProductSelected");
    
    if (!selectedProductInfo || !noProductSelected) {
      return;
    }
    
    // Hide selected product info, show "no product" message
    selectedProductInfo.style.display = "none";
    noProductSelected.style.display = "block";
  }

  // Add new product row
  function addProductRow() {
    const container = document.getElementById("productsContainer");
    const rowCount = container.children.length;
    const row = document.createElement("div");
    row.className = "product-row";
    row.innerHTML = `
        <div class="form-group">
            <label>Product SKU</label>
            <div class="product-scanner">
                <input type="text" name="products[${rowCount}][sku]" class="product-sku" placeholder="Scan barcode or enter SKU" required autocomplete="off">
                <button type="button" class="btn btn-icon scan-btn" style="background-color: #000; color: #fff;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                        <circle cx="12" cy="13" r="4"></circle>
                    </svg>
                </button>
            </div>
            <input type="hidden" name="products[${rowCount}][id]" class="product-id">
            <span class="product-name-display"></span>
            <span class="product-variant-display" style="display: block; margin-top: 4px; font-size: 13px; color: #6b7280;"></span>
            <span class="product-info-display" style="display: block; margin-top: 4px; font-size: 12px; color: #6b7280;"></span>
            <!-- Inline product info with image -->
            <div class="inline-product-info" style="margin-top: 8px; padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb; display: flex; gap: 12px; align-items: flex-start;">
                <img class="info-image" src="" alt="Product" style="width: 60px; height: 60px; border-radius: 6px; object-fit: cover; border: 1px solid #e5e7eb; display: none;">
                <div style="flex: 1;">
                    <span class="info-name" style="font-weight: 600; color: #111827; display: block;">-</span>
                    <span class="info-price" style="color: #059669; font-weight: 600;">₱0.00</span>
                    <span class="info-details" style="display: block; font-size: 12px; color: #6b7280; margin-top: 4px;"></span>
                </div>
            </div>
            <span class="row-total" style="display: block; font-weight: 600; color: #111827; margin-top: 8px;">₱0.00</span>
        </div>
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="products[${rowCount}][quantity]" min="1" value="1" required>
        </div>
        <div class="form-group" style="display: none;">
            <label>Size</label>
            <select name="products[${rowCount}][size]" class="product-size">
                <option value="">Select Size</option>
            </select>
        </div>
        <div class="form-group" style="display: none;">
            <label>Color</label>
            <select name="products[${rowCount}][color]" class="product-color">
                <option value="">Select Color</option>
            </select>
        </div>
        <div class="form-group">
            <label>Price</label>
            <input type="number" name="products[${rowCount}][price]" step="0.01" readonly>
        </div>
        <button type="button" class="btn btn-icon btn-danger remove-product">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    `;
    container.appendChild(row);
    
    // Add invalid event listener for custom error message on quantity input
    const qtyInput = row.querySelector('input[name*="[quantity]"]');
    if (qtyInput) {
      qtyInput.addEventListener('invalid', function(e) {
        // Get the max value if set
        const maxVal = this.max ? parseInt(this.max) : null;
        const value = parseInt(this.value);
        
        if (maxVal !== null && value > maxVal) {
          // Custom message for exceeding max stock
          e.preventDefault();
          this.setCustomValidity('');
          this.setCustomValidity('Insufficient stock. Available: ' + maxVal + ', Requested: ' + value);
        } else if (value < 1) {
          // Custom message for value less than 1
          e.preventDefault();
          this.setCustomValidity('');
          this.setCustomValidity('Quantity must be at least 1');
        }
      });
      
      // Clear custom validity on input
      qtyInput.addEventListener('input', function() {
        this.setCustomValidity('');
      });
    }
  }

  // Remove product row
  function removeProductRow(button) {
    if (document.querySelectorAll(".product-row").length > 1) {
      button.closest(".product-row").remove();
      updateTotal();
    } else {
      alert("At least one product is required");
    }
  }

// Event listeners
  document.addEventListener("DOMContentLoaded", async function () {
    // Load products on page load - wait for it to complete
    await loadProducts();

    // Add first product row on page load
    addProductRow();

    // Add product button
    document
      .getElementById("addProductBtn")
      .addEventListener("click", addProductRow);

    // SKU input change - auto lookup
    document.addEventListener("input", function (e) {
      if (e.target.classList.contains("product-sku")) {
        const row = e.target.closest(".product-row");
        const sku = e.target.value.trim();

        // Auto-lookup when SKU is entered (debounce)
        clearTimeout(e.target.lookupTimeout);
        e.target.lookupTimeout = setTimeout(() => {
          if (sku) {
            lookupProductBySKU(sku, row);
          }
        }, 500);
      }
    });

    // Quantity change - update total and row total
    document.addEventListener("change", function (e) {
      if (e.target.name && e.target.name.includes("[quantity]")) {
        const row = e.target.closest(".product-row");
        updateRowTotal(row);
        updateTotal();
      }
      
      // Size selection - populate colors
      if (e.target.classList.contains("product-size")) {
        const row = e.target.closest(".product-row");
        const sizeSelect = e.target;
        const colorSelect = row.querySelector(".product-color");
        const selectedSize = sizeSelect.value;
        
        // Get the stored size data from the row
        const sizeData = row.sizeColorData;
        
        colorSelect.innerHTML = '<option value="">Select Color</option>';
        
        if (sizeData && selectedSize) {
          const sizeInfo = sizeData.find(s => s.size === selectedSize);
          if (sizeInfo && sizeInfo.color_variants && sizeInfo.color_variants.length > 0) {
            sizeInfo.color_variants.forEach((colorVariant) => {
              if (colorVariant.quantity > 0) {
                const option = document.createElement("option");
                option.value = colorVariant.sku;
                option.textContent = `${colorVariant.color} (${colorVariant.quantity})`;
                option.dataset.color = colorVariant.color;
                colorSelect.appendChild(option);
              }
            });
            // Make color required if colors are available
            if (colorSelect.options.length > 1) {
              colorSelect.setAttribute("required", "required");
              colorSelect.dataset.hasColors = "true";
              colorSelect.closest(".form-group").style.display = "block";
            } else {
              colorSelect.removeAttribute("required");
              colorSelect.dataset.hasColors = "false";
              colorSelect.closest(".form-group").style.display = "none";
            }
          } else {
            // No colors with stock available
            colorSelect.removeAttribute("required");
            colorSelect.dataset.hasColors = "false";
            colorSelect.closest(".form-group").style.display = "none";
          }
        }
      }
      
// Color selection - update SKU and validate
      if (e.target.classList.contains("product-color")) {
        const row = e.target.closest(".product-row");
        const colorSelect = e.target;
        const selectedOption = colorSelect.options[colorSelect.selectedIndex];
        
        if (selectedOption && selectedOption.value) {
          // Update SKU with the full variant SKU
          const skuInput = row.querySelector(".product-sku");
          skuInput.value = selectedOption.value;
          
          // Get color name from data attribute
          const colorName = selectedOption.dataset.color || '';
          
          // Get size data to find quantity for this color
          const sizeSelect = row.querySelector(".product-size");
          const sizeData = row.sizeColorData;
          const selectedSize = sizeSelect.value;
          
          if (sizeData && selectedSize) {
            const sizeInfo = sizeData.find(s => s.size === selectedSize);
            if (sizeInfo && sizeInfo.color_variants) {
              const colorVariant = sizeInfo.color_variants.find(c => c.sku === selectedOption.value);
              if (colorVariant) {
                // Set the quantity input max to available stock (only if stock > 0)
                const qtyInput = row.querySelector('input[name*="[quantity]"]');
                if (colorVariant.quantity > 0) {
                  qtyInput.max = colorVariant.quantity;
                } else {
                  // Remove max attribute when out of stock
                  qtyInput.removeAttribute('max');
                }
              }
            }
          }
        }
      }
    });

    // Scan button click
    document.addEventListener("click", function (e) {
      if (e.target.closest(".scan-btn")) {
        e.preventDefault();
        const row = e.target.closest(".product-row");
        window.openScanner(row);
      }
    });

    // Remove product button
    document.addEventListener("click", function (e) {
      if (e.target.closest(".remove-product")) {
        removeProductRow(e.target.closest(".remove-product"));
      }
    });

    // Form submission
    document
      .getElementById("addSalesForm")
      .addEventListener("submit", async function (e) {
        e.preventDefault();

        // Validate that all products have valid IDs and stock availability
        const rows = document.querySelectorAll(".product-row");
        let isValid = true;
        let errorMessage = "";

        rows.forEach((row, index) => {
          const productId = row.querySelector(".product-id").value;
          const skuInput = row.querySelector(".product-sku");
          const qtyInput = row.querySelector('input[name*="[quantity]"]');
          const sku = skuInput.value.trim();
          const quantity = parseInt(qtyInput.value) || 0;

          // Check if product is valid
          if (!productId) {
            isValid = false;
            errorMessage = "Please ensure all products are valid";
            skuInput.style.borderColor = "red";
            return;
          }

          // Check stock availability
          let availableStock = 0;
          
          // If variant info exists, get stock from it
          if (row.variantInfo && row.variantInfo.quantity !== undefined) {
            availableStock = parseInt(row.variantInfo.quantity) || 0;
          } else if (row.sizeColorData && row.sizeColorData.length > 0) {
            // Check selected size/color combination
            const sizeSelect = row.querySelector(".product-size");
            const colorSelect = row.querySelector(".product-color");
            const selectedSize = sizeSelect.value;
            const selectedColorOption = colorSelect.options[colorSelect.selectedIndex];
            
            if (selectedSize && selectedSize !== 'N/A' && selectedColorOption && selectedColorOption.value) {
              // Find the color variant to get available quantity
              const sizeInfo = row.sizeColorData.find(s => s.size === selectedSize);
              if (sizeInfo && sizeInfo.color_variants) {
                const colorVariant = sizeInfo.color_variants.find(c => c.sku === selectedColorOption.value);
                if (colorVariant) {
                  availableStock = parseInt(colorVariant.quantity) || 0;
                }
              }
            } else {
              // No specific size/color selected, use product stock
              const product = products.find(p => p.id === productId);
              availableStock = parseInt(product?.stock) || 0;
            }
          } else {
            // Simple product - get stock from products array
            const product = products.find(p => p.id === productId);
            availableStock = parseInt(product?.stock) || 0;
          }
          
          // Check if out of stock
          if (availableStock === 0) {
            isValid = false;
            errorMessage = "This product is out of stock";
            skuInput.style.borderColor = "red";
            return;
          }
          
          // Check if quantity exceeds available stock
          if (quantity > availableStock) {
            isValid = false;
            errorMessage = "Invalid, quantity must not be higher than stock (Available: " + availableStock + ")";
            skuInput.style.borderColor = "red";
            
            // Show custom error message using alert for immediate feedback
            alert(errorMessage);
            return;
          }

          // Use extracted size/color from variant SKU if available
          const sizeSelect = row.querySelector(".product-size");
          const colorSelect = row.querySelector(".product-color");
          
          // If we have extracted variant info, use it; otherwise use dropdown values
          // Only check for size if we don't have extracted size from a variant SKU
          const hasExtractedSize = row.extractedSize && row.extractedSize !== '' && row.extractedSize !== 'N/A';
          const hasExtractedColor = row.extractedColor && row.extractedColor !== '' && row.extractedColor !== 'N/A';
          
          // If variant info exists and we've extracted size OR color, validation passes
          // This handles both size+color variants and color-only variants
          if (row.variantInfo && (hasExtractedSize || hasExtractedColor)) {
            // This is a valid variant SKU - size and/or color are auto-selected
            // No validation needed
          } else if (row.sizeColorData && row.sizeColorData.length > 0 && hasSizeVariantProducts(row.sizeColorData)) {
            // Product has sizes with stock from getSizes.php - need to select manually
            const size = sizeSelect.value;
            if (!size || size === '' || size === 'N/A') {
              isValid = false;
              errorMessage = "Please select a size";
              skuInput.style.borderColor = "red";
            }
          }
          // If no variantInfo and no valid sizes, it's a simple product
          // No additional validation needed - allow the sale
        });

        if (!isValid) {
          // Show custom error message
          const invalidMessage = document.getElementById("invalidMessage");
          if (invalidMessage) {
            const invalidText = invalidMessage.querySelector(".invalid-text");
            if (invalidText) {
              invalidText.textContent = errorMessage || "Invalid, please select size!";
            }
            invalidMessage.style.display = "block";
            setTimeout(() => {
              invalidMessage.style.display = "none";
            }, 3000);
          }
          return;
        }

        const formData = new FormData(this);

        try {
          const response = await fetch("../../back-end/create/addSales.php", {
            method: "POST",
            body: formData,
          });
          const result = await response.json();

          if (result.success) {
            showSuccessMessage();
            this.reset();

            // Reset products to one row
            const container = document.getElementById("productsContainer");
            while (container.children.length > 1) {
              container.lastChild.remove();
            }

            // Clear product displays and reset size selects
            document
              .querySelectorAll(".product-name-display")
              .forEach((display) => {
                display.textContent = "";
              });

            document
              .querySelectorAll(".product-variant-display")
              .forEach((display) => {
                display.textContent = "";
              });

            document
              .querySelectorAll(".product-info-display")
              .forEach((display) => {
                display.textContent = "";
                display.style.display = "none";
              });

            document.querySelectorAll(".product-size").forEach((select) => {
              select.innerHTML = '<option value="">Select Size</option>';
              select.removeAttribute("required");
              select.dataset.hasSizes = "false";
            });
            
            // Reset color selects
            document.querySelectorAll(".product-color").forEach((select) => {
              select.innerHTML = '<option value="">Select Color</option>';
              select.removeAttribute("required");
              select.dataset.hasColors = "false";
            });

            updateTotal();
            
            // Reset the product info panel
            resetProductInfoPanel();
          } else {
            showInvalidMessage();
          }
        } catch (error) {
          console.error("Error submitting form:", error);
          showInvalidMessage();
        }
      });

    // Form reset
    document
      .getElementById("addSalesForm")
      .addEventListener("reset", function () {
        // Clear product displays
        document
          .querySelectorAll(".product-name-display")
          .forEach((display) => {
            display.textContent = "";
          });

        // Clear variant displays
        document
          .querySelectorAll(".product-variant-display")
          .forEach((display) => {
            display.textContent = "";
          });

        // Clear info displays
        document
          .querySelectorAll(".product-info-display")
          .forEach((display) => {
            display.textContent = "";
            display.style.display = "none";
          });

        // Reset size selects
        document.querySelectorAll(".product-size").forEach((select) => {
          select.innerHTML = '<option value="">Select Size</option>';
          select.removeAttribute("required");
          select.dataset.hasSizes = "false";
        });
        
        // Reset color selects
        document.querySelectorAll(".product-color").forEach((select) => {
          select.innerHTML = '<option value="">Select Color</option>';
          select.removeAttribute("required");
          select.dataset.hasColors = "false";
        });

        // Reset inline product info displays
        document.querySelectorAll(".product-row").forEach((row) => {
          resetInlineProductInfo(row);
        });

        updateTotal();
      });
  });

  // Fetch and display sale details
  async function fetchSaleDetails(saleId) {
    try {
      const response = await fetch(
        `../../back-end/read/fetchSale.php?sale_id=${saleId}`,
      );
      const data = await response.json();

      if (data.success) {
        displaySaleDetails(data.sale);
      } else {
        console.error("Error fetching sale details:", data.error);
      }
    } catch (error) {
      console.error("Error fetching sale details:", error);
    }
  }

  // Display sale details in the right-side panel
  function displaySaleDetails(sale) {
    document.getElementById("saleId").textContent = sale.id;
    document.getElementById("saleTotal").textContent = parseFloat(
      sale.total_amount,
    ).toFixed(2);
    document.getElementById("salePayment").textContent = sale.payment_method;
    document.getElementById("saleDate").textContent = new Date(
      sale.created_at,
    ).toLocaleString();

    const productsList = document.getElementById("saleProducts");
    productsList.innerHTML = "";
    sale.items.forEach((item) => {
      const li = document.createElement("li");
      li.innerHTML = `<strong>${item.name}</strong> (SKU: ${item.sku}) - Qty: ${item.quantity}, Size: ${item.size}, Price: ₱${parseFloat(item.price).toFixed(2)}`;
      productsList.appendChild(li);
    });

    document.getElementById("saleDetails").style.display = "block";
    document.getElementById("noSaleMessage").style.display = "none";
  }

  // Close modal when clicking outside
  window.onclick = function (event) {
    const modal = document.getElementById("scannerModal");
    if (event.target == modal) {
      window.closeScanner();
    }
  };
})();
