<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get order ID
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$orderId) {
    header('Location: cart.php');
    exit;
}

// Get order details
$order = getOrderById($orderId);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: cart.php');
    exit;
}

// If order is already completed, redirect to success
if ($order['payment_status'] == 'completed') {
    header('Location: order-success.php?order_id=' . $orderId);
    exit;
}

$user = getUserById($_SESSION['user_id']);
$orderItems = getOrderItems($orderId);
$billingInfo = json_decode($order['billing_info'], true);

// Payment gateway settings
$stripeKey = getSetting('stripe_key');
$razorpayKey = getSetting('razorpay_key');
$paypalClientId = getSetting('paypal_client_id');

// Handle payment processing
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $paymentMethod = $order['payment_method'];
    $paymentData = $_POST;
    
    // Simulate payment processing (in real implementation, integrate with actual payment gateways)
    $success = processPayment($orderId, $paymentMethod, $paymentData);
    
    if ($success) {
        // Update order status
        $stmt = $pdo->prepare("UPDATE orders SET payment_status = 'completed', status = 'completed', transaction_id = ? WHERE id = ?");
        $transactionId = 'TXN_' . time() . '_' . $orderId;
        $stmt->execute([$transactionId, $orderId]);
        
        // Send confirmation email
        sendOrderConfirmationEmail($order, $orderItems, $user);
        
        header('Location: order-success.php?order_id=' . $orderId);
        exit;
    } else {
        $error = 'Payment failed. Please try again.';
    }
}

function processPayment($orderId, $method, $data) {
    // This is a simulation - in real implementation, integrate with actual payment gateways
    // For demo purposes, we'll just return true
    return true;
}

