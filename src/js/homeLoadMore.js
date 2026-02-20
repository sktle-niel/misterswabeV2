// Product Pagination with Load More Functionality
document.addEventListener("DOMContentLoaded", function () {
  const productsPerPage = 12;
  let currentlyShown = 0;
  const productCards = document.querySelectorAll(".product-card");
  const loadMoreBtn = document.getElementById("loadMoreBtn");
  const totalProducts = productCards.length;
  const STORAGE_KEY = "productLoadMoreState";
  const PAGE_ID_KEY = "productPageId";

  const currentPageId = window.location.pathname + window.location.search;

  productCards.forEach((card) => {
    card.classList.add("hidden");
  });

  // Function to save state to sessionStorage
  function saveState() {
    sessionStorage.setItem(STORAGE_KEY, currentlyShown.toString());
    sessionStorage.setItem(PAGE_ID_KEY, currentPageId);
  }

  // Function to load state from sessionStorage
  function loadState() {
    const savedPageId = sessionStorage.getItem(PAGE_ID_KEY);

    if (savedPageId === currentPageId) {
      const savedState = sessionStorage.getItem(STORAGE_KEY);
      if (savedState) {
        return parseInt(savedState, 10);
      }
    } else {
      clearState();
    }
    return 0;
  }

  // Function to clear state
  function clearState() {
    sessionStorage.removeItem(STORAGE_KEY);
    sessionStorage.removeItem(PAGE_ID_KEY);
  }

  // Function to show products
  function showProducts(count) {
    const endIndex = Math.min(currentlyShown + count, totalProducts);
    for (let i = currentlyShown; i < endIndex; i++) {
      productCards[i].classList.remove("hidden");
    }
    currentlyShown = endIndex;

    saveState();

    // Hide load more button if all products are shown
    if (currentlyShown >= totalProducts) {
      loadMoreBtn.style.display = "none";
    }

    updateButtonText();
  }

  function updateButtonText() {
    const remaining = totalProducts - currentlyShown;
    if (remaining > 0) {
      loadMoreBtn.textContent = `Load More Products (${remaining} remaining)`;
    }
  }

  // Check if this is a page reload or fresh navigation
  const navigation = performance.getEntriesByType("navigation")[0];
  const isReload = navigation && navigation.type === "reload";

  // Restore previous state only if it's a reload
  let savedCount = 0;
  if (isReload) {
    savedCount = loadState();
  } else {
    clearState();
  }

  if (savedCount > 0) {
    showProducts(savedCount);
  } else {
    // Show initial 12 products
    showProducts(productsPerPage);
  }

  // Load more button click event
  loadMoreBtn.addEventListener("click", function () {
    showProducts(productsPerPage);

    const firstNewProduct =
      productCards[
        currentlyShown -
          Math.min(
            productsPerPage,
            totalProducts - (currentlyShown - productsPerPage),
          )
      ];
    if (firstNewProduct) {
      setTimeout(() => {
        firstNewProduct.scrollIntoView({
          behavior: "smooth",
          block: "nearest",
        });
      }, 100);
    }
  });
});
