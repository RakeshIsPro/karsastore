<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Nest - Premium Digital Solutions by Shishir Basnet</title>
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

    <!-- Hero Section -->
    <section class="hero-section bg-gradient-primary text-white">
        <div class="container">
            <div class="row align-items-center min-vh-100 pt-5">
                <div class="col-lg-6">
                    <h1 class="display-1 fw-bold mb-4" style="font-size: 4rem; font-weight: 900;">Digital Nest</h1>
                    <h2 class="display-6 fw-bold mb-4 text-light">Premium Digital Solutions for Modern Professionals</h2>
                    <p class="lead mb-4">Discover high-quality digital assets, templates, and tools to boost your productivity and creativity.</p>
                    <div class="d-flex gap-3">
                        <a href="products.php" class="btn btn-light btn-lg">
                            <i class="fas fa-rocket me-2"></i>Explore Products
                        </a>
                        <a href="#featured" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-play me-2"></i>Learn More
                        </a>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="assets/images/hero-illustration.svg" alt="Digital Products" class="img-fluid">
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
    <section id="featured" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Featured Products</h2>
                <p class="text-muted">Handpicked digital products that deliver exceptional value</p>
            </div>
            
            <div class="row" id="featured-products">
                <!-- Products will be loaded via JavaScript -->
            </div>
            
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-primary btn-lg">View All Products</a>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">What Our Customers Say</h2>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"Excellent quality digital products. The templates saved me hours of work!"</p>
                            <h6 class="card-title">Sarah Johnson</h6>
                            <small class="text-muted">Web Designer</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"Great customer support and instant downloads. Highly recommended!"</p>
                            <h6 class="card-title">Mike Chen</h6>
                            <small class="text-muted">Marketing Manager</small>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                                <i class="fas fa-star text-warning"></i>
                            </div>
                            <p class="card-text">"Professional quality assets at affordable prices. Perfect for my projects!"</p>
                            <h6 class="card-title">Emily Davis</h6>
                            <small class="text-muted">Freelancer</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Frequently Asked Questions</h2>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-mdb-toggle="collapse" data-mdb-target="#faqCollapse1">
                                    How do I download my purchased products?
                                </button>
                            </h2>
                            <div id="faqCollapse1" class="accordion-collapse collapse show" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    After successful payment, you'll receive an email with download links. You can also access your purchases from your account dashboard.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#faqCollapse2">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faqCollapse2" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept all major credit cards, PayPal, and various local payment methods through our secure payment gateways.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#faqCollapse3">
                                    Do you offer refunds?
                                </button>
                            </h2>
                            <div id="faqCollapse3" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we offer a 30-day money-back guarantee on all digital products. Contact our support team for assistance.
                                </div>
                            </div>
                        </div>
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
                    <p class="text-muted">Digital Nest - Your trusted source for premium digital solutions and professional assets.</p>
                    <div class="d-flex gap-3">
                        <?php 
                        $facebookUrl = getSetting('facebook_url', '#');
                        $twitterUrl = getSetting('twitter_url', '#');
                        $instagramUrl = getSetting('instagram_url', '#');
                        $linkedinUrl = getSetting('linkedin_url', '#');
                        ?>
                        <?php if ($facebookUrl && $facebookUrl != '#'): ?>
                            <a href="<?php echo htmlspecialchars($facebookUrl); ?>" target="_blank" class="text-light" title="Follow us on Facebook">
                                <i class="fab fa-facebook-f fa-lg"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($twitterUrl && $twitterUrl != '#'): ?>
                            <a href="<?php echo htmlspecialchars($twitterUrl); ?>" target="_blank" class="text-light" title="Follow us on Twitter">
                                <i class="fab fa-twitter fa-lg"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($instagramUrl && $instagramUrl != '#'): ?>
                            <a href="<?php echo htmlspecialchars($instagramUrl); ?>" target="_blank" class="text-light" title="Follow us on Instagram">
                                <i class="fab fa-instagram fa-lg"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($linkedinUrl && $linkedinUrl != '#'): ?>
                            <a href="<?php echo htmlspecialchars($linkedinUrl); ?>" target="_blank" class="text-light" title="Connect on LinkedIn">
                                <i class="fab fa-linkedin-in fa-lg"></i>
                            </a>
                        <?php endif; ?>
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
                <p class="text-muted mb-0">&copy; 2025 Shishir Basnet. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bottom Navigation (Mobile) -->
    <nav class="bottom-nav d-lg-none">
        <a href="index.php" class="bottom-nav-item active">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="products.php" class="bottom-nav-item">
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
</body>
</html>
