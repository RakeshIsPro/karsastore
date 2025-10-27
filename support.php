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

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    $priority = sanitizeInput($_POST['priority']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        // Insert support ticket
        $stmt = $pdo->prepare("
            INSERT INTO support_tickets (user_id, name, email, subject, message, priority, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'open')
        ");
        
        $userId = $user ? $user['id'] : null;
        
        if ($stmt->execute([$userId, $name, $email, $subject, $message, $priority])) {
            $ticketId = $pdo->lastInsertId();
            $success = "Your support ticket has been submitted successfully! Ticket ID: #$ticketId";
            
            // Send confirmation email
            $emailSubject = "Support Ticket Received - #$ticketId";
            $emailMessage = "
                <h2>Support Ticket Confirmation</h2>
                <p>Dear $name,</p>
                <p>We have received your support request and will respond within 24 hours.</p>
                
                <h3>Ticket Details:</h3>
                <p><strong>Ticket ID:</strong> #$ticketId</p>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Priority:</strong> " . ucfirst($priority) . "</p>
                
                <p>Thank you for contacting YBT Digital support.</p>
                <p>Best regards,<br>YBT Digital Support Team</p>
            ";
            
            sendEmail($email, $emailSubject, $emailMessage);
            
            // Clear form
            $_POST = [];
        } else {
            $error = 'Failed to submit your request. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Center - YBT Digital</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-digital-tachograph me-2"></i>YBT Digital
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
                        <a class="nav-link active" href="support.php">Support</a>
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
    <section class="bg-primary text-white py-5" style="margin-top: 76px;">
        <div class="container">
            <div class="text-center">
                <i class="fas fa-headset fa-3x mb-3"></i>
                <h1 class="display-6 fw-bold mb-3">Support Center</h1>
                <p class="lead">We're here to help you with any questions or issues</p>
            </div>
        </div>
    </section>

    <!-- Support Options -->
    <section class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-question-circle fa-3x text-primary mb-3"></i>
                            <h5>FAQ</h5>
                            <p class="text-muted">Find answers to commonly asked questions</p>
                            <a href="#faq" class="btn btn-outline-primary">Browse FAQ</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-envelope fa-3x text-success mb-3"></i>
                            <h5>Email Support</h5>
                            <p class="text-muted">Send us a message and we'll respond within 24 hours</p>
                            <a href="#contact" class="btn btn-outline-success">Contact Us</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <div class="card h-100 text-center shadow-sm">
                        <div class="card-body">
                            <i class="fas fa-download fa-3x text-info mb-3"></i>
                            <h5>Download Issues</h5>
                            <p class="text-muted">Having trouble downloading your products?</p>
                            <a href="#download-help" class="btn btn-outline-info">Get Help</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Frequently Asked Questions</h2>
                <p class="text-muted">Quick answers to common questions</p>
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
                                    After successful payment, you can download your products from your account dashboard. Go to "My Orders" and click the download button next to each product. Download links are also sent to your email.
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
                                    We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and various local payment methods through our secure payment gateways including Stripe and Razorpay.
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
                                    Yes, we offer a 30-day money-back guarantee on all digital products. If you're not satisfied with your purchase, contact our support team within 30 days for a full refund.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#faqCollapse4">
                                    Can I use the products for commercial purposes?
                                </button>
                            </h2>
                            <div id="faqCollapse4" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes, all our digital products come with commercial licenses. You can use them for personal and commercial projects. However, you cannot resell or redistribute the original files.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq5">
                                <button class="accordion-button collapsed" type="button" data-mdb-toggle="collapse" data-mdb-target="#faqCollapse5">
                                    How long do download links remain active?
                                </button>
                            </h2>
                            <div id="faqCollapse5" class="accordion-collapse collapse" data-mdb-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Download links remain active indefinitely. You can access your purchased products anytime from your account dashboard. We recommend downloading and backing up your files after purchase.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Contact Support</h2>
                <p class="text-muted">Can't find what you're looking for? Send us a message</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label fw-bold text-dark fs-6">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo $user ? htmlspecialchars($user['name']) : htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Please enter your full name.</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label fw-bold text-dark fs-6">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo $user ? htmlspecialchars($user['email']) : htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Please enter a valid email address.</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="subject" class="form-label fw-bold text-dark fs-6">Subject *</label>
                                        <input type="text" class="form-control" id="subject" name="subject" 
                                               value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>" required>
                                        <div class="invalid-feedback">Please enter a subject.</div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="priority" class="form-label fw-bold text-dark fs-6">Priority</label>
                                        <select class="form-select" id="priority" name="priority">
                                            <option value="low" <?php echo ($_POST['priority'] ?? '') == 'low' ? 'selected' : ''; ?>>Low</option>
                                            <option value="medium" <?php echo ($_POST['priority'] ?? 'medium') == 'medium' ? 'selected' : ''; ?>>Medium</option>
                                            <option value="high" <?php echo ($_POST['priority'] ?? '') == 'high' ? 'selected' : ''; ?>>High</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label for="message" class="form-label fw-bold text-dark fs-6">Message *</label>
                                        <textarea class="form-control" id="message" name="message" rows="5" 
                                                  placeholder="Please describe your issue or question in detail..." required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                                        <div class="invalid-feedback">Please enter your message.</div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Download Help -->
    <section id="download-help" class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Download Help</h2>
                <p class="text-muted">Troubleshooting common download issues</p>
            </div>
            
            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5><i class="fas fa-exclamation-triangle text-warning me-2"></i>Download Not Starting?</h5>
                            <ul class="list-unstyled mt-3">
                                <li><i class="fas fa-check text-success me-2"></i>Check your internet connection</li>
                                <li><i class="fas fa-check text-success me-2"></i>Disable ad blockers temporarily</li>
                                <li><i class="fas fa-check text-success me-2"></i>Try a different browser</li>
                                <li><i class="fas fa-check text-success me-2"></i>Clear browser cache and cookies</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5><i class="fas fa-file-archive text-info me-2"></i>Can't Open Downloaded Files?</h5>
                            <ul class="list-unstyled mt-3">
                                <li><i class="fas fa-check text-success me-2"></i>Ensure you have appropriate software (WinRAR, 7-Zip)</li>
                                <li><i class="fas fa-check text-success me-2"></i>Check if download completed fully</li>
                                <li><i class="fas fa-check text-success me-2"></i>Scan files with antivirus software</li>
                                <li><i class="fas fa-check text-success me-2"></i>Re-download if file appears corrupted</li>
                            </ul>
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
                        <i class="fas fa-digital-tachograph me-2"></i>YBT Digital
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
                        <li><a href="#faq" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="terms.php" class="text-muted text-decoration-none">Terms of Service</a></li>
                        <li><a href="privacy.php" class="text-muted text-decoration-none">Privacy Policy</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <p class="text-muted mb-2">
                        <i class="fas fa-envelope me-2"></i>
                        <a href="mailto:pinnacleseo11@gmail.com" class="text-decoration-none">pinnacleseo11@gmail.com</a>
                    </p>
                    <p class="text-muted mb-2">
                        <i class="fas fa-phone me-2"></i>
                        <a href="#" class="text-decoration-none" onclick="showPhoneOptions(event)">9702454856</a>
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-clock me-2"></i>Mon-Fri: 9AM-6PM EST
                    </p>
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
        <a href="index.php" class="bottom-nav-item">
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

    <!-- Phone Options Modal -->
    <div class="modal fade" id="phoneOptionsModal" tabindex="-1" aria-labelledby="phoneOptionsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="phoneOptionsModalLabel">
                        <i class="fas fa-phone text-primary me-2"></i>Contact Options
                    </h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-dark mb-4 fw-bold">Choose how you'd like to contact us:</p>
                    <div class="d-grid gap-3">
                        <a href="tel:9702454856" class="btn btn-primary btn-lg text-white">
                            <i class="fas fa-phone me-3 fa-lg"></i>
                            <div class="text-start">
                                <div class="fw-bold fs-5">Phone Call</div>
                                <div class="text-white-50">Direct call to 9702454856</div>
                            </div>
                        </a>
                        <a href="https://wa.me/9779702454856" target="_blank" class="btn btn-success btn-lg text-white">
                            <i class="fab fa-whatsapp me-3 fa-lg"></i>
                            <div class="text-start">
                                <div class="fw-bold fs-5">WhatsApp</div>
                                <div class="text-white-50">Message us on WhatsApp</div>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Form validation
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
        
        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
        
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Show phone options modal
        function showPhoneOptions(event) {
            event.preventDefault();
            const phoneModal = new mdb.Modal(document.getElementById('phoneOptionsModal'));
            phoneModal.show();
        }
    </script>
</body>
</html>
