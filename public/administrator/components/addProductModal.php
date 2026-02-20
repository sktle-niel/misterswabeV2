<?php
// Default values for the add product modal
$modalId = $modalId ?? 'addProductModal';
$title = $title ?? 'Add New Product';
$cancelText = $cancelText ?? 'Cancel';
$confirmText = $confirmText ?? 'Add Product';
$confirmFunction = $confirmFunction ?? 'addProduct';
$closeFunction = $closeFunction ?? 'closeAddProductModal';
?>

<!-- Add Product Modal -->
<div id="successMessage" class="success-message" style="display: none;">
    <div class="success-content">
        <span class="success-icon">✓</span>
        <span class="success-text">Successfully Added!</span>
    </div>
</div>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addProductModalOverlay" onclick="closeAddProductModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 9999;">
    <div class="modal-content" style="max-width: 800px; background: white; border-radius: 12px; padding: 30px; position: relative; max-height: 90vh; overflow-y: auto;" onclick="event.stopPropagation();">
        <button class="close-btn" onclick="closeAddProductModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 28px; cursor: pointer; color: #666; line-height: 1;">×</button>

        <div class="modal-inner">
            <div style="margin-bottom: 20px;">
                <h2 style="margin-bottom: 10px; font-size: 24px; font-weight: 600;">Add New Product</h2>
                <p style="color: #666; line-height: 1.5; font-size: 14px;">
                    Fill in the product details below to add it to your inventory
                </p>
            </div>

            <form id="addProductForm" onsubmit="event.preventDefault(); addProduct();">
                <!-- Product Name (Full Width) -->
                <div style="margin-bottom: 20px;">
                    <label for="productName" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">
                        Product Name <span style="color: red;">*</span>
                    </label>
                    <input type="text" id="productName" name="productName" placeholder="Enter product name" required 
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                </div>

                <!-- Category (Full Width) -->
                <div style="margin-bottom: 20px;">
                    <label for="productCategory" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">
                        Category <span style="color: red;">*</span>
                    </label>
                    <select id="productCategory" name="productCategory" required 
                            style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;" 
                            onchange="updateSizeOptions()">
                        <option value="">Select Category</option>
                    </select>
                </div>

                <!-- Price -->
                <div style="margin-bottom: 20px;">
                    <label for="productPrice" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">
                        Price <span style="color: red;">*</span>
                    </label>
                    <input type="text" id="productPrice" name="productPrice" placeholder="₱0.00" required
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                </div>

                <!-- Available Sizes (Full Width) -->
                <div style="margin-bottom: 20px;">
                    <label for="productSizes" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">
                        Available Sizes
                    </label>
                    <input type="text" id="productSizes" name="productSizes" placeholder="Enter sizes separated by commas (e.g., S,M,L,XL)"
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                    <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Enter sizes separated by commas</small>
                </div>

                <!-- Available Colors (Full Width) -->
                <div style="margin-bottom: 20px;">
                    <label for="productColors" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">
                        Available Colors
                    </label>
                    <input type="text" id="productColors" name="productColors" placeholder="Enter colors separated by commas (e.g., Red, Blue, Green)"
                           style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; box-sizing: border-box;">
                    <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Enter colors separated by commas</small>
                </div>

                <!-- Product Images (Full Width) -->
                <div style="margin-bottom: 20px;">
                    <label for="productImages" style="display: block; margin-bottom: 5px; font-weight: 500; font-size: 14px;">
                        Product Images
                    </label>
                    <input type="file" id="productImages" name="productImages[]" multiple accept="image/*"
                        style="display: none;" onchange="handleImageSelection(event)">
                    <div id="imageUploadContainer" 
                         style="border: 2px dashed #ddd; border-radius: 8px; padding: 40px; text-align: center; cursor: pointer; transition: all 0.3s; background: #fafafa;"
                         onclick="document.getElementById('productImages').click();"
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         ondrop="handleDrop(event)">
                        <div style="font-size: 48px; color: #ccc; margin-bottom: 10px;">📁</div>
                        <div style="color: #666; font-size: 14px; font-weight: 500;">Choose Files</div>
                        <div style="color: #999; font-size: 12px; margin-top: 5px;">Select multiple images (max 4MB each). Leave empty to use default product image</div>
                    </div>
                    <div id="imagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" onclick="closeAddProductModal()" 
                            style="padding: 12px 24px; background: white; border: 1px solid #ddd; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500;">
                        Cancel
                    </button>
                    <button type="submit" 
                            style="padding: 12px 24px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 5px;">
                        <span style="font-size: 18px;">+</span> Add Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../../../src/js/addProductModal.js"></script>