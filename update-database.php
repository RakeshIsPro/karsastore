<?php
// Database update script to add missing columns to categories table
require_once 'config/database.php';

try {
    echo "<h2>YBT Digital - Database Update Script</h2>";
    
    // Check if parent_id column exists
    $result = $pdo->query("SHOW COLUMNS FROM categories LIKE 'parent_id'");
    if ($result->rowCount() == 0) {
        echo "<p>Adding parent_id column to categories table...</p>";
        $pdo->exec("ALTER TABLE categories ADD COLUMN parent_id INT DEFAULT NULL AFTER image");
        $pdo->exec("ALTER TABLE categories ADD CONSTRAINT fk_categories_parent FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL");
        echo "<p style='color: green;'>✓ parent_id column added successfully!</p>";
    } else {
        echo "<p style='color: blue;'>✓ parent_id column already exists</p>";
    }
    
    // Check if sort_order column exists
    $result = $pdo->query("SHOW COLUMNS FROM categories LIKE 'sort_order'");
    if ($result->rowCount() == 0) {
        echo "<p>Adding sort_order column to categories table...</p>";
        $pdo->exec("ALTER TABLE categories ADD COLUMN sort_order INT DEFAULT 0 AFTER parent_id");
        echo "<p style='color: green;'>✓ sort_order column added successfully!</p>";
    } else {
        echo "<p style='color: blue;'>✓ sort_order column already exists</p>";
    }
    
    // Check if updated_at column exists
    $result = $pdo->query("SHOW COLUMNS FROM categories LIKE 'updated_at'");
    if ($result->rowCount() == 0) {
        echo "<p>Adding updated_at column to categories table...</p>";
        $pdo->exec("ALTER TABLE categories ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
        echo "<p style='color: green;'>✓ updated_at column added successfully!</p>";
    } else {
        echo "<p style='color: blue;'>✓ updated_at column already exists</p>";
    }
    
    echo "<h3 style='color: green;'>Database update completed successfully!</h3>";
    echo "<p><a href='admin/categories.php'>Go to Categories Management</a></p>";
    echo "<p><a href='admin/'>Go to Admin Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error updating database:</h3>";
    echo "<p style='color: red;'>" . $e->getMessage() . "</p>";
}
?>
