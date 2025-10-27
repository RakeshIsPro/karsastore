<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

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
    
    if ($action == 'add_coupon') {
        $code = strtoupper(sanitizeInput($_POST['code']));
        $type = $_POST['type'];
        $value = (float)$_POST['value'];
        $minimum_amount = (float)$_POST['minimum_amount'];
        $usage_limit = !empty($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
        $expires_at = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        
        if (empty($code) || empty($value)) {
            $error = 'Code and value are required.';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO coupons (code, type, value, minimum_amount, usage_limit, expires_at, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'active')
            ");
            
            if ($stmt->execute([$code, $type, $value, $minimum_amount, $usage_limit, $expires_at])) {
                $success = 'Coupon added successfully!';
            } else {
                $error = 'Failed to add coupon.';
            }
        }
    } elseif ($action == 'update_status') {
        $couponId = (int)$_POST['coupon_id'];
        $status = $_POST['status'];
        
        $stmt = $pdo->prepare("UPDATE coupons SET status = ? WHERE id = ?");
        if ($stmt->execute([$status, $couponId])) {
            $success = 'Coupon status updated!';
        } else {
            $error = 'Failed to update status.';
        }
    } elseif ($action == 'delete_coupon') {
        $couponId = (int)$_POST['coupon_id'];
        
        $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
        if ($stmt->execute([$couponId])) {
            $success = 'Coupon deleted successfully!';
        } else {
            $error = 'Failed to delete coupon.';
        }
    }
}

// Get coupons
$stmt = $pdo->query("SELECT * FROM coupons ORDER BY created_at DESC");
$coupons = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons Management - YBT Digital Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: #1e293b; }
        .sidebar .nav-link { color: #cbd5e1; padding: 0.75rem 1rem; border-radius: 0.375rem; margin-bottom: 0.25rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { background: #334155; color: white; }
        .main-content { margin-left: 250px; }
        @media (max-width: 768px) { .main-content { margin-left: 0; } }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar position-fixed top-0 start-0 d-none d-md-block" style="width: 250px; z-index: 1000;">
        <div class="p-3">
            <h5 class="text-white mb-4"><i class="fas fa-digital-tachograph me-2"></i>YBT Admin</h5>
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php"><i class="fas fa-box me-2"></i>Products</a></li>
                <li class="nav-item"><a class="nav-link" href="orders.php"><i class="fas fa-shopping-cart me-2"></i>Orders</a></li>
                <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users me-2"></i>Users</a></li>
                <li class="nav-item"><a class="nav-link" href="categories.php"><i class="fas fa-tags me-2"></i>Categories</a></li>
                <li class="nav-item"><a class="nav-link active" href="coupons.php"><i class="fas fa-ticket-alt me-2"></i>Coupons</a></li>
                <li class="nav-item"><a class="nav-link" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item mt-3"><a class="nav-link" href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>
                <li class="nav-item"><a class="nav-link" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <h4 class="mb-0">Coupons Management</h4>
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

        <div class="container-fluid p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle me-2"></i><?php echo $success; ?></div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-12 text-end">
                    <button class="btn btn-success" data-mdb-toggle="modal" data-mdb-target="#addCouponModal">
                        <i class="fas fa-plus me-2"></i>Add Coupon
                    </button>
                </div>
            </div>

            <div class="card shadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Min Amount</th>
                                    <th>Usage</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coupons as $coupon): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($coupon['code']); ?></strong></td>
                                        <td><?php echo ucfirst($coupon['type']); ?></td>
                                        <td>
                                            <?php if ($coupon['type'] == 'percentage'): ?>
                                                <?php echo $coupon['value']; ?>%
                                            <?php else: ?>
                                                <?php echo formatPrice($coupon['value']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatPrice($coupon['minimum_amount']); ?></td>
                                        <td><?php echo $coupon['used_count']; ?>/<?php echo $coupon['usage_limit'] ?: '∞'; ?></td>
                                        <td><?php echo $coupon['expires_at'] ? date('M j, Y', strtotime($coupon['expires_at'])) : 'Never'; ?></td>
                                        <td>
                                            <select class="form-select form-select-sm" onchange="updateStatus(<?php echo $coupon['id']; ?>, this.value)">
                                                <option value="active" <?php echo $coupon['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="inactive" <?php echo $coupon['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                <option value="expired" <?php echo $coupon['status'] == 'expired' ? 'selected' : ''; ?>>Expired</option>
                                            </select>
                                        </td>
                                        <td>
                                            <button class="btn btn-outline-danger btn-sm" onclick="deleteCoupon(<?php echo $coupon['id']; ?>)">
                                                <i class="fas fa-trash"></i>
                                            </button>
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

    <!-- Add Coupon Modal -->
    <div class="modal fade" id="addCouponModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Coupon</h5>
                    <button type="button" class="btn-close" data-mdb-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_coupon">
                        <div class="mb-3">
                            <label class="form-label">Coupon Code *</label>
                            <input type="text" class="form-control" name="code" required>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Type *</label>
                                <select class="form-select" name="type" required>
                                    <option value="percentage">Percentage</option>
                                    <option value="flat">Flat Amount</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Value *</label>
                                <input type="number" class="form-control" name="value" step="0.01" required>
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Minimum Amount</label>
                                <input type="number" class="form-control" name="minimum_amount" step="0.01" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Usage Limit</label>
                                <input type="number" class="form-control" name="usage_limit">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Expires At</label>
                            <input type="datetime-local" class="form-control" name="expires_at">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-mdb-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Coupon</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
    <script>
        function updateStatus(couponId, status) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="coupon_id" value="${couponId}">
                <input type="hidden" name="status" value="${status}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        
        function deleteCoupon(couponId) {
            if (confirm('Are you sure you want to delete this coupon?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_coupon">
                    <input type="hidden" name="coupon_id" value="${couponId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
