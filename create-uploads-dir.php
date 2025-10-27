<?php
// Create uploads directory structure
$directories = [
    'uploads',
    'uploads/products',
    'uploads/users',
    'uploads/temp'
];

echo "<h2>Creating Upload Directories</h2>";

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: green;'>✓ Created directory: $dir</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create directory: $dir</p>";
        }
    } else {
        echo "<p style='color: blue;'>✓ Directory already exists: $dir</p>";
    }
}

// Create .htaccess for uploads directory
$htaccessContent = "# Prevent direct access to uploaded files
<Files *.php>
    Order Allow,Deny
    Deny from all
</Files>

# Allow image files
<FilesMatch \"\.(jpg|jpeg|png|gif|webp)$\">
    Order Allow,Deny
    Allow from all
</FilesMatch>";

if (!file_exists('uploads/.htaccess')) {
    if (file_put_contents('uploads/.htaccess', $htaccessContent)) {
        echo "<p style='color: green;'>✓ Created .htaccess for uploads security</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create .htaccess</p>";
    }
} else {
    echo "<p style='color: blue;'>✓ .htaccess already exists</p>";
}

echo "<h3 style='color: green;'>Upload directories setup completed!</h3>";
echo "<p><a href='admin/products.php'>Go to Products Management</a></p>";
?>
