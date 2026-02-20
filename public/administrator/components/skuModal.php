<!-- SKU View Modal -->
<div class="modal-overlay" id="skuModalOverlay" onclick="closeSkuModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 800px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="closeSkuModal()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #111827;">Product SKU</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                View the SKU for this product
            </p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 40px;">
            <div style="display: grid; grid-template-columns: 1fr; gap: 24px;">
                <!-- Product SKU -->
                <div>
                    <label for="productSku" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                        Product SKU
                    </label>
                    <input type="text" id="productSku" readonly
                        style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; background: #f9fafb; color: #6b7280;">
                </div>

                <!-- Product Barcode -->
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                        Product Barcode
                    </label>
                    <svg id="barcode"></svg>
                </div>
            </div>

            <!-- Modal Footer -->
            <div style="display: flex; gap: 12px; justify-content: space-between; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="button" onclick="printSkuBarcode()"
                    style="padding: 12px 28px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);"
                    onmouseover="this.style.background='#2563eb'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'"
                    onmouseout="this.style.background='#3b82f6'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'">
                    <span style="display: inline-flex; align-items: center; gap: 8px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect>
                        </svg>
                        Print
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
</div>

<script>
// SKU Modal Functions
function closeSkuModal() {
  document.getElementById("skuModalOverlay").style.display = "none";
}

function closeSkuModalOnOverlay(event) {
  if (event.target === document.getElementById("skuModalOverlay")) {
    closeSkuModal();
  }
}

function openSkuModal(sku) {
  const productSkuElement = document.getElementById("productSku");
  const overlay = document.getElementById("skuModalOverlay");

  if (!productSkuElement || !overlay) {
    console.error("SKU Modal elements not found in DOM");
    return;
  }

  productSkuElement.value = sku;

  // Generate barcode from SKU
  JsBarcode("#barcode", sku, {
    format: "CODE128",
    width: 2,
    height: 60,
    displayValue: true,
    fontSize: 14,
    margin: 0
  });

  overlay.style.display = "flex";
}
</script>
