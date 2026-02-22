<?php
// Default values for the edit product modal
$modalId = $modalId ?? 'editProductModal';
$title = $title ?? 'Edit Product';
$cancelText = $cancelText ?? 'Cancel';
$confirmText = $confirmText ?? 'Update Product';
$confirmFunction = $confirmFunction ?? 'updateProduct';
$closeFunction = $closeFunction ?? 'closeEditProductModal';
?>

<!-- Edit Product Modal -->
<div class="modal-overlay" id="<?php echo $modalId; ?>Overlay" onclick="<?php echo $closeFunction; ?>OnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 800px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="<?php echo $closeFunction; ?>()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #111827;">Edit Product</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                Update the product details below
            </p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 40px;">
            <form id="editProductForm" onsubmit="event.preventDefault(); <?php echo $confirmFunction; ?>();">
                <input type="hidden" id="editProductSku" name="editProductSku">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <!-- Product Name -->
                    <div style="grid-column: span 2;">
                        <label for="editProductName" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Product Name <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="editProductName" name="editProductName" required
                            placeholder="Enter product name"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                            onblur="this.style.borderColor='#e5e7eb';">
                    </div>

                    <!-- Category -->
                    <div style="grid-column: span 2;">
                        <label for="editProductCategory" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Category <span style="color: #ef4444;">*</span>
                        </label>
                        <select id="editProductCategory" name="editProductCategory" required
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s; background: white; cursor: pointer;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                            onblur="this.style.borderColor='#e5e7eb';">
                            <option value="">Select Category</option>
                        </select>
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="editProductPrice" style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Price <span style="color: #ef4444;">*</span>
                        </label>
                        <input type="text" id="editProductPrice" name="editProductPrice" required
                            placeholder="₱0.00"
                            style="width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; box-sizing: border-box; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                            onblur="this.style.borderColor='#e5e7eb';">
                    </div>

                    <!-- Size Selection for Editing -->
                    <div style="grid-column: span 2;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Available Sizes <span style="color: #6b7280; font-weight: normal; font-size: 12px;">(Uncheck to remove size)</span>
                        </label>
                        
                        <!-- Size Type Selection -->
                        <div id="editSizeTypeSection" style="margin-bottom: 16px;">
                            <div style="display: flex; gap: 12px;">
                                <button type="button" onclick="selectEditSizeType('alpha')" id="editBtnAlphaSize"
                                    style="flex: 1; padding: 12px; background: #f3f4f6; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500;">
                                    Alpha (XS, S, M, L, XL, XXL)
                                </button>
                                <button type="button" onclick="selectEditSizeType('numeric')" id="editBtnNumericSize"
                                    style="flex: 1; padding: 12px; background: #f3f4f6; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500;">
                                    Numeric (39-47)
                                </button>
                            </div>
                        </div>
                        
                        <!-- Alpha Sizes -->
                        <div id="editAlphaSizesSection" style="display: none; margin-bottom: 12px;">
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" value="XS" class="edit-size-checkbox" style="cursor: pointer;">
                                    <span>XS</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" value="S" class="edit-size-checkbox" style="cursor: pointer;">
                                    <span>S</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" value="M" class="edit-size-checkbox" style="cursor: pointer;">
                                    <span>M</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" value="L" class="edit-size-checkbox" style="cursor: pointer;">
                                    <span>L</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" value="XL" class="edit-size-checkbox" style="cursor: pointer;">
                                    <span>XL</span>
                                </label>
                                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" value="XXL" class="edit-size-checkbox" style="cursor: pointer;">
                                    <span>XXL</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Numeric Sizes -->
                        <div id="editNumericSizesSection" style="display: none; margin-bottom: 12px;">
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <?php for ($i = 39; $i <= 47; $i++): ?>
                                <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                    <input type="checkbox" value="<?php echo $i; ?>" class="edit-size-checkbox" style="cursor: pointer;">
                                    <span><?php echo $i; ?></span>
                                </label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        
                        <!-- Current Sizes Display -->
                        <div id="editCurrentSizesDisplay" style="margin-top: 12px; padding: 12px; background: #f9fafb; border-radius: 8px;">
                            <p style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">Current sizes (uncheck to remove):</p>
                            <div id="editSelectedSizesList" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <!-- Sizes will be populated here -->
                            </div>
                        </div>
                        
                        <input type="hidden" id="editProductSizes" name="editProductSizes">
                    </div>

                    <!-- Product Images -->
                    <div style="grid-column: span 2;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: #374151;">
                            Product Images
                        </label>
                        <div id="editImageUploadContainer" style="border: 2px dashed #ddd; border-radius: 8px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; background: #fafafa;"
                             onclick="document.getElementById('editProductImages').click();"
                             ondragover="handleEditDragOver(event)"
                             ondragleave="handleEditDragLeave(event)"
                             ondrop="handleEditDrop(event)">
                            <div style="font-size: 48px; color: #ccc; margin-bottom: 10px;">+</div>
                            <div style="color: #666; font-size: 16px;">Click to add images or drag & drop</div>
                            <div style="color: #999; font-size: 12px; margin-top: 5px;">PNG, JPG only (max 4MB each)</div>
                        </div>
                        <input type="file" id="editProductImages" name="editProductImages[]" multiple accept="image/png,image/jpeg" style="display: none;" onchange="handleEditImageSelection(event)">
                        <div id="editImagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                    <button type="button" onclick="<?php echo $closeFunction; ?>()"
                        style="padding: 12px 28px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s;"
                        onmouseover="this.style.background='#e5e7eb'"
                        onmouseout="this.style.background='#f3f4f6'">
                        Cancel
                    </button>
                    <button type="submit"
                        style="padding: 12px 32px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);"
                        onmouseover="this.style.background='#059669'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'"
                        onmouseout="this.style.background='#10b981'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'">
                        <span style="display: inline-flex; align-items: center; gap: 8px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                            </svg>
                            Update Product
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Edit modal variables
let editCategoriesLoaded = false;
let editCurrentSizes = [];

