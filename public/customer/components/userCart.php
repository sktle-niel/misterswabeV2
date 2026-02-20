<!-- Cart Overlay -->
<div class="cart-overlay" id="cartOverlay" onclick="closeCart()"></div>

<!-- Cart Sidebar -->
<div class="cart-sidebar" id="cartSidebar">
    <!-- Cart Header -->
    <div class="cart-header">
        <h2>Shopping Cart</h2>
        <button class="cart-close-btn" onclick="closeCart()" aria-label="Close cart">
            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <!-- Cart Content Will Be Loaded Here -->
    <div class="cart-content-container" id="cartContent">
        <div class="cart-loading">
            <div class="cart-loading-spinner"></div>
        </div>
    </div>
</div>

<script>
    let cartIsOpen = false;

    function openCart() {
        cartIsOpen = true;
        document.getElementById('cartOverlay').classList.add('active');
        document.getElementById('cartSidebar').classList.add('active');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
        
        // Load cart content from /userCart.php
        loadCartContent();
    }

    function closeCart() {
        cartIsOpen = false;
        document.getElementById('cartOverlay').classList.remove('active');
        document.getElementById('cartSidebar').classList.remove('active');
        document.body.style.overflow = ''; // Restore scrolling
    }

    function loadCartContent() {
        const cartContent = document.getElementById('cartContent');

        // Get cart from localStorage
        const cart = JSON.parse(localStorage.getItem('cart')) || [];

        if (cart.length === 0) {
            cartContent.innerHTML = `
                <div class="cart-empty">
                    <p>Your cart is empty</p>
                    <p>Add some products to get started!</p>
                </div>
            `;
            return;
        }

        // Generate cart HTML
        let cartHTML = '<div class="cart-items">';

        cart.forEach(item => {
            // Extract numeric price (remove ₱ and handle old price if present)
            const priceMatch = item.price.match(/₱([\d,]+)/);
            const price = priceMatch ? parseFloat(priceMatch[1].replace(',', '')) : 0;

            cartHTML += `
                <div class="cart-item" data-id="${item.id}">
                    <div class="cart-item-image-container">
                        <img src="${item.image}" alt="${item.name}" class="cart-item-image">
                    </div>
                    <div class="cart-item-content">
                        <div class="cart-item-header">
                            <h4 class="cart-item-name">${item.name}</h4>
                            <button class="cart-item-remove" onclick="removeCartItem(${item.id})" title="Remove item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                        <div class="cart-item-details">
                            <span class="cart-item-variant">Size: ${item.size}</span>
                            <span class="cart-item-variant">Color: ${item.color}</span>
                        </div>
                        <div class="cart-item-footer">
                            <div class="cart-item-price">${item.price}</div>
                            <div class="cart-item-quantity-controls">
                                <button class="quantity-btn quantity-decrease" onclick="updateCartItemQuantity(${item.id}, ${item.quantity - 1})" ${item.quantity <= 1 ? 'disabled' : ''}>
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                                <span class="quantity-value">${item.quantity}</span>
                                <button class="quantity-btn quantity-increase" onclick="updateCartItemQuantity(${item.id}, ${item.quantity + 1})">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        cartHTML += '</div>';

        // Calculate total amount
        let totalAmount = 0;
        cart.forEach(item => {
            const priceMatch = item.price.match(/₱([\d,]+)/);
            const price = priceMatch ? parseFloat(priceMatch[1].replace(',', '')) : 0;
            totalAmount += price * item.quantity;
        });

        // Add cart footer with total
        cartHTML += `
            <div class="cart-footer">
                <div class="cart-total-row">
                    <span class="cart-total-label">Total Amount:</span>
                    <span class="cart-total-amount">₱${totalAmount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>
                </div>
            </div>
        `;

        cartContent.innerHTML = cartHTML;


    }

    // Function to update item quantity
    function updateCartItemQuantity(itemId, newQuantity) {
        if (newQuantity < 1) return;

        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const itemIndex = cart.findIndex(item => item.id === itemId);

        if (itemIndex !== -1) {
            cart[itemIndex].quantity = newQuantity;
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCartContent(); // Refresh cart display
        }
    }

    // Function to remove item from cart
    function removeCartItem(itemId) {
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        cart = cart.filter(item => item.id !== itemId);
        localStorage.setItem('cart', JSON.stringify(cart));
        loadCartContent(); // Refresh cart display
    }

    // Add click event to cart icon when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        // Find all cart icon links and add click event
        const cartLinks = document.querySelectorAll('a[title="Cart"]');
        cartLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                openCart();
            });
        });

        // Close cart with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && cartIsOpen) {
                closeCart();
            }
        });
    });

    // Optional: Function to refresh cart (can be called from other scripts)
    function refreshCart() {
        if (cartIsOpen) {
            loadCartContent();
        }
    }

</script>


<style>
    .cart-sidebar {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .cart-content-container {
        flex: 1;
        overflow-y: auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
    }

    .cart-items {
        flex: 1;
        padding-bottom: 20px;
    }

    .cart-footer {
        border-top: 2px solid #e5e7eb;
        background: #ffffff;
        padding: 20px;
        margin-top: auto;
        box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.05);
    }

    .cart-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .cart-total-label {
        font-size: 16px;
        font-weight: 600;
        color: #374151;
    }

    .cart-total-amount {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }

</style>