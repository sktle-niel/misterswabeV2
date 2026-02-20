// Product Pagination with Load More Functionality
document.addEventListener("DOMContentLoaded", function () {
  const productsPerPage = 12;
  let currentlyShown = 12; // Start with 12 already shown
  const productCards = document.querySelectorAll(".product-card");
  const loadMoreBtn = document.getElementById("loadMoreBtn");
  const totalProducts = productCards.length;
  const STORAGE_KEY = "productLoadMoreState";
  const PAGE_ID_KEY = "productPageId";

  const currentPageId = window.location.pathname + window.location.search;

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
    return 12;
  }

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

    if (loadMoreBtn && currentlyShown >= totalProducts) {
      loadMoreBtn.style.display = "none";
    }

    updateButtonText();
  }

  // Function to update button text
  function updateButtonText() {
    if (loadMoreBtn) {
      const remaining = totalProducts - currentlyShown;
      if (remaining > 0) {
        loadMoreBtn.textContent = `Load More Products (${remaining} remaining)`;
      }
    }
  }

  // Check if this is a page reload or fresh navigation
  const navigation = performance.getEntriesByType("navigation")[0];
  const isReload = navigation && navigation.type === "reload";

  // Restore previous state only if it's a reload
  if (isReload) {
    const savedCount = loadState();
    if (savedCount > 12) {
      currentlyShown = 12;
      showProducts(savedCount - 12);
    }
  } else {
    clearState();
    saveState();
  }

  updateButtonText();

  if (loadMoreBtn && currentlyShown >= totalProducts) {
    loadMoreBtn.style.display = "none";
  }

  if (loadMoreBtn) {
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
  }
});
