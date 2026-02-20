let selectedBarcodes = []; // Will store objects like {sku: "ABC123", quantity: 1}

function renderProducts(productsToRender) {
  const tbody = document.getElementById("products-tbody");
  tbody.innerHTML = "";

  productsToRender.forEach((product) => {
    const row = document.createElement("tr");

    const stockClass =
      product.stock === 0
        ? "var(--accent-danger)"
        : product.stock <= 10
          ? "var(--accent-warning)"
          : "";
    const statusClass =
      product.status === "In Stock"
        ? "badge-success"
        : product.status === "Low Stock"
          ? "badge-warning"
          : "badge-danger";

    row.innerHTML = `
            <td>
                <img src="${product.image}" alt="${product.name}" style="width: 50px; height: 50px; border-radius: var(--radius-md); object-fit: cover; cursor: pointer;" onclick="previewImage('${product.image}')">
            </td>
            <td style="font-weight: 600; color: var(--text-primary);">${product.name}</td>
            <td style="color: var(--text-muted); font-size: 0.875rem;"><canvas id="barcode-${product.sku}" style="width: 250px; height: 60px;"></canvas></td>
            <td><span class="badge badge-info">${product.category}</span></td>
            <td>
                <button class="btn btn-icon btn-secondary" title="Print Barcode" onclick="printProductBarcode('${product.sku}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                        <rect x="6" y="14" width="12" height="8"></rect>
                    </svg>
                </button>
            </td>
        `;

    tbody.appendChild(row);
  });
}

function filterProducts() {
  const searchFilter = document
    .getElementById("search-filter")
    .value.toLowerCase()
    .trim();
  const categoryFilter = document
    .getElementById("category-filter")
    .value.toLowerCase()
    .trim();

  let filteredProducts = products;

  // Filter by search term (name or SKU)
  if (searchFilter) {
    filteredProducts = filteredProducts.filter(
      (product) =>
        product.name.toLowerCase().includes(searchFilter) ||
        product.sku.toLowerCase().includes(searchFilter),
    );
  }

  // Filter by category
  if (categoryFilter) {
    filteredProducts = filteredProducts.filter(
      (product) =>
        product.category && product.category.toLowerCase() === categoryFilter,
    );
  }

  renderProducts(filteredProducts);
  generateBarcodes(filteredProducts);
}

function clearFilters() {
  document.getElementById("search-filter").value = "";
  document.getElementById("category-filter").value = "";
  renderProducts(products);
  generateBarcodes(products);
}

