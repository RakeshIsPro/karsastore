<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Get some statistics
$stats = [];
try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'");
    $stats['products'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'");
    $stats['users'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT COUNT(*) FROM orders WHERE payment_status = 'completed'");
    $stats['orders'] = $stmt->fetchColumn();
    
    $stmt = $pdo->query("SELECT SUM(downloads_count) FROM products");
    $stats['downloads'] = $stmt->fetchColumn() ?: 0;
} catch (Exception $e) {
    $stats = ['products' => 0, 'users' => 0, 'orders' => 0, 'downloads' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Shishir Basnet</title>
    <meta name="description" content="Learn more about Shishir Basnet and our mission to provide premium digital products and professional assets.">
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
                        <a class="nav-link" href="categories.php">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about.php">About</a>
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
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-3">About Shishir Basnet</h1>
                    <p class="lead mb-4">Empowering creators and businesses with premium digital products and professional assets since 2024.</p>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-user-tie fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- About Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="text-center mb-5">
                        <h2 class="mb-4">Our Story</h2>
                        <p class="lead text-muted">
                            Welcome to Shishir Basnet's digital marketplace, where creativity meets functionality. 
                            We specialize in providing high-quality digital products that help businesses and 
                            individuals achieve their goals.
                        </p>
                    </div>
                    
                    <div class="row g-4 mb-5">
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-bullseye fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Our Mission</h5>
                                    <p class="card-text text-muted">
                                        To provide creators, developers, and businesses with premium digital assets 
                                        that save time and enhance their projects.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-body text-center p-4">
                                    <i class="fas fa-eye fa-3x text-primary mb-3"></i>
                                    <h5 class="card-title">Our Vision</h5>
                                    <p class="card-text text-muted">
                                        To become the go-to platform for digital products, fostering innovation 
                                        and creativity in the digital space.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Statistics -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Our Impact</h2>
                <p class="text-muted">Numbers that showcase our growing community and success</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-box-open fa-2x"></i>
                        </div>
                        <h3 class="fw-bold text-primary"><?php echo number_format($stats['products']); ?>+</h3>
                        <p class="text-muted mb-0">Digital Products</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                        <h3 class="fw-bold text-success"><?php echo number_format($stats['users']); ?>+</h3>
                        <p class="text-muted mb-0">Happy Customers</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-shopping-bag fa-2x"></i>
                        </div>
                        <h3 class="fw-bold text-warning"><?php echo number_format($stats['orders']); ?>+</h3>
                        <p class="text-muted mb-0">Completed Orders</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <div class="bg-info text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <i class="fas fa-download fa-2x"></i>
                        </div>
                        <h3 class="fw-bold text-info"><?php echo number_format($stats['downloads']); ?>+</h3>
                        <p class="text-muted mb-0">Total Downloads</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What We Offer -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">What We Offer</h2>
                <p class="text-muted">Discover our range of premium digital products</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-code fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Web Templates</h5>
                            <p class="card-text text-muted">
                                Professional website templates and themes for various industries and purposes.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-palette fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Graphics & Design</h5>
                            <p class="card-text text-muted">
                                High-quality graphics, logos, icons, and design assets for your projects.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-mobile-alt fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Mobile Apps</h5>
                            <p class="card-text text-muted">
                                Mobile application templates and source codes for iOS and Android platforms.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fab fa-wordpress fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">WordPress Themes</h5>
                            <p class="card-text text-muted">
                                Premium WordPress themes and plugins for blogs, businesses, and e-commerce.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-book fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">E-books</h5>
                            <p class="card-text text-muted">
                                Digital books, guides, and educational content across various topics.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="fas fa-tools fa-3x text-primary mb-3"></i>
                            <h5 class="card-title">Software Tools</h5>
                            <p class="card-text text-muted">
                                Productivity software and development tools to enhance your workflow.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Why Choose Us?</h2>
                <p class="text-muted">What makes us different from the competition</p>
            </div>
            
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <i class="fas fa-award fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">Premium Quality</h5>
                        <p class="text-muted">All products are carefully curated and tested for quality.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">24/7 Support</h5>
                        <p class="text-muted">Round-the-clock customer support for all your needs.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <i class="fas fa-sync-alt fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">Regular Updates</h5>
                        <p class="text-muted">Products are regularly updated with new features and fixes.</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h5 class="fw-bold">Secure Platform</h5>
                        <p class="text-muted">Your data and transactions are protected with top security.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="py-5">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="mb-3">Ready to Get Started?</h2>
                    <p class="text-muted mb-4">
                        Join thousands of satisfied customers and discover premium digital products 
                        that will take your projects to the next level.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag me-2"></i>Browse Products
                        </a>
                        <a href="support.php" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-envelope me-2"></i>Contact Us
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
        <a href="categories.php" class="bottom-nav-item">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
        </a>
        <a href="cart.php" class="bottom-nav-item">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
        </a>
        <a href="about.php" class="bottom-nav-item active">
            <i class="fas fa-info-circle"></i>
            <span>About</span>
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
