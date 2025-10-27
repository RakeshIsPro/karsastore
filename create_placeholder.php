<?php
// Create placeholder images for the demo
$directories = [
    'assets/images/',
    'assets/images/products/'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Create main placeholder
$img = imagecreate(400, 300);
$bg = imagecolorallocate($img, 240, 240, 240);
$text_color = imagecolorallocate($img, 100, 100, 100);
$border_color = imagecolorallocate($img, 200, 200, 200);

imagerectangle($img, 0, 0, 399, 299, $border_color);
imagestring($img, 5, 150, 140, 'Product Image', $text_color);
imagejpeg($img, 'assets/images/placeholder.jpg');
imagedestroy($img);

// Create product placeholders
$products = [
    'template-bundle.jpg',
    'logo-pack.jpg', 
    'react-native-app.jpg',
    'wp-ecommerce-theme.jpg',
    'marketing-guide.jpg',
    'project-management.jpg',
    'social-media-pack.jpg',
    'flutter-ui-kit.jpg'
];

foreach ($products as $product) {
    $img = imagecreate(400, 300);
    $bg = imagecolorallocate($img, 240, 240, 240);
    $text_color = imagecolorallocate($img, 100, 100, 100);
    $border_color = imagecolorallocate($img, 200, 200, 200);
    
    imagerectangle($img, 0, 0, 399, 299, $border_color);
    imagestring($img, 3, 160, 140, 'Product', $text_color);
    imagestring($img, 3, 170, 160, 'Image', $text_color);
    imagejpeg($img, 'assets/images/products/' . $product);
    imagedestroy($img);
}

echo "Placeholder images created successfully!";
?>
