<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

$user = getUserById($_SESSION['user_id']);
$orders = getUserOrders($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - YBT Digital</title>
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
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item active" href="orders.php"><i class="fas fa-download me-2"></i>My Orders</a></li>
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
                <i class="fas fa-download fa-2x me-3"></i>
                <div>
                    <h1 class="h3 mb-0">My Orders</h1>
                    <p class="mb-0 opacity-75">View and download your purchased products</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Orders Content -->
    <section class="py-5">
        <div class="container">
            <?php if (empty($orders)): ?>
                <!-- No Orders -->
                <div class="text-center py-5">
                    <i class="fas fa-shopping-bag fa-5x text-muted mb-4"></i>
                    <h3>No Orders Yet</h3>
                    <p class="text-muted mb-4">You haven't made any purchases yet. Browse our products to get started!</p>
                    <a href="products.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <!-- Orders List -->
                <div class="row">
                    <?php foreach ($orders as $order): ?>
                        <?php
                        $orderItems = getOrderItems($order['id']);
                        $statusClass = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ][$order['status']] ?? 'secondary';
                        
                        $paymentStatusClass = [
                            'pending' => 'warning',
                            'completed' => 'success',
                            'failed' => 'danger',
                            'refunded' => 'info'
                        ][$order['payment_status']] ?? 'secondary';
                        ?>
                        
                        <div class="col-12 mb-4">
                            <div class="card shadow-sm">
                                <div class="card-header bg-white">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-0">
                                                <i class="fas fa-receipt me-2"></i>
                                                Order #<?php echo $order['order_number']; ?>
                                            </h6>
                                            <small class="text-muted">
                                                Placed on <?php echo date('F j, Y g:i A', strtotime($order['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <span class="badge bg-<?php echo $statusClass; ?> me-2">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                            <span class="badge bg-<?php echo $paymentStatusClass; ?>">
                                                Payment <?php echo ucfirst($order['payment_status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card-body">
                                    <!-- Order Items -->
                                    <div class="row g-3">
                                        <?php foreach ($orderItems as $item): ?>
                                            <?php
                                            $images = json_decode($item['images'] ?? '[]', true);
                                            $mainImage = !empty($images) ? $images[0] : 'assets/images/placeholder.jpg';
                                            ?>
                                            
                                            <div class="col-md-6 col-lg-4">
                                                <div class="border rounded p-3 h-100">
                                                    <div class="d-flex align-items-start">
                                                        <img src="<?php echo $mainImage; ?>" 
                                                             alt="<?php echo htmlspecialchars($item['product_title']); ?>"
                                                             class="me-3 rounded" 
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                        
                                                        <div class="flex-grow-1">
                                                            <h6 class="mb-1"><?php echo htmlspecialchars($item['product_title']); ?></h6>
                                                            <p class="text-muted mb-2"><?php echo formatPrice($item['price']); ?></p>
                                                            
                                                            <?php if ($order['payment_status'] == 'completed'): ?>
                                                                <div class="d-flex gap-2">
                                                                    <?php if ($item['file_path']): ?>
                                                                        <a href="download.php?item_id=<?php echo $item['id']; ?>" 
                                                                           class="btn btn-primary btn-sm">
                                                                            <i class="fas fa-download me-1"></i>Download
                                                                        </a>
                                                                    <?php endif; ?>
                                                                    
                                                                    <a href="product.php?id=<?php echo $item['product_id']; ?>" 
                                                                       class="btn btn-outline-secondary btn-sm">
                                                                        <i class="fas fa-eye me-1"></i>View
                                                                    </a>
                                                                </div>
                                                                
                                                                <?php if ($item['download_count'] > 0): ?>
                                                                    <small class="text-muted d-block mt-1">
                                                                        Downloaded <?php echo $item['download_count']; ?> time(s)
                                                                    </small>
                                                                <?php endif; ?>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning">
                                                                    <i class="fas fa-clock me-1"></i>Pending Payment
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div class="card-footer bg-light">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <strong>Total: <?php echo formatPrice($order['total_amount']); ?></strong>
                                            <?php if ($order['payment_method']): ?>
                                                <small class="text-muted d-block">
                                                    Payment via <?php echo ucfirst($order['payment_method']); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-6 text-md-end">
                                            <div class="btn-group" role="group">
                                                <a href="order-details.php?id=<?php echo $order['id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-1"></i>View Details
                                                </a>
                                                
                                                <?php if ($order['payment_status'] == 'completed'): ?>
                                                    <button class="btn btn-outline-secondary btn-sm" 
                                                            onclick="downloadInvoice(<?php echo $order['id']; ?>)">
                                                        <i class="fas fa-file-pdf me-1"></i>Invoice
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <?php if ($order['status'] == 'pending' && $order['payment_status'] == 'pending'): ?>
                                                    <a href="payment.php?order_id=<?php echo $order['id']; ?>" 
                                                       class="btn btn-success btn-sm">
                                                        <i class="fas fa-credit-card me-1"></i>Pay Now
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Download All Button -->
                <div class="text-center mt-4">
                    <button class="btn btn-primary btn-lg" onclick="downloadAllProducts()">
                        <i class="fas fa-download me-2"></i>Download All Purchased Products
                    </button>
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
                    <h6 class="fw-bold mb-3">Account</h6>
                    <ul class="list-unstyled">
                        <li><a href="orders.php" class="text-muted text-decoration-none">My Orders</a></li>
                        <li><a href="profile.php" class="text-muted text-decoration-none">Profile</a></li>
                        <li><a href="support.php" class="text-muted text-decoration-none">Support</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-2 mb-4">
                    <h6 class="fw-bold mb-3">Help</h6>
                    <ul class="list-unstyled">
                        <li><a href="faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                        <li><a href="contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-4 mb-4">
                    <h6 class="fw-bold mb-3">Stay Updated</h6>
                    <p class="text-muted">Get notified about new products and exclusive offers.</p>
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
        <a href="orders.php" class="bottom-nav-item active">
            <i class="fas fa-user"></i>
            <span>Profile</span>
        </a>
    </nav>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        function downloadInvoice(orderId) {
            window.open(`invoice.php?order_id=${orderId}`, '_blank');
        }
        
        function downloadAllProducts() {
            if (confirm('This will download all your purchased products. Continue?')) {
                window.location.href = 'download-all.php';
            }
        }
        
        // Update cart count on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>
