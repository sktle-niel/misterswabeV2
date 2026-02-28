<?php
// Default values for the edit product modal
$modalId = $modalId ?? 'editProductModal';
$title = $title ?? 'Edit Product';
$cancelText = $cancelText ?? 'Cancel';
$confirmText = $confirmText ?? 'Update Product';
$confirmFunction = $confirmFunction ?? 'updateProduct';
$closeFunction = $closeFunction ?? 'closeEditProductModal';
?>

<!-- Confirm Remove Size Modal -->
<div class="modal-overlay" id="confirmRemoveSizeModalOverlay" onclick="closeConfirmRemoveSizeModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.5); justify-content: center; align-items: center; z-index: 11000;">
    <div class="modal-content" style="max-width: 500px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 24px 30px; border-bottom: 1px solid #e5e7eb;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #fef3c7; display: flex; align-items: center; justify-content: center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <h3 style="margin: 0 0 4px 0; font-size: 20px; font-weight: 700; color: #111827;">Warning: Size Has Data</h3>
                </div>
            </div>
        </div>

        <!-- Modal Body -->
        <div style="padding: 24px 30px;">
            <p style="margin: 0 0 16px 0; font-size: 15px; color: #374151; line-height: 1.5;">
                You are about to remove size <strong id="confirmRemoveSizeName" style="color: #dc2626;">[SIZE]</strong> from this product.
            </p>
            
            <div id="confirmRemoveSizeInfo" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: 600; color: #dc2626;">This size has the following colors and quantities:</p>
                <div id="confirmRemoveSizeColors" style="font-size: 14px; color: #374151;">
                    <!-- Colors and quantities will be inserted here -->
                </div>
            </div>
            
            <p style="margin: 0; font-size: 14px; color: #dc2626; font-weight: 500;">
                ⚠️ All color and quantity data for this size will be permanently deleted!
            </p>
        </div>

        <!-- Modal Footer -->
        <div style="display: flex; gap: 12px; justify-content: flex-end; padding: 20px 30px; border-top: 1px solid #e5e7eb; background: #f9fafb; border-radius: 0 0 16px 16px;">
            <button type="button" onclick="cancelRemoveSize()"
                style="padding: 10px 20px; background: white; color: #374151; border: 2px solid #d1d5db; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;">
                Cancel
            </button>
            <button type="button" onclick="confirmRemoveSize()"
                style="padding: 10px 20px; background: #dc2626; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.2s;"
                onmouseover="this.style.background='#b91c1c'"
                onmouseout="this.style.background='#dc2626'">
                Yes, Remove Size
            </button>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal-overlay" id="<?php echo $modalId; ?>Overlay" onclick="<?php echo $closeFunction; ?>OnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 1200px; width: 95%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 95vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
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

                        <!-- Size Configuration Section - Same as Add Product Modal (without color adding) -->
                    <div style="grid-column: span 2;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                            <label style="display: block; font-weight: 600; font-size: 14px; color: #374151;">
                                Product Sizes
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; color: #6b7280;">
<input type="checkbox" id="editNoSizeColorRequired" onchange="toggleEditSizeColorRequired()" style="width: 16px; height: 16px; cursor: pointer;">
                                <span>None size product</span>
                            </label>
                        </div>
                        
                        <!-- Size Configuration Container -->
                        <div id="editSizeConfigSection">
                            <!-- Size Type Selection -->
                            <div id="editSizeTypeSelection" style="margin-bottom: 16px;">
                                <p style="font-size: 13px; color: #6b7280; margin-bottom: 12px;">Select size type:</p>
                                <div style="display: flex; gap: 12px; margin-bottom: 16px;">
                                    <button type="button" onclick="selectEditSizeType('alpha')" id="editBtnAlphaSize"
                                        style="flex: 1; padding: 16px; background: #f3f4f6; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="4,7 4,4 20,4 20,7"></polyline>
                                            <line x1="9" y1="20" x2="15" y2="20"></line>
                                            <line x1="12" y1="4" x2="12" y2="20"></line>
                                        </svg>
                                        <span>Alpha Based</span>
                                        <span style="font-size: 11px; color: #6b7280; font-weight: normal;">(XS, S, M, L, XL, XXL)</span>
                                    </button>
                                    <button type="button" onclick="selectEditSizeType('numeric')" id="editBtnNumericSize"
                                        style="flex: 1; padding: 16px; background: #f3f4f6; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 500; display: flex; flex-direction: column; align-items: center; gap: 8px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="4" y1="6" x2="20" y2="6"></line>
                                            <line x1="4" y1="12" x2="20" y2="12"></line>
                                            <line x1="4" y1="18" x2="20" y2="18"></line>
                                        </svg>
                                        <span>Number Based</span>
                                        <span style="font-size: 11px; color: #6b7280; font-weight: normal;">(39, 40, 41, 42... 47)</span>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Size Selection (Hidden by default) -->
                            <div id="editSizeSelectionArea" style="display: none;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                                    <p style="font-size: 13px; color: #6b7280; margin: 0;">Select available sizes:</p>
                                    <button type="button" onclick="resetEditSizeType()" 
                                        style="padding: 6px 12px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; font-size: 12px; color: #6b7280;">
                                        Change Size Type
                                    </button>
                                </div>
                                
                                <!-- Alpha Sizes -->
                                <div id="editAlphaSizesSection" style="display: none;">
                                    <p style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 8px;">Clothing Sizes:</p>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;" id="editAlphaSizesContainer">
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="XS" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>XS</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="S" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>S</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="M" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>M</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="L" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>L</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="XL" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>XL</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="XXL" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>XXL</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Numeric Sizes -->
                                <div id="editNumericSizesSection" style="display: none;">
                                    <p style="font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 8px;">Shoe Sizes (EU):</p>
                                    <div style="display: flex; flex-wrap: wrap; gap: 8px;" id="editNumericSizesContainer">
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="39" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>39</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="40" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>40</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="41" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>41</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="42" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>42</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="43" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>43</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="44" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>44</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="45" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>45</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="46" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>46</span>
                                        </label>
                                        <label style="display: flex; align-items: center; gap: 6px; padding: 8px 12px; background: #f3f4f6; border-radius: 6px; cursor: pointer; font-size: 13px;">
                                            <input type="checkbox" value="47" class="edit-size-checkbox" onchange="toggleEditSize(this)" style="cursor: pointer;">
                                            <span>47</span>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Selected Sizes Display -->
                                <div id="editSelectedSizesDisplay" style="margin-top: 16px; padding: 16px; background: #f9fafb; border-radius: 8px;">
                                    <p style="font-size: 13px; color: #6b7280; margin-bottom: 8px;">Selected sizes:</p>
                                    <div id="editSelectedSizesList" style="display: flex; flex-wrap: wrap; gap: 8px;">
                                        <!-- Selected sizes will appear here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Simple Product Section (when No sizes is checked) -->
                        <div id="editSimpleProductSection" style="display: none; margin-top: 16px;">
                            <div style="display: grid; gap: 16px;">
                                <!-- Product Information Section -->
                                <div style="border-top: 1px solid #e5e7eb; padding-top: 16px;">
                                    <label style="display: block; margin-bottom: 12px; font-weight: 600; font-size: 14px; color: #374151;">
                                        Product Information <span style="color: #6b7280; font-weight: normal;">(Optional - for products like bags)</span>
                                    </label>
                                    
                                    <div style="display: grid; gap: 12px;">
                                        <!-- Brand -->
                                        <div>
                                            <label for="editProductBrand" style="display: block; margin-bottom: 4px; font-size: 13px; color: #6b7280;">
                                                Brand
                                            </label>
                                            <input type="text" id="editProductBrand" name="editProductBrand" 
                                                placeholder="e.g., Nike, Adidas, Hermes"
                                                style="width: 100%; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
                                                onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                                                onblur="this.style.borderColor='#e5e7eb';">
                                        </div>
                                        
                                        <!-- Material -->
                                        <div>
                                            <label for="editProductMaterial" style="display: block; margin-bottom: 4px; font-size: 13px; color: #6b7280;">
                                                Material
                                            </label>
                                            <input type="text" id="editProductMaterial" name="editProductMaterial" 
                                                placeholder="e.g., Leather, Canvas, Polyester"
                                                style="width: 100%; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
                                                onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                                                onblur="this.style.borderColor='#e5e7eb';">
                                        </div>
                                        
                                        <!-- Dimensions -->
                                        <div>
                                            <label for="editProductDimensions" style="display: block; margin-bottom: 4px; font-size: 13px; color: #6b7280;">
                                                Dimensions
                                            </label>
                                            <input type="text" id="editProductDimensions" name="editProductDimensions" 
                                                placeholder="e.g., 30cm x 20cm x 10cm"
                                                style="width: 100%; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; box-sizing: border-box;"
                                                onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                                                onblur="this.style.borderColor='#e5e7eb';">
                                        </div>
                                        
                                        <!-- Additional Info -->
                                        <div>
                                            <label for="editProductInfo" style="display: block; margin-bottom: 4px; font-size: 13px; color: #6b7280;">
                                                Additional Information
                                            </label>
                                            <textarea id="editProductInfo" name="editProductInfo" rows="3"
                                                placeholder="Any additional product details, features, or specifications..."
                                                style="width: 100%; padding: 8px 12px; border: 2px solid #e5e7eb; border-radius: 6px; font-size: 14px; box-sizing: border-box; resize: vertical;"
                                                onfocus="this.style.borderColor='#3b82f6'; this.style.outline='none';"
                                                onblur="this.style.borderColor='#e5e7eb';"></textarea>
                                        </div>
                                    </div>
                                </div>
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
let editSelectedSizes = [];
let editCurrentSizeColorQuantities = {}; // Store current size_color_quantities for the product being edited
let pendingSizeToRemove = null; // Store the size that's pending removal

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
    document.getElementById('editSizeTypeSelection').style.display = 'none';
    document.getElementById('editSizeSelectionArea').style.display = 'block';
    
    if (type === 'alpha') {
        document.getElementById('editAlphaSizesSection').style.display = 'block';
        document.getElementById('editNumericSizesSection').style.display = 'none';
    } else {
        document.getElementById('editAlphaSizesSection').style.display = 'none';
        document.getElementById('editNumericSizesSection').style.display = 'block';
    }
}

