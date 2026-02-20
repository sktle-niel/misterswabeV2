<?php
include 'components/skuScanner.php';
include 'status/successStatus.php';
include 'status/invalidStatus.php';
include '../../auth/sessionCheck.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    require_once '../../config/connection.php';

    $customer_name = $_POST['customerName'] ?? '';
    $total_amount = $_POST['totalAmount'] ?? 0;
    $payment_method = $_POST['paymentMethod'] ?? '';
    $products = $_POST['products'] ?? [];

    $conn->begin_transaction();

    try {
        // Generate 7-digit sale id
        $sale_id = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);

        // Insert into sales table
        $stmt = $conn->prepare("INSERT INTO sales (id, customer_name, total_amount, payment_method) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssds", $sale_id, $customer_name, $total_amount, $payment_method);
        $stmt->execute();

        // Insert each product into sale_items table
        $stmt_item = $conn->prepare("INSERT INTO sale_items (id, sale_id, product_id, quantity, price) VALUES (?, ?, ?, ?, ?)");
        foreach ($products as $product) {
            $item_id = str_pad(mt_rand(1, 9999999), 7, '0', STR_PAD_LEFT);
            $product_id = $product['id'] ?? 0;
            $quantity = $product['quantity'] ?? 0;
            $price = $product['price'] ?? 0;
            $stmt_item->bind_param("ssiid", $item_id, $sale_id, $product_id, $quantity, $price);
            $stmt_item->execute();
        }

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>
<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Point of Sale</h2>
            <p class="page-subtitle">Create a new sales transaction</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Sales Information</h3>
        </div>

        <form id="addSalesForm" class="form">
            <div class="products-section">
                <h4>Products</h4>
                <div id="productsContainer">
                    <div class="product-row">
                        <div class="form-group">
                            <label>Product SKU</label>
                            <div class="product-scanner">
                                <input type="text" name="products[0][sku]" class="product-sku" placeholder="Scan barcode or enter SKU" required autocomplete="off">
                                <button type="button" class="btn btn-icon scan-btn" style="background-color: #000; color: #fff;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                                        <circle cx="12" cy="13" r="4"></circle>
                                    </svg>
                                </button>
                            </div>
                            <input type="hidden" name="products[0][id]" class="product-id">
                            <span class="product-name-display"></span>
                        </div>

                        <div class="form-group">
                            <label>Quantity</label>
                            <input type="number" name="products[0][quantity]" min="1" value="1" required>
                        </div>

                        <div class="form-group">
                            <label>Size</label>
                            <select name="products[0][size]" class="product-size">
                                <option value="">Select Size</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Price</label>
                            <input type="number" name="products[0][price]" step="0.01" readonly>
                        </div>

                        <button type="button" class="btn btn-icon btn-danger remove-product">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="button" id="addProductBtn" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add Product
                </button>
            </div>

            <div class="form-group total-section">
                <label><strong>Total Amount: ₱<span id="totalAmount">0.00</span></strong></label>
                <input type="hidden" id="totalAmountInput" name="totalAmount" value="0.00">
            </div>

            <div class="form-group">
                <label for="paymentMethod">Payment Method</label>
                <select id="paymentMethod" name="paymentMethod" required>
                    <option value="">Select Payment Method</option>
                    <option value="cash">Cash</option>
                    <option value="online transfer">Online transfer</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-success" style="background-color: #000; color: #fff;">Create Sale</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>

<!-- Scanner Modal -->
<div id="scannerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Scan Barcode</h3>
            <button type="button" class="btn btn-icon btn-close" onclick="closeScanner()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <div id="scanner-container">
                <video id="scanner-video" autoplay playsinline></video>
            </div>
            <div class="scanner-status">
                <p>Position the barcode in front of the camera</p>
            </div>
            <div class="scanner-controls">
                <button type="button" class="btn btn-secondary" onclick="closeScanner()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.19.3/umd/index.min.js"></script>
<script src="../../../src/js/addSales.js"></script>