<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    $priority = sanitizeInput($_POST['priority']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Insert into support_tickets table
            $stmt = $pdo->prepare("
                INSERT INTO support_tickets (user_id, name, email, subject, message, priority, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'open')
            ");
            
            $userId = $user ? $user['id'] : null;
            
            if ($stmt->execute([$userId, $name, $email, $subject, $message, $priority])) {
                $success = 'Thank you for contacting us! We will get back to you within 24 hours.';
                
                // Send confirmation email (if email function exists)
                if (function_exists('sendEmail')) {
                    $emailSubject = "Contact Form Submission - " . $subject;
                    $emailMessage = "
                        <h2>New Contact Form Submission</h2>
                        <p><strong>Name:</strong> $name</p>
                        <p><strong>Email:</strong> $email</p>
                        <p><strong>Subject:</strong> $subject</p>
                        <p><strong>Priority:</strong> " . ucfirst($priority) . "</p>
                        <p><strong>Message:</strong></p>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                        
                        <p>Thank you for contacting Shishir Basnet.</p>
                        <p>Best regards,<br>Shishir Basnet Support Team</p>
                    ";
                    
                    sendEmail($email, $emailSubject, $emailMessage);
                }
                
                // Clear form data
                $_POST = [];
            } else {
                $error = 'Failed to send message. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Shishir Basnet</title>
    <meta name="description" content="Get in touch with Shishir Basnet. We're here to help with any questions about our digital products and services.">
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
                        <a class="nav-link active" href="contact.php">Contact</a>
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
                    <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
                    <p class="lead mb-4">Have questions? We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                </div>
                <div class="col-lg-6 text-center">
                    <i class="fas fa-envelope fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Content -->
    <section class="py-5">
        <div class="container">
            <div class="row g-5">
                <!-- Contact Form -->
                <div class="col-lg-8">
                    <div class="card shadow-lg border-0" style="border-radius: 15px;">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-paper-plane fa-3x text-primary mb-3"></i>
                                <h3 class="fw-bold text-primary">Send us a Message</h3>
                                <p class="text-muted">We'd love to hear from you. Fill out the form below and we'll get back to you soon!</p>
                            </div>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="text" class="form-control form-control-lg" id="name" name="name" 
                                                   placeholder="Enter your full name"
                                                   value="<?php echo $user ? htmlspecialchars($user['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''); ?>" required>
                                            <label for="name"><i class="fas fa-user me-2 text-primary"></i>Full Name *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-floating">
                                            <input type="email" class="form-control form-control-lg" id="email" name="email" 
                                                   placeholder="Enter your email address"
                                                   value="<?php echo $user ? htmlspecialchars($user['email']) : (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''); ?>" required>
                                            <label for="email"><i class="fas fa-envelope me-2 text-primary"></i>Email Address *</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-8">
                                        <div class="form-floating">
                                            <input type="text" class="form-control form-control-lg" id="subject" name="subject" 
                                                   placeholder="Enter message subject"
                                                   value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>" required>
                                            <label for="subject"><i class="fas fa-tag me-2 text-primary"></i>Subject *</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-floating">
                                            <select class="form-select form-control-lg" id="priority" name="priority">
                                                <option value="low" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'low') ? 'selected' : ''; ?>>🟢 Low Priority</option>
                                                <option value="medium" <?php echo (!isset($_POST['priority']) || $_POST['priority'] == 'medium') ? 'selected' : ''; ?>>🟡 Medium Priority</option>
                                                <option value="high" <?php echo (isset($_POST['priority']) && $_POST['priority'] == 'high') ? 'selected' : ''; ?>>🔴 High Priority</option>
                                            </select>
                                            <label for="priority"><i class="fas fa-flag me-2 text-primary"></i>Priority Level</label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-floating">
                                            <textarea class="form-control" id="message" name="message" 
                                                      placeholder="Enter your message here..." 
                                                      style="height: 150px; resize: vertical;" required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                                            <label for="message"><i class="fas fa-comment me-2 text-primary"></i>Your Message *</label>
                                        </div>
                                        <div class="form-text mt-2">
                                            <i class="fas fa-info-circle me-1"></i>Please provide as much detail as possible to help us assist you better.
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-primary btn-lg px-5 py-3" style="border-radius: 50px; font-weight: 600; box-shadow: 0 4px 15px rgba(0,123,255,0.3);">
                                            <i class="fas fa-paper-plane me-2"></i>Send Message
                                        </button>
                                        <p class="text-muted mt-3 mb-0">
                                            <i class="fas fa-clock me-1"></i>We typically respond within 24 hours
                                        </p>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-info-circle me-2"></i>Contact Information
                            </h5>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-envelope text-primary me-3"></i>
                                    <div>
                                        <strong>Email</strong><br>
                                        <a href="mailto:pinnacleseo11@gmail.com" class="text-decoration-none">pinnacleseo11@gmail.com</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-phone text-primary me-3"></i>
                                    <div>
                                        <strong>Phone</strong><br>
                                        <a href="tel:+9779702454856" class="text-decoration-none">+977 9702454856</a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-map-marker-alt text-primary me-3"></i>
                                    <div>
                                        <strong>Address</strong><br>
                                        <span class="text-muted">Lagankhel<br>Lalitpur, Nepal</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock text-primary me-3"></i>
                                    <div>
                                        <strong>Business Hours</strong><br>
                                        <span class="text-muted">Mon - Fri: 9:00 AM - 6:00 PM<br>Sat - Sun: 10:00 AM - 4:00 PM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <h5 class="card-title mb-4">
                                <i class="fas fa-question-circle me-2"></i>Need Help?
                            </h5>
                            
                            <div class="d-grid gap-2">
                                <a href="support.php" class="btn btn-outline-primary">
                                    <i class="fas fa-headset me-2"></i>Support Center
                                </a>
                                <a href="faq.php" class="btn btn-outline-primary">
                                    <i class="fas fa-question me-2"></i>FAQ
                                </a>
                                <a href="products.php" class="btn btn-outline-primary">
                                    <i class="fas fa-shopping-bag me-2"></i>Browse Products
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="mb-3">Frequently Asked Questions</h2>
                <p class="text-muted">Quick answers to common questions</p>
            </div>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-mdb-toggle="collapse" data-mdb-target="#faq1">
                                    How do I download my purchased products?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    After completing your purchase, you can download your products from your account dashboard. 
                                    Go to "My Orders" and click the download button next to each product.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#faq2">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept all major credit cards, PayPal, and other secure payment methods. 
                                    All transactions are processed securely through our payment partners.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#faq3">
                                    Do you offer refunds?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, we offer a 30-day money-back guarantee on all digital products. 
                                    If you're not satisfied with your purchase, contact our support team for a refund.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#faq4">
                                    How long do I have access to my downloads?
                                </button>
                            </h2>
                            <div id="faq4" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    You have lifetime access to your purchased products. You can re-download them 
                                    anytime from your account dashboard, even if you lose the original files.
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
        <a href="contact.php" class="bottom-nav-item active">
            <i class="fas fa-envelope"></i>
            <span>Contact</span>
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
