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
    
    if ($action == 'update_status') {
        $userId = (int)$_POST['user_id'];
        $status = $_POST['status'];
        
        // Prevent admin from blocking themselves
        if ($userId == $_SESSION['user_id']) {
            $error = 'You cannot change your own status.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
            if ($stmt->execute([$status, $userId])) {
                $success = 'User status updated successfully!';
            } else {
                $error = 'Failed to update user status.';
            }
        }
    } elseif ($action == 'update_role') {
        $userId = (int)$_POST['user_id'];
        $role = $_POST['role'];
        
        // Prevent admin from demoting themselves
        if ($userId == $_SESSION['user_id']) {
            $error = 'You cannot change your own role.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            if ($stmt->execute([$role, $userId])) {
                $success = 'User role updated successfully!';
            } else {
                $error = 'Failed to update user role.';
            }
        }
    } elseif ($action == 'delete_user') {
        $userId = (int)$_POST['user_id'];
        
        // Prevent admin from deleting themselves
        if ($userId == $_SESSION['user_id']) {
            $error = 'You cannot delete your own account.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            if ($stmt->execute([$userId])) {
                $success = 'User deleted successfully!';
            } else {
                $error = 'Failed to delete user.';
            }
        }
    }
}

// Get users with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';

$whereClause = "WHERE 1=1";
$params = [];

if ($search) {
    $whereClause .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($role_filter) {
    $whereClause .= " AND role = ?";
    $params[] = $role_filter;
}

if ($status_filter) {
    $whereClause .= " AND status = ?";
    $params[] = $status_filter;
}

$stmt = $pdo->prepare("
    SELECT u.*, 
           COUNT(DISTINCT o.id) as total_orders,
           SUM(CASE WHEN o.payment_status = 'completed' THEN o.total_amount ELSE 0 END) as total_spent
    FROM users u 
    LEFT JOIN orders o ON u.id = o.user_id 
    $whereClause 
    GROUP BY u.id
    ORDER BY u.created_at DESC 
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$users = $stmt->fetchAll();

// Get total count for pagination
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM users u $whereClause");
$countStmt->execute($params);
$totalUsers = $countStmt->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

// Get statistics
$statsStmt = $pdo->query("
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users,
        SUM(CASE WHEN role IN ('admin', 'super_admin') THEN 1 ELSE 0 END) as admin_users,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_registrations
    FROM users
");
$stats = $statsStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - YBT Digital Admin</title>
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
                    <a class="nav-link active" href="users.php">
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
                <h4 class="mb-0">Users Management</h4>
                
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

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Users
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['total_users']); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Active Users
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['active_users']); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Admin Users
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['admin_users']); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-shield fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Today's Registrations
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        <?php echo number_format($stats['today_registrations']); ?>
                                    </div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <input type="text" class="form-control" name="search" placeholder="Search users..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="role">
                                <option value="">All Roles</option>
                                <option value="user" <?php echo $role_filter == 'user' ? 'selected' : ''; ?>>User</option>
                                <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="super_admin" <?php echo $role_filter == 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo $status_filter == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="blocked" <?php echo $status_filter == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $userData): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                     style="width: 40px; height: 40px;">
                                                    <?php echo strtoupper(substr($userData['name'], 0, 1)); ?>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0"><?php echo htmlspecialchars($userData['name']); ?></h6>
                                                    <small class="text-muted"><?php echo htmlspecialchars($userData['email']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" 
                                                    onchange="updateUserRole(<?php echo $userData['id']; ?>, this.value)"
                                                    <?php echo $userData['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                <option value="user" <?php echo $userData['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                                <option value="admin" <?php echo $userData['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                                <option value="super_admin" <?php echo $userData['role'] == 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select form-select-sm" 
                                                    onchange="updateUserStatus(<?php echo $userData['id']; ?>, this.value)"
                                                    <?php echo $userData['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                                <option value="active" <?php echo $userData['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="blocked" <?php echo $userData['status'] == 'blocked' ? 'selected' : ''; ?>>Blocked</option>
                                                <option value="pending" <?php echo $userData['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            </select>
                                        </td>
                                        <td><?php echo number_format($userData['total_orders']); ?></td>
                                        <td><?php echo formatPrice($userData['total_spent'] ?: 0); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($userData['created_at'])); ?></td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-outline-primary btn-sm" 
                                                        onclick="viewUserDetails(<?php echo $userData['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($userData['id'] != $_SESSION['user_id']): ?>
                                                    <button class="btn btn-outline-danger btn-sm" 
                                                            onclick="deleteUser(<?php echo $userData['id']; ?>)">
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
                                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo $role_filter; ?>&status=<?php echo $status_filter; ?>">
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

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">User Details</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="userDetailsContent">
                    <!-- Content will be loaded via AJAX -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script>
        function updateUserStatus(userId, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function updateUserRole(userId, role) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_role">
                <input type="hidden" name="user_id" value="${userId}">
                <input type="hidden" name="role" value="${role}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" value="${userId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function viewUserDetails(userId) {
            // Load user details via AJAX
            fetch(`user-details-ajax.php?id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('userDetailsContent').innerHTML = data;
                    new mdb.Modal(document.getElementById('userDetailsModal')).show();
                })
                .catch(error => {
                    alert('Failed to load user details');
                });
        }
    </script>
    
    <style>
        .border-left-primary {
            border-left: 4px solid #2563eb !important;
        }
        .border-left-success {
            border-left: 4px solid #10b981 !important;
        }
        .border-left-info {
            border-left: 4px solid #06b6d4 !important;
        }
        .border-left-warning {
            border-left: 4px solid #f59e0b !important;
        }
    </style>
</body>
</html>