function sendOrderConfirmationEmail($order, $items, $user) {
    $subject = 'Order Confirmation - ' . $order['order_number'];
    $itemsList = '';
    foreach ($items as $item) {
        $itemsList .= "- {$item['product_title']} - " . formatPrice($item['price']) . "\n";
    }
    
    $message = "
        <h2>Thank you for your order!</h2>
        <p>Dear {$user['name']},</p>
        <p>Your order has been confirmed and payment processed successfully.</p>
        
        <h3>Order Details:</h3>
        <p><strong>Order Number:</strong> {$order['order_number']}</p>
        <p><strong>Total Amount:</strong> " . formatPrice($order['total_amount']) . "</p>
        
        <h3>Items:</h3>
        <ul>
    ";
    
    foreach ($items as $item) {
        $message .= "<li>{$item['product_title']} - " . formatPrice($item['price']) . "</li>";
    }
    
    $message .= "
        </ul>
        
        <p>You can download your products from your account dashboard.</p>
        <p><a href='" . $_SERVER['HTTP_HOST'] . "/digital nest/orders.php'>View Your Orders</a></p>
        
        <p>Best regards,<br>The YBT Digital Team</p>
    ";
    
    sendEmail($user['email'], $subject, $message);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - YBT Digital</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Payment Gateway Scripts -->
    <?php if ($order['payment_method'] == 'stripe' && $stripeKey): ?>
        <script src="https://js.stripe.com/v3/"></script>
    <?php endif; ?>
    
    <?php if ($order['payment_method'] == 'razorpay' && $razorpayKey): ?>
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <?php endif; ?>
    
    <?php if ($order['payment_method'] == 'paypal' && $paypalClientId): ?>
        <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypalClientId; ?>&currency=USD"></script>
    <?php endif; ?>
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
                    <i class="fas fa-lock me-1"></i>Secure Payment
                </span>
            </div>
        </div>
    </nav>

    <!-- Payment Content -->
    <section class="py-5" style="margin-top: 76px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>Complete Payment
                            </h4>
                        </div>
                        <div class="card-body">
                            <!-- Order Summary -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6>Order Details</h6>
                                    <p class="mb-1"><strong>Order #:</strong> <?php echo $order['order_number']; ?></p>
                                    <p class="mb-1"><strong>Total:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                                    <p class="mb-0"><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6>Billing Information</h6>
                                    <p class="mb-1"><?php echo htmlspecialchars($billingInfo['name']); ?></p>
                                    <p class="mb-1"><?php echo htmlspecialchars($billingInfo['email']); ?></p>
                                    <?php if ($billingInfo['address']): ?>
                                        <p class="mb-0"><?php echo htmlspecialchars($billingInfo['address']); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Payment Form -->
                            <?php if (isset($error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($order['payment_method'] == 'stripe'): ?>
                                <!-- Stripe Payment Form -->
                                <div id="stripe-payment">
                                    <h6 class="mb-3">Credit Card Information</h6>
                                    <form id="stripe-form" method="POST">
                                        <div class="mb-3">
                                            <label class="form-label">Card Details</label>
                                            <div id="card-element" class="form-control" style="height: 40px; padding: 10px;">
                                                <!-- Stripe Elements will create form elements here -->
                                            </div>
                                            <div id="card-errors" role="alert" class="text-danger mt-2"></div>
                                        </div>
                                        
                                        <button type="submit" id="stripe-submit" class="btn btn-primary btn-lg w-100">
                                            <i class="fas fa-lock me-2"></i>Pay <?php echo formatPrice($order['total_amount']); ?>
                                        </button>
                                    </form>
                                </div>

                            <?php elseif ($order['payment_method'] == 'paypal'): ?>
                                <!-- PayPal Payment -->
                                <div id="paypal-payment">
                                    <h6 class="mb-3">Pay with PayPal</h6>
                                    <div id="paypal-button-container"></div>
                                </div>

                            <?php elseif ($order['payment_method'] == 'razorpay'): ?>
                                <!-- Razorpay Payment -->
                                <div id="razorpay-payment">
                                    <h6 class="mb-3">Pay with Razorpay</h6>
                                    <button id="razorpay-button" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-credit-card me-2"></i>Pay <?php echo formatPrice($order['total_amount']); ?>
                                    </button>
                                </div>

                            <?php else: ?>
                                <!-- Demo Payment (for testing) -->
                                <div id="demo-payment">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Demo Mode:</strong> This is a demonstration. Click the button below to simulate a successful payment.
                                    </div>
                                    
                                    <form method="POST">
                                        <input type="hidden" name="demo_payment" value="1">
                                        <button type="submit" class="btn btn-success btn-lg w-100">
                                            <i class="fas fa-check me-2"></i>Complete Demo Payment
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>

                            <!-- Security Notice -->
                            <div class="text-center mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Your payment is secured with SSL encryption
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    
    <?php if ($order['payment_method'] == 'stripe' && $stripeKey): ?>
    <script>
        // Stripe Payment Integration
        const stripe = Stripe('<?php echo $stripeKey; ?>');
        const elements = stripe.elements();
        
        const cardElement = elements.create('card');
        cardElement.mount('#card-element');
        
        const form = document.getElementById('stripe-form');
        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            
            const {token, error} = await stripe.createToken(cardElement);
            
            if (error) {
                document.getElementById('card-errors').textContent = error.message;
            } else {
                // Submit token to server
                const hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripe_token');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);
                form.submit();
            }
        });
    </script>
    <?php endif; ?>

    <?php if ($order['payment_method'] == 'paypal' && $paypalClientId): ?>
    <script>
        // PayPal Payment Integration
        paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '<?php echo $order['total_amount']; ?>'
                        }
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Submit payment details to server
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="paypal_order_id" value="${data.orderID}">
                        <input type="hidden" name="paypal_payer_id" value="${data.payerID}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                });
            }
        }).render('#paypal-button-container');
    </script>
    <?php endif; ?>

    <?php if ($order['payment_method'] == 'razorpay' && $razorpayKey): ?>
    <script>
        // Razorpay Payment Integration
        document.getElementById('razorpay-button').onclick = function(e) {
            e.preventDefault();
            
            const options = {
                key: '<?php echo $razorpayKey; ?>',
                amount: <?php echo $order['total_amount'] * 100; ?>, // Amount in paise
                currency: 'INR',
                name: 'YBT Digital',
                description: 'Order #<?php echo $order['order_number']; ?>',
                order_id: '<?php echo $order['order_number']; ?>',
                handler: function(response) {
                    // Submit payment details to server
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.innerHTML = `
                        <input type="hidden" name="razorpay_payment_id" value="${response.razorpay_payment_id}">
                        <input type="hidden" name="razorpay_order_id" value="${response.razorpay_order_id}">
                        <input type="hidden" name="razorpay_signature" value="${response.razorpay_signature}">
                    `;
                    document.body.appendChild(form);
                    form.submit();
                },
                prefill: {
                    name: '<?php echo $billingInfo['name']; ?>',
                    email: '<?php echo $billingInfo['email']; ?>'
                }
            };
            
            const rzp = new Razorpay(options);
            rzp.open();
        };
    </script>
    <?php endif; ?>
</body>
</html>
