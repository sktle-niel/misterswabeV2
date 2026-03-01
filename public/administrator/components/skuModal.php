<!-- SKU View Modal -->
<div class="modal-overlay" id="skuModalOverlay" onclick="closeSkuModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 1200px; width: 95%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="closeSkuModal()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #111827;">Product SKU & Barcodes</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                View SKU and generate barcodes for each size and color variant
            </p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 40px;">
            <!-- Loading State -->
            <div id="skuLoadingState" style="text-align: center; padding: 40px; display: none;">
                <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f4f6; border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                <p style="margin-top: 12px; color: #6b7280;">Loading product variants...</p>
            </div>

            <!-- SKU and Barcode Container -->
            <div id="skuBarcodeContainer" style="display: none;">
                <!-- Size Variants Section -->
                <div id="sizeVariantsContainer" style="display: flex; flex-direction: column; gap: 16px;">
                    <!-- Size variants will be dynamically inserted here -->
                </div>
            </div>

            <!-- Error State -->
            <div id="skuErrorState" style="text-align: center; padding: 40px; display: none;">
                <p style="color: #ef4444; font-size: 15px;">Failed to load product variants. Please try again.</p>
            </div>

            <!-- Empty State -->
            <div id="skuEmptyState" style="text-align: center; padding: 40px; display: none;">
                <p style="color: #6b7280; font-size: 15px;">No size variants found for this product.</p>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="display: flex; gap: 12px; justify-content: space-between; margin-top: 32px; padding: 24px 40px; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 16px 16px;">
            <button type="button" onclick="printAllBarcodes()"
                style="padding: 12px 28px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);"
                onmouseover="this.style.background='#059669'"
                onmouseout="this.style.background='#10b981'">
                <span style="display: inline-flex; align-items: center; gap: 8px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                    Print Selected
                </span>
            </button>
            <button type="button" onclick="closeSkuModal()"
                style="padding: 12px 28px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s;"
                onmouseover="this.style.background='#e5e7eb'"
                onmouseout="this.style.background='#f3f4f6'">
                Close
            </button>
        </div>
    </div>
</div>

<style>
@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
// Store current product variants
let currentProductVariants = [];

// Store print quantities for each variant
let printQuantities = {};

// SKU Modal Functions
function closeSkuModal() {
  document.getElementById("skuModalOverlay").style.display = "none";
  currentProductVariants = [];
  printQuantities = {};
}

function closeSkuModalOnOverlay(event) {
  if (event.target === document.getElementById("skuModalOverlay")) {
    closeSkuModal();
  }
}

function openSkuModal(sku) {
  const overlay = document.getElementById("skuModalOverlay");
  const loadingState = document.getElementById("skuLoadingState");
  const barcodeContainer = document.getElementById("skuBarcodeContainer");
  const errorState = document.getElementById("skuErrorState");
  const emptyState = document.getElementById("skuEmptyState");

  if (!overlay) {
    console.error("SKU Modal elements not found in DOM");
    return;
  }

  // Reset states
  loadingState.style.display = "block";
  barcodeContainer.style.display = "none";
  errorState.style.display = "none";
  emptyState.style.display = "none";
  
  // Reset print quantities
  printQuantities = {};

  // Fetch product sizes with colors
  fetch("../../back-end/read/getSizes.php?sku=" + encodeURIComponent(sku))
    .then((response) => response.json())
    .then((data) => {
      loadingState.style.display = "none";
      
      if (data.success && data.sizes && data.sizes.length > 0) {
        currentProductVariants = data.sizes;
        renderSizeVariants(data.sizes);
        barcodeContainer.style.display = "block";
      } else if (data.success && (!data.sizes || data.sizes.length === 0)) {
        emptyState.style.display = "block";
      } else {
        errorState.style.display = "block";
        console.error("Error loading sizes:", data.message);
      }
    })
    .catch((error) => {
      loadingState.style.display = "none";
      errorState.style.display = "block";
      console.error("Fetch error:", error);
    });

  overlay.style.display = "flex";
}

