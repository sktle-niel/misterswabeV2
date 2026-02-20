// Function to show success message
function showSuccessMessage(message) {
  const successMessage = document.getElementById("successMessage");
  const successText = successMessage.querySelector(".success-text");
  successText.textContent = message;
  successMessage.style.display = "block";
  setTimeout(() => {
    successMessage.style.display = "none";
  }, 3000); // Hide after 3 seconds
}

// Function to show invalid message
function showInvalidMessage(message) {
  const invalidMessage = document.getElementById("invalidMessage");
  const invalidText = invalidMessage.querySelector(".invalid-text");
  invalidText.textContent = message;
  invalidMessage.style.display = "block";
  setTimeout(() => {
    invalidMessage.style.display = "none";
  }, 3000); // Hide after 3 seconds
}

// Category storage functions
// Fetch categories from database
async function getCategories() {
  try {
    const response = await fetch("../../back-end/read/fetchCategory.php");
    const result = await response.json();
    if (result.success) {
      return result.categories;
    } else {
      console.error("Failed to fetch categories:", result.message);
      return [];
    }
  } catch (error) {
    console.error("Error fetching categories:", error);
    return [];
  }
}

async function addCategory(name) {
  try {
    const response = await fetch("../../back-end/create/addCategory.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ name }),
    });
    const result = await response.json();
    if (result.success) {
      renderCategories();
      showSuccessMessage("Category added successfully!");
    } else {
      showInvalidMessage(result.message);
    }
  } catch (error) {
    alert("Error adding category: " + error.message);
  }
}

let categoryToDelete = null;

function deleteCategory(id) {
  categoryToDelete = id;
  document.getElementById("deleteCategoryModalOverlay").style.display = "flex";
}

async function confirmDeleteCategory() {
  if (!categoryToDelete) return;

  try {
    const response = await fetch(
      "../../back-end/delete/removeCategory.php",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id: categoryToDelete }),
      },
    );
    const result = await response.json();
    if (result.success) {
      showSuccessMessage("Category deleted successfully!");
      renderCategories();
      closeDeleteCategoryModal();
    } else {
      showInvalidMessage(result.message);
    }
  } catch (error) {
    alert("Error deleting category: " + error.message);
  }
  categoryToDelete = null;
}

async function updateCategory(id, newName) {
  try {
    const response = await fetch("../../back-end/update/editCategory.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ id, name: newName }),
    });
    const result = await response.json();
    if (result.success) {
      showSuccessMessage("Category updated successfully!");
      renderCategories();
    } else {
      showInvalidMessage(result.message);
    }
  } catch (error) {
    alert("Error updating category: " + error.message);
  }
}

// Render categories dynamically
async function renderCategories() {
  const categories = await getCategories();
  const grid = document.getElementById("categoriesGrid");

  // Clear existing categories
  grid.innerHTML = "";

  // If no categories, show message
  if (categories.length === 0) {
    grid.innerHTML =
      '<p style="text-align: center; color: var(--text-secondary); padding: 2rem;">No categories found. Add your first category!</p>';
    return;
  }

  // Render categories from database
  categories.forEach((category) => {
    grid.appendChild(createCategoryCard(category));
  });
}

function createCategoryCard(category) {
  const card = document.createElement("div");
  card.className = "card";
  card.style.position = "relative";

  const colors = [
    "rgba(99, 102, 241, 0.2)",
    "rgba(16, 185, 129, 0.2)",
    "rgba(139, 92, 246, 0.2)",
    "rgba(245, 158, 11, 0.2)",
    "rgba(236, 72, 153, 0.2)",
    "rgba(234, 179, 8, 0.2)",
  ];
  const color = colors[Math.floor(Math.random() * colors.length)];

  card.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: var(--spacing-lg);">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, ${color}, ${color.replace("0.2", "0.05")}); border-radius: var(--radius-lg); display: flex; align-items: center; justify-content: center;">
                <div style="width: 40px; height: 40px; background: ${color}; border-radius: var(--radius-md);"></div>
            </div>
            <div style="display: flex; gap: var(--spacing-sm);">
                <button class="btn btn-icon btn-secondary" title="Edit" onclick="editCategory('${category.id}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                </button>
                <button class="btn btn-icon btn-secondary" title="Delete" onclick="deleteCategory('${category.id}')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="3 6 5 6 21 6"></polyline>
                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                    </svg>
                </button>
            </div>
        </div>
        <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: var(--spacing-xs);">${category.name}</h3>
        <p style="font-size: 0.875rem; color: var(--text-secondary);">${category.productCount} products</p>
    `;

  return card;
}

async function editCategory(id) {
  const categories = await getCategories();
  const category = categories.find((cat) => cat.id === id);
  if (category) {
    document.getElementById("editCategoryName").value = category.name;
    document.getElementById("editCategoryForm").dataset.categoryId = id;
    document.getElementById("editCategoryModalOverlay").style.display = "flex";
  } else {
    alert("Category not found");
  }
}

// Modal functions
function openCategoryModal() {
  document.getElementById("categoryModalOverlay").style.display = "flex";
}

function closeCategoryModal() {
  document.getElementById("categoryModalOverlay").style.display = "none";
}

function closeCategoryModalOnOverlay(event) {
  if (event.target === document.getElementById("categoryModalOverlay")) {
    closeCategoryModal();
  }
}

// Edit modal functions
function closeEditCategoryModal() {
  document.getElementById("editCategoryModalOverlay").style.display = "none";
}

function closeEditCategoryModalOnOverlay(event) {
  if (event.target === document.getElementById("editCategoryModalOverlay")) {
    closeEditCategoryModal();
  }
}

// Delete modal functions
function closeDeleteCategoryModal() {
  document.getElementById("deleteCategoryModalOverlay").style.display = "none";
  categoryToDelete = null;
}

function closeDeleteCategoryModalOnOverlay(event) {
  if (event.target === document.getElementById("deleteCategoryModalOverlay")) {
    closeDeleteCategoryModal();
  }
}

// Handle form submission
document
  .getElementById("categoryForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();
    const categoryName = document.getElementById("categoryName").value.trim();

    if (categoryName) {
      addCategory(categoryName);
      closeCategoryModal();
      this.reset();
    }
  });

// Handle edit form submission
document
  .getElementById("editCategoryForm")
  .addEventListener("submit", function (event) {
    event.preventDefault();
    const newName = document.getElementById("editCategoryName").value.trim();
    const categoryId = this.dataset.categoryId;

    if (newName && categoryId) {
      updateCategory(categoryId, newName);
      closeEditCategoryModal();
      this.reset();
    }
  });

// Initialize categories on page load
document.addEventListener("DOMContentLoaded", function () {
  renderCategories();
});
