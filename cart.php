<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Get cart items
$userId = $_SESSION['user_id'] ?? null;
$sessionId = session_id();
$cartItems = getCartItems($userId, $sessionId);

// Calculate totals
$subtotal = array_sum(array_column($cartItems, 'price'));
$taxRate = (float)getSetting('tax_rate', 0) / 100;
$taxAmount = $subtotal * $taxRate;

// Apply coupon if exists
$discount = 0;
$appliedCoupon = $_SESSION['applied_coupon'] ?? null;
if ($appliedCoupon) {
    $discount = $appliedCoupon['discount'];
}

$total = $subtotal + $taxAmount - $discount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - YBT Digital</title>
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
                        <a class="nav-link position-relative active" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="badge badge-danger badge-pill position-absolute top-0 start-100 translate-middle" id="cart-count"><?php echo count($cartItems); ?></span>
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
    <section class="bg-primary text-white py-4" style="margin-top: 76px;">
        <div class="container">
            <div class="d-flex align-items-center">
                <i class="fas fa-shopping-cart fa-2x me-3"></i>
                <div>
                    <h1 class="h3 mb-0">Shopping Cart</h1>
                    <p class="mb-0 opacity-75"><?php echo count($cartItems); ?> item(s) in your cart</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Cart Content -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($cartItems)): ?>
                <!-- Empty Cart -->
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                    <h3>Your cart is empty</h3>
                    <p class="text-muted mb-4">Looks like you haven't added any products to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <!-- Cart Items -->
                    <div class="col-lg-8 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h5 class="mb-0">Cart Items</h5>
                            </div>
                            <div class="card-body p-0">
                                <?php foreach ($cartItems as $index => $item): ?>
                                    <?php
                                    $images = json_decode($item['images'] ?? '[]', true);
                                    $mainImage = !empty($images) ? $images[0] : 'assets/images/placeholder.jpg';
                                    ?>
                                    <div class="cart-item border-bottom p-4" data-product-id="<?php echo $item['product_id']; ?>">
                                        <div class="row align-items-center">
                                            <div class="col-md-2 mb-3 mb-md-0">
                                                <img src="<?php echo $mainImage; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                     class="img-fluid rounded" style="max-height: 80px; object-fit: cover;">
                                            </div>
                                            <div class="col-md-6 mb-3 mb-md-0">
                                                <h6 class="mb-1">
                                                    <a href="product.php?id=<?php echo $item['product_id']; ?>" 
                                                       class="text-decoration-none text-dark">
                                                        <?php echo htmlspecialchars($item['title']); ?>
                                                    </a>
                                                </h6>
                                                <small class="text-muted">Digital Product</small>
                                            </div>
                                            <div class="col-md-2 mb-3 mb-md-0 text-center">
                                                <span class="fw-bold"><?php echo formatPrice($item['price']); ?></span>
                                            </div>
                                            <div class="col-md-2 text-center">
                                                <button class="btn btn-outline-danger btn-sm remove-from-cart" 
                                                        data-product-id="<?php echo $item['product_id']; ?>"
                                                        title="Remove from cart">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <!-- Continue Shopping -->
                        <div class="mt-3">
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                            </a>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span id="subtotal"><?php echo formatPrice($subtotal); ?></span>
                                </div>
                                
                                <?php if ($taxRate > 0): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (<?php echo getSetting('tax_rate', 0); ?>%):</span>
                                        <span id="tax-amount"><?php echo formatPrice($taxAmount); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($discount > 0): ?>
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>Discount (<?php echo $appliedCoupon['code']; ?>):</span>
                                        <span id="discount-amount">-<?php echo formatPrice($discount); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong id="total" class="text-primary"><?php echo formatPrice($total); ?></strong>
                                </div>
                                
                                <!-- Coupon Code -->
                                <div class="mb-3">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="coupon-code" placeholder="Coupon code">
                                        <button class="btn btn-outline-secondary" type="button" id="apply-coupon">
                                            Apply
                                        </button>
                                    </div>
                                    <div id="coupon-message" class="mt-2"></div>
                                </div>
                                
                                <!-- Checkout Button -->
                                <?php if ($user): ?>
                                    <div class="d-grid">
                                        <a href="checkout.php" class="btn btn-primary btn-lg">
                                            <i class="fas fa-credit-card me-2"></i>Proceed to Checkout
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="d-grid gap-2">
                                        <a href="auth/login.php?redirect=<?php echo urlencode('checkout.php'); ?>" 
                                           class="btn btn-primary">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login to Checkout
                                        </a>
                                        <a href="auth/signup.php" class="btn btn-outline-primary">
                                            <i class="fas fa-user-plus me-2"></i>Create Account
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Security Info -->
                                <div class="mt-3 text-center">
                                    <small class="text-muted">
                                        <i class="fas fa-lock me-1"></i>
                                        Secure checkout with SSL encryption
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Methods -->
                        <div class="card mt-3">
                            <div class="card-body text-center">
                                <h6 class="mb-3">We Accept</h6>
                                <div class="d-flex justify-content-center gap-3">
                                    <i class="fab fa-cc-visa fa-2x text-muted"></i>
                                    <i class="fab fa-cc-mastercard fa-2x text-muted"></i>
                                    <i class="fab fa-cc-paypal fa-2x text-muted"></i>
                                    <i class="fab fa-cc-stripe fa-2x text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
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
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="products.php" class="text-muted text-decoration-none">Products</a></li>
                        <li><a href="about.php" class="text-muted text-decoration-none">About Us</a></li>
                        <li><a href="contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="support.php" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold mb-3">Newsletter</h6>
                    <p class="text-muted">Subscribe for updates and offers.</p>
                    <form class="d-flex">
                        <input type="email" class="form-control me-2" placeholder="Email">
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
        <a href="cart.php" class="bottom-nav-item active">
            <i class="fas fa-shopping-cart"></i>
            <span>Cart</span>
        </a>
        <a href="<?php echo $user ? 'profile.php' : 'auth/login.php'; ?>" class="bottom-nav-item">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initCouponCode();
        });
        
        function updateCartTotal() {
            // Recalculate totals after item removal
            fetch('api/cart.php?action=items')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const subtotal = data.total;
                        const taxRate = <?php echo $taxRate; ?>;
                        const taxAmount = subtotal * taxRate;
                        const total = subtotal + taxAmount;
                        
                        document.getElementById('subtotal').textContent = formatPrice(subtotal);
                        <?php if ($taxRate > 0): ?>
                            document.getElementById('tax-amount').textContent = formatPrice(taxAmount);
                        <?php endif; ?>
                        document.getElementById('total').textContent = formatPrice(total);
                        
                        // If cart is empty, reload page
                        if (data.items.length === 0) {
                            window.location.reload();
                        }
                    }
                });
        }
        
        function initCouponCode() {
            const applyCouponBtn = document.getElementById('apply-coupon');
            const couponInput = document.getElementById('coupon-code');
            const couponMessage = document.getElementById('coupon-message');
            
            if (applyCouponBtn) {
                applyCouponBtn.addEventListener('click', function() {
                    const code = couponInput.value.trim();
                    
                    if (!code) {
                        showCouponMessage('Please enter a coupon code', 'danger');
                        return;
                    }
                    
                    applyCoupon(code);
                });
                
                couponInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyCouponBtn.click();
                    }
                });
            }
        }
        
        async function applyCoupon(code) {
            const applyCouponBtn = document.getElementById('apply-coupon');
            const originalText = applyCouponBtn.textContent;
            
            applyCouponBtn.textContent = 'Applying...';
            applyCouponBtn.disabled = true;
            
            try {
                const response = await fetch('api/coupon.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        action: 'apply',
                        code: code,
                        amount: <?php echo $subtotal; ?>
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showCouponMessage(`Coupon applied! You saved ${formatPrice(data.discount)}`, 'success');
                    // Update totals (you might want to reload the page or update via AJAX)
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showCouponMessage(data.message || 'Invalid coupon code', 'danger');
                }
            } catch (error) {
                showCouponMessage('Failed to apply coupon. Please try again.', 'danger');
            } finally {
                applyCouponBtn.textContent = originalText;
                applyCouponBtn.disabled = false;
            }
        }
        
        function showCouponMessage(message, type) {
            const couponMessage = document.getElementById('coupon-message');
            couponMessage.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
            </div>`;
        }
        
        // Override the removeFromCart function to update totals
        const originalRemoveFromCart = window.removeFromCart;
        window.removeFromCart = async function(productId, button) {
            await originalRemoveFromCart(productId, button);
            updateCartTotal();
        };
    </script>
</body>
</html>