function resetEditSizeType() {
    editSelectedSizes = [];
    editCurrentSizeColorQuantities = {};
    document.getElementById('editSizeTypeSelection').style.display = 'block';
    document.getElementById('editSizeSelectionArea').style.display = 'none';
    document.querySelectorAll('.edit-size-checkbox').forEach(cb => cb.checked = false);
    renderEditSelectedSizes();
    updateEditHiddenInputs();
}

function toggleEditSizeColorRequired() {
    const checkbox = document.getElementById('editNoSizeColorRequired');
    const configSection = document.getElementById('editSizeConfigSection');
    const simpleProductSection = document.getElementById('editSimpleProductSection');
    
    if (checkbox.checked) {
        configSection.style.display = 'none';
        if (simpleProductSection) {
            simpleProductSection.style.display = 'block';
        }
    } else {
        configSection.style.display = 'block';
        if (simpleProductSection) {
            simpleProductSection.style.display = 'none';
        }
    }
    updateEditHiddenInputs();
}

function toggleEditSize(checkbox) {
    const size = checkbox.value;
    
    if (checkbox.checked) {
        // Adding a size - just add it
        if (!editSelectedSizes.includes(size)) {
            editSelectedSizes.push(size);
        }
        renderEditSelectedSizes();
        updateEditHiddenInputs();
    } else {
        // Removing a size - check if it has colors/quantities
        if (editCurrentSizeColorQuantities && editCurrentSizeColorQuantities[size]) {
            const sizeColors = editCurrentSizeColorQuantities[size];
            const hasColors = Object.keys(sizeColors).some(color => sizeColors[color] > 0);
            
            if (hasColors) {
                // Show confirmation modal
                showConfirmRemoveSizeModal(size, sizeColors);
                // Re-check the checkbox (we'll uncheck it after confirmation)
                checkbox.checked = true;
                return;
            }
        }
        
        // No colors/quantities, just remove
        editSelectedSizes = editSelectedSizes.filter(s => s !== size);
        renderEditSelectedSizes();
        updateEditHiddenInputs();
    }
}