// Set print quantity for a specific variant
function setPrintQuantity(sku, quantity) {
  printQuantities[sku] = parseInt(quantity) || 0;
}

function renderSizeVariants(sizes) {
  const container = document.getElementById("sizeVariantsContainer");
  container.innerHTML = "";
  
  sizes.forEach((sizeData, index) => {
    const sizeDiv = document.createElement("div");
    sizeDiv.style.padding = "20px";
    sizeDiv.style.background = "#f8fafc";
    sizeDiv.style.borderRadius = "8px";
    sizeDiv.style.border = "1px solid #e2e8f0";
    
    // Check if this is a simple product (no sizes)
    const isSimpleProduct = sizeData.isSimpleProduct === true;
    const hasSize = sizeData.size && sizeData.size !== '';
    
    // Build color variants HTML
    let colorVariantsHtml = "";
    const colorVariants = sizeData.color_variants || [];
    const sizeStock = sizeData.stock || 0;
    
    // Check if size needs restock (only for products with sizes)
    const needsSizeRestock = hasSize && sizeStock === 0;
    
    if (colorVariants.length > 0) {
      // Has color variants - show each with its own barcode
      colorVariants.forEach((variant, vIndex) => {
        const uniqueId = "barcode-" + index + "-" + vIndex;
        const variantQty = variant.quantity || 0;
        const needsRestock = variantQty === 0;
        
        colorVariantsHtml += `
          <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: white; border-radius: 6px; margin-bottom: 8px; border: 1px solid ${needsRestock ? '#fca5a5' : '#e5e7eb'}; flex-wrap: wrap; gap: 12px;">
            <div style="flex: 1; min-width: 200px;">
              <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                <span style="font-weight: 600; color: #374151;">${variant.color}</span>
                <span style="font-size: 12px; color: ${needsRestock ? '#dc2626' : '#6b7280'}; background: ${needsRestock ? '#fee2e2' : '#f3f4f6'}; padding: 2px 8px; border-radius: 4px;">Qty: ${variantQty}</span>
                ${needsRestock ? '<span style="font-size: 11px; color: #dc2626; font-weight: 600;">⚠️ Needs Restock</span>' : ''}
              </div>
              <div style="font-size: 12px; color: #6b7280; font-family: monospace;">${variant.sku}</div>
            </div>
            <div style="display: flex; align-items: center; gap: 12px;">
              <div style="display: flex; align-items: center; gap: 6px;">
                <label style="font-size: 12px; color: #6b7280; white-space: nowrap;">Print:</label>
                <input type="number" min="0" value="" placeholder="0" 
                    onchange="setPrintQuantity('${variant.sku}', this.value)"
                    oninput="setPrintQuantity('${variant.sku}', this.value)"
                    class="print-quantity-input"
                    style="width: 60px; padding: 6px 8px; border: 2px solid #e5e7eb; border-radius: 4px; font-size: 13px; text-align: center;">
              </div>
              <div style="margin-left: 8px;">
                <svg id="${uniqueId}" class="barcode-svg"></svg>
              </div>
              <button type="button" onclick="printSingleBarcode('${variant.sku}', '${uniqueId}', true)"
                  style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">
                  Print
              </button>
            </div>
          </div>
        `;
      });
    } else if (!hasSize) {
      // Simple product (no sizes) - show base SKU with stock
      const uniqueId = "barcode-" + index;
      const simpleProductNeedsRestock = sizeStock === 0;
      
      colorVariantsHtml = `
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: white; border-radius: 6px; border: 1px solid ${simpleProductNeedsRestock ? '#fca5a5' : '#e5e7eb'}; flex-wrap: wrap; gap: 12px;">
          <div style="flex: 1; min-width: 200px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
              <span style="font-weight: 600; color: #374151;">Simple Product</span>
              <span style="font-size: 12px; color: ${simpleProductNeedsRestock ? '#dc2626' : '#6b7280'}; background: ${simpleProductNeedsRestock ? '#fee2e2' : '#f3f4f6'}; padding: 2px 8px; border-radius: 4px;">Qty: ${sizeStock}</span>
              ${simpleProductNeedsRestock ? '<span style="font-size: 11px; color: #dc2626; font-weight: 600;">⚠️ Needs Restock</span>' : ''}
            </div>
            <div style="font-size: 12px; color: #6b7280; font-family: monospace;">${sizeData.sku}</div>
          </div>
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <label style="font-size: 12px; color: #6b7280; white-space: nowrap;">Print:</label>
              <input type="number" min="0" value="" placeholder="0" 
                  onchange="setPrintQuantity('${sizeData.sku}', this.value)"
                  oninput="setPrintQuantity('${sizeData.sku}', this.value)"
                  class="print-quantity-input"
                  style="width: 60px; padding: 6px 8px; border: 2px solid #e5e7eb; border-radius: 4px; font-size: 13px; text-align: center;">
            </div>
            <div style="margin-left: 8px;">
              <svg id="${uniqueId}" class="barcode-svg"></svg>
            </div>
            <button type="button" onclick="printSingleBarcode('${sizeData.sku}', '${uniqueId}', true)"
                style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">
                Print
            </button>
          </div>
        </div>
      `;
    } else {
      // No color variants - just show size
      const uniqueId = "barcode-" + index;
      const noColorWarning = sizeStock === 0 ? '<span style="font-size: 11px; color: #dc2626; font-weight: 600; margin-left: 8px;">⚠️ No colors set - Needs Restock</span>' : '<span style="font-size: 11px; color: #f59e0b; font-weight: 600; margin-left: 8px;">⚠️ No colors set</span>';
      
      colorVariantsHtml = `
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: white; border-radius: 6px; border: 1px solid ${needsSizeRestock ? '#fca5a5' : '#e5e7eb'}; flex-wrap: wrap; gap: 12px;">
          <div style="flex: 1; min-width: 200px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
              <span style="font-weight: 600; color: #374151;">Default</span>
              <span style="font-size: 12px; color: ${needsSizeRestock ? '#dc2626' : '#6b7280'}; background: ${needsSizeRestock ? '#fee2e2' : '#f3f4f6'}; padding: 2px 8px; border-radius: 4px;">Qty: ${sizeStock}</span>
              ${noColorWarning}
            </div>
            <div style="font-size: 12px; color: #6b7280; font-family: monospace;">${sizeData.sku}</div>
          </div>
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 6px;">
              <label style="font-size: 12px; color: #6b7280; white-space: nowrap;">Print:</label>
              <input type="number" min="0" value="" placeholder="0" 
                  onchange="setPrintQuantity('${sizeData.sku}', this.value)"
                  oninput="setPrintQuantity('${sizeData.sku}', this.value)"
                  class="print-quantity-input"
                  style="width: 60px; padding: 6px 8px; border: 2px solid #e5e7eb; border-radius: 4px; font-size: 13px; text-align: center;">
            </div>
            <div style="margin-left: 8px;">
              <svg id="${uniqueId}" class="barcode-svg"></svg>
            </div>
            <button type="button" onclick="printSingleBarcode('${sizeData.sku}', '${uniqueId}', true)"
                style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px;">
                Print
            </button>
          </div>
        </div>
      `;
    }
    
    // Add size-level warning if size has 0 stock (only for products with sizes)
    let sizeWarningHtml = '';
    if (hasSize && needsSizeRestock) {
      sizeWarningHtml = `
        <div style="margin-bottom: 12px; padding: 8px 12px; background: #fee2e2; border: 1px solid #fca5a5; border-radius: 6px;">
          <span style="font-size: 12px; color: #dc2626; font-weight: 600;">
            ⚠️ This size is out of stock. Please add quantity and colors.
          </span>
        </div>
      `;
    } else if (hasSize && colorVariants.length === 0) {
      // Has size but no colors set
      sizeWarningHtml = `
        <div style="margin-bottom: 12px; padding: 8px 12px; background: #fef3c7; border: 1px solid #fcd34d; border-radius: 6px;">
          <span style="font-size: 12px; color: #92400e; font-weight: 600;">
            ⚠️ No colors set for this size. Please add colors.
          </span>
        </div>
      `;
    }
    
    // For simple products, don't show size label
    const sizeLabelHtml = hasSize ? `
      <div style="margin-bottom: 12px;">
        <label style="font-weight: 600; font-size: 14px; color: #374151;">
          Size: ${sizeData.size}
        </label>
      </div>
    ` : '';
    
    sizeDiv.innerHTML = `
      ${sizeLabelHtml}
      ${sizeWarningHtml}
      <div>
        ${colorVariantsHtml}
      </div>
    `;
    
    container.appendChild(sizeDiv);
    
    // Generate barcodes after adding to DOM
    setTimeout(() => {
      // Generate barcode for each variant
      if (colorVariants.length > 0) {
        colorVariants.forEach((variant, vIndex) => {
          const uniqueId = "barcode-" + index + "-" + vIndex;
          try {
            JsBarcode("#" + uniqueId, variant.sku, {
              format: "CODE128",
              width: 1,
              height: 25,
              displayValue: true,
              fontSize: 9,
              margin: 0
            });
          } catch (e) {
            console.error("Error generating barcode for", variant.sku, e);
          }
        });
      } else {
        const uniqueId = "barcode-" + index;
        try {
          JsBarcode("#" + uniqueId, sizeData.sku, {
            format: "CODE128",
            width: 1,
            height: 25,
            displayValue: true,
            fontSize: 9,
            margin: 0
          });
        } catch (e) {
          console.error("Error generating barcode for", sizeData.sku, e);
        }
      }
    }, 100);
  });
}

