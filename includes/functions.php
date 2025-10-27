<?php
// Security functions
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// User functions
function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUserByEmail($email) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function createUser($name, $email, $password) {
    global $pdo;
    $hashedPassword = hashPassword($password);
    $verificationToken = generateToken();
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, verification_token) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $hashedPassword, $verificationToken]);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /digital nest/auth/login.php');
        exit;
    }
}

function isAdmin() {
    if (!isLoggedIn()) return false;
    $user = getUserById($_SESSION['user_id']);
    return $user && in_array($user['role'], ['admin', 'super_admin']);
}

function isSuperAdmin() {
    if (!isLoggedIn()) return false;
    $user = getUserById($_SESSION['user_id']);
    return $user && $user['role'] === 'super_admin';
}

function isLimitedAdmin() {
    if (!isLoggedIn()) return false;
    $user = getUserById($_SESSION['user_id']);
    return $user && $user['role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /digital nest/index.php');
        exit;
    }
}

function requireSuperAdmin() {
    if (!isSuperAdmin()) {
        header('Location: /digital nest/admin/index.php');
        exit;
    }
}

function hasPermission($permission) {
    if (!isLoggedIn()) return false;
    $user = getUserById($_SESSION['user_id']);
    
    if (!$user) return false;
    
    // Super admin has all permissions
    if ($user['role'] === 'super_admin') return true;
    
    // Limited admin permissions
    if ($user['role'] === 'admin') {
        $adminPermissions = [
            'view_products',
            'add_products',
            'edit_products',
            'view_orders',
            'update_order_status',
            'view_users',
            'view_categories',
            'view_dashboard',
            'view_support_tickets',
            'reply_support_tickets'
        ];
        return in_array($permission, $adminPermissions);
    }
    
    return false;
}

function canAccess($permission) {
    return hasPermission($permission);
}

// Coupon functions
function getCouponByCode($code) {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT * FROM coupons 
        WHERE code = ? 
        AND status = 'active' 
        AND (expires_at IS NULL OR expires_at > NOW())
        AND (usage_limit IS NULL OR used_count < usage_limit)
    ");
    $stmt->execute([strtoupper($code)]);
    return $stmt->fetch();
}

function applyCoupon($code, $amount) {
    $coupon = getCouponByCode($code);
    
    if (!$coupon) {
        return false;
    }
    
    // Check minimum amount
    if ($coupon['minimum_amount'] > 0 && $amount < $coupon['minimum_amount']) {
        return false;
    }
    
    // Calculate discount
    if ($coupon['type'] == 'flat') {
        $discount = min($coupon['value'], $amount); // Don't exceed order amount
    } else { // percentage
        $discount = ($amount * $coupon['value']) / 100;
        // Cap percentage discount at order amount
        $discount = min($discount, $amount);
    }
    
    return round($discount, 2);
}

function validateCoupon($code, $amount) {
    $coupon = getCouponByCode($code);
    
    if (!$coupon) {
        return ['valid' => false, 'message' => 'Invalid or expired coupon code'];
    }
    
    // Check if coupon is active
    if ($coupon['status'] != 'active') {
        return ['valid' => false, 'message' => 'This coupon is no longer active'];
    }
    
    // Check expiry date
    if ($coupon['expires_at'] && strtotime($coupon['expires_at']) < time()) {
        return ['valid' => false, 'message' => 'This coupon has expired'];
    }
    
    // Check usage limit
    if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
        return ['valid' => false, 'message' => 'This coupon has reached its usage limit'];
    }
    
    // Check minimum amount
    if ($coupon['minimum_amount'] > 0 && $amount < $coupon['minimum_amount']) {
        return [
            'valid' => false, 
            'message' => 'Minimum order amount of ' . formatPrice($coupon['minimum_amount']) . ' required for this coupon'
        ];
    }
    
    return ['valid' => true, 'coupon' => $coupon];
}

function incrementCouponUsage($code) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE coupons SET used_count = used_count + 1 WHERE code = ?");
    return $stmt->execute([strtoupper($code)]);
}

