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
    
    if ($action == 'add_category') {
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $sort_order = (int)$_POST['sort_order'];
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            $slug = generateSlug($name);
            
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, slug, description, parent_id, sort_order, status) 
                VALUES (?, ?, ?, ?, ?, 'active')
            ");
            
            if ($stmt->execute([$name, $slug, $description, $parent_id, $sort_order])) {
                $success = 'Category added successfully!';
            } else {
                $error = 'Failed to add category.';
            }
        }
    } elseif ($action == 'update_category') {
        $categoryId = (int)$_POST['category_id'];
        $name = sanitizeInput($_POST['name']);
        $description = sanitizeInput($_POST['description']);
        $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;
        $sort_order = (int)$_POST['sort_order'];
        
        if (empty($name)) {
            $error = 'Category name is required.';
        } else {
            $slug = generateSlug($name);
            
            $stmt = $pdo->prepare("
                UPDATE categories 
                SET name = ?, slug = ?, description = ?, parent_id = ?, sort_order = ? 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$name, $slug, $description, $parent_id, $sort_order, $categoryId])) {
                $success = 'Category updated successfully!';
            } else {
                $error = 'Failed to update category.';
            }
        }
    } elseif ($action == 'update_status') {
        $categoryId = (int)$_POST['category_id'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE categories SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $categoryId])) {
            $success = 'Category status updated successfully!';
        } else {
            $error = 'Failed to update category status.';
        }
    } elseif ($action == 'delete_category') {
        $categoryId = (int)$_POST['category_id'];
        
        // Check if category has products
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $checkStmt->execute([$categoryId]);
        $productCount = $checkStmt->fetchColumn();
        
        if ($productCount > 0) {
            $error = 'Cannot delete category with existing products. Please move or delete the products first.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt->execute([$categoryId])) {
                $success = 'Category deleted successfully!';
            } else {
                $error = 'Failed to delete category.';
            }
        }
    }
}

// Check if parent_id column exists and update table if needed
try {
    $columnCheck = $pdo->query("SHOW COLUMNS FROM categories LIKE 'parent_id'");
    if ($columnCheck->rowCount() == 0) {
        // Add missing columns
        $pdo->exec("ALTER TABLE categories ADD COLUMN parent_id INT DEFAULT NULL AFTER image");
        $pdo->exec("ALTER TABLE categories ADD COLUMN sort_order INT DEFAULT 0 AFTER parent_id");
        $pdo->exec("ALTER TABLE categories ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        $pdo->exec("ALTER TABLE categories ADD CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL");
    }
} catch (Exception $e) {
    // If alter fails, continue with basic query
}

// Get categories with product counts
try {
    $stmt = $pdo->query("
        SELECT c.*, 
               pc.name as parent_name,
               COUNT(p.id) as product_count
        FROM categories c 
        LEFT JOIN categories pc ON c.parent_id = pc.id
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
        GROUP BY c.id
        ORDER BY c.sort_order ASC, c.name ASC
    ");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    // Fallback query without parent_id if columns don't exist
    $stmt = $pdo->query("
        SELECT c.*, 
               '' as parent_name,
               COUNT(p.id) as product_count
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
        GROUP BY c.id
        ORDER BY c.name ASC
    ");
    $categories = $stmt->fetchAll();
    
    // Set default values for missing columns
    foreach ($categories as &$category) {
        if (!isset($category['parent_id'])) $category['parent_id'] = null;
        if (!isset($category['sort_order'])) $category['sort_order'] = 0;
    }
}

// Get parent categories for dropdown
try {
    $parentStmt = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL AND status = 'active' ORDER BY name");
    $parentCategories = $parentStmt->fetchAll();
} catch (Exception $e) {
    // Fallback if parent_id column doesn't exist
    $parentStmt = $pdo->query("SELECT * FROM categories WHERE status = 'active' ORDER BY name");
    $parentCategories = $parentStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management - YBT Digital Admin</title>
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
        .subcategory {
            padding-left: 2rem;
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
                    <a class="nav-link" href="products.php">
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
                    <a class="nav-link active" href="categories.php">
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
                <h4 class="mb-0">Categories Management</h4>
                
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

            <!-- Add Category Button -->
            <div class="row mb-4">
                <div class="col-12 text-end">
                    <button class="btn btn-success" data-mdb-toggle="modal" data-mdb-target="#addCategoryModal">
                        <i class="fas fa-plus me-2"></i>Add Category
                    </button>
                </div>
            </div>

            <!-- Categories Table -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Parent</th>
                                    <th>Products</th>
                                    <th>Sort Order</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($categories as $category): ?>
                                    <tr class="<?php echo $category['parent_id'] ? 'subcategory' : ''; ?>">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <?php if ($category['parent_id']): ?>
                                                    <i class="fas fa-level-up-alt fa-rotate-90 me-2 text-muted"></i>
                                                <?php endif; ?>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($category['name']); ?></h6>
                                                    <?php if ($category['description']): ?>
                                                        <small class="text-muted"><?php echo htmlspecialchars($category['description']); ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '-'; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info"><?php echo number_format($category['product_count']); ?></span>
                                        </td>
                                        <td><?php echo $category['sort_order']; ?></td>
                                        <td>
                                            <select class="form-select form-select-sm" 
                                                    onchange="updateCategoryStatus(<?php echo $category['id']; ?>, this.value)">
                                                <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($category['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-outline-danger btn-sm" 
                                                        onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div class="modal fade" id="addCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Category</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_category">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Category</label>
                            <select class="form-select" id="parent_id" name="parent_id">
                                <option value="">None (Main Category)</option>
                                <?php foreach ($parentCategories as $parent): ?>
                                    <option value="<?php echo $parent['id']; ?>"><?php echo htmlspecialchars($parent['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="sort_order" name="sort_order" value="0">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div class="modal fade" id="editCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Category</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_category">
                        <input type="hidden" name="category_id" id="edit_category_id">
                        
                        <div class="mb-3">
                            <label for="edit_name" class="form-label">Category Name *</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_parent_id" class="form-label">Parent Category</label>
                            <select class="form-select" id="edit_parent_id" name="parent_id">
                                <option value="">None (Main Category)</option>
                                <?php foreach ($parentCategories as $parent): ?>
                                    <option value="<?php echo $parent['id']; ?>"><?php echo htmlspecialchars($parent['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control" id="edit_sort_order" name="sort_order">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script>
        function updateCategoryStatus(categoryId, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="category_id" value="${categoryId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function editCategory(category) {
            document.getElementById('edit_category_id').value = category.id;
            document.getElementById('edit_name').value = category.name;
            document.getElementById('edit_parent_id').value = category.parent_id || '';
            document.getElementById('edit_description').value = category.description || '';
            document.getElementById('edit_sort_order').value = category.sort_order;
            
            new mdb.Modal(document.getElementById('editCategoryModal')).show();
        }
        
        function deleteCategory(categoryId) {
            if (confirm('Are you sure you want to delete this category? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_category">
                    <input type="hidden" name="category_id" value="${categoryId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
