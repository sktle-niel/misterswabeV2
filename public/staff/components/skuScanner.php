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
                <canvas id="scanner-canvas" style="display: none;"></canvas>
            </div>
            <div class="scanner-controls">
                <button type="button" class="btn btn-secondary" onclick="closeScanner()">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@zxing/library@0.19.3/umd/index.min.js"></script>
<script>
let codeReader;
let currentRow;

function openScanner(row) {
    currentRow = row;
    document.getElementById('scannerModal').style.display = 'block';

    // Initialize ZXing barcode reader
    codeReader = new ZXing.BrowserMultiFormatReader();

    codeReader.decodeFromVideoDevice(null, 'scanner-video', (result, err) => {
        if (result) {
            const code = result.text;
            const skuInput = currentRow.querySelector('.product-sku');
            skuInput.value = code;
            lookupProductBySKU(code, currentRow);
            closeScanner();
        }
        if (err && !(err instanceof ZXing.NotFoundException)) {
            console.error(err);
        }
    }).catch((err) => {
        console.error('Error starting scanner:', err);
        alert('Camera access denied or not available. Please enter SKU manually.');
        closeScanner();
    });
}

function closeScanner() {
    document.getElementById('scannerModal').style.display = 'none';
    if (codeReader) {
        codeReader.reset();
        codeReader = null;
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('scannerModal');
    if (event.target == modal) {
        closeScanner();
    }
}
</script>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: var(--border-radius);
    width: 90%;
    max-width: 500px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-color);
}

.modal-header h3 {
    margin: 0;
}

.btn-close {
    background: none;
    border: none;
    cursor: pointer;
    color: var(--text-secondary);
}

.modal-body {
    padding: var(--spacing-lg);
}

#scanner-container {
    position: relative;
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
}

#scanner-video {
    width: 100%;
    border-radius: var(--border-radius);
}

.scanner-controls {
    text-align: center;
    margin-top: var(--spacing-md);
}

.product-scanner {
    display: flex;
    gap: var(--spacing-sm);
    align-items: stretch;
}

.product-scanner input {
    flex: 1;
}

.product-scanner .btn {
    flex-shrink: 0;
}
</style>
