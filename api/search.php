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
    
    $query = isset($_GET['q']) ? sanitizeInput($_GET['q']) : '';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $category = isset($_GET['category']) ? (int)$_GET['category'] : null;
    
    if (empty($query)) {
        jsonResponse(['success' => false, 'message' => 'Search query is required'], 400);
    }
    
    if (strlen($query) < 2) {
        jsonResponse(['success' => false, 'message' => 'Search query must be at least 2 characters'], 400);
    }
    
    // Validate limit
    $limit = max(1, min(50, $limit));
    
    // Build search query with relevance scoring
    $sql = "SELECT p.*, c.name as category_name,
            (CASE 
                WHEN p.title LIKE ? THEN 10
                WHEN p.title LIKE ? THEN 8
                WHEN p.short_description LIKE ? THEN 6
                WHEN p.description LIKE ? THEN 4
                WHEN p.tags LIKE ? THEN 2
                ELSE 1
            END) as relevance_score
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.status = 'active' 
            AND (p.title LIKE ? OR p.description LIKE ? OR p.short_description LIKE ? OR p.tags LIKE ?)";
    
    $params = [];
    
    // Exact match gets highest score
    $exactMatch = $query;
    $params[] = $exactMatch;
    
    // Starts with gets high score
    $startsWithMatch = $query . '%';
    $params[] = $startsWithMatch;
    
    // Contains match for different fields
    $containsMatch = '%' . $query . '%';
    $params[] = $containsMatch; // short_description
    $params[] = $containsMatch; // description
    $params[] = $containsMatch; // tags
    
    // Main search conditions
    $params[] = $containsMatch; // title
    $params[] = $containsMatch; // description
    $params[] = $containsMatch; // short_description
    $params[] = $containsMatch; // tags
    
    // Add category filter if specified
    if ($category) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category;
    }
    
    // Order by relevance and popularity
    $sql .= " ORDER BY relevance_score DESC, p.featured DESC, p.downloads_count DESC, p.created_at DESC";
    
    // Add limit
    $sql .= " LIMIT ?";
    $params[] = $limit;
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    // Process product data
    foreach ($products as &$product) {
        $product['images'] = json_decode($product['images'] ?? '[]', true);
        $product['tags'] = json_decode($product['tags'] ?? '[]', true);
        $product['formatted_price'] = formatPrice($product['price']);
        
        // Add main image
        $product['main_image'] = !empty($product['images']) 
            ? $product['images'][0] 
            : '/digital nest/assets/images/placeholder.jpg';
        
        // Highlight search terms in title and description
        $product['highlighted_title'] = highlightSearchTerms($product['title'], $query);
        $product['highlighted_description'] = highlightSearchTerms($product['short_description'] ?? '', $query);
    }
    
    // Get search suggestions if no results found
    $suggestions = [];
    if (empty($products)) {
        $suggestions = getSearchSuggestions($query);
    }
    
    // Log search query for analytics (optional)
    logSearchQuery($query, count($products));
    
    jsonResponse([
        'success' => true,
        'products' => $products,
        'query' => $query,
        'total_results' => count($products),
        'suggestions' => $suggestions,
        'search_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
    ]);
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Search failed: ' . $e->getMessage()], 500);
}

function highlightSearchTerms($text, $query) {
    if (empty($text) || empty($query)) {
        return $text;
    }
    
    // Escape special regex characters in query
    $escapedQuery = preg_quote($query, '/');
    
    // Highlight matching terms (case insensitive)
    return preg_replace('/(' . $escapedQuery . ')/i', '<mark>$1</mark>', $text);
}

function getSearchSuggestions($query) {
    global $pdo;
    
    // Get similar product titles
    $stmt = $pdo->prepare("
        SELECT DISTINCT title 
        FROM products 
        WHERE status = 'active' 
        AND title LIKE ? 
        LIMIT 5
    ");
    $stmt->execute(['%' . $query . '%']);
    $suggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get popular search terms from categories
    if (empty($suggestions)) {
        $stmt = $pdo->prepare("
            SELECT name 
            FROM categories 
            WHERE status = 'active' 
            AND name LIKE ? 
            LIMIT 3
        ");
        $stmt->execute(['%' . $query . '%']);
        $categorySuggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $suggestions = array_merge($suggestions, $categorySuggestions);
    }
    
    return $suggestions;
}

function logSearchQuery($query, $resultCount) {
    global $pdo;
    
    try {
        // Create search_logs table if it doesn't exist
        $pdo->exec("CREATE TABLE IF NOT EXISTS search_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            query VARCHAR(255) NOT NULL,
            result_count INT DEFAULT 0,
            user_id INT NULL,
            ip_address VARCHAR(45),
            user_agent TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_query (query),
            INDEX idx_created_at (created_at)
        )");
        
        $stmt = $pdo->prepare("
            INSERT INTO search_logs (query, result_count, user_id, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $userId = $_SESSION['user_id'] ?? null;
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt->execute([$query, $resultCount, $userId, $ipAddress, $userAgent]);
    } catch (Exception $e) {
        // Log error but don't fail the search
        error_log('Failed to log search query: ' . $e->getMessage());
    }
}
?>
