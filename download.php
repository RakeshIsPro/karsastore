<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Require login
requireLogin();

// Get item ID
$itemId = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

if (!$itemId) {
    header('Location: orders.php');
    exit;
}

// Get order item details
$stmt = $pdo->prepare("
    SELECT oi.*, o.user_id, o.payment_status, p.file_path, p.title, p.file_size
    FROM order_items oi 
    JOIN orders o ON oi.order_id = o.id 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.id = ?
");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    header('Location: orders.php');
    exit;
}

// Check if user owns this order
if ($item['user_id'] != $_SESSION['user_id']) {
    header('Location: orders.php');
    exit;
}

// Check if payment is completed
if ($item['payment_status'] != 'completed') {
    header('Location: orders.php?error=payment_pending');
    exit;
}

// Check if file exists
if (!$item['file_path'] || !file_exists($item['file_path'])) {
    header('Location: orders.php?error=file_not_found');
    exit;
}

// Check download expiry (if set)
$downloadExpiryDays = (int)getSetting('download_expiry_days', 0);
if ($downloadExpiryDays > 0 && $item['download_expires']) {
    if (strtotime($item['download_expires']) < time()) {
        header('Location: orders.php?error=download_expired');
        exit;
    }
}

// Update download count
$stmt = $pdo->prepare("UPDATE order_items SET download_count = download_count + 1 WHERE id = ?");
$stmt->execute([$itemId]);

// Update product download count
$stmt = $pdo->prepare("UPDATE products SET downloads_count = downloads_count + 1 WHERE id = ?");
$stmt->execute([$item['product_id']]);

// Log download activity
$stmt = $pdo->prepare("
    INSERT INTO download_logs (user_id, order_item_id, product_id, ip_address, user_agent, created_at) 
    VALUES (?, ?, ?, ?, ?, NOW())
");

// Create download logs table if it doesn't exist
$pdo->exec("CREATE TABLE IF NOT EXISTS download_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_item_id INT NOT NULL,
    product_id INT NOT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (order_item_id) REFERENCES order_items(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
)");

$stmt->execute([
    $_SESSION['user_id'],
    $itemId,
    $item['product_id'],
    $_SERVER['REMOTE_ADDR'] ?? '',
    $_SERVER['HTTP_USER_AGENT'] ?? ''
]);

// Prepare file for download
$filePath = $item['file_path'];
$fileName = basename($filePath);
$fileSize = $item['file_size'] ?: filesize($filePath);

// Clean filename for download
$downloadName = sanitizeFileName($item['title']) . '.' . pathinfo($fileName, PATHINFO_EXTENSION);

// Set headers for secure download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Prevent direct access to file path
header('X-Robots-Tag: noindex, nofollow');

// Stream file to user
if ($fileHandle = fopen($filePath, 'rb')) {
    while (!feof($fileHandle)) {
        echo fread($fileHandle, 8192);
        flush();
    }
    fclose($fileHandle);
} else {
    // File read error
    header('Location: orders.php?error=download_failed');
    exit;
}

function sanitizeFileName($filename) {
    // Remove or replace invalid characters
    $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
    $filename = preg_replace('/_{2,}/', '_', $filename);
    return trim($filename, '_');
}

exit;
?>
