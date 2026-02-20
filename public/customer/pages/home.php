  <!-- Hero Slider -->
  <div class="hero-slider">
    <div class="slide active">
      <img
        src="https://images.unsplash.com/photo-1556906781-9a412961c28c?w=1600&h=600&fit=crop&q=90"
        alt="Featured Collection"
      />
      <div class="slide-overlay">
        <div class="slide-content">
          <h1>New Sneaker Collection</h1>
          <p>Step up your style game with our latest arrivals</p>
        </div>
      </div>
    </div>
    <div class="slide">
      <img
        src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=1600&h=600&fit=crop&q=90"
        alt="Shirt Collection"
      />
      <div class="slide-overlay">
        <div class="slide-content">
          <h1>Premium T-Shirts</h1>
          <p>Comfort meets style in every piece</p>
        </div>
      </div>
    </div>
    <div class="slide">
      <img
        src="https://images.unsplash.com/photo-1603252109303-2751441dd157?w=1600&h=600&fit=crop&q=90"
        alt="Sale Banner"
      />
      <div class="slide-overlay">
        <div class="slide-content">
          <h1>Flash Sale - Up to 50% OFF</h1>
          <p>Limited time offer on selected items</p>
        </div>
      </div>
    </div>
    <div class="slider-nav">
      <div class="slider-dot active"></div>
      <div class="slider-dot"></div>
      <div class="slider-dot"></div>
    </div>
  </div>

  <!-- Category Banners -->
  <div class="section-header">
    <h2 class="section-title">Shop by Category</h2>
    <p class="section-subtitle">Explore our diverse collection of fashion items</p>
  </div>
  <div class="category-banners">
    <?php
    include '../../back-end/read/fetchCategoriesWithImages.php';
    $categories = fetchCategoriesWithImages($conn);
    
    foreach ($categories as $category) {
        $categoryName = htmlspecialchars($category['name']);
        $categoryImage = htmlspecialchars($category['image']);
        $categoryUrl = 'main.php?page=products&category=' . urlencode(strtolower($category['name']));
        echo '<div class="category-banner" onclick="window.location.href=\'' . $categoryUrl . '\'">';
        echo '<img src="' . $categoryImage . '" alt="' . $categoryName . '" />';
        echo '<div class="category-banner-overlay">';
        echo '<h3>' . strtoupper($categoryName) . '</h3>';
        echo '</div>';
        echo '</div>';
    }
    ?>
  </div>


  <!-- Featured Products -->
  <section class="featured-products">
    <div class="section-header">
      <h2 class="section-title">Featured Products</h2>
      <p class="section-subtitle">Handpicked items just for you</p>
    </div>

    <div class="products-grid">
      <?php
      include '../../back-end/read/homeFetchProduct.php';
      $products = fetchHomeProducts();

      $productIndex = 0;
      foreach ($products as $product) {
        $hiddenClass = $productIndex >= 12 ? ' hidden' : '';
        echo '<div class="product-card' . $hiddenClass . '" onclick="openModal(this)" data-name="' . htmlspecialchars($product['name']) . '" data-price="' . htmlspecialchars($product['price']) . '" data-image="' . htmlspecialchars($product['image']) . '" data-sizes="' . htmlspecialchars($product['sizes']) . '" data-colors="' . htmlspecialchars($product['colors']) . '" data-size-quantities="' . htmlspecialchars($product['size_quantities']) . '">';



        echo '<img src="' . $product['image'] . '" class="product-image" alt="Product" />';
        echo '<div class="product-info">';
        echo '<p class="product-brand">' . $product['brand'] . '</p>';
        echo '<h3 class="product-name">' . $product['name'] . '</h3>';
        echo '<p class="product-price">' . $product['price'] . '</p>';
        echo '</div>';
        echo '</div>';
        $productIndex++;
      }
      ?>
    </div>

    <!-- Load More Button -->
    <div class="load-more-container">
      <button id="loadMoreBtn" class="load-more-btn">Load More Products</button>
    </div>
  </section>

  <script src="../../../src/js/homeLoadMore.js"></script>
