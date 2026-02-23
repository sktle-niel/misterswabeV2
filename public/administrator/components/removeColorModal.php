<?php
// Remove Color Confirmation Modal
?>

<!-- Remove Color Confirmation Modal -->
<div class="modal-overlay" id="removeColorModalOverlay" onclick="closeRemoveColorModalOnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10001;">
    <div class="modal-content" style="max-width: 450px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="closeRemoveColorModal()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 24px; font-weight: 700; color: #111827;">Remove Color</h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                Are you sure you want to remove the color "<span id="removeColorName" style="font-weight: 600; color: #374151;"></span>"?
            </p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 40px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 48px; margin-bottom: 15px;">🗑️</div>
                <p style="color: #ef4444; font-size: 14px; font-weight: 500;">This will delete all quantity for this color.</p>
            </div>

            <!-- Modal Footer -->
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="button" onclick="closeRemoveColorModal()" style="padding: 12px 28px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">Cancel</button>
                <button type="button" onclick="confirmRemoveColor()" style="padding: 12px 32px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);" onmouseover="this.style.background='#dc2626'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.background='#ef4444'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'">Remove Color</button>
            </div>
        </div>
    </div>
</div>

<script>
// Remove Color Modal Functions
function closeRemoveColorModal() {
    document.getElementById("removeColorModalOverlay").style.display = "none";
    if (typeof window.pendingColorRemoval !== 'undefined') {
        window.pendingColorRemoval = null;
    }
}

function closeRemoveColorModalOnOverlay(event) {
    if (event.target === document.getElementById("removeColorModalOverlay")) {
        closeRemoveColorModal();
    }
}
</script>