// Show confirmation modal when removing a size with data
function showConfirmRemoveSizeModal(size, sizeColors) {
    pendingSizeToRemove = size;
    
    // Update modal content
    document.getElementById('confirmRemoveSizeName').textContent = size;
    
    // Build colors and quantities display
    const colorsContainer = document.getElementById('confirmRemoveSizeColors');
    let colorsHtml = '';
    let totalQty = 0;
    
    for (const [color, qty] of Object.entries(sizeColors)) {
        if (qty > 0) {
            colorsHtml += `<div style="display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #fecaca;">
                <span>${color}</span>
                <span style="font-weight: 600;">${qty}</span>
            </div>`;
            totalQty += qty;
        }
    }
    
    if (totalQty > 0) {
        colorsHtml += `<div style="display: flex; justify-content: space-between; padding: 8px 0 0 0; margin-top: 8px; border-top: 2px solid #dc2626; font-weight: 600;">
            <span>Total</span>
            <span>${totalQty}</span>
        </div>`;
    }
    
    colorsContainer.innerHTML = colorsHtml || '<p style="color: #6b7280; font-style: italic;">No quantities recorded</p>';
    
    // Show modal
    document.getElementById('confirmRemoveSizeModalOverlay').style.display = 'flex';
}

function closeConfirmRemoveSizeModalOnOverlay(event) {
    if (event.target === document.getElementById('confirmRemoveSizeModalOverlay')) {
        closeConfirmRemoveSizeModal();
    }
}

