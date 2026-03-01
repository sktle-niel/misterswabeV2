<!-- Summary Modal -->
<div class="modal-overlay" id="summaryModalOverlay" onclick="closeSummaryModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 600px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="closeSummaryModal()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 24px; font-weight: 700; color: #111827;">Product Summary</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                Complete product information and stock details
            </p>
        </div>

        <!-- Modal Body -->
        <div id="summaryModalBody" style="padding: 30px 40px;">
            <!-- Content will be dynamically populated -->
        </div>

        <!-- Modal Footer -->
        <div style="padding: 20px 40px; border-top: 1px solid #e5e7eb; display: flex; justify-content: flex-end;">
            <button type="button" onclick="closeSummaryModal()" 
                style="padding: 12px 28px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s;" 
                onmouseover="this.style.background='#e5e7eb'" 
                onmouseout="this.style.background='#f3f4f6'">
                Close
            </button>
        </div>
    </div>
</div>

<script>
function openSummaryModal(sku) {
    // Find the product data
    const product = products.find(p => p.sku === sku);
    if (!product) {
        alert('Product not found');
        return;
    }

    // Parse size_color_quantities properly
    let sizeColorQuantities = null;
    if (product.size_color_quantities && product.size_color_quantities !== 'null' && product.size_color_quantities !== '{}') {
        try {
            sizeColorQuantities = typeof product.size_color_quantities === 'string' 
                ? JSON.parse(product.size_color_quantities) 
                : product.size_color_quantities;
        } catch (e) {
            sizeColorQuantities = null;
        }
    }
    
    // Check if it's a simple product (no sizes)
    // A product has sizes if it has size_color_quantities with data
    const hasSizeColorQuantities = sizeColorQuantities && 
                                    typeof sizeColorQuantities === 'object' &&
                                    Object.keys(sizeColorQuantities).length > 0;
    
    const isSimpleProduct = !hasSizeColorQuantities && (!product.size || product.size === 'N/A' || product.size === '');

    // Build summary content
    let summaryHtml = '';

    // Product Image and Basic Info
    summaryHtml += `
        <div style="display: flex; gap: 20px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid #e5e7eb;">
            <div style="flex-shrink: 0;">
                <img src="${product.image}" alt="${product.name}" style="width: 120px; height: 120px; border-radius: 12px; object-fit: cover; border: 1px solid #e5e7eb;">
            </div>
            <div style="flex: 1;">
                <h3 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700; color: #111827;">${product.name}</h3>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">SKU: <span style="color: #374151; font-weight: 500;">${product.sku}</span></p>
                <p style="margin: 0 0 8px 0; color: #6b7280; font-size: 14px;">Category: <span style="color: #374151; font-weight: 500;">${product.category}</span></p>
                <p style="margin: 0; color: #6b7280; font-size: 14px;">Price: <span style="color: #10b981; font-weight: 600;">${product.price}</span></p>
            </div>
        </div>
    `;

    // Stock Information
    const stockStatusClass = product.status === 'In Stock' ? 'badge-success' : (product.status === 'Low Stock' ? 'badge-warning' : 'badge-danger');
    summaryHtml += `
        <div style="margin-bottom: 24px;">
            <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #374151;">Stock Information</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div style="background: #f9fafb; padding: 16px; border-radius: 8px;">
                    <p style="margin: 0 0 4px 0; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Total Stock</p>
                    <p style="margin: 0; font-size: 24px; font-weight: 700; color: #111827;">${product.stock}</p>
                </div>
                <div style="background: #f9fafb; padding: 16px; border-radius: 8px;">
                    <p style="margin: 0 0 4px 0; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px;">Status</p>
                    <p style="margin: 0; font-size: 16px; font-weight: 600;"><span class="badge ${stockStatusClass}">${product.status}</span></p>
                </div>
            </div>
        </div>
    `;

    // Product Information (always show if available - for all products)
    if (product.information && product.information !== 'null') {
        try {
            const productInfo = typeof product.information === 'string' ? JSON.parse(product.information) : product.information;
            
            if (productInfo && typeof productInfo === 'object') {
                let infoHtml = '';
                
                if (productInfo.brand) {
                    infoHtml += `
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                            <span style="color: #6b7280; font-size: 14px;">Brand</span>
                            <span style="color: #374151; font-size: 14px; font-weight: 500;">${productInfo.brand}</span>
                        </div>
                    `;
                }
                
                if (productInfo.material) {
                    infoHtml += `
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                            <span style="color: #6b7280; font-size: 14px;">Material</span>
                            <span style="color: #374151; font-size: 14px; font-weight: 500;">${productInfo.material}</span>
                        </div>
                    `;
                }
                
                if (productInfo.dimensions) {
                    infoHtml += `
                        <div style="display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                            <span style="color: #6b7280; font-size: 14px;">Dimensions</span>
                            <span style="color: #374151; font-size: 14px; font-weight: 500;">${productInfo.dimensions}</span>
                        </div>
                    `;
                }
                
                if (productInfo.product_info) {
                    infoHtml += `
                        <div style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;">
                            <span style="color: #6b7280; font-size: 14px; display: block; margin-bottom: 4px;">Additional Info</span>
                            <span style="color: #374151; font-size: 14px;">${productInfo.product_info}</span>
                        </div>
                    `;
                }
                
                if (infoHtml) {
                    summaryHtml += `
                        <div>
                            <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #374151;">Product Information</h4>
                            <div style="background: #f9fafb; padding: 16px; border-radius: 8px;">
                                ${infoHtml}
                            </div>
                        </div>
                    `;
                }
            }
        } catch (e) {
            console.log('Error parsing product information:', e);
        }
    }

    // Size and Color Quantities - use the already parsed sizeColorQuantities
    if (hasSizeColorQuantities) {
        let sizeColorHtml = '';
        
        Object.entries(sizeColorQuantities).forEach(([size, colors]) => {
            if (colors && typeof colors === 'object' && Object.keys(colors).length > 0) {
                // Build color quantities string for this size like "black (2), red (2)"
                // Data structure: { "Size": { "Color": { "quantity": 10, "sku": "..." } } }
                const colorParts = Object.entries(colors).map(([color, colorData]) => {
                    // Handle both new nested structure {quantity, sku} and old simple {qty} or direct number
                    let qty = 0;
                    if (typeof colorData === 'object' && colorData !== null) {
                        if (typeof colorData.quantity !== 'undefined') {
                            qty = parseInt(colorData.quantity) || 0;
                        } else if (typeof colorData.qty !== 'undefined') {
                            qty = parseInt(colorData.qty) || 0;
                        }
                    } else if (typeof colorData === 'number') {
                        qty = parseInt(colorData) || 0;
                    }
                    return `${color} (${qty})`;
                });
                const colorStr = colorParts.join(', ');
                
                // Calculate total for this size
                let totalForSize = 0;
                Object.values(colors).forEach((colorData) => {
                    if (typeof colorData === 'object' && colorData !== null) {
                        if (typeof colorData.quantity !== 'undefined') {
                            totalForSize += parseInt(colorData.quantity) || 0;
                        } else if (typeof colorData.qty !== 'undefined') {
                            totalForSize += parseInt(colorData.qty) || 0;
                        }
                    } else if (typeof colorData === 'number') {
                        totalForSize += parseInt(colorData) || 0;
                    }
                });
                
                sizeColorHtml += `
                    <tr>
                        <td style="padding: 12px 10px; font-size: 14px; color: #374151; border-bottom: 1px solid #e5e7eb; font-weight: 600;">${size}</td>
                        <td style="padding: 12px 10px; font-size: 14px; color: #374151; border-bottom: 1px solid #e5e7eb;">${colorStr}</td>
                        <td style="padding: 12px 10px; font-size: 14px; color: #374151; border-bottom: 1px solid #e5e7eb; text-align: center; font-weight: 600;">${totalForSize}</td>
                    </tr>
                `;
            }
        });

        if (sizeColorHtml) {
            summaryHtml += `
                <div>
                    <h4 style="margin: 0 0 12px 0; font-size: 16px; font-weight: 600; color: #374151;">Size and Color Quantities</h4>
                    <div style="background: #f9fafb; padding: 16px; border-radius: 8px; overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; min-width: 300px;">
                            <thead>
                                <tr>
                                    <th style="padding: 10px; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; text-align: left;">Size</th>
                                    <th style="padding: 10px; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; text-align: left;">Colors (Qty)</th>
                                    <th style="padding: 10px; font-size: 12px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; text-align: center;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${sizeColorHtml}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }
    }

    document.getElementById('summaryModalBody').innerHTML = summaryHtml;
    document.getElementById('summaryModalOverlay').style.display = 'flex';
}

function closeSummaryModal() {
    document.getElementById('summaryModalOverlay').style.display = 'none';
}

function closeSummaryModalOnOverlay(event) {
    if (event.target === document.getElementById('summaryModalOverlay')) {
        closeSummaryModal();
    }
}
</script>
