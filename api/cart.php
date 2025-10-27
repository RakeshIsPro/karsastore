<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;

// Get user ID or session ID for cart identification
$userId = $_SESSION['user_id'] ?? null;
$sessionId = session_id();

try {
    switch ($method) {
        case 'GET':
            handleGetRequest($action, $userId, $sessionId);
            break;
            
        case 'POST':
            handlePostRequest($userId, $sessionId);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

function handleGetRequest($action, $userId, $sessionId) {
    switch ($action) {
        case 'count':
            $count = getCartCount($userId, $sessionId);
            jsonResponse(['success' => true, 'count' => $count]);
            break;
            
        case 'items':
            $items = getCartItems($userId, $sessionId);
            $total = array_sum(array_column($items, 'price'));
            jsonResponse([
                'success' => true, 
                'items' => $items, 
                'total' => $total,
                'formatted_total' => formatPrice($total)
            ]);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
}

function handlePostRequest($userId, $sessionId) {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid request data'], 400);
    }
    
    $action = $input['action'];
    $productId = $input['product_id'] ?? null;
    
    if (!$productId) {
        jsonResponse(['success' => false, 'message' => 'Product ID is required'], 400);
    }
    
    // Verify product exists and is active
    $product = getProductById($productId);
    if (!$product) {
        jsonResponse(['success' => false, 'message' => 'Product not found'], 404);
    }
    
    switch ($action) {
        case 'add':
            // Check if product is already in cart
            $existingItems = getCartItems($userId, $sessionId);
            $alreadyInCart = false;
            
            foreach ($existingItems as $item) {
                if ($item['product_id'] == $productId) {
                    $alreadyInCart = true;
                    break;
                }
            }
            
            if ($alreadyInCart) {
                jsonResponse(['success' => false, 'message' => 'Product is already in your cart']);
            }
            
            $result = addToCart($productId, $userId, $sessionId);
            if ($result) {
                $count = getCartCount($userId, $sessionId);
                jsonResponse([
                    'success' => true, 
                    'message' => 'Product added to cart',
                    'count' => $count
                ]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to add product to cart']);
            }
            break;
            
        case 'remove':
            $result = removeFromCart($productId, $userId, $sessionId);
            if ($result) {
                $count = getCartCount($userId, $sessionId);
                $items = getCartItems($userId, $sessionId);
                $total = array_sum(array_column($items, 'price'));
                
                jsonResponse([
                    'success' => true, 
                    'message' => 'Product removed from cart',
                    'count' => $count,
                    'total' => $total,
                    'formatted_total' => formatPrice($total)
                ]);
            } else {
                jsonResponse(['success' => false, 'message' => 'Failed to remove product from cart']);
            }
            break;
            
        case 'clear':
            clearCart($userId, $sessionId);
            jsonResponse(['success' => true, 'message' => 'Cart cleared']);
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
}
?>
