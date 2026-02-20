// Fetch and populate categories
function loadCategories() {
  fetch("../../back-end/read/fetchCategory.php")
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        const categorySelects = document.querySelectorAll(
          "#productCategory, #editProductCategory",
        );
        categorySelects.forEach((select) => {
          // Clear existing options except the first one
          select.innerHTML = '<option value="">Select Category</option>';
          data.categories.forEach((category) => {
            const option = document.createElement("option");
            option.value = category.name;
            option.textContent = category.name;
            select.appendChild(option);
          });
        });
      }
    })
    .catch((error) => console.error("Error loading categories:", error));
}

// Load categories when page loads
document.addEventListener("DOMContentLoaded", loadCategories);

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

function handleDragOver(event) {
  event.preventDefault();
  event.stopPropagation();
  const container = document.getElementById("imageUploadContainer");
  container.style.borderColor = "#3b82f6";
  container.style.backgroundColor = "#eff6ff";
}

function handleDragLeave(event) {
  event.preventDefault();
  event.stopPropagation();
  const container = document.getElementById("imageUploadContainer");
  container.style.borderColor = "#ddd";
  container.style.backgroundColor = "#fafafa";
}

function handleDrop(event) {
  event.preventDefault();
  event.stopPropagation();
  const container = document.getElementById("imageUploadContainer");
  container.style.borderColor = "#ddd";
  container.style.backgroundColor = "#fafafa";

  const files = event.dataTransfer.files;
  const fileInput = document.getElementById("productImages");
  fileInput.files = files;
  handleImageSelection({ target: { files: files } });
}

function handleImageSelection(event) {
  const files = event.target.files;
  const previewContainer = document.getElementById("imagePreview");
  previewContainer.innerHTML = "";

  for (let i = 0; i < files.length; i++) {
    const file = files[i];
    if (file.type !== "image/png" && file.type !== "image/jpeg") {
      alert("Only PNG and JPG files are allowed.");
      event.target.value = "";
      return;
    }
    if (file.size > 4 * 1024 * 1024) {
      alert("File size exceeds 4MB limit.");
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
  const selectedSizes = sizesInput
    ? sizesInput
        .split(",")
        .map((size) => size.trim())
        .filter((size) => size)
    : [];

  console.log("Selected sizes:", selectedSizes);

  // Validate that at least one size is entered
  if (selectedSizes.length === 0) {
    alert("Please enter at least one size for the product.");
    return;
  }

  // Prepare data for AJAX request
  const formData = new FormData();
  formData.append("productName", name);
  formData.append("productCategory", category);
  formData.append("productPrice", price);
  formData.append("productSizes", sizesInput);

  // Append image files
  const imageInput = document.getElementById("productImages");
  for (let i = 0; i < imageInput.files.length; i++) {
    formData.append("productImages[]", imageInput.files[i]);
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
          sku: data.sku, // SKU will be generated by the backend
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
              ? data.images[0]
              : "https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop&q=90",
          size: selectedSizes.length > 0 ? selectedSizes.join(", ") : "N/A",
          size_quantities: data.size_quantities,
        };
        products.push(newProduct);
        localStorage.setItem("inventoryProducts", JSON.stringify(products));
        renderProducts(products);

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
