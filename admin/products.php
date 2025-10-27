<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if admin is logged in
if (!isAdmin()) {
    header('Location: login.php');
    exit;
}

$user = getUserById($_SESSION['user_id']);
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'add_product') {
        $title = sanitizeInput($_POST['title']);
        $description = sanitizeInput($_POST['description']);
        $short_description = sanitizeInput($_POST['short_description']);
        $price = (float)$_POST['price'];
        $category_id = (int)$_POST['category_id'];
        $tags = sanitizeInput($_POST['tags']);
        $demo_url = sanitizeInput($_POST['demo_url']);
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        if (empty($title) || empty($price) || $price <= 0) {
            $error = 'Please fill in all required fields with valid data.';
        } else {
            $slug = generateSlug($title);
            $tagsArray = array_map('trim', explode(',', $tags));
            
            // Handle image upload
            $images = [];
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $uploadDir = '../uploads/products/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                
                foreach ($_FILES['images']['name'] as $key => $fileName) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $fileType = $_FILES['images']['type'][$key];
                        $fileSize = $_FILES['images']['size'][$key];
                        $tmpName = $_FILES['images']['tmp_name'][$key];
                        
                        if (in_array($fileType, $allowedTypes) && $fileSize <= $maxSize) {
                            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                            $newFileName = $slug . '_' . time() . '_' . $key . '.' . $extension;
                            $uploadPath = $uploadDir . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $uploadPath)) {
                                $images[] = 'uploads/products/' . $newFileName;
                            }
                        }
                    }
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO products (title, slug, description, short_description, price, category_id, demo_url, tags, images, featured, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
            ");
            
            if ($stmt->execute([$title, $slug, $description, $short_description, $price, $category_id, $demo_url, json_encode($tagsArray), json_encode($images), $featured])) {
                $success = 'Product added successfully!';
            } else {
                $error = 'Failed to add product.';
            }
        }
    } elseif ($action == 'update_status') {
        $productId = (int)$_POST['product_id'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $productId])) {
            $success = 'Product status updated successfully!';
        } else {
            $error = 'Failed to update product status.';
        }
    } elseif ($action == 'delete_product') {
        if (!isSuperAdmin()) {
            $error = 'Only Super Admins can delete products.';
        } else {
            $productId = (int)$_POST['product_id'];
            
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            if ($stmt->execute([$productId])) {
                $success = 'Product deleted successfully!';
            } else {
                $error = 'Failed to delete product.';
            }
        }
    }
}

// Get products with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$status_filter = $_GET['status'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($search) {
    $whereClause .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category_filter) {
    $whereClause .= " AND category_id = ?";
    $params[] = $category_filter;
}

if ($status_filter) {
    $whereClause .= " AND status = ?";
    $params[] = $status_filter;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    $whereClause 
    ORDER BY p.created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p $whereClause");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Get categories for dropdown
$categoriesStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
$categories = $categoriesStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products Management - YBT Digital Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #1e293b;
        }
        .sidebar .nav-link {
            color: #cbd5e1;
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: #334155;
            color: white;
        }
        .main-content {
            margin-left: 250px;
        }
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar position-fixed top-0 start-0 d-none d-md-block" style="width: 250px; z-index: 1000;">
        <div class="p-3">
            <h5 class="text-white mb-4">
                <i class="fas fa-digital-tachograph me-2"></i>YBT Admin
            </h5>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="products.php">
                        <i class="fas fa-box me-2"></i>Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="orders.php">
                        <i class="fas fa-shopping-cart me-2"></i>Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-users me-2"></i>Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="categories.php">
                        <i class="fas fa-tags me-2"></i>Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="coupons.php">
                        <i class="fas fa-ticket-alt me-2"></i>Coupons
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a class="nav-link" href="../index.php" target="_blank">
                        <i class="fas fa-external-link-alt me-2"></i>View Site
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <h4 class="mb-0">Products Management</h4>
                
                <div class="navbar-nav ms-auto">
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-mdb-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i><?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="container-fluid p-4">
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

            <!-- Filters and Add Product Button -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search products..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="category">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" 
                                            <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $status_filter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                <option value="draft" <?php echo $status_filter == 'draft' ? 'selected' : ''; ?>>Draft</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
                <div class="col-md-4 text-end">
                    <button class="btn btn-success" data-mdb-toggle="modal" data-mdb-target="#addProductModal">
                        <i class="fas fa-plus me-2"></i>Add Product
                    </button>
                </div>
            </div>

            <!-- Products Table -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Downloads</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php
                                                $images = json_decode($product['images'] ?? '[]', true);
                                                $mainImage = !empty($images) ? $images[0] : '../assets/images/placeholder.jpg';
                                                ?>
                                                <img src="<?php echo $mainImage; ?>" alt="<?php echo htmlspecialchars($product['title']); ?>" 
                                                     class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($product['title']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo $product['featured'] ? '<i class="fas fa-star text-warning"></i> Featured' : ''; ?>
                                                    </small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                        <td><?php echo formatPrice($product['price']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : ($product['status'] == 'draft' ? 'warning' : 'danger'); ?>">
                                                <?php echo ucfirst($product['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo number_format($product['downloads_count']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($product['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="../product.php?id=<?php echo $product['id']; ?>" 
                                                   class="btn btn-outline-primary btn-sm" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-outline-warning btn-sm" 
                                                        onclick="toggleStatus(<?php echo $product['id']; ?>, '<?php echo $product['status']; ?>')">
                                                    <i class="fas fa-toggle-<?php echo $product['status'] == 'active' ? 'on' : 'off'; ?>"></i>
                                                </button>
                                                <?php if (isSuperAdmin()): ?>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteProduct(<?php echo $product['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_filter; ?>&status=<?php echo $status_filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_product">
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label">Product Title *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="col-md-4">
                                <label for="price" class="form-label">Price *</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="category_id" class="form-label">Category</label>
                                <select class="form-select" id="category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="demo_url" class="form-label">Demo URL</label>
                                <input type="url" class="form-control" id="demo_url" name="demo_url">
                            </div>
                            
                            <div class="col-12">
                                <label for="short_description" class="form-label">Short Description</label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="2"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label for="description" class="form-label">Full Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <label for="tags" class="form-label">Tags (comma separated)</label>
                                <input type="text" class="form-control" id="tags" name="tags" placeholder="HTML, CSS, JavaScript">
                            </div>
                            
                            <div class="col-12">
                                <label for="images" class="form-label">Product Images</label>
                                <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Select multiple images (JPEG, PNG, GIF, WebP). Max 5MB per image.
                                </div>
                                <div id="image-preview" class="mt-2 d-flex flex-wrap gap-2"></div>
                            </div>
                            
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                    <label class="form-check-label" for="featured">
                                        Featured Product
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script>
        function toggleStatus(productId, currentStatus) {
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
            
            if (confirm(`Are you sure you want to ${newStatus === 'active' ? 'activate' : 'deactivate'} this product?`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="product_id" value="${productId}">
                    <input type="hidden" name="status" value="${newStatus}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="${productId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Image preview functionality
        document.getElementById('images').addEventListener('change', function(e) {
            const files = e.target.files;
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            
            if (files.length > 0) {
                Array.from(files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const div = document.createElement('div');
                            div.className = 'position-relative';
                            div.innerHTML = `
                                <img src="${e.target.result}" alt="Preview ${index + 1}" 
                                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #dee2e6;">
                                <small class="d-block text-center mt-1 text-muted">${file.name}</small>
                            `;
                            preview.appendChild(div);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
</body>
</html>
