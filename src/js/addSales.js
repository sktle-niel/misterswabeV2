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
      console.log("Products loaded:", products.length);
    } catch (error) {
      console.error("Error fetching products:", error);
      products = [];
    }
  }

  // Lookup product by SKU and fill form
  function lookupProductBySKU(sku, row) {
    if (!sku || sku.trim() === "") {
      return;
    }

    const product = products.find((p) => p.sku === sku.trim());

    if (product) {
      // Fill in the product details
      row.querySelector(".product-id").value = product.id;
      row.querySelector('input[name*="[price]"]').value = product.price;

      // Populate size options
      const sizeSelect = row.querySelector(".product-size");
      sizeSelect.innerHTML = '<option value="">Select Size</option>';

      // Fetch sizes from getSizes.php
      fetch(`../../back-end/read/getSizes.php?sku=${encodeURIComponent(sku)}`)
        .then((response) => response.json())
        .then((data) => {
          if (data.success && data.sizes.length > 0) {
            data.sizes.forEach((sizeData) => {
              if (sizeData.stock > 0) {
                const option = document.createElement("option");
                option.value = sizeData.size;
                option.textContent = `${sizeData.size} (${sizeData.stock})`;
                sizeSelect.appendChild(option);
              }
            });
            // Make size required if sizes are available
            if (sizeSelect.options.length > 1) {
              sizeSelect.setAttribute("required", "required");
              sizeSelect.dataset.hasSizes = "true";
            } else {
              // No sizes with stock available
              sizeSelect.removeAttribute("required");
              sizeSelect.dataset.hasSizes = "false";
            }
          } else {
            // No sizes available, make size optional and hide
            sizeSelect.removeAttribute("required");
            sizeSelect.dataset.hasSizes = "false";
            sizeSelect.closest(".form-group").style.display = "none";
            console.log("No sizes available for this product");
          }
        })
        .catch((error) => {
          console.error("Error fetching sizes:", error);
          // On error, make size optional and hide
          sizeSelect.removeAttribute("required");
          sizeSelect.dataset.hasSizes = "false";
          sizeSelect.closest(".form-group").style.display = "none";
        });

      // Display product name
      const nameDisplay = row.querySelector(".product-name-display");
      if (nameDisplay) {
        nameDisplay.textContent = product.name;
        nameDisplay.style.color = "green";
        nameDisplay.style.fontWeight = "bold";
      }

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

      const sizeSelect = row.querySelector(".product-size");
      sizeSelect.innerHTML = '<option value="">Select Size</option>';
      sizeSelect.removeAttribute("required");
      sizeSelect.dataset.hasSizes = "false";

      const nameDisplay = row.querySelector(".product-name-display");
      if (nameDisplay) {
        nameDisplay.textContent = "Product not found";
        nameDisplay.style.color = "red";
      }

      // Visual feedback
      const skuInput = row.querySelector(".product-sku");
      skuInput.style.borderColor = "red";
      setTimeout(() => {
        skuInput.style.borderColor = "";
      }, 2000);
    }
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
          console.log("Barcode detected:", code);

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
        </div>
        <div class="form-group">
            <label>Quantity</label>
            <input type="number" name="products[${rowCount}][quantity]" min="1" value="1" required>
        </div>
        <div class="form-group">
            <label>Size</label>
            <select name="products[${rowCount}][size]" class="product-size">
                <option value="">Select Size</option>
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
  document.addEventListener("DOMContentLoaded", function () {
    // Load products on page load
    loadProducts();

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

    // Quantity change - update total
    document.addEventListener("change", function (e) {
      if (e.target.name && e.target.name.includes("[quantity]")) {
        updateTotal();
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

        // Validate that all products have valid IDs
        const rows = document.querySelectorAll(".product-row");
        let isValid = true;
        let errorMessage = "";

        rows.forEach((row, index) => {
          const productId = row.querySelector(".product-id").value;
          const sizeSelect = row.querySelector(".product-size");
          const skuInput = row.querySelector(".product-sku");

          // Check if product is valid
          if (!productId) {
            isValid = false;
            errorMessage = "Please ensure all products are valid";
            skuInput.style.borderColor = "red";
            return;
          }

          // Check if size is required and not selected
          if (
            sizeSelect.hasAttribute("required") &&
            sizeSelect.dataset.hasSizes === "true" &&
            !sizeSelect.value
          ) {
            isValid = false;
            errorMessage = `Please select a size for product in row ${index + 1}`;
            sizeSelect.style.borderColor = "red";
            setTimeout(() => {
              sizeSelect.style.borderColor = "";
            }, 3000);
            return;
          }
        });

        if (!isValid) {
          showInvalidMessage();
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

            document.querySelectorAll(".product-size").forEach((select) => {
              select.innerHTML = '<option value="">Select Size</option>';
              select.removeAttribute("required");
              select.dataset.hasSizes = "false";
            });

            updateTotal();
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

        // Reset size selects
        document.querySelectorAll(".product-size").forEach((select) => {
          select.innerHTML = '<option value="">Select Size</option>';
          select.removeAttribute("required");
          select.dataset.hasSizes = "false";
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
