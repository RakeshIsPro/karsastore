// Main JavaScript for YBT Digital

document.addEventListener('DOMContentLoaded', function() {
    // Initialize components
    initThemeToggle();
    initCartFunctionality();
    initSearchFunctionality();
    initProductFilters();
    loadFeaturedProducts();
    updateCartCount();
    
    // Initialize mobile navigation
    initMobileNavigation();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize tooltips and popovers
    initBootstrapComponents();
});

// Theme toggle functionality
function initThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const currentTheme = localStorage.getItem('theme') || 'light';
    
    // Set initial theme
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);
    
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            document.documentElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });
    }
}

function updateThemeIcon(theme) {
    const themeIcon = document.getElementById('theme-icon');
    if (themeIcon) {
        themeIcon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Cart functionality
function initCartFunctionality() {
    // Add to cart buttons
    document.addEventListener('click', function(e) {
        if (e.target.matches('.add-to-cart') || e.target.closest('.add-to-cart')) {
            e.preventDefault();
            const button = e.target.matches('.add-to-cart') ? e.target : e.target.closest('.add-to-cart');
            const productId = button.dataset.productId;
            addToCart(productId, button);
        }
        
        // Remove from cart buttons
        if (e.target.matches('.remove-from-cart') || e.target.closest('.remove-from-cart')) {
            e.preventDefault();
            const button = e.target.matches('.remove-from-cart') ? e.target : e.target.closest('.remove-from-cart');
            const productId = button.dataset.productId;
            removeFromCart(productId, button);
        }
    });
}

async function addToCart(productId, button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    button.disabled = true;
    
    try {
        const response = await fetch('/digital nest/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            button.innerHTML = '<i class="fas fa-check"></i> Added!';
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            
            updateCartCount();
            showNotification('Product added to cart!', 'success');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('btn-success');
                button.classList.add('btn-primary');
                button.disabled = false;
            }, 2000);
        } else {
            throw new Error(data.message || 'Failed to add to cart');
        }
    } catch (error) {
        button.innerHTML = originalText;
        button.disabled = false;
        showNotification(error.message, 'error');
    }
}

async function removeFromCart(productId, button) {
    if (!confirm('Remove this item from cart?')) return;
    
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    try {
        const response = await fetch('/digital nest/api/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'remove',
                product_id: productId
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const cartItem = button.closest('.cart-item');
            if (cartItem) {
                cartItem.style.opacity = '0.5';
                cartItem.style.transform = 'translateX(-100%)';
                setTimeout(() => cartItem.remove(), 300);
            }
            
            updateCartCount();
            updateCartTotal();
            showNotification('Item removed from cart', 'success');
        } else {
            throw new Error(data.message || 'Failed to remove from cart');
        }
    } catch (error) {
        button.innerHTML = originalText;
        button.disabled = false;
        showNotification(error.message, 'error');
    }
}

async function updateCartCount() {
    try {
        const response = await fetch('/digital nest/api/cart.php?action=count');
        const data = await response.json();
        
        const cartCountElement = document.getElementById('cart-count');
        if (cartCountElement) {
            cartCountElement.textContent = data.count || 0;
            cartCountElement.style.display = data.count > 0 ? 'flex' : 'none';
        }
    } catch (error) {
        console.error('Failed to update cart count:', error);
    }
}

// Search functionality
function initSearchFunctionality() {
    const searchInput = document.querySelector('.search-input');
    const searchClear = document.querySelector('.search-clear');
    
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            
            if (searchClear) {
                searchClear.style.display = query ? 'block' : 'none';
            }
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (query.length >= 2) {
                    performSearch(query);
                } else if (query.length === 0) {
                    clearSearch();
                }
            }, 300);
        });
        
        if (searchClear) {
            searchClear.addEventListener('click', function() {
                searchInput.value = '';
                this.style.display = 'none';
                clearSearch();
                searchInput.focus();
            });
        }
    }
}

async function performSearch(query) {
    const resultsContainer = document.getElementById('search-results');
    if (!resultsContainer) return;
    
    try {
        showLoadingState(resultsContainer);
        
        const response = await fetch(`/digital nest/api/search.php?q=${encodeURIComponent(query)}`);
        const data = await response.json();
        
        if (data.success) {
            displaySearchResults(data.products, resultsContainer);
        } else {
            throw new Error(data.message || 'Search failed');
        }
    } catch (error) {
        resultsContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> 
                Search failed. Please try again.
            </div>
        `;
    }
}

function displaySearchResults(products, container) {
    if (products.length === 0) {
        container.innerHTML = `
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h5>No products found</h5>
                <p class="text-muted">Try adjusting your search terms</p>
            </div>
        `;
        return;
    }
    
    const productsHTML = products.map(product => createProductCard(product)).join('');
    container.innerHTML = `<div class="product-grid">${productsHTML}</div>`;
}

// Product filters
function initProductFilters() {
    const filterForm = document.getElementById('filter-form');
    if (!filterForm) return;
    
    const filterInputs = filterForm.querySelectorAll('input[type="checkbox"], input[type="radio"], select');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', function() {
            applyFilters();
        });
    });
    
    // Price range slider
    const priceRange = document.getElementById('price-range');
    if (priceRange) {
        priceRange.addEventListener('input', debounce(applyFilters, 500));
    }
}

async function applyFilters() {
    const filterForm = document.getElementById('filter-form');
    const resultsContainer = document.getElementById('products-container');
    
    if (!filterForm || !resultsContainer) return;
    
    const formData = new FormData(filterForm);
    const params = new URLSearchParams();
    
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    
    try {
        showLoadingState(resultsContainer);
        
        const response = await fetch(`/digital nest/api/products.php?${params.toString()}`);
        const data = await response.json();
        
        if (data.success) {
            displayProducts(data.products, resultsContainer);
            updateFilterCounts(data.counts);
        } else {
            throw new Error(data.message || 'Filter failed');
        }
    } catch (error) {
        resultsContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> 
                Failed to load products. Please try again.
            </div>
        `;
    }
}

