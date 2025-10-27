<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login for checkout
requireLogin();

$user = getUserById($_SESSION['user_id']);

// Get cart items
$cartItems = getCartItems($_SESSION['user_id']);

if (empty($cartItems)) {
    header('Location: cart.php');
    exit;
}

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

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process checkout
    $billingInfo = [
        'name' => sanitizeInput($_POST['billing_name']),
        'email' => sanitizeInput($_POST['billing_email']),
        'address' => sanitizeInput($_POST['billing_address']),
        'city' => sanitizeInput($_POST['billing_city']),
        'country' => sanitizeInput($_POST['billing_country']),
        'postal_code' => sanitizeInput($_POST['billing_postal'])
    ];
    
    $paymentMethod = sanitizeInput($_POST['payment_method']);
    
    // Validate required fields
    if (empty($billingInfo['name']) || empty($billingInfo['email'])) {
        $error = 'Please fill in all required billing information.';
    } else {
        // Create order
        $orderId = createOrder(
            $_SESSION['user_id'],
            $cartItems,
            $total,
            $taxAmount,
            $discount,
            $appliedCoupon['code'] ?? null
        );
        
        if ($orderId) {
            // Update order with billing info and payment method
            $stmt = $pdo->prepare("UPDATE orders SET billing_info = ?, payment_method = ? WHERE id = ?");
            $stmt->execute([json_encode($billingInfo), $paymentMethod, $orderId]);
            
            // Clear cart and coupon
            clearCart($_SESSION['user_id']);
            unset($_SESSION['applied_coupon']);
            
            // Redirect to payment processing
            header("Location: payment.php?order_id=$orderId");
            exit;
        } else {
            $error = 'Failed to create order. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - YBT Digital</title>
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
            
            <div class="navbar-nav ms-auto">
                <span class="navbar-text">
                    <i class="fas fa-lock me-1"></i>Secure Checkout
                </span>
            </div>
        </div>
    </nav>

    <!-- Checkout Progress -->
    <section class="bg-light py-3" style="margin-top: 76px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="step completed">
                            <div class="step-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <span>Cart</span>
                        </div>
                        <div class="step-line completed"></div>
                        <div class="step active">
                            <div class="step-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <span>Checkout</span>
                        </div>
                        <div class="step-line"></div>
                        <div class="step">
                            <div class="step-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <span>Complete</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Checkout Form -->
    <section class="py-5">
        <div class="container">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="needs-validation" novalidate>
                <div class="row">
                    <!-- Billing Information -->
                    <div class="col-lg-7 mb-4">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-user me-2"></i>Billing Information
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="billing_name" class="form-label">Full Name *</label>
                                        <input type="text" class="form-control" id="billing_name" name="billing_name" 
                                               value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="billing_email" class="form-label">Email Address *</label>
                                        <input type="email" class="form-control" id="billing_email" name="billing_email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="billing_address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="billing_address" name="billing_address">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="billing_city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="billing_city" name="billing_city">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="billing_postal" class="form-label">Postal Code</label>
                                        <input type="text" class="form-control" id="billing_postal" name="billing_postal">
                                    </div>
                                    <div class="col-md-3">
                                        <label for="billing_country" class="form-label">Country</label>
                                        <select class="form-select" id="billing_country" name="billing_country">
                                            <option value="">Select Country</option>
                                            <option value="US">United States</option>
                                            <option value="CA">Canada</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="AU">Australia</option>
                                            <option value="IN">India</option>
                                            <option value="DE">Germany</option>
                                            <option value="FR">France</option>
                                            <option value="JP">Japan</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Method -->
                        <div class="card shadow-sm mt-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-credit-card me-2"></i>Payment Method
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="stripe" value="stripe" checked>
                                            <label class="form-check-label d-flex align-items-center" for="stripe">
                                                <i class="fab fa-cc-stripe fa-2x me-3"></i>
                                                <div>
                                                    <strong>Credit/Debit Card</strong>
                                                    <div class="text-muted small">Secure payment via Stripe</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="paypal" value="paypal">
                                            <label class="form-check-label d-flex align-items-center" for="paypal">
                                                <i class="fab fa-paypal fa-2x me-3"></i>
                                                <div>
                                                    <strong>PayPal</strong>
                                                    <div class="text-muted small">Pay with your PayPal account</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" 
                                                   id="razorpay" value="razorpay">
                                            <label class="form-check-label d-flex align-items-center" for="razorpay">
                                                <i class="fas fa-university fa-2x me-3"></i>
                                                <div>
                                                    <strong>Razorpay</strong>
                                                    <div class="text-muted small">UPI, Net Banking, Cards</div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-header">
                                <h5 class="mb-0">Order Summary</h5>
                            </div>
                            <div class="card-body">
                                <!-- Order Items -->
                                <div class="mb-3">
                                    <?php foreach ($cartItems as $item): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($item['title']); ?></h6>
                                                <small class="text-muted">Digital Product</small>
                                            </div>
                                            <span class="fw-bold"><?php echo formatPrice($item['price']); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <hr>
                                
                                <!-- Coupon Code -->
                                <div class="mb-3">
                                    <?php if (!$appliedCoupon): ?>
                                        <div class="coupon-form">
                                            <label class="form-label fw-bold">
                                                <i class="fas fa-ticket-alt me-2 text-primary"></i>Have a Coupon Code?
                                            </label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="couponCode" 
                                                       placeholder="Enter coupon code" style="text-transform: uppercase;">
                                                <button class="btn btn-outline-primary" type="button" id="applyCoupon">
                                                    <i class="fas fa-check me-1"></i>Apply
                                                </button>
                                            </div>
                                            <div id="couponMessage" class="mt-2"></div>
                                        </div>
                                    <?php else: ?>
                                        <div class="applied-coupon bg-success bg-opacity-10 border border-success rounded p-3">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <strong>Coupon Applied: <?php echo $appliedCoupon['code']; ?></strong>
                                                    <div class="small text-muted">
                                                        <?php if ($appliedCoupon['type'] == 'flat'): ?>
                                                            Flat discount of <?php echo formatPrice($appliedCoupon['value']); ?>
                                                        <?php else: ?>
                                                            <?php echo $appliedCoupon['value']; ?>% discount
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm btn-outline-danger" id="removeCoupon">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <hr>
                                
                                <!-- Totals -->
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal:</span>
                                    <span><?php echo formatPrice($subtotal); ?></span>
                                </div>
                                
                                <?php if ($taxRate > 0): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Tax (<?php echo getSetting('tax_rate', 0); ?>%):</span>
                                        <span><?php echo formatPrice($taxAmount); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($discount > 0): ?>
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>Discount (<?php echo $appliedCoupon['code']; ?>):</span>
                                        <span>-<?php echo formatPrice($discount); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total:</strong>
                                    <strong class="text-primary"><?php echo formatPrice($total); ?></strong>
                                </div>
                                
                                <!-- Place Order Button -->
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-lock me-2"></i>Place Order
                                    </button>
                                </div>
                                
                                <!-- Security Notice -->
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Your payment information is secure and encrypted
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script>
        // Coupon functionality
        document.addEventListener('DOMContentLoaded', function() {
            const applyCouponBtn = document.getElementById('applyCoupon');
            const removeCouponBtn = document.getElementById('removeCoupon');
            const couponCodeInput = document.getElementById('couponCode');
            const couponMessage = document.getElementById('couponMessage');
            
            // Apply coupon
            if (applyCouponBtn) {
                applyCouponBtn.addEventListener('click', function() {
                    const code = couponCodeInput.value.trim().toUpperCase();
                    
                    if (!code) {
                        showCouponMessage('Please enter a coupon code', 'danger');
                        return;
                    }
                    
                    // Calculate current subtotal
                    const subtotal = <?php echo $subtotal; ?>;
                    
                    // Show loading
                    applyCouponBtn.disabled = true;
                    applyCouponBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Applying...';
                    
                    // Apply coupon via API
                    fetch('api/coupon.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            action: 'apply',
                            code: code,
                            amount: subtotal
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showCouponMessage(data.message, 'success');
                            // Reload page to show updated totals
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        } else {
                            showCouponMessage(data.message, 'danger');
                        }
                    })
                    .catch(error => {
                        showCouponMessage('Error applying coupon. Please try again.', 'danger');
                    })
                    .finally(() => {
                        applyCouponBtn.disabled = false;
                        applyCouponBtn.innerHTML = '<i class="fas fa-check me-1"></i>Apply';
                    });
                });
                
                // Apply coupon on Enter key
                couponCodeInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        applyCouponBtn.click();
                    }
                });
            }
            
            // Remove coupon
            if (removeCouponBtn) {
                removeCouponBtn.addEventListener('click', function() {
                    if (confirm('Remove applied coupon?')) {
                        fetch('api/coupon.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'remove'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            }
                        })
                        .catch(error => {
                            alert('Error removing coupon. Please refresh the page.');
                        });
                    }
                });
            }
            
            function showCouponMessage(message, type) {
                couponMessage.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show">
                    ${message}
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>`;
            }
        });
        
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
    </script>
    
    <style>
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
            color: #6c757d;
        }
        
        .step.active .step-icon {
            background: #2563eb;
            color: white;
        }
        
        .step.completed .step-icon {
            background: #10b981;
            color: white;
        }
        
        .step-line {
            flex: 1;
            height: 2px;
            background: #e9ecef;
            margin: 0 1rem;
            margin-top: 20px;
        }
        
        .step-line.completed {
            background: #10b981;
        }
        
        .step span {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .step.active span,
        .step.completed span {
            color: #495057;
            font-weight: 500;
        }
    </style>
</body>
</html>
