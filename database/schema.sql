-- YBT Digital Database Schema
-- Import this file in phpMyAdmin to create the complete database structure

-- Create database
CREATE DATABASE IF NOT EXISTS `ybt_digital` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ybt_digital`;

-- --------------------------------------------------------

-- Table structure for table `users`
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin','super_admin') DEFAULT 'user',
  `status` enum('active','blocked','pending') DEFAULT 'active',
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` timestamp NULL DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `categories`
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `products`
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` longtext,
  `short_description` text,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_size` bigint(20) DEFAULT NULL,
  `demo_url` varchar(500) DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `downloads_count` int(11) DEFAULT 0,
  `views_count` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `reviews_count` int(11) DEFAULT 0,
  `status` enum('active','inactive','draft') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `idx_status` (`status`),
  KEY `idx_featured` (`featured`),
  KEY `idx_price` (`price`),
  FULLTEXT KEY `search_idx` (`title`,`description`,`short_description`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `orders`
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `coupon_code` varchar(50) DEFAULT NULL,
  `status` enum('pending','processing','completed','cancelled','refunded') DEFAULT 'pending',
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `billing_info` json DEFAULT NULL,
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_payment_status` (`payment_status`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `order_items`
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_price` decimal(10,2) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `images` json DEFAULT NULL,
  `download_count` int(11) DEFAULT 0,
  `download_expires` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `cart`
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `coupons`
CREATE TABLE `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `type` enum('percentage','flat') NOT NULL DEFAULT 'percentage',
  `value` decimal(10,2) NOT NULL,
  `minimum_amount` decimal(10,2) DEFAULT 0.00,
  `maximum_discount` decimal(10,2) DEFAULT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `user_limit` int(11) DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','inactive','expired') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_status` (`status`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `support_tickets`
CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL,
  `response` longtext,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `assigned_to` (`assigned_to`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `support_tickets_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `settings`
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` longtext,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `download_logs`
CREATE TABLE `download_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `order_item_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `product_id` (`product_id`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `download_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `download_logs_ibfk_2` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `download_logs_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Insert default categories
INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `sort_order`, `status`) VALUES
(1, 'Web Templates', 'web-templates', 'HTML, CSS, and JavaScript website templates', 1, 'active'),
(2, 'Graphics & Design', 'graphics-design', 'Logos, icons, graphics, and design assets', 2, 'active'),
(3, 'Mobile Apps', 'mobile-apps', 'Mobile app templates and source code', 3, 'active'),
(4, 'WordPress Themes', 'wordpress-themes', 'Premium WordPress themes and plugins', 4, 'active'),
(5, 'E-books', 'ebooks', 'Digital books, guides, and educational content', 5, 'active'),
(6, 'Software Tools', 'software-tools', 'Desktop software and development tools', 6, 'active'),
(7, 'Audio & Music', 'audio-music', 'Sound effects, music tracks, and audio files', 7, 'active'),
(8, 'Video Templates', 'video-templates', 'Video templates and motion graphics', 8, 'active');

-- --------------------------------------------------------

-- Insert default admin user
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `status`, `email_verified`) VALUES
(1, 'Admin User', 'admin@ybtdigital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active', 1);
-- Password: admin123

-- --------------------------------------------------------

-- Insert default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'YBT Digital', 'text', 'Website name'),
('site_description', 'Premium Digital Products Marketplace', 'text', 'Website description'),
('site_email', 'admin@ybtdigital.com', 'text', 'Site contact email'),
('currency', 'USD', 'text', 'Default currency'),
('currency_symbol', '$', 'text', 'Currency symbol'),
('tax_rate', '0', 'number', 'Tax rate percentage'),
('stripe_key', '', 'text', 'Stripe publishable key'),
('stripe_secret', '', 'text', 'Stripe secret key'),
('paypal_client_id', '', 'text', 'PayPal client ID'),
('paypal_secret', '', 'text', 'PayPal secret key'),
('razorpay_key', '', 'text', 'Razorpay key ID'),
('razorpay_secret', '', 'text', 'Razorpay secret key'),
('smtp_host', '', 'text', 'SMTP server host'),
('smtp_port', '587', 'number', 'SMTP server port'),
('smtp_username', '', 'text', 'SMTP username'),
('smtp_password', '', 'text', 'SMTP password'),
('download_expiry_days', '0', 'number', 'Download link expiry in days (0 = never expires)'),
('max_downloads', '0', 'number', 'Maximum downloads per purchase (0 = unlimited)'),
('maintenance_mode', '0', 'boolean', 'Enable maintenance mode'),
('allow_registration', '1', 'boolean', 'Allow user registration'),
('require_email_verification', '0', 'boolean', 'Require email verification for new accounts');

-- --------------------------------------------------------

-- Insert sample coupons
INSERT INTO `coupons` (`code`, `type`, `value`, `minimum_amount`, `usage_limit`, `expires_at`, `status`) VALUES
('WELCOME10', 'percentage', 10.00, 20.00, 100, DATE_ADD(NOW(), INTERVAL 30 DAY), 'active'),
('SAVE25', 'flat', 25.00, 100.00, 50, DATE_ADD(NOW(), INTERVAL 60 DAY), 'active'),
('NEWUSER', 'percentage', 15.00, 0.00, 200, DATE_ADD(NOW(), INTERVAL 90 DAY), 'active');

-- --------------------------------------------------------

-- Insert sample products
INSERT INTO `products` (`title`, `slug`, `description`, `short_description`, `price`, `category_id`, `images`, `file_path`, `file_size`, `demo_url`, `tags`, `featured`, `status`) VALUES
('Premium Website Template Bundle', 'premium-website-template-bundle', 'A comprehensive collection of 10 premium website templates designed for modern businesses. Each template is fully responsive, SEO-optimized, and includes HTML, CSS, and JavaScript files. Perfect for agencies, portfolios, and corporate websites.', 'Collection of 10 premium responsive website templates', 49.99, 1, '["assets/images/products/template-bundle.jpg"]', 'uploads/products/template-bundle.zip', 15728640, 'https://demo.ybtdigital.com/templates', '["HTML", "CSS", "JavaScript", "Responsive", "Bootstrap"]', 1, 'active'),

('Logo Design Mega Pack', 'logo-design-mega-pack', 'Over 500 professional logo designs in various formats including AI, EPS, PNG, and SVG. Suitable for all types of businesses and industries. Each logo is carefully crafted and ready to use.', '500+ professional logos in multiple formats', 29.99, 2, '["assets/images/products/logo-pack.jpg"]', 'uploads/products/logo-pack.zip', 52428800, NULL, '["Logo", "Branding", "AI", "EPS", "SVG"]', 1, 'active'),

('React Native Mobile App Template', 'react-native-mobile-app-template', 'Complete React Native app template with authentication, navigation, API integration, and modern UI components. Includes both iOS and Android compatibility with detailed documentation.', 'Complete React Native app template with modern UI', 79.99, 3, '["assets/images/products/react-native-app.jpg"]', 'uploads/products/react-native-template.zip', 25165824, 'https://demo.ybtdigital.com/react-app', '["React Native", "Mobile", "iOS", "Android", "JavaScript"]', 0, 'active'),

('WordPress E-commerce Theme', 'wordpress-ecommerce-theme', 'Professional WordPress theme designed specifically for online stores. WooCommerce compatible with advanced product filtering, wishlist functionality, and mobile-optimized checkout process.', 'Professional WooCommerce-ready WordPress theme', 59.99, 4, '["assets/images/products/wp-ecommerce-theme.jpg"]', 'uploads/products/wp-ecommerce-theme.zip', 18874368, 'https://demo.ybtdigital.com/wp-theme', '["WordPress", "WooCommerce", "E-commerce", "Responsive"]', 1, 'active'),

('Digital Marketing Guide 2024', 'digital-marketing-guide-2024', 'Comprehensive 200-page guide covering all aspects of digital marketing including SEO, social media marketing, email campaigns, and conversion optimization. Written by industry experts.', '200-page comprehensive digital marketing guide', 19.99, 5, '["assets/images/products/marketing-guide.jpg"]', 'uploads/products/marketing-guide.pdf', 5242880, NULL, '["Marketing", "SEO", "Social Media", "E-book", "Guide"]', 0, 'active'),

('Project Management Software Suite', 'project-management-software-suite', 'Complete project management solution with task tracking, team collaboration, time management, and reporting features. Includes desktop and web versions with full source code.', 'Complete project management software with source code', 149.99, 6, '["assets/images/products/project-management.jpg"]', 'uploads/products/project-management-suite.zip', 104857600, 'https://demo.ybtdigital.com/project-manager', '["Software", "Project Management", "Productivity", "Source Code"]', 1, 'active'),

('Social Media Graphics Pack', 'social-media-graphics-pack', 'Over 1000 social media graphics including Instagram posts, Facebook covers, Twitter headers, and LinkedIn banners. All graphics are editable in Photoshop and Canva.', '1000+ social media graphics for all platforms', 24.99, 2, '["assets/images/products/social-media-pack.jpg"]', 'uploads/products/social-media-graphics.zip', 31457280, NULL, '["Social Media", "Graphics", "Instagram", "Facebook", "Photoshop"]', 0, 'active'),

('Flutter Mobile App UI Kit', 'flutter-mobile-app-ui-kit', 'Beautiful Flutter UI kit with 50+ screens covering login, profile, e-commerce, chat, and dashboard layouts. Includes dark mode support and custom animations.', '50+ Flutter UI screens with dark mode support', 39.99, 3, '["assets/images/products/flutter-ui-kit.jpg"]', 'uploads/products/flutter-ui-kit.zip', 12582912, 'https://demo.ybtdigital.com/flutter-ui', '["Flutter", "UI Kit", "Mobile", "Dark Mode", "Animations"]', 0, 'active');

-- --------------------------------------------------------

-- Create indexes for better performance
CREATE INDEX idx_products_search ON products(title, price, featured, status);
CREATE INDEX idx_orders_user_date ON orders(user_id, created_at);
CREATE INDEX idx_cart_user_session ON cart(user_id, session_id);

-- --------------------------------------------------------

COMMIT;