// Load categories for edit modal
function loadEditCategories() {
    return fetch("../../back-end/read/fetchCategory.php")
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                const categorySelect = document.getElementById("editProductCategory");
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                data.categories.forEach((category) => {
                    const option = document.createElement("option");
                    option.value = category.name;
                    option.textContent = category.name;
                    categorySelect.appendChild(option);
                });
                editCategoriesLoaded = true;
            }
        })
        .catch((error) => console.error("Error loading categories:", error));
}

function selectEditSizeType(type) {
    if (type === 'alpha') {
        document.getElementById('editAlphaSizesSection').style.display = 'block';
        document.getElementById('editNumericSizesSection').style.display = 'none';
    } else {
        document.getElementById('editAlphaSizesSection').style.display = 'none';
        document.getElementById('editNumericSizesSection').style.display = 'block';
    }
}

// Edit Product Modal Functions
function closeEditProductModal() {
    document.getElementById("editProductModalOverlay").style.display = "none";
    document.getElementById("editProductForm").reset();
    document.getElementById("editImagePreview").innerHTML = "";
    editCurrentSizes = [];
}

function closeEditProductModalOnOverlay(event) {
    if (event.target === document.getElementById("editProductModalOverlay")) {
        closeEditProductModal();
    }
}

function openEditProductModal(sku) {
    const product = products.find((p) => p.sku === sku);
    if (!product) return;

    // Reset form
    document.getElementById("editProductForm").reset();
    document.getElementById("editImagePreview").innerHTML = "";
    editCurrentSizes = [];

    // Load categories first, then populate form
    if (!editCategoriesLoaded) {
        loadEditCategories().then(() => {
            populateEditForm(product);
        });
    } else {
        populateEditForm(product);
    }
}

function populateEditForm(product) {
    // Populate basic fields
    document.getElementById("editProductSku").value = product.sku;
    document.getElementById("editProductName").value = product.name;
    document.getElementById("editProductCategory").value = product.category;
    document.getElementById("editProductPrice").value = product.price;
    
    // Parse and display current sizes
    let currentSizes = [];
    if (product.size && product.size !== 'N/A') {
        currentSizes = product.size.split(',').map(s => {
            s = s.trim();
            if (s.startsWith('EUR ')) {
                return s.replace('EUR ', '');
            }
            return s;
        });
    }
    
    editCurrentSizes = [...currentSizes];
    
    // Determine size type and show appropriate section
    const numericSizes = ['39', '40', '41', '42', '43', '44', '45', '46', '47'];
    const hasNumericSizes = currentSizes.some(s => numericSizes.includes(s));
    
    if (hasNumericSizes) {
        selectEditSizeType('numeric');
    } else {
        selectEditSizeType('alpha');
    }
    
    // Check the checkboxes for current sizes
    setTimeout(() => {
        document.querySelectorAll('.edit-size-checkbox').forEach(checkbox => {
            checkbox.checked = currentSizes.includes(checkbox.value);
        });
    }, 100);
    
    // Add event listeners to checkboxes
    document.querySelectorAll('.edit-size-checkbox').forEach(checkbox => {
        checkbox.onchange = function() {
            if (this.checked) {
                if (!editCurrentSizes.includes(this.value)) {
                    editCurrentSizes.push(this.value);
                }
            } else {
                editCurrentSizes = editCurrentSizes.filter(s => s !== this.value);
            }
        };
    });

    document.getElementById("editProductModalOverlay").style.display = "flex";
}

