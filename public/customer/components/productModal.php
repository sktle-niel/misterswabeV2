<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModalOnOverlay(event)">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal()">×</button>
        
        <div class="modal-inner">
            <!-- Left Side - Product Images -->
            <div class="product-image-section">
                <img 
                    src="https://via.placeholder.com/600x800/f5f5f5/333333?text=Summer+Collection" 
                    alt="Elegant Summer Maxi Dress"     
                    class="main-image"
                    id="mainImage"
                >
            </div>

            <!-- Right Side - Product Info -->
            <div class="product-info-section">
                <h2 class="product-title" id="productTitle"></h2>

                <div class="product-price" id="productPrice"></div>

                <!-- Color Selection -->
                <div class="color-section">
                    <label class="section-label">Color</label>
                    <div class="color-options" id="colorOptions">
                        <!-- Color options will be populated dynamically -->
                    </div>
                </div>


                <!-- Size Selection -->
                <div class="size-section">
                    <label class="section-label">Size</label>
                    <div class="size-options">
                        <button class="size-option" onclick="selectSize(this)">Small</button>
                        <button class="size-option" onclick="selectSize(this)">Medium</button>
                        <button class="size-option" onclick="selectSize(this)">Large</button>
                        <button class="size-option" onclick="selectSize(this)">X-Large</button>
                    </div>
                </div>

                <!-- Quantity -->
                <div class="quantity-section">
                    <label class="section-label">Quantity</label>
                    <div class="quantity-selector">
                        <button class="quantity-btn" onclick="decreaseQuantity()">−</button>
                        <input type="number" class="quantity-input" value="1" min="1" id="quantityInput" readonly>
                        <button class="quantity-btn" onclick="increaseQuantity()">+</button>
                    </div>
                </div>

                <!-- Add to Cart -->
                <button class="add-to-cart-btn" onclick="addToCart()">Add to Cart</button>
            </div>
        </div>
    </div>
</div>

<style>
.size-qty {
    display: block;
    font-size: 11px;
    color: #666;
    margin-top: 2px;
    font-weight: normal;
}

.size-option.out-of-stock {
    background-color: #f5f5f5;
    color: #999;
    border-color: #ddd;
}

.size-option.selected {
    background-color: #000;
    color: #fff;
    border-color: #000;
}
</style>
<script src="../../../src/js/modal.js"></script>
