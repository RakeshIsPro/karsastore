<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'GET') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
    
    // Get query parameters
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $category = isset($_GET['category']) && $_GET['category'] !== '' && $_GET['category'] > 0 ? (int)$_GET['category'] : null;
    $search = isset($_GET['search']) && $_GET['search'] !== '' ? sanitizeInput($_GET['search']) : null;
    $featured = isset($_GET['featured']) ? (bool)$_GET['featured'] : false;
    $sortBy = isset($_GET['sort']) ? sanitizeInput($_GET['sort']) : 'newest';
    $minPrice = isset($_GET['min_price']) && $_GET['min_price'] !== '' && $_GET['min_price'] > 0 ? (float)$_GET['min_price'] : null;
    $maxPrice = isset($_GET['max_price']) && $_GET['max_price'] !== '' && $_GET['max_price'] > 0 ? (float)$_GET['max_price'] : null;
    
    // Validate limits
    $limit = max(1, min(50, $limit)); // Between 1 and 50
    $offset = max(0, $offset);
    
    // Build query
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
        $sql .= " AND (p.title LIKE ? OR p.description LIKE ? OR p.short_description LIKE ?)";
        $searchTerm = "%$search%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    if ($minPrice !== null) {
        $sql .= " AND p.price >= ?";
        $params[] = $minPrice;
    }
    
    if ($maxPrice !== null) {
        $sql .= " AND p.price <= ?";
        $params[] = $maxPrice;
    }
    
    // Add sorting
    switch ($sortBy) {
        case 'price_low':
            $sql .= " ORDER BY p.price ASC";
            break;
        case 'price_high':
            $sql .= " ORDER BY p.price DESC";
            break;
        case 'popular':
            $sql .= " ORDER BY p.downloads_count DESC";
            break;
        case 'featured':
            $sql .= " ORDER BY p.featured DESC, p.created_at DESC";
            break;
        case 'oldest':
            $sql .= " ORDER BY p.created_at ASC";
            break;
        default: // newest
            $sql .= " ORDER BY p.created_at DESC";
    }
    
    // Get total count for pagination
    $countSql = str_replace('SELECT p.*, c.name as category_name', 'SELECT COUNT(*)', $sql);
    $countSql = preg_replace('/ORDER BY.*$/', '', $countSql);
    
    $countStmt = $pdo->prepare($countSql);
    $countStmt->execute($params);
    $totalCount = $countStmt->fetchColumn();
    
    // Add pagination
    $sql .= " LIMIT $limit OFFSET $offset";
    
    // Execute main query
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Process product data
    foreach ($products as &$product) {
        $product['images'] = json_decode($product['images'] ?? '[]', true);
        $product['tags'] = json_decode($product['tags'] ?? '[]', true);
        $product['formatted_price'] = formatPrice($product['price']);
        
        // Add main image for easier access
        $product['main_image'] = !empty($product['images']) 
            ? $product['images'][0] 
            : '/digital nest/assets/images/placeholder.jpg';
    }
    
    // Get category counts for filters
    $categoryCounts = [];
    if (!$search && !$category) {
        $categoryStmt = $pdo->query("
            SELECT c.id, c.name, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
            WHERE c.status = 'active'
            GROUP BY c.id, c.name
            ORDER BY c.name
        ");
        $categoryCounts = $categoryStmt->fetchAll();
    }
    
    // Calculate pagination info
    $totalPages = ceil($totalCount / $limit);
    $currentPage = floor($offset / $limit) + 1;
    $hasNextPage = $currentPage < $totalPages;
    $hasPrevPage = $currentPage > 1;
    
    jsonResponse([
        'success' => true,
        'products' => $products,
        'pagination' => [
            'total_count' => $totalCount,
            'total_pages' => $totalPages,
            'current_page' => $currentPage,
            'limit' => $limit,
            'offset' => $offset,
            'has_next' => $hasNextPage,
            'has_prev' => $hasPrevPage
        ],
        'filters' => [
            'categories' => $categoryCounts,
            'applied' => [
                'category' => $category,
                'search' => $search,
                'featured' => $featured,
                'sort' => $sortBy,
                'min_price' => $minPrice,
                'max_price' => $maxPrice
            ]
        ]
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}
?>
