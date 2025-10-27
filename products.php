<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Get categories for filters
$categories = getCategories();

// Get filter parameters
$selectedCategory = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$searchQuery = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';
$sortBy = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
$minPrice = isset($_GET['min_price']) && $_GET['min_price'] > 0 ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && $_GET['max_price'] > 0 ? (float)$_GET['max_price'] : null;

// Get selected category info
$selectedCategoryInfo = null;
if ($selectedCategory) {
    $selectedCategoryInfo = getCategoryById($selectedCategory);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $selectedCategoryInfo ? $selectedCategoryInfo['name'] . ' - ' : ''; ?>Products - Shishir Basnet</title>
    <meta name="description" content="Browse our collection of premium digital products including templates, graphics, and tools for professionals.">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-digital-tachograph me-2"></i>Shishir Basnet
            </a>
            
            <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#navbarNav">
                <i class="fas fa-bars"></i>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support.php">Support</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge badge-danger badge-pill position-absolute top-0 start-100 translate-middle" id="cart-count">0</span>
                        </a>
                    </li>
                    
                    <?php if ($user): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-mdb-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($user['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-download me-2"></i>My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light ms-2" href="auth/signup.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-6 fw-bold mb-3">
                        <?php if ($selectedCategoryInfo): ?>
                            <?php echo htmlspecialchars($selectedCategoryInfo['name']); ?>
                        <?php elseif ($searchQuery): ?>
                            Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"
                        <?php else: ?>
                            All Products
                        <?php endif; ?>
                    </h1>
                    <p class="lead mb-0">
                        <?php if ($selectedCategoryInfo && $selectedCategoryInfo['description']): ?>
                            <?php echo htmlspecialchars($selectedCategoryInfo['description']); ?>
                        <?php else: ?>
                            Discover premium digital products to boost your productivity
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="search-container">
                        <form method="GET" class="position-relative">
                            <input type="text" class="form-control search-input" name="search" 
                                   placeholder="Search products..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                            <i class="fas fa-search search-icon"></i>
                            <?php if ($selectedCategory): ?>
                                <input type="hidden" name="category" value="<?php echo $selectedCategory; ?>">
                            <?php endif; ?>
                            <?php if ($searchQuery): ?>
                                <button type="button" class="search-clear" onclick="clearSearch()">
                                    <i class="fas fa-times"></i>
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Filters Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="filter-sidebar">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-filter me-2"></i>Filters
                        </h5>
                        
                        <form id="filter-form" method="GET">
                            <?php if ($searchQuery): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>">
                            <?php endif; ?>
                            
                            <!-- Categories -->
                            <div class="filter-group">
                                <h6>Categories</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="category" value="" id="cat-all" 
                                           <?php echo !$selectedCategory ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="cat-all">
                                        All Categories
                                    </label>
                                </div>
                                <?php foreach ($categories as $category): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="category" 
                                               value="<?php echo $category['id']; ?>" id="cat-<?php echo $category['id']; ?>"
                                               <?php echo $selectedCategory == $category['id'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="cat-<?php echo $category['id']; ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Price Range -->
                            <div class="filter-group">
                                <h6>Price Range</h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="min_price" 
                                               placeholder="Min" value="<?php echo $minPrice; ?>" min="0" step="0.01">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" class="form-control form-control-sm" name="max_price" 
                                               placeholder="Max" value="<?php echo $maxPrice; ?>" min="0" step="0.01">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Sort By -->
                            <div class="filter-group">
                                <h6>Sort By</h6>
                                <select class="form-select form-select-sm" name="sort">
                                    <option value="newest" <?php echo $sortBy == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                                    <option value="oldest" <?php echo $sortBy == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                                    <option value="price_low" <?php echo $sortBy == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                    <option value="price_high" <?php echo $sortBy == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                    <option value="popular" <?php echo $sortBy == 'popular' ? 'selected' : ''; ?>>Most Popular</option>
                                    <option value="featured" <?php echo $sortBy == 'featured' ? 'selected' : ''; ?>>Featured</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search me-1"></i>Apply Filters
                                </button>
                                <a href="products.php" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-undo me-1"></i>Clear All
                                </a>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Quick Categories -->
                    <div class="mt-4">
                        <h6 class="fw-bold mb-3">Quick Categories</h6>
                        <div class="d-flex flex-wrap gap-3">
                            <?php foreach (array_slice($categories, 0, 6) as $category): ?>
                                <a href="products.php?category=<?php echo $category['id']; ?>" 
                                   class="btn btn-outline-primary btn-sm text-decoration-none" 
                                   style="border-radius: 20px; padding: 0.375rem 0.75rem; margin-bottom: 0.5rem;">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="col-lg-9">
                    <!-- Results Info -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="text-muted" id="results-info">Loading products...</span>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <!-- View Toggle -->
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm active" id="grid-view">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="list-view">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Products Container -->
                    <div id="products-container">
                        <!-- Products will be loaded here via JavaScript -->
                    </div>
                    
                    <!-- Pagination -->
                    <nav aria-label="Products pagination" class="mt-5">
                        <ul class="pagination justify-content-center" id="pagination">
                            <!-- Pagination will be generated via JavaScript -->
                        </ul>
                    </nav>
                    
                    <!-- Load More Button (for infinite scroll) -->
                    <div class="text-center mt-4 d-none" id="load-more-container">
                        <button class="btn btn-outline-primary" id="load-more-btn">
                            <i class="fas fa-plus me-2"></i>Load More Products
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-light py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h5 class="fw-bold mb-3">
                        <i class="fas fa-digital-tachograph me-2"></i>Shishir Basnet
                    </h5>
                    <p class="text-muted">Your trusted source for premium digital products and professional assets.</p>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="products.php" class="text-muted text-decoration-none">Products</a></li>
                        <li><a href="categories.php" class="text-muted text-decoration-none">Categories</a></li>
                        <li><a href="about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="support.php" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="terms.php" class="text-muted text-decoration-none">Terms of Service</a></li>
                        <li><a href="privacy.php" class="text-muted text-decoration-none">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold mb-3">Newsletter</h6>
                    <p class="text-muted">Subscribe to get updates on new products and exclusive offers.</p>
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Your email address">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <hr class="my-4">
            <div class="text-center">
                <p class="text-muted mb-0">&copy; 2024 Shishir Basnet. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav d-lg-none">
        <a href="index.php" class="bottom-nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="products.php" class="bottom-nav-item active">
            <i class="fas fa-th-large"></i>
            <span>Products</span>
        </a>
        <a href="cart.php" class="bottom-nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
        </a>
        <a href="<?php echo $user ? 'profile.php' : 'auth/login.php'; ?>" class="bottom-nav-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <!-- Dark Mode Toggle -->
    <button class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4 d-none d-lg-block" id="theme-toggle" style="z-index: 1000;">
        <i class="fas fa-moon" id="theme-icon"></i>
    </button>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        let currentPage = 1;
        let isLoading = false;
        let viewMode = 'grid';
        
        document.addEventListener('DOMContentLoaded', function() {
            loadProducts();
            initViewToggle();
            initInfiniteScroll();
        });
        
        async function loadProducts(page = 1, append = false) {
            if (isLoading) return;
            isLoading = true;
            
            const container = document.getElementById('products-container');
            const resultsInfo = document.getElementById('results-info');
            
            if (!append) {
                showLoadingState(container);
            }
            
            try {
                const params = new URLSearchParams(window.location.search);
                params.set('limit', '12');
                params.set('offset', (page - 1) * 12);
                
                const response = await fetch(`api/products.php?${params.toString()}`);
                const data = await response.json();
                
                if (data.success) {
                    displayProducts(data.products, container, append);
                    updateResultsInfo(data.pagination, resultsInfo);
                    updatePagination(data.pagination);
                    currentPage = page;
                } else {
                    throw new Error(data.message || 'Failed to load products');
                }
            } catch (error) {
                container.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h5>Failed to load products</h5>
                            <p>${error.message}</p>
                            <button class="btn btn-primary" onclick="loadProducts()">Try Again</button>
                        </div>
                    </div>
                `;
            } finally {
                isLoading = false;
            }
        }
        
        function displayProducts(products, container, append = false) {
            if (products.length === 0) {
                container.innerHTML = `
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No products found</h4>
                        <p class="text-muted">Try adjusting your search criteria or browse all products.</p>
                        <a href="products.php" class="btn btn-primary">View All Products</a>
                    </div>
                `;
                return;
            }
            
            const productsHTML = products.map(product => createProductCard(product, viewMode)).join('');
            
            if (append) {
                container.insertAdjacentHTML('beforeend', productsHTML);
            } else {
                container.innerHTML = `<div class="row ${viewMode === 'grid' ? 'g-4' : 'g-3'}">${productsHTML}</div>`;
            }
        }
        
        function createProductCard(product, mode = 'grid') {
            const images = product.images || [];
            const mainImage = images.length > 0 ? images[0] : 'assets/images/placeholder.jpg';
            
            if (mode === 'list') {
                return `
                    <div class="col-12">
                        <div class="card product-card mb-3">
                            <div class="row g-0">
                                <div class="col-md-3">
                                    ${product.featured ? '<span class="badge bg-warning position-absolute top-0 end-0 m-2">Featured</span>' : ''}
                                    <img src="${mainImage}" class="img-fluid rounded-start h-100" alt="${product.title}" style="object-fit: cover;">
                                </div>
                                <div class="col-md-9">
                                    <div class="card-body h-100 d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title mb-0">${product.title}</h5>
                                            <span class="price fw-bold text-primary fs-5">${product.formatted_price}</span>
                                        </div>
                                        <p class="card-text text-muted flex-grow-1">${product.short_description || ''}</p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-tag me-1"></i>${product.category_name || 'Uncategorized'}
                                                <i class="fas fa-download ms-3 me-1"></i>${product.downloads_count} downloads
                                            </small>
                                            <div class="btn-group">
                                                <button class="btn btn-primary add-to-cart" data-product-id="${product.id}">
                                                    <i class="fas fa-cart-plus me-1"></i>Add to Cart
                                                </button>
                                                <a href="product.php?id=${product.id}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            return `
                <div class="col-lg-4 col-md-6">
                    <div class="card product-card h-100">
                        ${product.featured ? '<span class="badge bg-warning position-absolute top-0 end-0 m-2">Featured</span>' : ''}
                        <img src="${mainImage}" class="card-img-top" alt="${product.title}">
                        <div class="card-body d-flex flex-column">
                            <h6 class="card-title">${product.title}</h6>
                            <p class="card-text text-muted small flex-grow-1">${product.short_description || ''}</p>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="price fw-bold text-primary">${product.formatted_price}</span>
                                <small class="text-muted">
                                    <i class="fas fa-download me-1"></i>${product.downloads_count}
                                </small>
                            </div>
                            <div class="btn-group w-100">
                                <button class="btn btn-primary btn-sm add-to-cart" data-product-id="${product.id}">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                                <a href="product.php?id=${product.id}" class="btn btn-outline-primary btn-sm flex-grow-1">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function updateResultsInfo(pagination, element) {
            const start = pagination.offset + 1;
            const end = Math.min(pagination.offset + pagination.limit, pagination.total_count);
            element.textContent = `Showing ${start}-${end} of ${pagination.total_count} products`;
        }
        
        function updatePagination(pagination) {
            const paginationElement = document.getElementById('pagination');
            if (pagination.total_pages <= 1) {
                paginationElement.innerHTML = '';
                return;
            }
            
            let paginationHTML = '';
            
            // Previous button
            paginationHTML += `
                <li class="page-item ${!pagination.has_prev ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${pagination.current_page - 1})">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            `;
            
            // Page numbers
            const startPage = Math.max(1, pagination.current_page - 2);
            const endPage = Math.min(pagination.total_pages, pagination.current_page + 2);
            
            if (startPage > 1) {
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1)">1</a></li>`;
                if (startPage > 2) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }
            
            for (let i = startPage; i <= endPage; i++) {
                paginationHTML += `
                    <li class="page-item ${i === pagination.current_page ? 'active' : ''}">
                        <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
                    </li>
                `;
            }
            
            if (endPage < pagination.total_pages) {
                if (endPage < pagination.total_pages - 1) {
                    paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${pagination.total_pages})">${pagination.total_pages}</a></li>`;
            }
            
            // Next button
            paginationHTML += `
                <li class="page-item ${!pagination.has_next ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${pagination.current_page + 1})">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            `;
            
            paginationElement.innerHTML = paginationHTML;
        }
        
        function changePage(page) {
            if (page < 1 || isLoading) return;
            loadProducts(page);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        
        function initViewToggle() {
            const gridBtn = document.getElementById('grid-view');
            const listBtn = document.getElementById('list-view');
            
            gridBtn.addEventListener('click', () => {
                viewMode = 'grid';
                gridBtn.classList.add('active');
                listBtn.classList.remove('active');
                loadProducts(currentPage);
            });
            
            listBtn.addEventListener('click', () => {
                viewMode = 'list';
                listBtn.classList.add('active');
                gridBtn.classList.remove('active');
                loadProducts(currentPage);
            });
        }
        
        function initInfiniteScroll() {
            // Optional: Implement infinite scroll for mobile
            if (window.innerWidth <= 768) {
                window.addEventListener('scroll', throttle(() => {
                    if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
                        // Load more products when near bottom
                        // This can be implemented as an alternative to pagination
                    }
                }, 200));
            }
        }
        
        function clearSearch() {
            const url = new URL(window.location);
            url.searchParams.delete('search');
            window.location.href = url.toString();
        }
        
        // Filter form submission
        document.getElementById('filter-form').addEventListener('change', function() {
            // Auto-submit form when filters change
            setTimeout(() => this.submit(), 100);
        });
        
        // Clean up empty parameters before form submission
        document.getElementById('filter-form').addEventListener('submit', function(e) {
            const formData = new FormData(this);
            const url = new URL(window.location.href.split('?')[0]);
            
            for (let [key, value] of formData.entries()) {
                if (value && value !== '0' && value !== '') {
                    url.searchParams.set(key, value);
                }
            }
            
            // Preserve search parameter if it exists
            const currentSearch = new URLSearchParams(window.location.search).get('search');
            if (currentSearch) {
                url.searchParams.set('search', currentSearch);
            }
            
            window.location.href = url.toString();
            e.preventDefault();
        });
        
        // Add to cart functionality
        document.addEventListener('click', function(e) {
            if (e.target.closest('.add-to-cart')) {
                e.preventDefault();
                const button = e.target.closest('.add-to-cart');
                const productId = button.getAttribute('data-product-id');
                
                if (!productId) return;
                
                // Show loading state
                const originalText = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
                button.disabled = true;
                
                // Add to cart via API
                fetch('api/cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'add',
                        product_id: productId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        button.innerHTML = '<i class="fas fa-check"></i> Added!';
                        button.classList.remove('btn-primary');
                        button.classList.add('btn-success');
                        
                        // Update cart count if element exists
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount && data.cart_count) {
                            cartCount.textContent = data.cart_count;
                        }
                        
                        // Reset button after 2 seconds
                        setTimeout(() => {
                            button.innerHTML = originalText;
                            button.classList.remove('btn-success');
                            button.classList.add('btn-primary');
                            button.disabled = false;
                        }, 2000);
                        
                        // Show toast notification if available
                        showToast('Product added to cart successfully!', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to add to cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showToast(error.message || 'Failed to add to cart', 'error');
                });
            }
        });
        
        // Simple toast notification function
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            document.body.appendChild(toast);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 5000);
        }
    </script>
</body>
</html>