// Product functions
function getProducts($limit = null, $offset = 0, $category = null, $search = null, $featured = false) {
    global $pdo;
    
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active'";
    $params = [];
    
    if ($featured) {
        $sql .= " AND p.featured = 1";
    }
    
    if ($category) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category;
    }
    
    if ($search) {
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    if ($limit) {
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getProductById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.id = ? AND p.status = 'active'");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getProductBySlug($slug) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                          LEFT JOIN categories c ON p.category_id = c.id 
                          WHERE p.slug = ? AND p.status = 'active'");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

function getRelatedProducts($productId, $categoryId, $limit = 4) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM products 
                          WHERE category_id = ? AND id != ? AND status = 'active' 
                          ORDER BY RAND() LIMIT ?");
    $stmt->execute([$categoryId, $productId, $limit]);
    return $stmt->fetchAll();
}

// Category functions
function getCategories($activeOnly = true) {
    global $pdo;
    $sql = "SELECT * FROM categories";
    if ($activeOnly) {
        $sql .= " WHERE status = 'active'";
    }
    $sql .= " ORDER BY name";
    
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll();
}

function getCategoryById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

// Cart functions
function getCartItems($userId = null, $sessionId = null) {
    global $pdo;
    
    if ($userId) {
        $stmt = $pdo->prepare("SELECT c.*, p.title, p.price, p.images FROM cart c 
                              JOIN products p ON c.product_id = p.id 
                              WHERE c.user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT c.*, p.title, p.price, p.images FROM cart c 
                              JOIN products p ON c.product_id = p.id 
                              WHERE c.session_id = ?");
        $stmt->execute([$sessionId]);
    }
    
    return $stmt->fetchAll();
}

function addToCart($productId, $userId = null, $sessionId = null) {
    global $pdo;
    
    try {
        if ($userId) {
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
            $stmt->execute([$userId, $productId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO cart (session_id, product_id) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE created_at = CURRENT_TIMESTAMP");
            $stmt->execute([$sessionId, $productId]);
        }
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function removeFromCart($productId, $userId = null, $sessionId = null) {
    global $pdo;
    
    if ($userId) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ? AND product_id = ?");
        $stmt->execute([$sessionId, $productId]);
    }
    
    return $stmt->rowCount() > 0;
}

function getCartCount($userId = null, $sessionId = null) {
    global $pdo;
    
    if ($userId) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }
    
    return $stmt->fetchColumn();
}

function clearCart($userId = null, $sessionId = null) {
    global $pdo;
    
    if ($userId) {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE session_id = ?");
        $stmt->execute([$sessionId]);
    }
}

// Order functions
function createOrder($userId, $items, $totalAmount, $taxAmount = 0, $discountAmount = 0, $couponCode = null) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();
        
        $orderNumber = 'SB' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, order_number, total_amount, tax_amount, discount_amount, coupon_code) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $orderNumber, $totalAmount, $taxAmount, $discountAmount, $couponCode]);
        
        $orderId = $pdo->lastInsertId();
        
        foreach ($items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_title, price) 
                                  VALUES (?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['product_id'], $item['title'], $item['price']]);
        }
        
        // Increment coupon usage if coupon was used
        if ($couponCode) {
            incrementCouponUsage($couponCode);
        }
        
        $pdo->commit();
        return $orderId;
    } catch (Exception $e) {
        $pdo->rollback();
        return false;
    }
}

function getOrderById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

function getUserOrders($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

function getOrderItems($orderId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT oi.*, p.file_path, p.images FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll();
}

// Settings functions
function getSetting($key, $default = null) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : $default;
}

function updateSetting($key, $value) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                          ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = CURRENT_TIMESTAMP");
    return $stmt->execute([$key, $value, $value]);
}

// Utility functions
function formatPrice($amount, $currency = null) {
    if (!$currency) {
        $currency = getSetting('currency', 'USD');
    }
    
    $symbols = [
        'USD' => '$',
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹'
    ];
    
    $symbol = $symbols[$currency] ?? $currency . ' ';
    return $symbol . number_format($amount, 2);
}

function generateSlug($string) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string), '-'));
    return $slug;
}

function uploadFile($file, $directory = 'uploads/') {
    $uploadDir = __DIR__ . '/../' . $directory;
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $directory . $fileName;
    }
    
    return false;
}

function sendEmail($to, $subject, $message, $headers = []) {
    // Basic email function - can be enhanced with PHPMailer or similar
    $defaultHeaders = [
        'From: ' . getSetting('site_name', 'YBT Digital') . ' <noreply@ybtdigital.com>',
        'Reply-To: support@ybtdigital.com',
        'Content-Type: text/html; charset=UTF-8'
    ];
    
    $allHeaders = array_merge($defaultHeaders, $headers);
    return mail($to, $subject, $message, implode("\r\n", $allHeaders));
}

// JSON response function for AJAX
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// CSRF protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Rate limiting (basic implementation)
function checkRateLimit($identifier, $maxAttempts = 5, $timeWindow = 300) {
    $key = 'rate_limit_' . md5($identifier);
    
    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    $data = $_SESSION[$key];
    
    if (time() - $data['first_attempt'] > $timeWindow) {
        $_SESSION[$key] = ['count' => 1, 'first_attempt' => time()];
        return true;
    }
    
    if ($data['count'] >= $maxAttempts) {
        return false;
    }
    
    $_SESSION[$key]['count']++;
    return true;
}
?>