// Drag and drop handlers
function handleEditDragOver(event) {
    event.preventDefault();
    event.stopPropagation();
    const container = document.getElementById("editImageUploadContainer");
    container.style.borderColor = "#3b82f6";
    container.style.backgroundColor = "#eff6ff";
}

function handleEditDragLeave(event) {
    event.preventDefault();
    event.stopPropagation();
    const container = document.getElementById("editImageUploadContainer");
    container.style.borderColor = "#ddd";
    container.style.backgroundColor = "#fafafa";
}

function handleEditDrop(event) {
    event.preventDefault();
    event.stopPropagation();
    const container = document.getElementById("editImageUploadContainer");
    container.style.borderColor = "#ddd";
    container.style.backgroundColor = "#fafafa";

    const files = event.dataTransfer.files;
    const fileInput = document.getElementById("editProductImages");
    fileInput.files = files;
    handleEditImageSelection({ target: { files: files } });
}

function handleEditImageSelection(event) {
    const files = event.target.files;
    const previewContainer = document.getElementById("editImagePreview");
    previewContainer.innerHTML = "";

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        if (file.type !== "image/png" && file.type !== "image/jpeg") {
            showInvalidMessage("Only PNG and JPG files are allowed.");
            event.target.value = "";
            return;
        }
        if (file.size > 4 * 1024 * 1024) {
            showInvalidMessage("File size exceeds 4MB limit.");
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

function updateProduct() {
    const form = document.getElementById("editProductForm");
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Get form elements
    const skuElement = document.getElementById("editProductSku");
    const nameElement = document.getElementById("editProductName");
    const categoryElement = document.getElementById("editProductCategory");
    const priceElement = document.getElementById("editProductPrice");

    if (!skuElement || !nameElement || !categoryElement || !priceElement) {
        console.error("Missing required form elements");
        showInvalidMessage("Form error: Missing required fields");
        return;
    }

    const originalSku = skuElement.value;
    const name = nameElement.value.trim();
    const category = categoryElement.value;
    const price = priceElement.value.trim().replace(/[₱,]/g, "");
    
    // Get selected sizes (from checkboxes)
    const selectedSizes = [];
    document.querySelectorAll('.edit-size-checkbox:checked').forEach(checkbox => {
        selectedSizes.push(checkbox.value);
    });

    // Validate required fields
    if (!originalSku || !name || !category || !price) {
        showInvalidMessage("Please fill in all required fields");
        return;
    }

    // Prepare data for AJAX request
    const formData = new FormData();
    formData.append("originalSku", originalSku);
    formData.append("name", name);
    formData.append("category", category);
    formData.append("price", price);
    formData.append("productSizes", selectedSizes.join(','));

    // Append image files
    const imageInput = document.getElementById("editProductImages");
    if (imageInput && imageInput.files) {
        for (let i = 0; i < imageInput.files.length; i++) {
            formData.append("editProductImages[]", imageInput.files[i]);
        }
    }

    // Send AJAX request to editProduct.php
    fetch("../../back-end/update/editProduct.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                // Update the local products array
                const productIndex = products.findIndex((p) => p.sku === originalSku);
                if (productIndex !== -1) {
                    const updatedProduct = {
                        id: products[productIndex].id,
                        name,
                        sku: data.sku || originalSku,
                        category,
                        price,
                        stock: data.stock || products[productIndex].stock,
                        status: data.status || products[productIndex].status,
                        image: data.images && data.images.length > 0
                            ? data.images[0]
                            : products[productIndex].image,
                        size: selectedSizes.length > 0 ? selectedSizes.join(', ') : 'N/A',
                        size_quantities: data.size_quantities || products[productIndex].size_quantities,
                        color: products[productIndex].color,
                    };

                    products[productIndex] = updatedProduct;
                    localStorage.setItem("inventoryProducts", JSON.stringify(products));
                    filterProducts();
                }

                // Show success message
                const successMessage = document.getElementById("successMessage");
                if (successMessage) {
                    const successText = successMessage.querySelector(".success-text");
                    if (successText) {
                        successText.textContent = "Product Updated Successfully!";
                    }
                    successMessage.style.display = "block";

                    setTimeout(() => {
                        successMessage.style.display = "none";
                    }, 3000);
                }

                closeEditProductModal();
            } else {
                showInvalidMessage(data.message || "Error updating product");
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showInvalidMessage("Error updating product");
        });
}
</script>
