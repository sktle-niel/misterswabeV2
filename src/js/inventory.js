let currentPage = 1;
const itemsPerPage = 7;

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

    // Format size quantities for display
    let sizeQuantitiesDisplay = "N/A";
    if (
      product.size_quantities &&
      product.size_quantities !== "null" &&
      product.size_quantities !== "{}"
    ) {
      try {
        const sizeQuantities =
          typeof product.size_quantities === "string"
            ? JSON.parse(product.size_quantities)
            : product.size_quantities;
        const formattedQuantities = Object.entries(sizeQuantities)
          .filter(([size, qty]) => qty > 0) // Only show sizes with quantity > 0
          .map(([size, qty]) => `${size}: ${qty}`)
          .join(", ");
        sizeQuantitiesDisplay = formattedQuantities || "N/A";
      } catch (e) {
        console.log(
          "Error parsing size_quantities:",
          product.size_quantities,
          e,
        );
        sizeQuantitiesDisplay = "N/A";
      }
    }

    row.innerHTML = `
            <td>
                <img src="${product.image}" alt="${product.name}" style="width: 50px; height: 50px; border-radius: var(--radius-md); object-fit: cover; cursor: pointer;" onclick="previewImage('${product.image}')">
            </td>
            <td style="font-weight: 600; color: var(--text-primary);">${product.name}</td>
            <td><span class="badge badge-info">${product.category}</span></td>
            <td style="font-weight: 600;">${product.price}</td>
            <td style="${stockClass ? `color: ${stockClass}; font-weight: 600;` : ""}">${product.stock}</td>
            <td>${product.size || "N/A"}</td>
            <td style="font-size: 0.875rem;">${sizeQuantitiesDisplay}</td>
            <td style="font-size: 0.875rem;">${product.color || "N/A"}</td>
            <td><span class="badge ${statusClass}">${product.status}</span></td>
            <td>
                <div style="display: flex; gap: var(--spacing-xs);">
                    <button class="btn btn-icon btn-secondary" title="View" data-image="${product.image}" onclick="previewImage(this.dataset.image)">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                    <button class="btn btn-icon btn-secondary" title="Add Quantity" ${product.size === "N/A" ? 'disabled style="opacity: 0.5; cursor: not-allowed;"' : ""} onclick="${product.size !== "N/A" ? `openAddQuantityModal('${product.sku}')` : ""}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </button>
                    <button class="btn btn-icon btn-secondary" title="Actions" onclick="showActionsMenu(event, '${product.sku}')" style="font-size: 18px; font-weight: bold; padding: 4px 8px;">
                        ⋯
                    </button>
                </div>
            </td>
        `;

    tbody.appendChild(row);
  });
}

function renderPagination(totalItems, currentPage, itemsPerPage) {
  const totalPages = Math.ceil(totalItems / itemsPerPage);
  const paginationContainer = document.getElementById("pagination-container");

  if (totalPages <= 1) {
    paginationContainer.innerHTML = "";
    return;
  }

  let paginationHTML =
    '<div style="display: flex; justify-content: center; align-items: center; gap: var(--spacing-sm); margin-top: var(--spacing-lg);">';

  // Previous button
  if (currentPage > 1) {
    paginationHTML += `<button class="btn btn-secondary" onclick="changePage(${currentPage - 1})">Previous</button>`;
  }

  // Page numbers
  const startPage = Math.max(1, currentPage - 2);
  const endPage = Math.min(totalPages, currentPage + 2);

  if (startPage > 1) {
    paginationHTML += `<button class="btn btn-secondary" onclick="changePage(1)">1</button>`;
    if (startPage > 2) {
      paginationHTML += "<span>...</span>";
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    const isActive = i === currentPage;
    const buttonStyle = isActive
      ? "background: black; color: white; border: 1px solid black;"
      : "";
    paginationHTML += `<button class="btn btn-secondary" style="${buttonStyle}" onclick="changePage(${i})">${i}</button>`;
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      paginationHTML += "<span>...</span>";
    }
    paginationHTML += `<button class="btn btn-secondary" onclick="changePage(${totalPages})">${totalPages}</button>`;
  }

  // Next button
  if (currentPage < totalPages) {
    paginationHTML += `<button class="btn btn-secondary" onclick="changePage(${currentPage + 1})">Next</button>`;
  }

  paginationHTML += "</div>";
  paginationContainer.innerHTML = paginationHTML;
}

function changePage(page) {
  currentPage = page;
  filterProducts();
}

function getPaginatedProducts(products) {
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  return products.slice(startIndex, endIndex);
}

