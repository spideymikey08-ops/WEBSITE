<?php
require_once 'includes/auth_check.php';
include 'includes/header.php';
include 'includes/navbar.php';

// Fetch products from API
$api_url = "https://dummyjson.com/products";
$response = @file_get_contents($api_url);
$products_data = json_decode($response, true);
$products = isset($products_data['products']) ? $products_data['products'] : [];
?>

<div class="container mt-5 mb-5 flex-grow-1">
    <div class="row align-items-center mb-5">
        <div class="col-lg-6">
            <h2 class="display-6 fw-800 text-theme mb-0">Global <span class="text-gradient">Catalog</span></h2>
            <p class="text-muted mb-0">Explore our curated selection of premium products.</p>
        </div>
        <div class="col-lg-6 mt-4 mt-lg-0">
            <div class="row g-2">
                <div class="col-md-8">
                    <div class="input-group">
                        <span class="input-group-text bg-glass border-secondary text-muted">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="productSearch" class="form-control border-secondary" placeholder="Search products...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="categoryFilter" class="form-select bg-glass text-theme border-secondary">
                        <option value="all">All Categories</option>
                        <?php 
                        $categories = array_unique(array_column($products, 'category'));
                        foreach($categories as $cat): 
                        ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo ucfirst(htmlspecialchars($cat)); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>
    
    <div id="noResults" class="glass-card text-center p-5 d-none">
        <i class="fas fa-search fa-3x text-muted mb-4"></i>
        <h3 class="text-theme">No matches found</h3>
        <p class="text-muted">Try adjusting your search or filters.</p>
    </div>

    <?php if(empty($products)): ?>
        <div class="glass-card text-center p-5">
            <i class="fas fa-search fa-3x text-muted mb-4"></i>
            <h3 class="text-theme">No items found</h3>
            <p class="text-muted">We couldn't retrieve the product catalog at this moment.</p>
            <a href="products.php" class="btn btn-primary mt-3">Try Reloading</a>
        </div>
    <?php else: ?>
        <div class="row g-4" id="productGrid">
            <?php foreach($products as $product): ?>
                <div class="col-md-6 col-lg-4 col-xl-3 product-item" data-category="<?php echo htmlspecialchars($product['category']); ?>" data-title="<?php echo strtolower(htmlspecialchars($product['title'])); ?>">
                    <div class="glass-card product-card h-100 d-flex flex-column p-0 overflow-hidden transition-hover">
                        <div class="card-img-container cursor-pointer view-details" 
                             data-title="<?php echo htmlspecialchars($product['title']); ?>"
                             data-price="<?php echo htmlspecialchars($product['price']); ?>"
                             data-desc="<?php echo htmlspecialchars($product['description']); ?>"
                             data-img="<?php echo htmlspecialchars($product['thumbnail']); ?>"
                             data-rating="<?php echo htmlspecialchars($product['rating']); ?>"
                             data-stock="<?php echo htmlspecialchars($product['stock']); ?>">
                            <img src="<?php echo htmlspecialchars($product['thumbnail']); ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" style="mix-blend-mode: multiply;">
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="text-muted small text-uppercase fw-600 letter-spacing-1"><?php echo htmlspecialchars($product['category']); ?></span>
                                <div class="text-warning small">
                                    <i class="fas fa-star"></i> <?php echo isset($product['rating']) ? htmlspecialchars($product['rating']) : '4.5'; ?>
                                </div>
                            </div>
                            <h5 class="text-theme fw-700 mb-2 line-clamp-2 cursor-pointer view-details" 
                                data-title="<?php echo htmlspecialchars($product['title']); ?>"
                                data-price="<?php echo htmlspecialchars($product['price']); ?>"
                                data-desc="<?php echo htmlspecialchars($product['description']); ?>"
                                data-img="<?php echo htmlspecialchars($product['thumbnail']); ?>"
                                data-rating="<?php echo htmlspecialchars($product['rating']); ?>"
                                data-stock="<?php echo htmlspecialchars($product['stock']); ?>">
                                <?php echo htmlspecialchars($product['title']); ?>
                            </h5>
                            <p class="text-muted small mb-3 line-clamp-2"><?php echo htmlspecialchars($product['description'] ?? ''); ?></p>
                            
                            <div class="mt-auto pt-3 border-top border-secondary d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fs-4 fw-800 text-gradient">$<?php echo htmlspecialchars($product['price']); ?></span>
                                </div>
                                <span class="badge bg-glass text-muted border border-secondary small"><?php echo htmlspecialchars($product['stock']); ?> left</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
    // Search and Filter Logic
    const searchInput = document.getElementById('productSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const productItems = document.querySelectorAll('.product-item');
    const noResults = document.getElementById('noResults');
    const productGrid = document.getElementById('productGrid');

    function filterProducts() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedCategory = categoryFilter.value;
        let visibleCount = 0;

        productItems.forEach(item => {
            const title = item.getAttribute('data-title');
            const category = item.getAttribute('data-category');
            
            const matchesSearch = title.includes(searchTerm);
            const matchesCategory = selectedCategory === 'all' || category === selectedCategory;

            if (matchesSearch && matchesCategory) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        noResults.classList.toggle('d-none', visibleCount > 0);
        productGrid.classList.toggle('d-none', visibleCount === 0);
    }

    searchInput.addEventListener('input', filterProducts);
    categoryFilter.addEventListener('change', filterProducts);

    document.querySelectorAll('.view-details').forEach(el => {
        el.addEventListener('click', function() {
            modalTitle.innerText = this.getAttribute('data-title');
            modalPrice.innerText = '$' + this.getAttribute('data-price');
            modalDesc.innerText = this.getAttribute('data-desc');
            modalImg.src = this.getAttribute('data-img');
            modalStock.innerText = this.getAttribute('data-stock') + ' in stock';
            currentProductName = this.getAttribute('data-title');
            
            const rating = Math.round(parseFloat(this.getAttribute('data-rating')));
            modalRating.innerHTML = '<i class="fas fa-star"></i>'.repeat(rating) + '<i class="far fa-star"></i>'.repeat(5-rating);
            
            quickViewModal.show();
        });
    });

    document.querySelector('.modal-add-to-cart').addEventListener('click', function() {
        showToast(`${currentProductName} added to cart!`);
        quickViewModal.hide();
    });

    // Toast Logic
    function showToast(message, type = 'success') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        toast.className = `custom-toast ${type}`;
        
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-info-circle';
        
        toast.innerHTML = `
            <i class="fas ${icon}"></i>
            <span>${message}</span>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Magnetic Glow Tracking
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('mousemove', e => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            card.style.setProperty('--mouse-x', `${x}px`);
            card.style.setProperty('--mouse-y', `${y}px`);
        });
    });
</script>

<?php include 'includes/footer.php'; ?>
