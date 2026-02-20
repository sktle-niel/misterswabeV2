<?php
include '../../config/connection.php';

$query = "SELECT id, name FROM categories WHERE 1";
$result = $conn->query($query);
$categories = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}
?>

<!-- Top Banner -->
<style>
  .nav-container a{
    color: #fff !important;
  }
</style>

<div class="announcement-bar">
  RIZAL AVENUE, PUERTO PRINCESA CITY, PALAWAN. 5300 | ALWAYS OPEN 9:00 AM - 8:00 PM 
</div>

<!-- Header -->
<header>
  <div class="header-container">
    <div class="logo-section">
      <a href="/public/customer/main.php?page=home" class="logo">SWABE COLLECTION</a>
      <div class="tagline">Your Style, Your Way</div>
    </div>
    <div class="header-icons">
      <!-- Search Container -->
      <div class="search-container" id="searchContainer">
        <input
          type="text"
          class="search-input"
          placeholder="Search products..."
          id="searchInput"
        />
        <button class="search-icon-btn search-close" id="searchClose">
          <svg
            width="18"
            height="18"
            viewBox="0 0 24 24"
            fill="none"
            stroke="black"
            stroke-width="2"
          >
            <line x1="18" y1="6" x2="6" y2="18"></line>
            <line x1="6" y1="6" x2="18" y2="18"></line>
          </svg>
        </button>
      </div>

      <a
        href="#"
        class="icon-link search-icon-animated"
        id="searchIcon"
        title="Search"
      >
        <svg
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <circle cx="11" cy="11" r="8"></circle>
          <path d="m21 21-4.35-4.35"></path>
        </svg>
      </a>
      <a href="#" class="icon-link" title="Cart">
        <svg
          width="20"
          height="20"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <circle cx="9" cy="21" r="1"></circle>
          <circle cx="20" cy="21" r="1"></circle>
          <path
            d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"
          ></path>
        </svg>
      </a>
    </div>
  </div>
</header>

<!-- Navigation -->
<nav>
  <div class="nav-container">
    <?php foreach ($categories as $category): ?>
      <a href="/public/customer/main.php?page=products&category=<?php echo urlencode($category['name']); ?>" class="nav-link"><?php echo htmlspecialchars($category['name']); ?></a>
    <?php endforeach; ?>
  </div>
</nav>
