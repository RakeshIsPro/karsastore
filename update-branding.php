<?php
// Bulk branding update script
echo "<h2>Updating Branding from YBT Digital to Shishir Basnet</h2>";

$files = [
    'admin/products.php',
    'admin/orders.php', 
    'admin/users.php',
    'admin/categories.php',
    'admin/coupons.php',
    'admin/settings.php',
    'product.php',
    'profile.php',
    'support.php',
    'cart.php',
    'checkout.php',
    'payment.php',
    'order-success.php',
    'orders.php',
    'README.md',
    'SETUP.md',
    'sample-data.php',
    'update-database.php'
];

$replacements = [
    'YBT Digital' => 'Shishir Basnet',
    'YBT Admin' => 'Shishir Admin',
    'admin@ybtdigital.com' => 'admin@shishirbasnet.com',
    'ybt_digital' => 'shishir_basnet'
];

$totalReplacements = 0;

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<h3>Processing: $file</h3>";
        $content = file_get_contents($file);
        $originalContent = $content;
        
        foreach ($replacements as $search => $replace) {
            $count = 0;
            $content = str_replace($search, $replace, $content, $count);
            if ($count > 0) {
                echo "<p style='color: green;'>✓ Replaced '$search' with '$replace' ($count times)</p>";
                $totalReplacements += $count;
            }
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "<p style='color: blue;'>✓ File updated successfully</p>";
        } else {
            echo "<p style='color: gray;'>- No changes needed</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ File not found: $file</p>";
    }
    echo "<hr>";
}

echo "<h3 style='color: green;'>Branding Update Complete!</h3>";
echo "<p>Total replacements made: <strong>$totalReplacements</strong></p>";
echo "<p><a href='index.php'>Go to Homepage</a> | <a href='admin/'>Go to Admin Panel</a></p>";
?>
