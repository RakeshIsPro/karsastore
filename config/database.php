<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ybt_digital');

// Create connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // If database doesn't exist, create it
    try {
        $pdo_temp = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo_temp->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Now connect to the created database
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Create tables
        createTables($pdo);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

function createTables($pdo) {
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin', 'super_admin') DEFAULT 'user',
        status ENUM('active', 'blocked') DEFAULT 'active',
        email_verified BOOLEAN DEFAULT FALSE,
        verification_token VARCHAR(100) NULL,
        reset_token VARCHAR(100) NULL,
        reset_expires DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Categories table
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        image VARCHAR(255),
        parent_id INT DEFAULT NULL,
        sort_order INT DEFAULT 0,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
    )");

    // Products table
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        slug VARCHAR(200) UNIQUE NOT NULL,
        description TEXT,
        short_description VARCHAR(500),
        price DECIMAL(10,2) NOT NULL,
        category_id INT,
        images JSON,
        file_path VARCHAR(255),
        file_size BIGINT,
        demo_url VARCHAR(255),
        tags JSON,
        status ENUM('active', 'inactive') DEFAULT 'active',
        featured BOOLEAN DEFAULT FALSE,
        downloads_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    )");

    // Orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        tax_amount DECIMAL(10,2) DEFAULT 0,
        discount_amount DECIMAL(10,2) DEFAULT 0,
        coupon_code VARCHAR(50),
        payment_method VARCHAR(50),
        payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
        transaction_id VARCHAR(100),
        payment_gateway VARCHAR(50),
        billing_info JSON,
        status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // Order items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_title VARCHAR(200) NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        download_count INT DEFAULT 0,
        download_expires DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
    )");

    // Coupons table
    $pdo->exec("CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) UNIQUE NOT NULL,
        type ENUM('flat', 'percentage') NOT NULL,
        value DECIMAL(10,2) NOT NULL,
        minimum_amount DECIMAL(10,2) DEFAULT 0,
        usage_limit INT DEFAULT NULL,
        used_count INT DEFAULT 0,
        expires_at DATETIME NULL,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Cart table
    $pdo->exec("CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        session_id VARCHAR(100),
        product_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_cart_item (user_id, product_id),
        UNIQUE KEY unique_session_item (session_id, product_id)
    )");

    // Support tickets table
    $pdo->exec("CREATE TABLE IF NOT EXISTS support_tickets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        subject VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('open', 'in_progress', 'resolved', 'closed') DEFAULT 'open',
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        admin_response TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    )");

    // Settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        setting_key VARCHAR(100) UNIQUE NOT NULL,
        setting_value TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Insert default settings
    $defaultSettings = [
        ['site_name', 'Shishir Basnet'],
        ['site_logo', ''],
        ['tax_rate', '18'],
        ['currency', 'USD'],
        ['razorpay_key', ''],
        ['razorpay_secret', ''],
        ['stripe_key', ''],
        ['stripe_secret', ''],
        ['paypal_client_id', ''],
        ['paypal_secret', ''],
        ['email_notifications', '1'],
        ['download_expiry_days', '30']
    ];

    $stmt = $pdo->prepare("INSERT IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
    foreach ($defaultSettings as $setting) {
        $stmt->execute($setting);
    }

    // Insert default admin user
    $adminExists = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'super_admin'")->fetchColumn();
    if ($adminExists == 0) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, email_verified) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['Admin', 'admin@shishirbasnet.com', password_hash('admin123', PASSWORD_DEFAULT), 'super_admin', 1]);
    }

    // Insert sample categories
    $categoriesExist = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
    if ($categoriesExist == 0) {
        $categories = [
            ['Web Templates', 'web-templates', 'Professional website templates and themes'],
            ['Graphics & Design', 'graphics-design', 'Digital graphics, logos, and design assets'],
            ['Mobile Apps', 'mobile-apps', 'Mobile application templates and source codes'],
            ['WordPress Themes', 'wordpress-themes', 'Premium WordPress themes and plugins'],
            ['E-books', 'ebooks', 'Digital books and educational content'],
            ['Software Tools', 'software-tools', 'Productivity software and development tools']
        ];

        $stmt = $pdo->prepare("INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)");
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
    }
}
?>