function filterProducts() {
  const searchFilter = document
    .getElementById("search-filter")
    .value.toLowerCase();
  const categoryFilter = document.getElementById("category-filter").value;
  const statusFilter = document.getElementById("status-filter").value;

  let filteredProducts = products;

  if (searchFilter) {
    filteredProducts = filteredProducts.filter(
      (product) =>
        product.name.toLowerCase().includes(searchFilter) ||
        product.sku.toLowerCase().includes(searchFilter),
    );
  }

  if (categoryFilter) {
    filteredProducts = filteredProducts.filter(
      (product) => product.category.toLowerCase() === categoryFilter,
    );
  }

  if (statusFilter) {
    const statusMap = {
      "in-stock": "In Stock",
      "low-stock": "Low Stock",
      "out-of-stock": "Out of Stock",
    };
    filteredProducts = filteredProducts.filter(
      (product) => product.status === statusMap[statusFilter],
    );
  }

  const paginatedProducts = getPaginatedProducts(filteredProducts);
  renderProducts(paginatedProducts);
  renderPagination(filteredProducts.length, currentPage, itemsPerPage);
}

function clearFilters() {
  document.getElementById("search-filter").value = "";
  document.getElementById("category-filter").value = "";
  document.getElementById("status-filter").value = "";
  currentPage = 1;
  filterProducts();
}

function filterActivities() {
  const searchValue = document
    .getElementById("activity-search")
    .value.toLowerCase();
  const activityItems = document.querySelectorAll(".card .card > div > div");

  activityItems.forEach((item) => {
    const text = item.textContent.toLowerCase();
    if (text.includes(searchValue)) {
      item.style.display = "";
    } else {
      item.style.display = "none";
    }
  });
}

function showActionsMenu(event, sku) {
  event.stopPropagation();

  // Remove any existing menu
  const existingMenu = document.querySelector(".actions-menu");
  if (existingMenu) {
    existingMenu.remove();
  }

  // Create menu container
  const menu = document.createElement("div");
  menu.className = "actions-menu";
  menu.style.position = "absolute";
  menu.style.backgroundColor = "white";
  menu.style.border = "1px solid var(--border-color)";
  menu.style.borderRadius = "var(--radius-md)";
  menu.style.boxShadow = "0 4px 12px rgba(0, 0, 0, 0.15)";
  menu.style.zIndex = "1000";
  menu.style.minWidth = "120px";
  menu.style.padding = "var(--spacing-xs) 0";

  // Edit option
  const editOption = document.createElement("div");
  editOption.style.padding = "var(--spacing-sm) var(--spacing-md)";
  editOption.style.cursor = "pointer";
  editOption.style.display = "flex";
  editOption.style.alignItems = "center";
  editOption.style.gap = "var(--spacing-sm)";
  editOption.style.color = "var(--text-primary)";
  editOption.style.fontSize = "0.875rem";
  editOption.innerHTML =
    '<span style="background: white; border-radius: 2px; display: inline-block; padding: 2px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg></span> Edit';
  editOption.addEventListener("click", () => {
    menu.remove();
    openEditProductModal(sku);
  });

  // Generate option
  const generateOption = document.createElement("div");
  generateOption.style.padding = "var(--spacing-sm) var(--spacing-md)";
  generateOption.style.cursor = "pointer";
  generateOption.style.display = "flex";
  generateOption.style.alignItems = "center";
  generateOption.style.gap = "var(--spacing-sm)";
  generateOption.style.color = "var(--text-primary)";
  generateOption.style.fontSize = "0.875rem";
  generateOption.innerHTML =
    '<span style="background: white; border-radius: 2px; display: inline-block; padding: 2px;"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 4 23 10 17 10"></polyline><polyline points="1 20 1 14 7 14"></polyline><path d="M20.49 9A9 9 0 0 0 5.64 5.64L1 10m22 4l-4.64 4.36A9 9 0 0 1 3.51 15"></path></svg></span> Generate';
  generateOption.addEventListener("click", () => {
    menu.remove();
    generateProduct(sku);
  });

  // Delete option
  const deleteOption = document.createElement("div");
  deleteOption.style.padding = "var(--spacing-sm) var(--spacing-md)";
  deleteOption.style.cursor = "pointer";
  deleteOption.style.display = "flex";
  deleteOption.style.alignItems = "center";
  deleteOption.style.gap = "var(--spacing-sm)";
  deleteOption.style.color = "var(--accent-danger)";
  deleteOption.style.fontSize = "0.875rem";
  deleteOption.innerHTML = `
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="background: white; border-radius: 2px;">
            <polyline points="3,6 5,6 21,6"></polyline>
            <path d="M19,6v14a2,2 0 0,1-2,2H7a2,2 0 0,1-2-2V6m3,0V4a2,2 0 0,1,2-2h4a2,2 0 0,1,2,2v2"></path>
            <line x1="10" y1="11" x2="10" y2="17"></line>
            <line x1="14" y1="11" x2="14" y2="17"></line>
        </svg>
        Delete
    `;
  deleteOption.addEventListener("click", () => {
    menu.remove();
    window.productToDelete = sku;
    document.getElementById("deleteModalOverlay").style.display = "flex";
  });

  menu.appendChild(editOption);
  menu.appendChild(generateOption);
  menu.appendChild(deleteOption);

  // Position the menu
  const button = event.target.closest("button");
  const rect = button.getBoundingClientRect();
  menu.style.top = `${rect.bottom + 5}px`;
  menu.style.left = `${rect.left}px`;

  document.body.appendChild(menu);

  // Close menu when clicking outside
  const closeMenu = (e) => {
    if (!menu.contains(e.target) && e.target !== button) {
      menu.remove();
      document.removeEventListener("click", closeMenu);
    }
  };
  setTimeout(() => {
    document.addEventListener("click", closeMenu);
  }, 0);
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

function confirmDelete() {
  if (window.productToDelete) {
    // Send AJAX request to delete product from database
    fetch("../../back-end/delete/removeProduct.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: "sku=" + encodeURIComponent(window.productToDelete),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          // Reload products from server to ensure UI is updated
          fetch("../../../back-end/read/fetchProduct.php")
            .then((response) => response.json())
            .then((data) => {
              products = data;
              localStorage.setItem(
                "inventoryProducts",
                JSON.stringify(products),
              );
              filterProducts(); // Re-apply current filters to update the UI
            })
            .catch((error) => {
              console.error("Error reloading products:", error);
              // Fallback: remove from local array
              const index = products.findIndex(
                (p) => p.sku === window.productToDelete,
              );
              if (index !== -1) {
                products.splice(index, 1);
                localStorage.setItem(
                  "inventoryProducts",
                  JSON.stringify(products),
                );
                filterProducts();
              }
            });

          // Show success message
          const successMessage = document.getElementById("successMessage");
          const successText = successMessage.querySelector(".success-text");
          successText.textContent = "Successfully Deleted!";
          successMessage.style.display = "block";

          setTimeout(() => {
            successMessage.style.display = "none";
          }, 3000);
        } else {
          alert(data.message || "Error deleting product");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Error deleting product");
      });

    window.productToDelete = null;
    document.getElementById("deleteModalOverlay").style.display = "none";
  }
}

