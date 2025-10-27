<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

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
    <title>Privacy Policy - Shishir Basnet</title>
    <meta name="description" content="Read our Privacy Policy to understand how we collect, use, and protect your personal information.">
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="categories.php">Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="support.php">Support</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
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
                        <li class="nav-item"><a class="nav-link" href="auth/login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="auth/signup.php">Sign Up</a></li>
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
                    <h1 class="display-4 fw-bold mb-3">Privacy Policy</h1>
                    <p class="lead mb-4">Your privacy is important to us. This policy explains how we collect, use, and protect your information.</p>
                    <p class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Last updated: October 17, 2024</p>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fas fa-shield-alt fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Privacy Content -->
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
                                <p>At Shishir Basnet, we respect your privacy and are committed to protecting your personal data. This privacy policy explains how we collect, use, and safeguard your information when you visit our website or use our services.</p>
                            </div>

                            <!-- Information We Collect -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-database me-2"></i>Information We Collect
                                </h2>
                                <p>We collect the following types of information:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-user text-info me-2"></i><strong>Personal Information:</strong> Name, email address, phone number</li>
                                    <li class="mb-2"><i class="fas fa-credit-card text-info me-2"></i><strong>Payment Information:</strong> Billing address, payment method details</li>
                                    <li class="mb-2"><i class="fas fa-mouse-pointer text-info me-2"></i><strong>Usage Data:</strong> Pages visited, time spent, browser information</li>
                                    <li class="mb-2"><i class="fas fa-cookie-bite text-info me-2"></i><strong>Cookies:</strong> Website preferences and session data</li>
                                </ul>
                            </div>

                            <!-- How We Use Information -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-cogs me-2"></i>How We Use Your Information
                                </h2>
                                <p>We use your information to:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Process your orders and payments</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Provide customer support</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Send order confirmations and updates</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Improve our website and services</li>
                                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Send promotional emails (with your consent)</li>
                                </ul>
                            </div>

                            <!-- Information Sharing -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-share-alt me-2"></i>Information Sharing
                                </h2>
                                <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in these circumstances:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-credit-card text-warning me-2"></i>With payment processors to complete transactions</li>
                                    <li class="mb-2"><i class="fas fa-gavel text-warning me-2"></i>When required by law or legal process</li>
                                    <li class="mb-2"><i class="fas fa-shield-alt text-warning me-2"></i>To protect our rights and prevent fraud</li>
                                </ul>
                            </div>

                            <!-- Data Security -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-lock me-2"></i>Data Security
                                </h2>
                                <p>We implement appropriate security measures to protect your personal information:</p>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="alert alert-light border">
                                            <i class="fas fa-encrypt text-primary me-2"></i>
                                            <strong>SSL Encryption</strong><br>
                                            <small class="text-muted">All data transmission is encrypted</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-light border">
                                            <i class="fas fa-server text-primary me-2"></i>
                                            <strong>Secure Servers</strong><br>
                                            <small class="text-muted">Protected hosting environment</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Your Rights -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-user-shield me-2"></i>Your Rights
                                </h2>
                                <p>You have the right to:</p>
                                <ul class="list-unstyled ms-4">
                                    <li class="mb-2"><i class="fas fa-eye text-primary me-2"></i>Access your personal data</li>
                                    <li class="mb-2"><i class="fas fa-edit text-primary me-2"></i>Correct inaccurate information</li>
                                    <li class="mb-2"><i class="fas fa-trash text-primary me-2"></i>Request deletion of your data</li>
                                    <li class="mb-2"><i class="fas fa-ban text-primary me-2"></i>Opt-out of marketing communications</li>
                                </ul>
                            </div>

                            <!-- Cookies -->
                            <div class="mb-5">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-cookie-bite me-2"></i>Cookies Policy
                                </h2>
                                <p>We use cookies to enhance your browsing experience. You can control cookies through your browser settings.</p>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Note:</strong> Disabling cookies may affect website functionality.
                                </div>
                            </div>

                            <!-- Contact Information -->
                            <div class="mb-0">
                                <h2 class="h4 text-primary mb-3">
                                    <i class="fas fa-envelope me-2"></i>Contact Us
                                </h2>
                                <p>For privacy-related questions or concerns, contact us:</p>
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
                    <h2 class="mb-3">Questions About Privacy?</h2>
                    <p class="text-muted mb-4">We're committed to transparency. Contact us if you have any privacy concerns.</p>
                    <div class="d-flex gap-3 justify-content-center flex-wrap">
                        <a href="contact.php" class="btn btn-primary">
                            <i class="fas fa-envelope me-2"></i>Contact Support
                        </a>
                        <a href="terms.php" class="btn btn-outline-primary">
                            <i class="fas fa-file-contract me-2"></i>Terms of Service
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
        });
    </script>
</body>
</html>