function closeConfirmRemoveSizeModal() {
    document.getElementById('confirmRemoveSizeModalOverlay').style.display = 'none';
    pendingSizeToRemove = null;
    
    // Uncheck the checkbox
    if (pendingSizeToRemove) {
        document.querySelectorAll('.edit-size-checkbox').forEach(cb => {
            if (cb.value === pendingSizeToRemove) {
                cb.checked = false;
            }
        });
    }
}

function cancelRemoveSize() {
    // Re-uncheck the checkbox
    if (pendingSizeToRemove) {
        document.querySelectorAll('.edit-size-checkbox').forEach(cb => {
            if (cb.value === pendingSizeToRemove) {
                cb.checked = false;
            }
        });
    }
    closeConfirmRemoveSizeModal();
}

function confirmRemoveSize() {
    // Actually remove the size
    if (pendingSizeToRemove) {
        editSelectedSizes = editSelectedSizes.filter(s => s !== pendingSizeToRemove);
        renderEditSelectedSizes();
        updateEditHiddenInputs();
        
        // Also uncheck the checkbox
        document.querySelectorAll('.edit-size-checkbox').forEach(cb => {
            if (cb.value === pendingSizeToRemove) {
                cb.checked = false;
            }
        });
    }
    closeConfirmRemoveSizeModal();
}

function removeEditSize(size) {
    // Check if size has colors/quantities
    if (editCurrentSizeColorQuantities && editCurrentSizeColorQuantities[size]) {
        const sizeColors = editCurrentSizeColorQuantities[size];
        const hasColors = Object.keys(sizeColors).some(color => sizeColors[color] > 0);
        
        if (hasColors) {
            // Show confirmation modal
            showConfirmRemoveSizeModal(size, sizeColors);
            return;
        }
    }
    
    // No colors/quantities, just remove
    editSelectedSizes = editSelectedSizes.filter(s => s !== size);
    document.querySelectorAll('.edit-size-checkbox').forEach(cb => {
        if (cb.value === size) {
            cb.checked = false;
        }
    });
    renderEditSelectedSizes();
    updateEditHiddenInputs();
}