function previewImage(imageUrl) {
  // Create modal overlay
  const modal = document.createElement("div");
  modal.style.position = "fixed";
  modal.style.top = "0";
  modal.style.left = "0";
  modal.style.width = "100%";
  modal.style.height = "100%";
  modal.style.backgroundColor = "transparent";
  modal.style.display = "flex";
  modal.style.justifyContent = "center";
  modal.style.alignItems = "center";
  modal.style.zIndex = "1000";
  modal.style.animation = "fadeIn 0.3s ease-in-out";

  // Create modal content container
  const modalContent = document.createElement("div");
  modalContent.style.position = "relative";
  modalContent.style.maxWidth = "90%";
  modalContent.style.maxHeight = "90%";
  modalContent.style.overflow = "hidden";
  modalContent.style.animation = "zoomIn 0.3s ease-in-out";

  // Create image element
  const img = document.createElement("img");
  img.src = imageUrl;
  img.style.width = "100%";
  img.style.height = "auto";
  img.style.maxHeight = "80vh";
  img.style.objectFit = "contain";
  img.style.display = "block";
  img.style.borderRadius = "var(--radius-md)";

  // Create close button
  const closeBtn = document.createElement("button");
  closeBtn.innerHTML = "×";
  closeBtn.style.position = "absolute";
  closeBtn.style.top = "15px";
  closeBtn.style.right = "15px";
  closeBtn.style.color = "white";
  closeBtn.style.fontSize = "32px";
  closeBtn.style.cursor = "pointer";
  closeBtn.style.background = "none";
  closeBtn.style.border = "none";

  // Close modal on click outside image
  modal.addEventListener("click", () => {
    modal.style.animation = "fadeOut 0.3s ease-in-out";
    modalContent.style.animation = "zoomOut 0.3s ease-in-out";
    setTimeout(() => {
      document.body.removeChild(modal);
    }, 300);
  });

  closeBtn.addEventListener("click", (e) => {
    e.stopPropagation();
    modal.style.animation = "fadeOut 0.3s ease-in-out";
    modalContent.style.animation = "zoomOut 0.3s ease-in-out";
    setTimeout(() => {
      document.body.removeChild(modal);
    }, 300);
  });

  // Prevent modal close when clicking on image
  img.addEventListener("click", (e) => {
    e.stopPropagation();
  });

  modalContent.appendChild(img);
  modalContent.appendChild(closeBtn);
  modal.appendChild(modalContent);
  document.body.appendChild(modal);

  // Add CSS animations
  const style = document.createElement("style");
  style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        @keyframes zoomIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @keyframes zoomOut {
            from { transform: scale(1); opacity: 1; }
            to { transform: scale(0.9); opacity: 0; }
        }
    `;
  document.head.appendChild(style);
}

// Function to open add product modal
function openProductModal(mode) {
  if (mode === "add") {
    document.getElementById("addProductModalOverlay").style.display = "flex";
  }
}

function printSkuBarcode() {
  const barcodeSvg = document.getElementById("barcode");
  if (!barcodeSvg) {
    console.error("Barcode element not found");
    return;
  }

  const printWindow = window.open("", "_blank");
  const barcodeHtml = barcodeSvg.outerHTML;

  printWindow.document.write(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Product Barcode</title>
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
        }
        .barcode svg {
          width: 300px;
          height: auto;
          max-width: 100%;
        }
        @media print {
          body { margin: 0; }
          .barcode-container { border: none; padding: 10px; }
        }
      </style>
    </head>
    <body>
      <div class="barcode-container">
        <div class="barcode">${barcodeHtml}</div>
      </div>
      <script>
        window.onload = function() {
          window.print();
          setTimeout(function() {
            window.close();
          }, 100);
        }
      </script>
    </body>
    </html>
  `);

  printWindow.document.close();
}

function printProductBarcode(sku) {
  const existingIndex = selectedBarcodes.findIndex((item) => item.sku === sku);
  if (existingIndex !== -1) {
    // Remove from selected
    selectedBarcodes.splice(existingIndex, 1);
  } else {
    // Add to selected with default quantity of 1
    selectedBarcodes.push({ sku: sku, quantity: 1 });
  }
  saveSelectedBarcodesToStorage();
  updatePrintBarcodeContainer();
}

function generateBarcodes(productsToRender) {
  productsToRender.forEach((product) => {
    const canvas = document.getElementById(`barcode-${product.sku}`);
    if (canvas) {
      JsBarcode(canvas, product.sku, {
        format: "CODE128",
        width: 2,
        height: 60,
        displayValue: true,
        fontSize: 14,
        margin: 0,
      });
    }
  });
}

