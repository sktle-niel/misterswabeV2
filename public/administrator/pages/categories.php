<?php
include '../../auth/sessionCheck.php';


$modalId = 'deleteCategoryModal';
$title = 'Delete Category';
$message = 'Are you sure you want to delete this category? This action cannot be undone.';
$cancelText = 'Cancel';
$confirmText = 'Delete Category';
$confirmFunction = 'confirmDeleteCategory';
$closeFunction = 'closeDeleteCategoryModal';
include 'components/deleteModal.php';
?>

<div class="main-content">
    <div class="content-header">
        <div>
            <h2 class="page-title">Categories</h2>
            <p class="page-subtitle">Organize your products into categories</p>
        </div>
        <button style="padding: 12px 24px; background: #000; color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 8px;" onclick="openCategoryModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Add Category
        </button>
    </div>
    
    <!-- Categories Grid -->
    <div id="categoriesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: var(--spacing-lg);">
        <!-- Categories will be loaded dynamically -->
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal-overlay" id="categoryModalOverlay" onclick="closeCategoryModalOnOverlay(event)">
    <div class="modal-content" style="max-width: 500px;">
        <button class="close-btn" onclick="closeCategoryModal()">×</button>

        <div class="modal-inner">
            <h2 style="grid-column: span 2; text-align: center; margin-bottom: 20px;">Add New Category</h2>

            <form id="categoryForm" style="grid-column: span 2; display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <label for="categoryName" style="display: block; font-weight: 600; margin-bottom: 8px;">Category Name</label>
                    <input type="text" id="categoryName" required style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 16px;">
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeCategoryModal()" style="padding: 12px 24px; background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 6px; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 12px 24px; background: #333; color: white; border: none; border-radius: 6px; cursor: pointer;">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal-overlay" id="editCategoryModalOverlay" onclick="closeEditCategoryModalOnOverlay(event)">
    <div class="modal-content" style="max-width: 500px;">
        <button class="close-btn" onclick="closeEditCategoryModal()">×</button>

        <div class="modal-inner">
            <h2 style="grid-column: span 2; text-align: center; margin-bottom: 20px;">Edit Category</h2>

            <form id="editCategoryForm" style="grid-column: span 2; display: flex; flex-direction: column; gap: 20px;">
                <div>
                    <label for="editCategoryName" style="display: block; font-weight: 600; margin-bottom: 8px;">Category Name</label>
                    <input type="text" id="editCategoryName" required style="width: 100%; padding: 12px; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 16px;">
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeEditCategoryModal()" style="padding: 12px 24px; background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 6px; cursor: pointer;">Cancel</button>
                    <button type="submit" style="padding: 12px 24px; background: #333; color: white; border: none; border-radius: 6px; cursor: pointer;">Update Category</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../../../src/js/category.js"></script>
