<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get product ID
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$productId) {
    header('Location: products.php');
    exit;
}

// Get product details
$product = getProductById($productId);

if (!$product) {
    header('Location: products.php');
    exit;
}

// Check if user is logged in
$user = null;
if (isset($_SESSION['user_id'])) {
    $user = getUserById($_SESSION['user_id']);
}

// Get related products
$relatedProducts = getRelatedProducts($productId, $product['category_id'], 4);

// Process product data
$images = json_decode($product['images'] ?? '[]', true);
$tags = json_decode($product['tags'] ?? '[]', true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - YBT Digital</title>
    <meta name="description" content="<?php echo htmlspecialchars($product['short_description'] ?? ''); ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .product-gallery img {
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .product-gallery img:hover {
            transform: scale(1.05);
        }
        .thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .main-image {
            max-height: 400px;
            object-fit: cover;
            border-radius: 12px;
        }
    </style>
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

    <!-- Breadcrumb -->
    <section class="py-3 bg-light" style="margin-top: 76px;">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                    <?php if ($product['category_name']): ?>
                        <li class="breadcrumb-item">
                            <a href="products.php?category=<?php echo $product['category_id']; ?>">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['title']); ?></li>
                </ol>
            </nav>
        </div>
    </section>

    <!-- Product Details -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-lg-6 mb-4">
                    <div class="product-gallery">
                        <div class="main-image-container mb-3">
                            <img src="<?php echo !empty($images) ? $images[0] : 'assets/images/placeholder.jpg'; ?>" 
                                 class="img-fluid main-image w-100" alt="<?php echo htmlspecialchars($product['title']); ?>" id="mainImage">
                        </div>
                        
                        <?php if (count($images) > 1): ?>
                            <div class="d-flex gap-2 flex-wrap">
                                <?php foreach ($images as $index => $image): ?>
                                    <img src="<?php echo $image; ?>" class="thumbnail" alt="Product image <?php echo $index + 1; ?>" 
                                         onclick="changeMainImage('<?php echo $image; ?>')">
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="col-lg-6">
                    <div class="product-info">
                        <?php if ($product['featured']): ?>
                            <span class="badge bg-warning mb-2">
                                <i class="fas fa-star me-1"></i>Featured
                            </span>
                        <?php endif; ?>
                        
                        <h1 class="display-6 fw-bold mb-3"><?php echo htmlspecialchars($product['title']); ?></h1>
                        
                        <div class="d-flex align-items-center mb-3">
                            <span class="display-6 fw-bold text-primary me-3"><?php echo formatPrice($product['price']); ?></span>
                            <small class="text-muted">
                                <i class="fas fa-download me-1"></i><?php echo $product['downloads_count']; ?> downloads
                            </small>
                        </div>
                        
                        <?php if ($product['short_description']): ?>
                            <p class="lead mb-4"><?php echo htmlspecialchars($product['short_description']); ?></p>
                        <?php endif; ?>
                        
                        <!-- Add to Cart -->
                        <div class="d-grid gap-2 d-md-flex mb-4">
                            <button class="btn btn-primary btn-lg flex-md-fill add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                <i class="fas fa-cart-plus me-2"></i>Add to Cart
                            </button>
                            <?php if ($product['demo_url']): ?>
                                <a href="<?php echo htmlspecialchars($product['demo_url']); ?>" target="_blank" 
                                   class="btn btn-outline-primary btn-lg">
                                    <i class="fas fa-external-link-alt me-2"></i>Live Demo
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Product Meta -->
                        <div class="product-meta">
                            <div class="row g-3">
                                <div class="col-6">
                                    <strong>Category:</strong><br>
                                    <a href="products.php?category=<?php echo $product['category_id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <strong>File Size:</strong><br>
                                    <?php echo $product['file_size'] ? formatBytes($product['file_size']) : 'N/A'; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Tags -->
                        <?php if (!empty($tags)): ?>
                            <div class="mt-4">
                                <strong class="d-block mb-2">Tags:</strong>
                                <div class="d-flex flex-wrap gap-2">
                                    <?php foreach ($tags as $tag): ?>
                                        <span class="badge bg-light text-dark"><?php echo htmlspecialchars($tag); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Product Description -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h3 class="fw-bold mb-4">Product Description</h3>
                    <div class="content">
                        <?php echo nl2br(htmlspecialchars($product['description'] ?? 'No description available.')); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Products -->
    <?php if (!empty($relatedProducts)): ?>
        <section class="py-5">
            <div class="container">
                <h3 class="fw-bold mb-4">Related Products</h3>
                <div class="row g-4">
                    <?php foreach ($relatedProducts as $relatedProduct): ?>
                        <?php
                        $relatedImages = json_decode($relatedProduct['images'] ?? '[]', true);
                        $relatedMainImage = !empty($relatedImages) ? $relatedImages[0] : 'assets/images/placeholder.jpg';
                        ?>
                        <div class="col-lg-3 col-md-6">
                            <div class="card product-card h-100">
                                <img src="<?php echo $relatedMainImage; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($relatedProduct['title']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h6 class="card-title"><?php echo htmlspecialchars($relatedProduct['title']); ?></h6>
                                    <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($relatedProduct['short_description'] ?? ''); ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price fw-bold text-primary"><?php echo formatPrice($relatedProduct['price']); ?></span>
                                        <a href="product.php?id=<?php echo $relatedProduct['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

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
        <a href="cart.php" class="bottom-nav-item">
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
        function changeMainImage(src) {
            document.getElementById('mainImage').src = src;
        }
        
        function formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    </script>
</body>
</html>

<?php
function formatBytes($size, $precision = 2) {
    if ($size > 0) {
        $size = (int) $size;
        $base = log($size) / log(1024);
        $suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
    return $size;
}
?>
