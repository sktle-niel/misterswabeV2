<?php
// Default values for the delete modal
$modalId = 'deleteModal';
$title = 'Delete Product';
$message = 'Are you sure you want to delete this product? This action cannot be undone.';
$cancelText = 'Cancel';
$confirmText = 'Delete';
$confirmFunction = 'confirmDelete';
$closeFunction = 'closeDeleteModal';
?>

<!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="<?php echo $modalId; ?>Overlay" onclick="<?php echo $closeFunction; ?>OnOverlay(event)" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; justify-content: center; align-items: center; z-index: 10000;">
    <div class="modal-content" style="max-width: 500px; width: 90%; background: white; border-radius: 16px; padding: 0; position: relative; max-height: 90vh; overflow-y: auto; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);" onclick="event.stopPropagation();">
        <!-- Modal Header -->
        <div style="padding: 30px 40px; border-bottom: 1px solid #e5e7eb; position: sticky; top: 0; background: white; z-index: 10; border-radius: 16px 16px 0 0;">
            <button class="close-btn" onclick="<?php echo $closeFunction; ?>()" style="position: absolute; top: 20px; right: 25px; background: none; border: none; font-size: 28px; cursor: pointer; color: #9ca3af; line-height: 1; transition: color 0.2s;" onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">×</button>
            <h2 style="margin: 0 0 8px 0; font-size: 28px; font-weight: 700; color: #111827;"><?php echo $title; ?></h2>
            <p style="margin: 0; color: #6b7280; font-size: 15px; line-height: 1.5;">
                <?php echo $message; ?>
            </p>
        </div>

        <!-- Modal Body -->
        <div style="padding: 40px;">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="font-size: 48px; color: #ef4444; margin-bottom: 15px;">⚠️</div>
            </div>

            <!-- Modal Footer -->
            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 24px; border-top: 1px solid #e5e7eb;">
                <button type="button" onclick="<?php echo $closeFunction; ?>()" style="padding: 12px 28px; background: #f3f4f6; color: #374151; border: 2px solid #e5e7eb; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'"><?php echo $cancelText; ?></button>
                <button type="button" onclick="<?php echo $confirmFunction; ?>()" style="padding: 12px 32px; background: #ef4444; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);" onmouseover="this.style.background='#dc2626'; this.style.boxShadow='0 4px 6px rgba(0, 0, 0, 0.1)'" onmouseout="this.style.background='#ef4444'; this.style.boxShadow='0 1px 3px rgba(0, 0, 0, 0.1)'"><?php echo $confirmText; ?></button>
            </div>
        </div>
    </div>
</div>

<script>
// Reusable delete modal functions
function <?php echo $closeFunction; ?>() {
    document.getElementById("<?php echo $modalId; ?>Overlay").style.display = "none";
    if (typeof window.categoryToDelete !== 'undefined') {
        window.categoryToDelete = null;
    }
}

function <?php echo $closeFunction; ?>OnOverlay(event) {
    if (event.target === document.getElementById("<?php echo $modalId; ?>Overlay")) {
        <?php echo $closeFunction; ?>();
    }
}

function <?php echo $confirmFunction; ?>() {
    // This function should be overridden by the including page
    console.log('Confirm delete function called');
}
</script>