// Load featured products
async function loadFeaturedProducts() {
    const container = document.getElementById('featured-products');
    if (!container) return;
    
    try {
        const response = await fetch('/digital nest/api/products.php?featured=1&limit=6');
        const data = await response.json();
        
        if (data.success) {
            const productsHTML = data.products.map(product => createProductCard(product)).join('');
            container.innerHTML = productsHTML;
        }
    } catch (error) {
        console.error('Failed to load featured products:', error);
    }
}

// Create product card HTML
function createProductCard(product) {
    const images = JSON.parse(product.images || '[]');
    const mainImage = images.length > 0 ? images[0] : '/digital nest/assets/images/placeholder.jpg';
    
    return `
        <div class="col">
            <div class="card product-card h-100 fade-in">
                ${product.featured ? '<span class="badge bg-warning position-absolute top-0 end-0 m-2">Featured</span>' : ''}
                <img src="${mainImage}" class="card-img-top" alt="${product.title}" loading="lazy">
                <div class="card-body d-flex flex-column">
                    <h6 class="card-title">${product.title}</h6>
                    <p class="card-text text-muted small flex-grow-1">${product.short_description || ''}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="price fw-bold">${formatPrice(product.price)}</span>
                        <div class="btn-group">
                            <button class="btn btn-primary btn-sm add-to-cart" data-product-id="${product.id}">
                                <i class="fas fa-cart-plus"></i>
                            </button>
                            <a href="/digital nest/product.php?id=${product.id}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

// Mobile navigation
function initMobileNavigation() {
    const bottomNavItems = document.querySelectorAll('.bottom-nav-item');
    const currentPage = window.location.pathname;
    
    bottomNavItems.forEach(item => {
        const href = item.getAttribute('href');
        if (currentPage.includes(href) || (href === '/digital nest/index.php' && currentPage === '/digital nest/')) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}

// Form validation
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
    
    // Real-time validation
    const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
        
        input.addEventListener('input', function() {
            if (this.classList.contains('is-invalid')) {
                validateField(this);
            }
        });
    });
}

function validateField(field) {
    const isValid = field.checkValidity();
    field.classList.toggle('is-valid', isValid);
    field.classList.toggle('is-invalid', !isValid);
    
    // Show custom error message
    const feedback = field.parentNode.querySelector('.invalid-feedback');
    if (feedback && !isValid) {
        feedback.textContent = field.validationMessage;
    }
}

// Bootstrap components initialization
function initBootstrapComponents() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-mdb-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new mdb.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-mdb-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new mdb.Popover(popoverTriggerEl);
    });
}

// Utility functions
function showLoadingState(container) {
    container.innerHTML = `
        <div class="text-center py-5">
            <div class="loading mb-3"></div>
            <p class="text-muted">Loading...</p>
        </div>
    `;
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 5000);
}

function formatPrice(amount, currency = 'USD') {
    const symbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'INR': '₹'
    };
    
    const symbol = symbols[currency] || currency + ' ';
    return symbol + parseFloat(amount).toFixed(2);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Lazy loading for images
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Initialize lazy loading
document.addEventListener('DOMContentLoaded', initLazyLoading);

// Smooth scrolling for anchor links
document.addEventListener('click', function(e) {
    if (e.target.matches('a[href^="#"]')) {
        e.preventDefault();
        const target = document.querySelector(e.target.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
});

// Back to top button
function initBackToTop() {
    const backToTopBtn = document.createElement('button');
    backToTopBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    backToTopBtn.className = 'btn btn-primary rounded-circle position-fixed';
    backToTopBtn.style.cssText = 'bottom: 100px; right: 20px; z-index: 1000; display: none; width: 50px; height: 50px;';
    backToTopBtn.id = 'back-to-top';
    
    document.body.appendChild(backToTopBtn);
    
    window.addEventListener('scroll', throttle(() => {
        if (window.pageYOffset > 300) {
            backToTopBtn.style.display = 'block';
        } else {
            backToTopBtn.style.display = 'none';
        }
    }, 100));
    
    backToTopBtn.addEventListener('click', () => {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
}

// Initialize back to top button
document.addEventListener('DOMContentLoaded', initBackToTop);
