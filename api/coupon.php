<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method !== 'POST') {
        jsonResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        jsonResponse(['success' => false, 'message' => 'Invalid request data'], 400);
    }
    
    $action = $input['action'];
    
    switch ($action) {
        case 'apply':
            applyCouponCode($input);
            break;
            
        case 'remove':
            removeCouponCode();
            break;
            
        default:
            jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
    }
    
} catch (Exception $e) {
    jsonResponse(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
}

function applyCouponCode($input) {
    $code = $input['code'] ?? '';
    $amount = (float)($input['amount'] ?? 0);
    
    if (empty($code)) {
        jsonResponse(['success' => false, 'message' => 'Coupon code is required']);
    }
    
    if ($amount <= 0) {
        jsonResponse(['success' => false, 'message' => 'Invalid order amount']);
    }
    
    // Get coupon details
    $coupon = getCouponByCode($code);
    
    if (!$coupon) {
        jsonResponse(['success' => false, 'message' => 'Invalid or expired coupon code']);
    }
    
    // Check minimum amount requirement
    if ($coupon['minimum_amount'] > 0 && $amount < $coupon['minimum_amount']) {
        jsonResponse([
            'success' => false, 
            'message' => 'Minimum order amount of ' . formatPrice($coupon['minimum_amount']) . ' required for this coupon'
        ]);
    }
    
    // Calculate discount
    $discount = applyCoupon($code, $amount);
    
    if ($discount === false) {
        jsonResponse(['success' => false, 'message' => 'Failed to apply coupon']);
    }
    
    // Store coupon in session
    $_SESSION['applied_coupon'] = [
        'code' => $code,
        'discount' => $discount,
        'type' => $coupon['type'],
        'value' => $coupon['value']
    ];
    
    jsonResponse([
        'success' => true,
        'message' => 'Coupon applied successfully',
        'discount' => $discount,
        'formatted_discount' => formatPrice($discount),
        'coupon' => [
            'code' => $code,
            'type' => $coupon['type'],
            'value' => $coupon['value']
        ]
    ]);
}

function removeCouponCode() {
    if (isset($_SESSION['applied_coupon'])) {
        unset($_SESSION['applied_coupon']);
        jsonResponse(['success' => true, 'message' => 'Coupon removed']);
    } else {
        jsonResponse(['success' => false, 'message' => 'No coupon to remove']);
    }
}
?>
