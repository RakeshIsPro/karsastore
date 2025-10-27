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
    <title>Terms of Service - Shishir Basnet</title>
    <meta name="description" content="Read our Terms of Service to understand the rules and regulations for using Shishir Basnet's digital products and services.">
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
                        <a class="nav-link" href="about.php">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="support.php">Support</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
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
                    <h1 class="display-4 fw-bold mb-3">Terms of Service</h1>
                    <p class="lead mb-4">Please read these terms and conditions carefully before using our services.</p>
                    <p class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Last updated: October 17, 2024</p>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fas fa-file-contract fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms Content -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-5">
                            
                            <!-- Introduction -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Introduction
                                </h2>
                                <p>Welcome to Shishir Basnet's digital marketplace. These Terms of Service ("Terms") govern your use of our website and services. By accessing or using our services, you agree to be bound by these Terms.</p>
                            </div>

                            <!-- Acceptance of Terms -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-handshake me-2"></i>1. Acceptance of Terms
                                </h2>
                                <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement. Additionally, when using this website's particular services, you shall be subject to any posted guidelines or rules applicable to such services.</p>
                            </div>

                            <!-- Use License -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-certificate me-2"></i>2. Use License
                                </h2>
                                <p>Permission is granted to temporarily download one copy of the materials on Shishir Basnet's website for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Modify or copy the materials</li>
                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Use the materials for any commercial purpose or for any public display</li>
                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Attempt to reverse engineer any software contained on the website</li>
                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>Remove any copyright or other proprietary notations from the materials</li>
                                </ul>
                            </div>

                            <!-- Digital Products -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-download me-2"></i>3. Digital Products
                                </h2>
                                <p>When you purchase digital products from our platform:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>You receive a non-exclusive license to use the product</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>You may use the product for personal or commercial projects</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>You cannot resell or redistribute the original files</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Downloads are available for 30 days after purchase</li>
                                </ul>
                            </div>

                            <!-- User Accounts -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-user-cog me-2"></i>4. User Accounts
                                </h2>
                                <p>When you create an account with us, you must provide information that is accurate, complete, and current at all times. You are responsible for safeguarding the password and for maintaining the security of your account.</p>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Important:</strong> You are fully responsible for all activities that occur under your account.
                                </div>
                            </div>

                            <!-- Payment Terms -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-credit-card me-2"></i>5. Payment Terms
                                </h2>
                                <p>Payment for digital products is required at the time of purchase. We accept various payment methods including:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fab fa-cc-visa text-primary me-2"></i>Credit Cards (Visa, MasterCard, American Express)</li>
                                    <li class="mb-2"><i class="fab fa-paypal text-primary me-2"></i>PayPal</li>
                                    <li class="mb-2"><i class="fas fa-university text-primary me-2"></i>Bank Transfer</li>
                                </ul>
                                <p>All prices are in USD unless otherwise specified. Prices are subject to change without notice.</p>
                            </div>

                            <!-- Refund Policy -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-undo me-2"></i>6. Refund Policy
                                </h2>
                                <p>We offer a 30-day money-back guarantee on all digital products. To be eligible for a refund:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-clock text-info me-2"></i>Request must be made within 30 days of purchase</li>
                                    <li class="mb-2"><i class="fas fa-envelope text-info me-2"></i>Contact our support team with your order details</li>
                                    <li class="mb-2"><i class="fas fa-file-alt text-info me-2"></i>Provide a valid reason for the refund request</li>
                                </ul>
                            </div>

                            <!-- Prohibited Uses -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-ban me-2"></i>7. Prohibited Uses
                                </h2>
                                <p>You may not use our products or services:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>For any unlawful purpose or to solicit others to perform unlawful acts</li>
                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>To violate any international, federal, provincial, or state regulations, rules, laws, or local ordinances</li>
                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>To infringe upon or violate our intellectual property rights or the intellectual property rights of others</li>
                                    <li class="mb-2"><i class="fas fa-times text-danger me-2"></i>To harass, abuse, insult, harm, defame, slander, disparage, intimidate, or discriminate</li>
                                </ul>
                            </div>

                            <!-- Intellectual Property -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-copyright me-2"></i>8. Intellectual Property Rights
                                </h2>
                                <p>The service and its original content, features, and functionality are and will remain the exclusive property of Shishir Basnet and its licensors. The service is protected by copyright, trademark, and other laws.</p>
                            </div>

                            <!-- Disclaimer -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-exclamation-triangle me-2"></i>9. Disclaimer
                                </h2>
                                <p>The information on this website is provided on an "as is" basis. To the fullest extent permitted by law, this Company:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-minus text-muted me-2"></i>Excludes all representations and warranties relating to this website and its contents</li>
                                    <li class="mb-2"><i class="fas fa-minus text-muted me-2"></i>Excludes all liability for damages arising out of or in connection with your use of this website</li>
                                </ul>
                            </div>

                            <!-- Limitation of Liability -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-shield-alt me-2"></i>10. Limitation of Liability
                                </h2>
                                <p>In no event shall Shishir Basnet, nor its directors, employees, partners, agents, suppliers, or affiliates, be liable for any indirect, incidental, special, consequential, or punitive damages, including without limitation, loss of profits, data, use, goodwill, or other intangible losses, resulting from your use of the service.</p>
                            </div>

                            <!-- Changes to Terms -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-edit me-2"></i>11. Changes to Terms
                                </h2>
                                <p>We reserve the right, at our sole discretion, to modify or replace these Terms at any time. If a revision is material, we will try to provide at least 30 days notice prior to any new terms taking effect.</p>
                            </div>

                            <!-- Contact Information -->
                            <div class="mb-0">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-envelope me-2"></i>12. Contact Information
                                </h2>
                                <p>If you have any questions about these Terms of Service, please contact us:</p>
                                <div class="alert alert-light border">
                                    <p class="mb-2"><strong>Email:</strong> <a href="mailto:pinnacleseo11@gmail.com">pinnacleseo11@gmail.com</a></p>
                                    <p class="mb-2"><strong>Phone:</strong> <a href="tel:+9779702454856">+977 9702454856</a></p>
                                    <p class="mb-0"><strong>Address:</strong> Lagankhel, Lalitpur, Nepal</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h2 class="mb-3">Questions About Our Terms?</h2>
                    <p class="text-muted mb-4">
                        If you have any questions about these Terms of Service, don't hesitate to reach out to our support team.
                    </p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="contact.php" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Contact Support
                        </a>
                        <a href="privacy.php" class="btn btn-outline-primary">
                            <i class="fas fa-shield-alt me-2"></i>Privacy Policy
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
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <ul class="list-unstyled">
                        <li><a href="terms.php" class="text-muted text-decoration-none">Terms of Service</a></li>
                        <li><a href="privacy.php" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li><a href="support.php" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
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
        <a href="support.php" class="bottom-nav-item">
            <i class="fas fa-question-circle"></i>
            <span>Support</span>
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
