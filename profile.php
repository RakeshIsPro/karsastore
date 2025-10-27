<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

$user = getUserById($_SESSION['user_id']);
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_profile') {
        $name = sanitizeInput($_POST['name']);
        $email = sanitizeInput($_POST['email']);
        
        if (empty($name) || empty($email)) {
            $error = 'Please fill in all required fields.';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address.';
        } else {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $_SESSION['user_id']]);
            
            if ($stmt->fetch()) {
                $error = 'This email address is already in use.';
            } else {
                // Update profile
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$name, $email, $_SESSION['user_id']])) {
                    $success = 'Profile updated successfully!';
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $_SESSION['user_name'] = $name;
                } else {
                    $error = 'Failed to update profile. Please try again.';
                }
            }
        }
    } elseif ($action == 'change_password') {
        $currentPassword = $_POST['current_password'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $error = 'Please fill in all password fields.';
        } elseif (!verifyPassword($currentPassword, $user['password'])) {
            $error = 'Current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $error = 'New password must be at least 6 characters long.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New passwords do not match.';
        } else {
            // Update password
            $hashedPassword = hashPassword($newPassword);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $_SESSION['user_id']])) {
                $success = 'Password changed successfully!';
            } else {
                $error = 'Failed to change password. Please try again.';
            }
        }
    }
}

// Get user statistics
$stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$totalOrders = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT SUM(total_amount) FROM orders WHERE user_id = ? AND payment_status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$totalSpent = $stmt->fetchColumn() ?: 0;

$stmt = $pdo->prepare("SELECT COUNT(DISTINCT oi.product_id) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE o.user_id = ? AND o.payment_status = 'completed'");
$stmt->execute([$_SESSION['user_id']]);
$totalProducts = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - YBT Digital</title>
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
                    
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-mdb-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item active" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-download me-2"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="bg-primary text-white py-4" style="margin-top: 76px;">
        <div class="container">
            <div class="d-flex align-items-center">
                <i class="fas fa-user-circle fa-2x me-3"></i>
                <div>
                    <h1 class="h3 mb-0">My Profile</h1>
                    <p class="mb-0 opacity-75">Manage your account settings and preferences</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Profile Content -->
    <section class="py-5">
        <div class="container">
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

            <div class="row">
                <!-- Profile Stats -->
                <div class="col-lg-4 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body text-center">
                            <div class="avatar-container mb-3">
                                <div class="avatar bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" 
                                     style="width: 80px; height: 80px; font-size: 2rem;">
                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                </div>
                            </div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($user['name']); ?></h5>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email']); ?></p>
                            <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Account Stats -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Account Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-0"><?php echo $totalOrders; ?></h4>
                                        <small class="text-muted">Orders</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border-end">
                                        <h4 class="text-success mb-0"><?php echo $totalProducts; ?></h4>
                                        <small class="text-muted">Products</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-info mb-0"><?php echo formatPrice($totalSpent); ?></h4>
                                    <small class="text-muted">Spent</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="orders.php" class="btn btn-outline-primary">
                                    <i class="fas fa-download me-2"></i>My Orders
                                </a>
                                <a href="products.php" class="btn btn-outline-success">
                                    <i class="fas fa-shopping-bag me-2"></i>Browse Products
                                </a>
                                <a href="support.php" class="btn btn-outline-info">
                                    <i class="fas fa-headset me-2"></i>Get Support
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Forms -->
                <div class="col-lg-8">
                    <!-- Update Profile -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-user me-2"></i>Profile Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="update_profile">
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        <div class="invalid-feedback">
                                            Please enter your full name.
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('F j, Y', strtotime($user['created_at'])); ?>" readonly>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" class="needs-validation" novalidate id="passwordForm">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label for="current_password" class="form-label">Current Password *</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        <div class="invalid-feedback">
                                            Please enter your current password.
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="new_password" class="form-label">New Password *</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" 
                                               required minlength="6">
                                        <div class="invalid-feedback">
                                            Password must be at least 6 characters long.
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label for="confirm_password" class="form-label">Confirm New Password *</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        <div class="invalid-feedback" id="confirmPasswordFeedback">
                                            Please confirm your new password.
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
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
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Account</h6>
                    <ul class="list-unstyled">
                        <li><a href="profile.php" class="text-muted text-decoration-none">Profile</a></li>
                        <li><a href="orders.php" class="text-muted text-decoration-none">My Orders</a></li>
                        <li><a href="support.php" class="text-muted text-decoration-none">Support</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Help</h6>
                    <ul class="list-unstyled">
                        <li><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold mb-3">Stay Updated</h6>
                    <p class="text-muted">Get notifications about new products and exclusive offers.</p>
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Your email">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
            
            <hr class="my-4">
            <div class="text-center">
                <p class="text-muted mb-0">&copy; 2024 YBT Digital. All rights reserved.</p>
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
        <a href="profile.php" class="bottom-nav-item active">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

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
        
        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            const feedback = document.getElementById('confirmPasswordFeedback');
            
            if (confirmPassword && newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                feedback.textContent = 'Passwords do not match.';
            } else {
                this.setCustomValidity('');
                feedback.textContent = 'Please confirm your new password.';
            }
        });
        
        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>
