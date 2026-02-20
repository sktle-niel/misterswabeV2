// Modal Functions
function openModal(card) {
  const name = card.getAttribute("data-name");
  const price = card.getAttribute("data-price");
  const image = card.getAttribute("data-image");
  const sizes = card.getAttribute("data-sizes");
  const colors = card.getAttribute("data-colors");
  const sizeQuantities = card.getAttribute("data-size-quantities");

  document.getElementById("productTitle").textContent = name;
  document.getElementById("productPrice").textContent = price;
  document.getElementById("mainImage").src = image;
  document.getElementById("mainImage").alt = name;

  // Parse size quantities JSON
  let sizeQtyMap = {};
  if (sizeQuantities) {
    try {
      sizeQtyMap = JSON.parse(sizeQuantities);
    } catch (e) {
      console.log("Error parsing size_quantities:", e);
    }
  }

  // Handle color section visibility and population
  const colorSection = document.querySelector(".color-section");
  const colorOptionsContainer = document.getElementById("colorOptions");

  if (colors && colors.trim() !== "") {
    colorSection.style.display = "block"; // Show color section
    colorOptionsContainer.innerHTML = ""; // Clear existing options

    const colorArray = colors.split(",");
    colorArray.forEach((color, index) => {
      const colorButton = document.createElement("button");
      colorButton.className = "color-option";
      colorButton.textContent = color.trim();
      colorButton.onclick = function () {
        selectColor(this);
      };
      colorOptionsContainer.appendChild(colorButton);

      // Auto-select first color option
      if (index === 0) {
        selectColor(colorButton);
      }
    });
  } else {
    colorSection.style.display = "none"; // Hide color section if no colors
  }

  // Handle size section visibility and population
  const sizeSection = document.querySelector(".size-section");
  const sizeOptionsContainer = document.querySelector(".size-options");

  if (sizes && sizes.trim() !== "") {
    sizeSection.style.display = "block"; // Show size section
    sizeOptionsContainer.innerHTML = ""; // Clear existing options

    const sizeArray = sizes.split(",");
    sizeArray.forEach((size, index) => {
      const sizeTrimmed = size.trim();
      const quantity =
        sizeQtyMap[sizeTrimmed] !== undefined ? sizeQtyMap[sizeTrimmed] : 0;

      const sizeButton = document.createElement("button");
      sizeButton.className =
        "size-option" + (quantity <= 0 ? " out-of-stock" : "");
      sizeButton.innerHTML =
        sizeTrimmed + '<span class="size-qty">(' + quantity + ")</span>";
      sizeButton.onclick = function () {
        if (quantity > 0) {
          selectSize(this);
        }
      };

      // Disable button if out of stock
      if (quantity <= 0) {
        sizeButton.disabled = true;
        sizeButton.style.opacity = "0.5";
        sizeButton.style.cursor = "not-allowed";
      }

      sizeOptionsContainer.appendChild(sizeButton);

      // Auto-select first size option with stock
      if (index === 0 && quantity > 0) {
        selectSize(sizeButton);
      }
    });
  } else {
    sizeSection.style.display = "none"; // Hide size section for accessories
  }

  document.getElementById("modalOverlay").style.display = "flex";
}

function closeModal() {
  document.getElementById("modalOverlay").style.display = "none";
}

function closeModalOnOverlay(event) {
  if (event.target === document.getElementById("modalOverlay")) {
    closeModal();
  }
}

// Color Selection
function selectColor(element) {
  document.querySelectorAll(".color-option").forEach((btn) => {
    btn.classList.remove("selected");
  });
  element.classList.add("selected");
}

// Size Selection
function selectSize(element) {
  document.querySelectorAll(".size-option").forEach((btn) => {
    btn.classList.remove("selected");
  });
  element.classList.add("selected");
}

// Quantity Control
function increaseQuantity() {
  const input = document.getElementById("quantityInput");
  input.value = parseInt(input.value) + 1;
}

function decreaseQuantity() {
  const input = document.getElementById("quantityInput");
  if (parseInt(input.value) > 1) {
    input.value = parseInt(input.value) - 1;
  }
}

// Add to Cart
function addToCart() {
  const size = document.querySelector(".size-option.selected");
  const color = document.querySelector(".color-option.selected");
  const quantity = document.getElementById("quantityInput").value;
  const name = document.getElementById("productTitle").textContent;
  const price = document.getElementById("productPrice").textContent;
  const image = document.getElementById("mainImage").src;

  // Check if size section is visible (only require size if sizes are available)
  const sizeSection = document.querySelector(".size-section");
  const isSizeRequired = sizeSection && sizeSection.style.display !== "none";

  if (isSizeRequired && !size) {
    alert("Please select a size");
    return;
  }

  if (!color) {
    alert("Please select a color");
    return;
  }

  // Get existing cart from localStorage or initialize empty array
  let cart = JSON.parse(localStorage.getItem("cart")) || [];

  // Create cart item object
  const cartItem = {
    id: Date.now(), // Simple unique ID
    name: name,
    price: price,
    image: image,
    size: size ? size.textContent : "N/A",
    color: color.textContent,
    quantity: parseInt(quantity),
  };

  // Add item to cart (latest first)
  cart.unshift(cartItem);

  // Save back to localStorage
  localStorage.setItem("cart", JSON.stringify(cart));

  const sizeText = size ? ` in size ${size.textContent}` : "";
  showSuccessMessage(`Added ${quantity} item(s)${sizeText} to cart!`);
  closeModal();

  // Refresh cart if it's open
  if (typeof refreshCart === "function") {
    refreshCart();
  }
}

// Keyboard shortcut to close modal (ESC key)
document.addEventListener("keydown", function (event) {
  if (event.key === "Escape") {
    closeModal();
  }
});

// Show success message
function showSuccessMessage(message) {
  const successMessage = document.getElementById("successMessage");
  const successText = document.querySelector(".success-text");
  successText.textContent = message;
  successMessage.style.display = "flex";
  setTimeout(() => {
    successMessage.style.display = "none";
  }, 3000); // Hide after 3 seconds
}
