<?php
// This script adds sample data to the database for demonstration purposes
// Run this once after setting up the database

session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if sample data already exists
$stmt = $pdo->query("SELECT COUNT(*) FROM products");
$productCount = $stmt->fetchColumn();

if ($productCount > 0) {
    echo "Sample data already exists. Delete existing products first if you want to reload sample data.";
    exit;
}

echo "<h2>Adding Sample Data to YBT Digital...</h2>";

try {
    // Sample products data
    $sampleProducts = [
        [
            'title' => 'Premium Website Template Bundle',
            'slug' => 'premium-website-template-bundle',
            'description' => 'A comprehensive collection of 10 premium website templates designed for modern businesses. Each template is fully responsive, SEO-optimized, and includes HTML, CSS, and JavaScript files. Perfect for agencies, portfolios, and corporate websites.',
            'short_description' => 'Collection of 10 premium responsive website templates',
            'price' => 49.99,
            'category_id' => 1, // Web Templates
            'images' => json_encode(['assets/images/products/template-bundle.jpg']),
            'file_path' => 'uploads/products/template-bundle.zip',
            'file_size' => 15728640, // 15MB
            'demo_url' => 'https://demo.ybtdigital.com/templates',
            'tags' => json_encode(['HTML', 'CSS', 'JavaScript', 'Responsive', 'Bootstrap']),
            'featured' => 1
        ],
        [
            'title' => 'Logo Design Mega Pack',
            'slug' => 'logo-design-mega-pack',
            'description' => 'Over 500 professional logo designs in various formats including AI, EPS, PNG, and SVG. Suitable for all types of businesses and industries. Each logo is carefully crafted and ready to use.',
            'short_description' => '500+ professional logos in multiple formats',
            'price' => 29.99,
            'category_id' => 2, // Graphics & Design
            'images' => json_encode(['assets/images/products/logo-pack.jpg']),
            'file_path' => 'uploads/products/logo-pack.zip',
            'file_size' => 52428800, // 50MB
            'tags' => json_encode(['Logo', 'Branding', 'AI', 'EPS', 'SVG']),
            'featured' => 1
        ],
        [
            'title' => 'React Native Mobile App Template',
            'slug' => 'react-native-mobile-app-template',
            'description' => 'Complete React Native app template with authentication, navigation, API integration, and modern UI components. Includes both iOS and Android compatibility with detailed documentation.',
            'short_description' => 'Complete React Native app template with modern UI',
            'price' => 79.99,
            'category_id' => 3, // Mobile Apps
            'images' => json_encode(['assets/images/products/react-native-app.jpg']),
            'file_path' => 'uploads/products/react-native-template.zip',
            'file_size' => 25165824, // 24MB
            'demo_url' => 'https://demo.ybtdigital.com/react-app',
            'tags' => json_encode(['React Native', 'Mobile', 'iOS', 'Android', 'JavaScript']),
            'featured' => 0
        ],
        [
            'title' => 'WordPress E-commerce Theme',
            'slug' => 'wordpress-ecommerce-theme',
            'description' => 'Professional WordPress theme designed specifically for online stores. WooCommerce compatible with advanced product filtering, wishlist functionality, and mobile-optimized checkout process.',
            'short_description' => 'Professional WooCommerce-ready WordPress theme',
            'price' => 59.99,
            'category_id' => 4, // WordPress Themes
            'images' => json_encode(['assets/images/products/wp-ecommerce-theme.jpg']),
            'file_path' => 'uploads/products/wp-ecommerce-theme.zip',
            'file_size' => 18874368, // 18MB
            'demo_url' => 'https://demo.ybtdigital.com/wp-theme',
            'tags' => json_encode(['WordPress', 'WooCommerce', 'E-commerce', 'Responsive']),
            'featured' => 1
        ],
        [
            'title' => 'Digital Marketing Guide 2024',
            'slug' => 'digital-marketing-guide-2024',
            'description' => 'Comprehensive 200-page guide covering all aspects of digital marketing including SEO, social media marketing, email campaigns, and conversion optimization. Written by industry experts.',
            'short_description' => '200-page comprehensive digital marketing guide',
            'price' => 19.99,
            'category_id' => 5, // E-books
            'images' => json_encode(['assets/images/products/marketing-guide.jpg']),
            'file_path' => 'uploads/products/marketing-guide.pdf',
            'file_size' => 5242880, // 5MB
            'tags' => json_encode(['Marketing', 'SEO', 'Social Media', 'E-book', 'Guide']),
            'featured' => 0
        ],
        [
            'title' => 'Project Management Software Suite',
            'slug' => 'project-management-software-suite',
            'description' => 'Complete project management solution with task tracking, team collaboration, time management, and reporting features. Includes desktop and web versions with full source code.',
            'short_description' => 'Complete project management software with source code',
            'price' => 149.99,
            'category_id' => 6, // Software Tools
            'images' => json_encode(['assets/images/products/project-management.jpg']),
            'file_path' => 'uploads/products/project-management-suite.zip',
            'file_size' => 104857600, // 100MB
            'demo_url' => 'https://demo.ybtdigital.com/project-manager',
            'tags' => json_encode(['Software', 'Project Management', 'Productivity', 'Source Code']),
            'featured' => 1
        ],
        [
            'title' => 'Social Media Graphics Pack',
            'slug' => 'social-media-graphics-pack',
            'description' => 'Over 1000 social media graphics including Instagram posts, Facebook covers, Twitter headers, and LinkedIn banners. All graphics are editable in Photoshop and Canva.',
            'short_description' => '1000+ social media graphics for all platforms',
            'price' => 24.99,
            'category_id' => 2, // Graphics & Design
            'images' => json_encode(['assets/images/products/social-media-pack.jpg']),
            'file_path' => 'uploads/products/social-media-graphics.zip',
            'file_size' => 31457280, // 30MB
            'tags' => json_encode(['Social Media', 'Graphics', 'Instagram', 'Facebook', 'Photoshop']),
            'featured' => 0
        ],
        [
            'title' => 'Flutter Mobile App UI Kit',
            'slug' => 'flutter-mobile-app-ui-kit',
            'description' => 'Beautiful Flutter UI kit with 50+ screens covering login, profile, e-commerce, chat, and dashboard layouts. Includes dark mode support and custom animations.',
            'short_description' => '50+ Flutter UI screens with dark mode support',
            'price' => 39.99,
            'category_id' => 3, // Mobile Apps
            'images' => json_encode(['assets/images/products/flutter-ui-kit.jpg']),
            'file_path' => 'uploads/products/flutter-ui-kit.zip',
            'file_size' => 12582912, // 12MB
            'demo_url' => 'https://demo.ybtdigital.com/flutter-ui',
            'tags' => json_encode(['Flutter', 'UI Kit', 'Mobile', 'Dark Mode', 'Animations']),
            'featured' => 0
        ]
    ];

    // Insert sample products
    $stmt = $pdo->prepare("
        INSERT INTO products (title, slug, description, short_description, price, category_id, images, file_path, file_size, demo_url, tags, featured, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
    ");

    foreach ($sampleProducts as $product) {
        $stmt->execute([
            $product['title'],
            $product['slug'],
            $product['description'],
            $product['short_description'],
            $product['price'],
            $product['category_id'],
            $product['images'],
            $product['file_path'],
            $product['file_size'],
            $product['demo_url'] ?? null,
            $product['tags'],
            $product['featured']
        ]);
        echo "Added product: " . $product['title'] . "<br>";
    }

    // Add sample coupons
    $sampleCoupons = [
        [
            'code' => 'WELCOME10',
            'type' => 'percentage',
            'value' => 10,
            'minimum_amount' => 20,
            'usage_limit' => 100,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ],
        [
            'code' => 'SAVE25',
            'type' => 'flat',
            'value' => 25,
            'minimum_amount' => 100,
            'usage_limit' => 50,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+60 days'))
        ],
        [
            'code' => 'NEWUSER',
            'type' => 'percentage',
            'value' => 15,
            'minimum_amount' => 0,
            'usage_limit' => 200,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+90 days'))
        ]
    ];

    $stmt = $pdo->prepare("
        INSERT INTO coupons (code, type, value, minimum_amount, usage_limit, expires_at, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    ");

    foreach ($sampleCoupons as $coupon) {
        $stmt->execute([
            $coupon['code'],
            $coupon['type'],
            $coupon['value'],
            $coupon['minimum_amount'],
            $coupon['usage_limit'],
            $coupon['expires_at']
        ]);
        echo "Added coupon: " . $coupon['code'] . "<br>";
    }

    // Create uploads directory structure
    $uploadDirs = [
        'uploads/',
        'uploads/products/',
        'uploads/images/',
        'uploads/temp/'
    ];

    foreach ($uploadDirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "Created directory: " . $dir . "<br>";
        }
    }

    // Create placeholder image files (you would replace these with actual images)
    $placeholderImages = [
        'assets/images/products/template-bundle.jpg',
        'assets/images/products/logo-pack.jpg',
        'assets/images/products/react-native-app.jpg',
        'assets/images/products/wp-ecommerce-theme.jpg',
        'assets/images/products/marketing-guide.jpg',
        'assets/images/products/project-management.jpg',
        'assets/images/products/social-media-pack.jpg',
        'assets/images/products/flutter-ui-kit.jpg'
    ];

    // Create assets/images/products directory
    if (!file_exists('assets/images/products/')) {
        mkdir('assets/images/products/', 0755, true);
    }

    // Create placeholder images (1x1 pixel images for demo)
    foreach ($placeholderImages as $imagePath) {
        if (!file_exists($imagePath)) {
            // Create a simple placeholder image
            $img = imagecreate(400, 300);
            $bg = imagecolorallocate($img, 240, 240, 240);
            $text_color = imagecolorallocate($img, 100, 100, 100);
            imagestring($img, 5, 150, 140, 'Product Image', $text_color);
            imagejpeg($img, $imagePath);
            imagedestroy($img);
            echo "Created placeholder image: " . $imagePath . "<br>";
        }
    }

    echo "<br><strong>Sample data added successfully!</strong><br>";
    echo "<br>You can now:<br>";
    echo "- Browse products at: <a href='products.php'>products.php</a><br>";
    echo "- Access admin panel at: <a href='admin/'>admin/</a><br>";
    echo "- Use coupon codes: WELCOME10, SAVE25, NEWUSER<br>";
    echo "<br>Default admin login:<br>";
    echo "Email: admin@ybtdigital.com<br>";
    echo "Password: admin123<br>";

} catch (Exception $e) {
    echo "Error adding sample data: " . $e->getMessage();
}
?>