function updatePrintBarcodeContainer() {
  const container = document.getElementById("print-barcode-container");
  if (!container) return;

  container.innerHTML = "";

  if (selectedBarcodes.length === 0) {
    container.innerHTML =
      "<p style='color: var(--text-muted); font-size: 0.875rem;'>No barcodes selected</p>";
    return;
  }

  selectedBarcodes.forEach((item, index) => {
    const barcodeDiv = document.createElement("div");
    barcodeDiv.style.marginBottom = "15px";
    barcodeDiv.style.padding = "10px";
    barcodeDiv.style.border = "1px solid var(--border-color)";
    barcodeDiv.style.borderRadius = "var(--radius-md)";
    barcodeDiv.style.backgroundColor = "var(--bg-primary)";

    // Barcode canvas
    const canvas = document.createElement("canvas");
    canvas.style.width = "180px";
    canvas.style.height = "auto";
    canvas.style.display = "block";
    canvas.style.marginBottom = "8px";
    JsBarcode(canvas, item.sku, {
      format: "CODE128",
      width: 2,
      height: 60,
      displayValue: true,
      fontSize: 14,
      margin: 0,
    });
    barcodeDiv.appendChild(canvas);

    // Quantity controls
    const quantityDiv = document.createElement("div");
    quantityDiv.style.display = "flex";
    quantityDiv.style.alignItems = "center";
    quantityDiv.style.justifyContent = "center";
    quantityDiv.style.gap = "8px";

    // Decrement button
    const decrementBtn = document.createElement("button");
    decrementBtn.className = "btn btn-icon btn-secondary";
    decrementBtn.style.width = "30px";
    decrementBtn.style.height = "30px";
    decrementBtn.style.padding = "0";
    decrementBtn.textContent = "-";
    decrementBtn.onclick = () => updateQuantity(index, item.quantity - 1);
    quantityDiv.appendChild(decrementBtn);

    // Quantity input
    const quantityInput = document.createElement("input");
    quantityInput.type = "number";
    quantityInput.min = "1";
    quantityInput.value = item.quantity;
    quantityInput.style.width = "60px";
    quantityInput.style.textAlign = "center";
    quantityInput.style.border = "1px solid var(--border-color)";
    quantityInput.style.borderRadius = "var(--radius-sm)";
    quantityInput.style.padding = "4px";
    quantityInput.onchange = (e) =>
      updateQuantity(index, parseInt(e.target.value) || 1);
    quantityDiv.appendChild(quantityInput);

    // Increment button
    const incrementBtn = document.createElement("button");
    incrementBtn.className = "btn btn-icon btn-secondary";
    incrementBtn.style.width = "30px";
    incrementBtn.style.height = "30px";
    incrementBtn.style.padding = "0";
    incrementBtn.textContent = "+";
    incrementBtn.onclick = () => updateQuantity(index, item.quantity + 1);
    quantityDiv.appendChild(incrementBtn);

    // Remove button
    const removeBtn = document.createElement("button");
    removeBtn.className = "btn btn-icon btn-danger";
    removeBtn.style.width = "30px";
    removeBtn.style.height = "30px";
    removeBtn.style.padding = "0";
    removeBtn.style.marginLeft = "8px";
    removeBtn.title = "Remove";
    removeBtn.innerHTML = "×";
    removeBtn.onclick = () => removeBarcode(index);
    quantityDiv.appendChild(removeBtn);

    barcodeDiv.appendChild(quantityDiv);
    container.appendChild(barcodeDiv);
  });

  // Add buttons container
  const buttonsContainer = document.createElement("div");
  buttonsContainer.style.display = "flex";
  buttonsContainer.style.flexDirection = "column";
  buttonsContainer.style.gap = "8px";
  buttonsContainer.style.marginTop = "10px";

  // Remove all button
  const removeAllBtn = document.createElement("button");
  removeAllBtn.className = "btn";
  removeAllBtn.style.width = "100%";
  removeAllBtn.style.backgroundColor = "#dc2626";
  removeAllBtn.style.color = "white";
  removeAllBtn.style.border = "1px solid #dc2626";
  removeAllBtn.style.textAlign = "center";
  removeAllBtn.style.padding = "12px";
  removeAllBtn.style.display = "block";
  removeAllBtn.textContent = "Remove All";
  removeAllBtn.onclick = removeAllBarcodes;
  buttonsContainer.appendChild(removeAllBtn);

  // Print all button
  const printAllBtn = document.createElement("button");
  printAllBtn.className = "btn";
  printAllBtn.style.width = "100%";
  printAllBtn.style.backgroundColor = "black";
  printAllBtn.style.color = "white";
  printAllBtn.style.border = "1px solid black";
  printAllBtn.style.textAlign = "center";
  printAllBtn.style.padding = "12px";
  printAllBtn.style.display = "block";
  printAllBtn.textContent = "Print All Selected Barcodes";
  printAllBtn.onclick = printAllSelectedBarcodes;
  buttonsContainer.appendChild(printAllBtn);

  container.appendChild(buttonsContainer);
}

