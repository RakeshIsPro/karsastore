<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get order ID
$orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$orderId) {
    header('Location: index.php');
    exit;
}

// Get order details
$order = getOrderById($orderId);

if (!$order || $order['user_id'] != $_SESSION['user_id']) {
    header('Location: index.php');
    exit;
}

$user = getUserById($_SESSION['user_id']);
$orderItems = getOrderItems($orderId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - YBT Digital</title>
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
                <a class="nav-link" href="orders.php">
                    <i class="fas fa-download me-1"></i>My Orders
                </a>
                <a class="nav-link" href="index.php">
                    <i class="fas fa-home me-1"></i>Home
                </a>
            </div>
        </div>
    </nav>

    <!-- Success Content -->
    <section class="py-5" style="margin-top: 76px;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <!-- Success Message -->
                    <div class="text-center mb-5">
                        <div class="success-icon mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h1 class="display-6 fw-bold text-success mb-3">Payment Successful!</h1>
                        <p class="lead text-muted">Thank you for your purchase. Your order has been processed successfully.</p>
                    </div>

                    <!-- Order Details Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-receipt me-2"></i>Order Confirmation
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Order Information</h6>
                                    <p class="mb-1"><strong>Order Number:</strong> <?php echo $order['order_number']; ?></p>
                                    <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?></p>
                                    <p class="mb-1"><strong>Payment Status:</strong> 
                                        <span class="badge bg-success">Completed</span>
                                    </p>
                                    <p class="mb-0"><strong>Total Amount:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold">Customer Information</h6>
                                    <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                                    <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                                </div>
                            </div>

                            <!-- Order Items -->
                            <h6 class="fw-bold mb-3">Order Items</h6>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orderItems as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <?php
                                                        $images = json_decode($item['images'] ?? '[]', true);
                                                        $mainImage = !empty($images) ? $images[0] : 'assets/images/placeholder.jpg';
                                                        ?>
                                                        <img src="<?php echo $mainImage; ?>" alt="<?php echo htmlspecialchars($item['product_title']); ?>" 
                                                             class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                        <div>
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($item['product_title']); ?></h6>
                                                            <small class="text-muted">Digital Product</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="fw-bold"><?php echo formatPrice($item['price']); ?></td>
                                                <td>
                                                    <?php if ($item['file_path']): ?>
                                                        <a href="download.php?item_id=<?php echo $item['id']; ?>" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fas fa-download me-1"></i>Download
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">Processing...</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div class="card shadow mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">What's Next?</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-download fa-2x text-primary mb-2"></i>
                                        <h6>Download Your Products</h6>
                                        <p class="small text-muted mb-0">Access your purchased digital products instantly</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-envelope fa-2x text-info mb-2"></i>
                                        <h6>Check Your Email</h6>
                                        <p class="small text-muted mb-0">Order confirmation sent to your email address</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center p-3 border rounded">
                                        <i class="fas fa-headset fa-2x text-success mb-2"></i>
                                        <h6>Need Help?</h6>
                                        <p class="small text-muted mb-0">Contact our support team for assistance</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center">
                        <div class="d-flex gap-3 justify-content-center flex-wrap">
                            <a href="orders.php" class="btn btn-primary btn-lg">
                                <i class="fas fa-list me-2"></i>View All Orders
                            </a>
                            <a href="products.php" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                            <a href="support.php" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-question-circle me-2"></i>Get Support
                            </a>
                        </div>
                    </div>

                    <!-- Social Sharing -->
                    <div class="text-center mt-4">
                        <p class="text-muted mb-3">Share your experience:</p>
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="#" class="btn btn-outline-info btn-sm">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="#" class="btn btn-outline-success btn-sm">
                                <i class="fab fa-whatsapp"></i>
                            </a>
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
                    <p class="text-muted">Thank you for choosing YBT Digital for your digital product needs.</p>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li><a href="orders.php" class="text-muted text-decoration-none">My Orders</a></li>
                        <li><a href="products.php" class="text-muted text-decoration-none">Products</a></li>
                        <li><a href="support.php" class="text-muted text-decoration-none">Support</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li><a href="support.php" class="text-muted text-decoration-none">Help Center</a></li>
                        <li><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold mb-3">Stay Connected</h6>
                    <p class="text-muted">Follow us for updates and new products.</p>
                    <div class="d-flex gap-3">
                        <a href="#" class="text-light"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="text-light"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            <div class="text-center">
                <p class="text-muted mb-0">&copy; 2024 YBT Digital. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    
    <style>
        .success-icon {
            animation: successPulse 2s ease-in-out;
        }
        
        @keyframes successPulse {
            0% { transform: scale(0.8); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .card {
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</body>
</html>
