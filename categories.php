<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Get all categories with product counts
$stmt = $pdo->query("
    SELECT c.*, COUNT(p.id) as product_count 
    FROM categories c 
    LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
    WHERE c.status = 'active'
    GROUP BY c.id 
    ORDER BY c.name ASC
");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Shishir Basnet</title>
    <meta name="description" content="Browse all product categories and find the digital products you need.">
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
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support.php">Support</a>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <?php if ($user): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-mdb-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($user['name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/signup.php">Sign Up</a>
                        </li>
                    <?php endif; ?>
                    
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count badge bg-danger position-absolute top-0 start-100 translate-middle rounded-pill">0</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Product Categories</h1>
                    <p class="lead mb-4">Explore our diverse collection of digital products organized by category. Find exactly what you're looking for.</p>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fas fa-tags fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Grid -->
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <?php foreach ($categories as $category): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card category-card h-100 shadow-sm">
                            <div class="card-body text-center p-4">
                                <div class="category-icon mb-3">
                                    <?php
                                    // Category icons based on name
                                    $icons = [
                                        'Web Templates' => 'fas fa-code',
                                        'Graphics & Design' => 'fas fa-palette',
                                        'Mobile Apps' => 'fas fa-mobile-alt',
                                        'WordPress Themes' => 'fab fa-wordpress',
                                        'E-books' => 'fas fa-book',
                                        'Software Tools' => 'fas fa-tools'
                                    ];
                                    $icon = $icons[$category['name']] ?? 'fas fa-folder';
                                    ?>
                                    <i class="<?php echo $icon; ?> fa-3x text-primary"></i>
                                </div>
                                
                                <h5 class="card-title mb-3"><?php echo htmlspecialchars($category['name']); ?></h5>
                                
                                <?php if ($category['description']): ?>
                                    <p class="card-text text-muted mb-3"><?php echo htmlspecialchars($category['description']); ?></p>
                                <?php endif; ?>
                                
                                <div class="mb-3">
                                    <span class="badge bg-primary fs-6"><?php echo number_format($category['product_count']); ?> Products</span>
                                </div>
                                
                                <a href="products.php?category=<?php echo $category['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-arrow-right me-2"></i>Browse Products
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                    <h3>No Categories Available</h3>
                    <p class="text-muted">Categories will appear here once they are added.</p>
                    <a href="products.php" class="btn btn-primary">Browse All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="mb-3">Can't Find What You're Looking For?</h2>
                    <p class="text-muted mb-4">Browse all our products or use our search feature to find specific items.</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-th-large me-2"></i>View All Products
                        </a>
                        <a href="support.php" class="btn btn-outline-primary">
                            <i class="fas fa-question-circle me-2"></i>Contact Support
                        </a>
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
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-linkedin-in"></i></a>
                    </div>
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
        <a href="products.php" class="bottom-nav-item">
            <i class="fas fa-th-large"></i>
            <span>Products</span>
        </a>
        <a href="categories.php" class="bottom-nav-item active">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
        </a>
        <a href="cart.php" class="bottom-nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
        </a>
        <a href="profile.php" class="bottom-nav-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Load cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadCartCount();
        });
        
        function loadCartCount() {
            fetch('api/cart.php?action=count')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = data.count || 0;
                            cartCount.style.display = data.count > 0 ? 'inline' : 'none';
                        }
                    }
                })
                .catch(error => console.error('Error loading cart count:', error));
        }
    </script>
</body>
</html>