function updateQuantity(index, newQuantity) {
  if (newQuantity < 1) newQuantity = 1;
  selectedBarcodes[index].quantity = newQuantity;
  saveSelectedBarcodesToStorage();
  updatePrintBarcodeContainer();
}

function removeBarcode(index) {
  selectedBarcodes.splice(index, 1);
  saveSelectedBarcodesToStorage();
  updatePrintBarcodeContainer();
}

function removeAllBarcodes() {
  selectedBarcodes = [];
  saveSelectedBarcodesToStorage();
  updatePrintBarcodeContainer();
}

function saveSelectedBarcodesToStorage() {
  localStorage.setItem("selectedBarcodes", JSON.stringify(selectedBarcodes));
}

function loadSelectedBarcodesFromStorage() {
  const stored = localStorage.getItem("selectedBarcodes");
  if (stored) {
    selectedBarcodes = JSON.parse(stored);
  }
}

function printAllSelectedBarcodes() {
  if (selectedBarcodes.length === 0) return;

  const printWindow = window.open("", "_blank");
  let htmlContent = `
    <!DOCTYPE html>
    <html>
    <head>
      <title>Selected Product Barcodes</title>
      <style>
        body {
          font-family: Arial, sans-serif;
          padding: 20px;
          margin: 0;
        }
        .barcode-container {
          display: inline-block;
          margin: 10px;
          padding: 10px;
          border: 1px solid #ccc;
          border-radius: 8px;
          background: white;
        }
        .barcode svg {
          width: 200px;
          height: auto;
        }
        @media print {
          body { margin: 0; }
          .barcode-container { border: none; margin: 5px; padding: 5px; }
        }
      </style>
    </head>
    <body>
  `;

  // Create a temporary container to draw SVGs
  const tempContainer = document.createElement("div");
  tempContainer.style.display = "none";
  document.body.appendChild(tempContainer);

  selectedBarcodes.forEach((item) => {
    // Print the specified quantity of each barcode
    for (let i = 0; i < item.quantity; i++) {
      const tempSvg = document.createElementNS(
        "http://www.w3.org/2000/svg",
        "svg",
      );
      tempContainer.appendChild(tempSvg);
      JsBarcode(tempSvg, item.sku, {
        format: "CODE128",
        width: 3,
        height: 80,
        displayValue: true,
        fontSize: 16,
        margin: 10,
      });
      htmlContent += `<div class="barcode-container"><div class="barcode">${tempSvg.outerHTML}</div></div>`;
      tempContainer.removeChild(tempSvg);
    }
  });

  document.body.removeChild(tempContainer);

  htmlContent += `
      <script>
        window.onload = function() {
          window.print();
          setTimeout(function() {
            window.close();
          }, 100);
        }
      </script>
    </body>
    </html>
  `;

  printWindow.document.write(htmlContent);
  printWindow.document.close();
}

document.addEventListener("DOMContentLoaded", () => {
  // Load selected barcodes from localStorage
  loadSelectedBarcodesFromStorage();

  // Load temporary changes from localStorage
  window.temporaryChanges =
    JSON.parse(localStorage.getItem("temporaryChanges")) || [];

  // Apply temporary changes to products array
  window.temporaryChanges.forEach((change) => {
    const productIndex = products.findIndex(
      (p) => p.sku === change.originalSku,
    );
    if (productIndex !== -1) {
      products[productIndex] = { ...products[productIndex], ...change };
      delete products[productIndex].originalSku;
    }
  });

  renderProducts(products);
  generateBarcodes(products);
  updatePrintBarcodeContainer();

  // Add event listeners for filters
  document
    .getElementById("search-filter")
    .addEventListener("input", filterProducts);
  document
    .getElementById("category-filter")
    .addEventListener("change", filterProducts);

  const activitySearch = document.getElementById("activity-search");
  if (activitySearch) {
    activitySearch.addEventListener("input", filterActivities);
  }

  document
    .querySelector(".table-container")
    .addEventListener("scroll", handleScroll);
});
