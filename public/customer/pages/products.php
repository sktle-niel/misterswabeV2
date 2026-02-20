<!-- Products Section -->
<section class="featured-products">
  <div class="section-header">
    <h2 class="section-title">
      <?php
      $category = isset($_GET['category']) ? $_GET['category'] : 'all';
      $search = isset($_GET['search']) ? $_GET['search'] : '';
      $title = $search ? 'Search Results for "' . htmlspecialchars($search) . '"' : ucfirst($category) . ' Products';
      echo $title;
      ?>
    </h2>
    <p class="section-subtitle">Handpicked items just for you</p>
  </div>

  <div class="products-grid">
    <?php
    include '../../back-end/read/getProducts.php';

    $products = getProducts($category, $search);
    $totalMatchingProducts = count($products);

    foreach ($products as $productIndex => $product) {
      $hiddenClass = $productIndex >= 12 ? ' hidden' : '';
      echo '<div class="product-card' . $hiddenClass . '" onclick="openModal(this)" data-name="' . htmlspecialchars($product['name']) . '" data-price="' . htmlspecialchars($product['price']) . '" data-image="' . htmlspecialchars($product['image']) . '" data-sizes="' . htmlspecialchars($product['sizes'] ?? '') . '" data-colors="' . htmlspecialchars($product['colors'] ?? '') . '" data-size-quantities="' . htmlspecialchars($product['size_quantities'] ?? '{}') . '">';

      echo '<img src="' . $product['image'] . '" class="product-image" alt="Product" />';
      echo '<div class="product-info">';
      echo '<p class="product-brand">' . $product['brand'] . '</p>';
      echo '<h3 class="product-name">' . $product['name'] . '</h3>';
      echo '<p class="product-price">' . $product['price'] . '</p>';
      echo '</div>';
      echo '</div>';
    }
    ?>
  </div>

    <!-- Load More Button -->
    <?php if ($totalMatchingProducts > 12): ?>
    <div class="load-more-container">
      <button id="loadMoreBtn" class="load-more-btn">Load More Products</button>
    </div>
    <?php endif; ?>
</section>

<script src="../../../src/js/productsLoadMore.js"></script>
