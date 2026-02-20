<!-- Add Quantity Modal -->
<div class="modal-overlay" id="addQuantityModalOverlay" onclick="closeAddQuantityModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 500px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="closeAddQuantityModal()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #111827;">Add Quantity</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                Increase the stock quantity for each size
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
                    <!-- Sizes will be dynamically inserted here -->
                </div>

                <!-- Error State -->
                <div id="sizesErrorState" style="text-align: center; padding: 20px; display: none;">
                    <p style="color: #ef4444; font-size: 15px;">Failed to load product sizes. Please try again.</p>
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

  // Use the full SKU as base SKU
  const baseSku = sku;
  
  document.getElementById("addQuantityBaseSku").value = baseSku;
  document.getElementById("addQuantityModalOverlay").style.display = "flex";
  
  // Show loading state
  document.getElementById("sizesLoadingState").style.display = "block";
  document.getElementById("sizesContainer").style.display = "none";
  document.getElementById("sizesErrorState").style.display = "none";
  
  // Fetch sizes for this product
  fetchProductSizes(baseSku);
}

function fetchProductSizes(baseSku) {
  fetch("../../back-end/read/getSizes.php?sku=" + encodeURIComponent(baseSku))
    .then((response) => {
      // Log the raw response
      console.log("Response status:", response.status);
      console.log("Response headers:", response.headers);
      
      return response.text(); // Get as text first to see what we're receiving
    })
    .then((text) => {
      console.log("Raw response:", text); // Debug: see what we're actually getting
      
      try {
        const data = JSON.parse(text);
        console.log("Parsed JSON:", data);
        
        if (data.success && data.sizes && data.sizes.length > 0) {
          renderSizeInputs(data.sizes);
          document.getElementById("sizesLoadingState").style.display = "none";
          document.getElementById("sizesContainer").style.display = "block";
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

function renderSizeInputs(sizes) {
  const container = document.getElementById("sizesContainer");
  container.innerHTML = "";
  
  sizes.forEach((sizeData, index) => {
    const sizeDiv = document.createElement("div");
    sizeDiv.style.marginBottom = index < sizes.length - 1 ? "20px" : "0";
    
    sizeDiv.innerHTML = `
      <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
        Size: ${sizeData.size}
      </label>
      <input type="number"
             class="size-quantity-input"
             data-sku="${sizeData.sku}"
             data-size="${sizeData.size}"
             min="0"
             value="${sizeData.stock || 0}"
             placeholder="Current: ${sizeData.stock || 0}, enter quantity to add for ${sizeData.size}"
             style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s;"
             onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
             onblur="this.style.borderColor='#e5e7eb';">
    `;
    
    container.appendChild(sizeDiv);
  });
}

function addQuantity() {
  const form = document.getElementById("addQuantityForm");
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const sizeInputs = document.querySelectorAll(".size-quantity-input");
  const updates = [];
  
  sizeInputs.forEach((input) => {
    const amount = parseInt(input.value);
    if (amount > 0) {
      updates.push({
        sku: input.dataset.sku,
        size: input.dataset.size,
        amount: amount
      });
    }
  });
  
  if (updates.length === 0) {
    alert("Please enter at least one quantity to add");
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
    
    // Show success message
    const successMessage = document.getElementById("successMessage");
    const successText = successMessage.querySelector(".success-text");
    successText.textContent = "Quantities Added Successfully!";
    successMessage.style.display = "block";

    setTimeout(() => {
      successMessage.style.display = "none";
    }, 3000);

    closeAddQuantityModal();

    // Reload products to get updated stock values
    location.reload();
    return;
  }
  
  const update = updates[index];
  
  fetch("../../back-end/update/addQuantity.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: "sku=" + encodeURIComponent(update.sku) + "&amount=" + encodeURIComponent(update.amount),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update the local products array
        const productIndex = products.findIndex((p) => p.sku === update.sku);
        if (productIndex !== -1) {
          products[productIndex].stock += update.amount;
          // Update status
          if (products[productIndex].stock === 0) {
            products[productIndex].status = "Out of Stock";
          } else if (products[productIndex].stock <= 10) {
            products[productIndex].status = "Low Stock";
          } else {
            products[productIndex].status = "In Stock";
          }
          localStorage.setItem("inventoryProducts", JSON.stringify(products));
        }
        
        // Process next update
        processQuantityUpdates(updates, index + 1, submitBtn);
      } else {
        submitBtn.disabled = false;
        submitBtn.style.opacity = "1";
        submitBtn.style.cursor = "pointer";
        alert("Error adding quantity for size " + update.size + ": " + (data.message || "Unknown error"));
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      submitBtn.disabled = false;
      submitBtn.style.opacity = "1";
      submitBtn.style.cursor = "pointer";
      alert("Error adding quantity for size " + update.size);
    });
}
</script>