// Search Bar Animation
const searchIcon = document.getElementById("searchIcon");
const searchContainer = document.getElementById("searchContainer");
const searchInput = document.getElementById("searchInput");
const searchClose = document.getElementById("searchClose");

console.log("Search elements found:", {
  searchIcon,
  searchContainer,
  searchInput,
  searchClose,
});

searchIcon.addEventListener("click", function (e) {
  e.preventDefault();
  searchContainer.classList.add("active");
  searchInput.focus();
});

searchClose.addEventListener("click", function () {
  searchContainer.classList.remove("active");
  searchInput.value = "";
});

// Close search when clicking outside
document.addEventListener("click", function (e) {
  if (!searchContainer.contains(e.target) && !searchIcon.contains(e.target)) {
    searchContainer.classList.remove("active");
  }
});

// Search functionality
searchInput.addEventListener("keypress", function (e) {
  if (e.key === "Enter") {
    const query = searchInput.value.trim();
    if (query) {
      window.location.href = `${window.location.origin}/public/customer/main.php?page=products&search=${encodeURIComponent(query)}`;
    }
  }
});

// Hero Slider
let currentSlide = 0;
const slides = document.querySelectorAll(".slide");
const dots = document.querySelectorAll(".slider-dot");

function showSlide(index) {
  slides.forEach((slide) => slide.classList.remove("active"));
  dots.forEach((dot) => dot.classList.remove("active"));

  slides[index].classList.add("active");
  dots[index].classList.add("active");
}

function nextSlide() {
  if (slides.length > 0) {
    currentSlide = (currentSlide + 1) % slides.length;
    showSlide(currentSlide);
  }
}

// Auto advance slides
setInterval(nextSlide, 5000);

// Dot navigation
dots.forEach((dot, index) => {
  dot.addEventListener("click", () => {
    currentSlide = index;
    showSlide(currentSlide);
  });
});

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({ behavior: "smooth" });
    }
  });
});
