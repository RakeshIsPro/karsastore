<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

// Check if super admin is logged in
requireSuperAdmin();

$user = getUserById($_SESSION['user_id']);
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $settings = $_POST['settings'] ?? [];
    
    foreach ($settings as $key => $value) {
        $stmt = $pdo->prepare("
            INSERT INTO settings (setting_key, setting_value) 
            VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->execute([$key, $value]);
    }
    
    $success = 'Settings updated successfully!';
}

// Get all settings
$stmt = $pdo->query("SELECT setting_key, setting_value FROM settings ORDER BY setting_key");
$settingsData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - YBT Digital Admin</title>
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
                <li class="nav-item"><a class="nav-link" href="coupons.php"><i class="fas fa-ticket-alt me-2"></i>Coupons</a></li>
                <li class="nav-item"><a class="nav-link active" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item mt-3"><a class="nav-link" href="../index.php" target="_blank"><i class="fas fa-external-link-alt me-2"></i>View Site</a></li>
                <li class="nav-item"><a class="nav-link" href="../auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <h4 class="mb-0">Settings</h4>
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

            <form method="POST">
                <div class="row">
                    <!-- General Settings -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header"><h5><i class="fas fa-cog me-2"></i>General Settings</h5></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Site Name</label>
                                    <input type="text" class="form-control" name="settings[site_name]" 
                                           value="<?php echo htmlspecialchars($settingsData['site_name'] ?? 'YBT Digital'); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Site Description</label>
                                    <textarea class="form-control" name="settings[site_description]" rows="3"><?php echo htmlspecialchars($settingsData['site_description'] ?? ''); ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contact Email</label>
                                    <input type="email" class="form-control" name="settings[site_email]" 
                                           value="<?php echo htmlspecialchars($settingsData['site_email'] ?? ''); ?>">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Currency</label>
                                        <select class="form-select" name="settings[currency]">
                                            <option value="USD" <?php echo ($settingsData['currency'] ?? 'USD') == 'USD' ? 'selected' : ''; ?>>USD</option>
                                            <option value="EUR" <?php echo ($settingsData['currency'] ?? '') == 'EUR' ? 'selected' : ''; ?>>EUR</option>
                                            <option value="GBP" <?php echo ($settingsData['currency'] ?? '') == 'GBP' ? 'selected' : ''; ?>>GBP</option>
                                            <option value="INR" <?php echo ($settingsData['currency'] ?? '') == 'INR' ? 'selected' : ''; ?>>INR</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Tax Rate (%)</label>
                                        <input type="number" class="form-control" name="settings[tax_rate]" step="0.01" 
                                               value="<?php echo $settingsData['tax_rate'] ?? '0'; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Settings -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header"><h5><i class="fas fa-credit-card me-2"></i>Payment Settings</h5></div>
                            <div class="card-body">
                                <h6>Stripe</h6>
                                <div class="mb-3">
                                    <label class="form-label">Publishable Key</label>
                                    <input type="text" class="form-control" name="settings[stripe_key]" 
                                           value="<?php echo htmlspecialchars($settingsData['stripe_key'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Secret Key</label>
                                    <input type="password" class="form-control" name="settings[stripe_secret]" 
                                           value="<?php echo htmlspecialchars($settingsData['stripe_secret'] ?? ''); ?>">
                                </div>
                                
                                <h6>PayPal</h6>
                                <div class="mb-3">
                                    <label class="form-label">Client ID</label>
                                    <input type="text" class="form-control" name="settings[paypal_client_id]" 
                                           value="<?php echo htmlspecialchars($settingsData['paypal_client_id'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Secret Key</label>
                                    <input type="password" class="form-control" name="settings[paypal_secret]" 
                                           value="<?php echo htmlspecialchars($settingsData['paypal_secret'] ?? ''); ?>">
                                </div>
                                
                                <h6>Razorpay</h6>
                                <div class="mb-3">
                                    <label class="form-label">Key ID</label>
                                    <input type="text" class="form-control" name="settings[razorpay_key]" 
                                           value="<?php echo htmlspecialchars($settingsData['razorpay_key'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Secret Key</label>
                                    <input type="password" class="form-control" name="settings[razorpay_secret]" 
                                           value="<?php echo htmlspecialchars($settingsData['razorpay_secret'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Settings -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header"><h5><i class="fas fa-envelope me-2"></i>Email Settings</h5></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">SMTP Host</label>
                                    <input type="text" class="form-control" name="settings[smtp_host]" 
                                           value="<?php echo htmlspecialchars($settingsData['smtp_host'] ?? ''); ?>">
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Port</label>
                                        <input type="number" class="form-control" name="settings[smtp_port]" 
                                               value="<?php echo $settingsData['smtp_port'] ?? '587'; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">SMTP Username</label>
                                        <input type="text" class="form-control" name="settings[smtp_username]" 
                                               value="<?php echo htmlspecialchars($settingsData['smtp_username'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">SMTP Password</label>
                                    <input type="password" class="form-control" name="settings[smtp_password]" 
                                           value="<?php echo htmlspecialchars($settingsData['smtp_password'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Social Media Settings -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header"><h5><i class="fab fa-facebook me-2"></i>Social Media Links</h5></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fab fa-facebook-f me-2 text-primary"></i>Facebook URL
                                    </label>
                                    <input type="url" class="form-control" name="settings[facebook_url]" 
                                           placeholder="https://facebook.com/yourpage"
                                           value="<?php echo htmlspecialchars($settingsData['facebook_url'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fab fa-twitter me-2 text-info"></i>Twitter URL
                                    </label>
                                    <input type="url" class="form-control" name="settings[twitter_url]" 
                                           placeholder="https://twitter.com/yourusername"
                                           value="<?php echo htmlspecialchars($settingsData['twitter_url'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fab fa-instagram me-2 text-danger"></i>Instagram URL
                                    </label>
                                    <input type="url" class="form-control" name="settings[instagram_url]" 
                                           placeholder="https://instagram.com/yourusername"
                                           value="<?php echo htmlspecialchars($settingsData['instagram_url'] ?? ''); ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">
                                        <i class="fab fa-linkedin-in me-2 text-primary"></i>LinkedIn URL
                                    </label>
                                    <input type="url" class="form-control" name="settings[linkedin_url]" 
                                           placeholder="https://linkedin.com/in/yourprofile"
                                           value="<?php echo htmlspecialchars($settingsData['linkedin_url'] ?? ''); ?>">
                                </div>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <small>Leave empty to hide the social media icon. Only icons with URLs will be displayed on the website.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Download Settings -->
                    <div class="col-lg-6 mb-4">
                        <div class="card shadow">
                            <div class="card-header"><h5><i class="fas fa-download me-2"></i>Download Settings</h5></div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label">Download Expiry (Days)</label>
                                    <input type="number" class="form-control" name="settings[download_expiry_days]" 
                                           value="<?php echo $settingsData['download_expiry_days'] ?? '0'; ?>">
                                    <small class="text-muted">0 = Never expires</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Max Downloads per Purchase</label>
                                    <input type="number" class="form-control" name="settings[max_downloads]" 
                                           value="<?php echo $settingsData['max_downloads'] ?? '0'; ?>">
                                    <small class="text-muted">0 = Unlimited</small>
                                </div>
                                
                                <h6>System Settings</h6>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="settings[maintenance_mode]" value="1" 
                                           <?php echo ($settingsData['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Maintenance Mode</label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" name="settings[allow_registration]" value="1" 
                                           <?php echo ($settingsData['allow_registration'] ?? '1') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Allow User Registration</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="settings[require_email_verification]" value="1" 
                                           <?php echo ($settingsData['require_email_verification'] ?? '0') == '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label">Require Email Verification</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/6.4.2/mdb.min.js"></script>
</body>
</html>