function printSingleBarcode(sku, elementId, useQuantity = false) {
  // Create a temporary container for printing
  const tempContainer = document.createElement("div");
  const svg = document.createElement("svg");
  
  // Determine how many copies to print
  let copies = 1;
  if (useQuantity) {
    const inputQty = printQuantities[sku];
    if (inputQty && inputQty > 0) {
      copies = inputQty;
    }
  }
  
  try {
    // Use mid size for printing
    JsBarcode(svg, sku, {
      format: "CODE128",
      width: 2,
      height: 50,
      displayValue: true,
      fontSize: 12,
      margin: 0
    });
    
    tempContainer.appendChild(svg);
    
    const printWindow = window.open("", "_blank");
    
    // Generate HTML for all copies
    let copiesHtml = '';
    for (let i = 0; i < copies; i++) {
      copiesHtml += `
        <div class="barcode-container">
          <div class="barcode">${svg.outerHTML}</div>
        </div>
      `;
    }
    
    printWindow.document.write(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Barcode - ${sku}</title>
        <style>
          body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
            margin: 0;
          }
          .barcode-container {
            display: inline-block;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background: white;
            margin: 10px;
          }
          .barcode svg {
            width: 250px;
            height: auto;
            max-width: 100%;
          }
          @media print {
            body { margin: 0; }
            .barcode-container { border: none; padding: 10px; page-break-inside: avoid; }
          }
        </style>
      </head>
      <body>
        ${copiesHtml}
        <script>
          window.onload = function() {
            window.print();
            setTimeout(function() {
              window.close();
            }, 100);
          }
        <\/script>
      </body>
      </html>
    `);
    printWindow.document.close();
  } catch (e) {
    console.error("Error printing barcode:", e);
    alert("Error generating barcode");
  }
}

function printAllBarcodes() {
  const container = document.getElementById("sizeVariantsContainer");
  if (!container || container.children.length === 0) {
    alert("No barcodes to print");
    return;
  }
  
  // Check if any quantities are set
  const totalSelected = Object.values(printQuantities).reduce((sum, qty) => sum + (qty || 0), 0);
  if (totalSelected === 0) {
    alert("Please set the number of barcodes to print for each variant using the quantity inputs.");
    return;
  }
  
  let htmlContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>All Barcodes</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          padding: 20px;
          margin: 0;
        }
        .barcode-page {
          page-break-after: always;
          display: flex;
          flex-wrap: wrap;
          gap: 15px;
          justify-content: center;
        }
        .barcode-page:last-child {
          page-break-after: avoid;
        }
        .barcode-item {
          display: inline-block;
          padding: 15px;
          border: 1px solid #ccc;
          border-radius: 8px;
          background: white;
          text-align: center;
          margin: 8px;
        }
        .barcode-item svg {
          width: 250px;
          height: auto;
          max-width: 100%;
        }
        .barcode-label {
          font-size: 10px;
          color: #666;
          margin-top: 5px;
        }
        @media print {
          body { margin: 0; }
          .barcode-item { border: none; padding: 10px; page-break-inside: avoid; }
        }
      </style>
    </head>
    <body>
  `;
  
  let barcodeCount = 0;
  const barcodesPerPage = 10;
  let pageContent = '<div class="barcode-page">';
  
  // Add all variant barcodes based on selected quantities
  currentProductVariants.forEach((sizeData) => {
    const colorVariants = sizeData.color_variants || [];
    
    if (colorVariants.length > 0) {
      colorVariants.forEach((variant) => {
        const quantity = printQuantities[variant.sku] || 0;
        
        if (quantity > 0) {
          const tempSvg = document.createElement("svg");
          try {
            // Use mid size for printing
            JsBarcode(tempSvg, variant.sku, {
              format: "CODE128",
              width: 2,
              height: 50,
              displayValue: true,
              fontSize: 12,
              margin: 0
            });
            
            // Add requested quantity of barcodes
            for (let q = 0; q < quantity; q++) {
              // Check if we need a new page
              if (barcodeCount > 0 && barcodeCount % barcodesPerPage === 0) {
                pageContent += '</div><div class="barcode-page">';
              }
              
              pageContent += `
                <div class="barcode-item">
                  <div>${tempSvg.outerHTML}</div>
                  <div class="barcode-label">${sizeData.size} - ${variant.color}</div>
                </div>
              `;
              barcodeCount++;
            }
          } catch (e) {
            console.error("Error generating variant barcode:", e);
          }
        }
      });
    } else {
      const quantity = printQuantities[sizeData.sku] || 0;
      
      if (quantity > 0) {
        const tempSvg = document.createElement("svg");
        try {
          // Use mid size for printing
          JsBarcode(tempSvg, sizeData.sku, {
            format: "CODE128",
            width: 2,
            height: 50,
            displayValue: true,
            fontSize: 12,
            margin: 0
          });
          
          // Add requested quantity of barcodes
          for (let q = 0; q < quantity; q++) {
            if (barcodeCount > 0 && barcodeCount % barcodesPerPage === 0) {
              pageContent += '</div><div class="barcode-page">';
            }
            
            pageContent += `
              <div class="barcode-item">
                <div>${tempSvg.outerHTML}</div>
                <div class="barcode-label">${sizeData.size || 'Default'}</div>
              </div>
            `;
            barcodeCount++;
          }
        } catch (e) {
          console.error("Error generating size barcode:", e);
        }
      }
    }
  });
  
  if (barcodeCount === 0) {
    alert("Please set the number of barcodes to print for each variant using the quantity inputs.");
    return;
  }
  
  pageContent += '</div>';
  htmlContent += pageContent;
  htmlContent += `
      <script>
        window.onload = function() {
          window.print();
        }
      <\/script>
    </body>
    </html>
  `;
  
  const printWindow = window.open("", "_blank");
  printWindow.document.write(htmlContent);
  printWindow.document.close();
}
</script>