function renderEditSelectedSizes() {
    const container = document.getElementById('editSelectedSizesList');
    if (!container) return;
    
    if (editSelectedSizes.length === 0) {
        container.innerHTML = '<span style="color: #9ca3af; font-size: 13px;">No sizes selected</span>';
        return;
    }
    
    let html = '';
    for (let i = 0; i < editSelectedSizes.length; i++) {
        const size = editSelectedSizes[i];
        const sizeEscaped = size.replace(/'/g, "\\'");
        
        // Check if this size has colors/quantities
        let hasData = false;
        if (editCurrentSizeColorQuantities && editCurrentSizeColorQuantities[size]) {
            const sizeColors = editCurrentSizeColorQuantities[size];
            hasData = Object.keys(sizeColors).some(color => sizeColors[color] > 0);
        }
        
        const badgeStyle = hasData ? 'background: #fef3c7; color: #92400e;' : 'background: #dbeafe; color: #1e40af;';
        
        html += `
            <span style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 12px; ${badgeStyle} border-radius: 16px; font-size: 13px; font-weight: 500;">
                ${size}
                ${hasData ? '<span title="Has colors/quantities" style="font-size: 10px;">⚠️</span>' : ''}
                <button type="button" onclick="removeEditSize('${sizeEscaped}')" 
                    style="background: none; border: none; cursor: pointer; padding: 0; display: flex; align-items: center; color: inherit;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
            </span>
        `;
    }
    
    container.innerHTML = html;
}

function updateEditHiddenInputs() {
    document.getElementById('editProductSizes').value = editSelectedSizes.join(',');
}

function resetEditForm() {
    editSelectedSizes = [];
    editCurrentSizeColorQuantities = {};
    pendingSizeToRemove = null;
    document.getElementById('editNoSizeColorRequired').checked = false;
    document.getElementById('editNoSizeColorRequired').disabled = false;
    document.getElementById('editSizeConfigSection').style.display = 'block';
    document.getElementById('editSizeTypeSelection').style.display = 'block';
    document.getElementById('editSizeSelectionArea').style.display = 'none';
    document.getElementById('editAlphaSizesSection').style.display = 'none';
    document.getElementById('editNumericSizesSection').style.display = 'none';
    
    // Reset simple product section
    const simpleProductSection = document.getElementById('editSimpleProductSection');
    if (simpleProductSection) {
        simpleProductSection.style.display = 'none';
    }
    // Clear product info fields
    const brandInput = document.getElementById('editProductBrand');
    const materialInput = document.getElementById('editProductMaterial');
    const dimensionsInput = document.getElementById('editProductDimensions');
    const infoInput = document.getElementById('editProductInfo');
    if (brandInput) brandInput.value = '';
    if (materialInput) materialInput.value = '';
    if (dimensionsInput) dimensionsInput.value = '';
    if (infoInput) infoInput.value = '';
    
    document.querySelectorAll('.edit-size-checkbox').forEach(cb => cb.checked = false);
    renderEditSelectedSizes();
    updateEditHiddenInputs();
}

// Edit Product Modal Functions
function closeEditProductModal() {
    document.getElementById("editProductModalOverlay").style.display = "none";
    document.getElementById("editProductForm").reset();
    document.getElementById("editImagePreview").innerHTML = "";
    resetEditForm();
}

function closeEditProductModalOnOverlay(event) {
    if (event.target === document.getElementById("editProductModalOverlay")) {
        closeEditProductModal();
    }
}

function openEditProductModal(sku) {
    const product = products.find((p) => p.sku === sku);
    if (!product) return;

    document.getElementById("editProductForm").reset();
    document.getElementById("editImagePreview").innerHTML = "";
    resetEditForm();

    if (!editCategoriesLoaded) {
        loadEditCategories().then(() => {
            populateEditForm(product);
        });
    } else {
        populateEditForm(product);
    }
}

function populateEditForm(product) {
    document.getElementById("editProductSku").value = product.sku;
    document.getElementById("editProductName").value = product.name;
    document.getElementById("editProductCategory").value = product.category;
    document.getElementById("editProductPrice").value = product.price;
    
    // Store size_color_quantities for checking when unchecking sizes
    if (product.size_color_quantities) {
        editCurrentSizeColorQuantities = typeof product.size_color_quantities === 'string' 
            ? JSON.parse(product.size_color_quantities) 
            : product.size_color_quantities;
    } else {
        editCurrentSizeColorQuantities = {};
    }
    
    // Preview existing product images
    const previewContainer = document.getElementById("editImagePreview");
    previewContainer.innerHTML = "";
    
    // Store existing images in a data attribute for tracking
    const existingImages = [];
    
    if (product.image) {
        // Handle single image or multiple images
        const images = Array.isArray(product.image) ? product.image : [product.image];
        images.forEach((imgSrc, index) => {
            if (imgSrc && imgSrc !== 'null') {
                // Fix image path if needed
                let finalSrc = imgSrc;
                if (!imgSrc.startsWith('http') && !imgSrc.startsWith('../../')) {
                    finalSrc = '../../../' + imgSrc;
                }
                
                const imgContainer = document.createElement("div");
                imgContainer.style.position = "relative";
                imgContainer.style.display = "inline-block";
                imgContainer.dataset.existingImage = "true";
                imgContainer.dataset.imageIndex = index;
                imgContainer.dataset.imageSrc = imgSrc;

                const img = document.createElement("img");
                img.src = finalSrc;
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
                
                existingImages.push(imgSrc);
            }
        });
    }
    
    // Store existing images count for validation
    previewContainer.dataset.existingImagesCount = existingImages.length;
    
    // Parse sizes
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
    
    // Determine if it's a simple product (no sizes)
    const isSimpleProduct = !product.size || product.size === 'N/A' || currentSizes.length === 0;
    
if (isSimpleProduct) {
        document.getElementById('editNoSizeColorRequired').checked = true;
        // Disable the checkbox since product has no sizes (simple product)
        document.getElementById('editNoSizeColorRequired').disabled = true;
        document.getElementById('editSizeConfigSection').style.display = 'none';
        // Show simple product section
        const simpleProductSection = document.getElementById('editSimpleProductSection');
        if (simpleProductSection) {
            simpleProductSection.style.display = 'block';
        }
        
        // Populate existing product information from 'information' column
        if (product.information && product.information !== 'null') {
            try {
                const productInfo = typeof product.information === 'string' 
                    ? JSON.parse(product.information) 
                    : product.information;
                
                if (productInfo && productInfo.brand) {
                    document.getElementById('editProductBrand').value = productInfo.brand;
                }
                if (productInfo && productInfo.material) {
                    document.getElementById('editProductMaterial').value = productInfo.material;
                }
                if (productInfo && productInfo.dimensions) {
                    document.getElementById('editProductDimensions').value = productInfo.dimensions;
                }
                if (productInfo && productInfo.product_info) {
                    document.getElementById('editProductInfo').value = productInfo.product_info;
                }
            } catch (e) {
                console.log('Error parsing product information:', e);
            }
        }
    } else {
        // Disable the checkbox for products with sizes
        document.getElementById('editNoSizeColorRequired').disabled = true;
        
        // Determine size type
        const numericSizes = ['39', '40', '41', '42', '43', '44', '45', '46', '47'];
        const hasNumericSizes = currentSizes.some(s => numericSizes.includes(s));
        
        if (hasNumericSizes) {
            selectEditSizeType('numeric');
        } else {
            selectEditSizeType('alpha');
        }
        
        // Set selected sizes
        editSelectedSizes = [...currentSizes];
        
        // Check the checkboxes
        setTimeout(() => {
            document.querySelectorAll('.edit-size-checkbox').forEach(checkbox => {
                checkbox.checked = currentSizes.includes(checkbox.value);
            });
            renderEditSelectedSizes();
            updateEditHiddenInputs();
        }, 100);
    }

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

    // Don't clear existing images - append new ones instead
    
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

    // Validation: Check if product has at least one image
    const previewContainer = document.getElementById("editImagePreview");
    const existingImagesCount = parseInt(previewContainer.dataset.existingImagesCount) || 0;
    const newImageInput = document.getElementById("editProductImages");
    const newImagesCount = newImageInput && newImageInput.files ? newImageInput.files.length : 0;
    const remainingImages = previewContainer.querySelectorAll('[data-existing-image="true"]').length;
    
    if (existingImagesCount === 0 && newImagesCount === 0 && remainingImages === 0) {
        showInvalidMessage('Please upload at least one product image.');
        return;
    }

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
    
    // Get sizes
    const isSimpleProduct = document.getElementById('editNoSizeColorRequired').checked;
    let selectedSizes = [];
    
    if (!isSimpleProduct) {
        selectedSizes = editSelectedSizes;
    }

    if (!originalSku || !name || !category || !price) {
        showInvalidMessage("Please fill in all required fields");
        return;
    }

    const formData = new FormData();
    formData.append("originalSku", originalSku);
    formData.append("name", name);
    formData.append("category", category);
    formData.append("price", price);
    formData.append("productSizes", selectedSizes.join(','));
formData.append("isSimpleProduct", isSimpleProduct ? '1' : '0');
    
    // Add product information fields for simple products
    if (isSimpleProduct) {
        const brandInput = document.getElementById("editProductBrand");
        const materialInput = document.getElementById("editProductMaterial");
        const dimensionsInput = document.getElementById("editProductDimensions");
        const infoInput = document.getElementById("editProductInfo");
        
        if (brandInput && brandInput.value) {
            formData.append("productBrand", brandInput.value);
        }
        if (materialInput && materialInput.value) {
            formData.append("productMaterial", materialInput.value);
        }
        if (dimensionsInput && dimensionsInput.value) {
            formData.append("productDimensions", dimensionsInput.value);
        }
        if (infoInput && infoInput.value) {
            formData.append("productInfo", infoInput.value);
        }
    }

    const imageInput = document.getElementById("editProductImages");
    if (imageInput && imageInput.files) {
        for (let i = 0; i < imageInput.files.length; i++) {
            formData.append("editProductImages[]", imageInput.files[i]);
        }
    }

    fetch("../../back-end/update/editProduct.php", {
        method: "POST",
        body: formData,
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
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
                        size_color_quantities: data.size_color_quantities || products[productIndex].size_color_quantities,
                        color: data.color || products[productIndex].color,
                    };

                    products[productIndex] = updatedProduct;
                    localStorage.setItem("inventoryProducts", JSON.stringify(products));
                    filterProducts();
                }

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