// Add Product Modal Functions
function closeAddProductModal() {
  document.getElementById("addProductModalOverlay").style.display = "none";
  document.getElementById("addProductForm").reset();
}

function closeAddProductModalOnOverlay(event) {
  if (event.target === document.getElementById("addProductModalOverlay")) {
    closeAddProductModal();
  }
}

function addProduct() {
  const form = document.getElementById("addProductForm");
  if (!form.checkValidity()) {
    form.reportValidity();
    return;
  }

  const name = document.getElementById("productName").value.trim();
  const category = document.getElementById("productCategory").value;
  const price = document.getElementById("productPrice").value.trim();
  const sizesInput = document.getElementById("productSizes").value.trim();
  const colorsInput = document.getElementById("productColors").value.trim();
  const selectedSizes = sizesInput
    ? sizesInput
        .split(",")
        .map((size) => size.trim())
        .filter((size) => size)
    : [];
  const imageFiles = document.getElementById("productImages").files;

  // Validate file sizes (max 4MB each)
  for (let file of imageFiles) {
    if (file.size > 4 * 1024 * 1024) {
      // 4MB
      alert(`File ${file.name} is too large. Maximum size is 4MB.`);
      return;
    }
  }

  // Prepare data for AJAX request
  const formData = new FormData();
  formData.append("productName", name);
  formData.append("productCategory", category);
  formData.append("productPrice", price);
  formData.append("productSizes", sizesInput);
  formData.append("productColors", colorsInput);

  // Append image files
  for (let i = 0; i < imageFiles.length; i++) {
    formData.append("productImages[]", imageFiles[i]);
  }

  // Send AJAX request to addProduct.php
  fetch("../../back-end/create/addProduct.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Add to local products array for immediate UI update
        const newProduct = {
          id: data.id,
          name,
          sku: data.sku, // SKU is now generated by the backend
          category,
          price,
          stock: data.stock,
          status:
            data.stock === 0
              ? "Out of Stock"
              : data.stock <= 10
                ? "Low Stock"
                : "In Stock",
          image:
            data.images && data.images.length > 0
              ? "../../../" + data.images[0]
              : "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90",
          size: sizesInput || "N/A",
          size_quantities: data.size_quantities,
          color: colorsInput || "N/A",
        };

        products.push(newProduct);
        localStorage.setItem("inventoryProducts", JSON.stringify(products));
        filterProducts();

        // Show success message
        const successMessage = document.getElementById("successMessage");
        const successText = successMessage.querySelector(".success-text");
        successText.textContent = "Product Added Successfully!";
        successMessage.style.display = "block";

        setTimeout(() => {
          successMessage.style.display = "none";
        }, 3000);

        closeAddProductModal();
      } else {
        alert(data.message || "Error adding product");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Error adding product");
    });
}

// Function to generate product (opens SKU modal)
function generateProduct(sku) {
  openSkuModal(sku);
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

document.addEventListener("DOMContentLoaded", () => {
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

  filterProducts();

  document
    .getElementById("search-filter")
    .addEventListener("input", filterProducts);
  document
    .getElementById("category-filter")
    .addEventListener("change", filterProducts);
  document
    .getElementById("status-filter")
    .addEventListener("change", filterProducts);

  const activitySearch = document.getElementById("activity-search");
  if (activitySearch) {
    activitySearch.addEventListener("input", filterActivities);
  }

  // Debug scroll event
  document.querySelector(".table-container").addEventListener("scroll", () => {
    console.log("Scroll event triggered on table-container");
  });
});
