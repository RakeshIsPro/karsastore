<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

requireSuperAdmin(); // Only super admins can access this page

$success = '';
$error = '';

// Handle role updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'update_role') {
        $userId = (int)$_POST['user_id'];
        $newRole = sanitizeInput($_POST['role']);
        
        if (in_array($newRole, ['user', 'admin', 'super_admin'])) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
                if ($stmt->execute([$newRole, $userId])) {
                    $success = 'User role updated successfully!';
                } else {
                    $error = 'Failed to update user role.';
                }
            } catch (Exception $e) {
                $error = 'Error updating role: ' . $e->getMessage();
            }
        } else {
            $error = 'Invalid role selected.';
        }
    }
}

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY role DESC, name ASC");
$users = $stmt->fetchAll();

$currentUser = getUserById($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Role Management - Shishir Basnet Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #1e293b; }
        .sidebar .nav-link { color: #cbd5e1; padding: 0.75rem 1rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: white; }
        .main-content { margin-left: 250px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar position-fixed top-0 start-0 d-none d-md-block" style="width: 250px; z-index: 1000;">
        <div class="p-3">
            <h5 class="text-white mb-4">
                <i class="fas fa-digital-tachograph me-2"></i>Shishir Admin
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
                    <a class="nav-link active" href="roles.php">
                        <i class="fas fa-user-shield me-2"></i>Role Management
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
                    <a class="nav-link text-danger" href="../auth/logout.php">
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
                <button class="btn btn-link d-md-none" type="button" data-mdb-toggle="offcanvas" data-mdb-target="#sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                
                <h4 class="mb-0">Role Management</h4>
                
                <div class="ms-auto">
                    <span class="text-muted">Welcome, <?php echo htmlspecialchars($currentUser['name']); ?></span>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="container-fluid p-4">
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-mdb-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Role Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-info-circle text-primary me-2"></i>Role Permissions
                            </h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="border rounded p-3 mb-3">
                                        <h6 class="text-success">
                                            <i class="fas fa-crown me-2"></i>Super Admin
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li><i class="fas fa-check text-success me-1"></i>Full system access</li>
                                            <li><i class="fas fa-check text-success me-1"></i>Manage all users & roles</li>
                                            <li><i class="fas fa-check text-success me-1"></i>Delete products & categories</li>
                                            <li><i class="fas fa-check text-success me-1"></i>System settings</li>
                                            <li><i class="fas fa-check text-success me-1"></i>All admin permissions</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 mb-3">
                                        <h6 class="text-warning">
                                            <i class="fas fa-user-tie me-2"></i>Limited Admin
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li><i class="fas fa-check text-success me-1"></i>View & add products</li>
                                            <li><i class="fas fa-check text-success me-1"></i>Edit existing products</li>
                                            <li><i class="fas fa-check text-success me-1"></i>View & manage orders</li>
                                            <li><i class="fas fa-check text-success me-1"></i>View users (read-only)</li>
                                            <li><i class="fas fa-times text-danger me-1"></i>Cannot delete items</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3 mb-3">
                                        <h6 class="text-primary">
                                            <i class="fas fa-user me-2"></i>Regular User
                                        </h6>
                                        <ul class="list-unstyled mb-0 small">
                                            <li><i class="fas fa-check text-success me-1"></i>Browse products</li>
                                            <li><i class="fas fa-check text-success me-1"></i>Make purchases</li>
                                            <li><i class="fas fa-check text-success me-1"></i>View own orders</li>
                                            <li><i class="fas fa-check text-success me-1"></i>Contact support</li>
                                            <li><i class="fas fa-times text-danger me-1"></i>No admin access</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-users me-2"></i>User Role Management
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Current Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-primary text-white rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <div class="fw-bold"><?php echo htmlspecialchars($user['name']); ?></div>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <small class="text-muted">(You)</small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php
                                            $roleColors = [
                                                'super_admin' => 'success',
                                                'admin' => 'warning',
                                                'user' => 'primary'
                                            ];
                                            $roleIcons = [
                                                'super_admin' => 'crown',
                                                'admin' => 'user-tie',
                                                'user' => 'user'
                                            ];
                                            $roleNames = [
                                                'super_admin' => 'Super Admin',
                                                'admin' => 'Limited Admin',
                                                'user' => 'User'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $roleColors[$user['role']]; ?>">
                                                <i class="fas fa-<?php echo $roleIcons[$user['role']]; ?> me-1"></i>
                                                <?php echo $roleNames[$user['role']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $user['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($user['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <button type="button" class="btn btn-sm btn-outline-primary" 
                                                        data-mdb-toggle="modal" 
                                                        data-mdb-target="#roleModal<?php echo $user['id']; ?>">
                                                    <i class="fas fa-edit me-1"></i>Change Role
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">Cannot modify own role</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- Role Change Modal -->
                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                        <div class="modal fade" id="roleModal<?php echo $user['id']; ?>" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Change Role for <?php echo htmlspecialchars($user['name']); ?></h5>
                                                        <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                                                    </div>
                                                    <form method="POST">
                                                        <div class="modal-body">
                                                            <input type="hidden" name="action" value="update_role">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            
                                                            <div class="mb-3">
                                                                <label class="form-label">Select New Role:</label>
                                                                <select name="role" class="form-select" required>
                                                                    <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>
                                                                        👤 Regular User
                                                                    </option>
                                                                    <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>
                                                                        👔 Limited Admin
                                                                    </option>
                                                                    <option value="super_admin" <?php echo $user['role'] == 'super_admin' ? 'selected' : ''; ?>>
                                                                        👑 Super Admin
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                                <strong>Warning:</strong> Changing user roles will immediately affect their access permissions.
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                                                            <button type="submit" class="btn btn-primary">Update Role</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
</body>
</html>
