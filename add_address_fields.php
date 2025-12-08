<?php
require_once 'includes/config.php';

try {
    // Add address fields to users table
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS address TEXT");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS city VARCHAR(50)");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS state VARCHAR(50)");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS zip_code VARCHAR(10)");
    
    echo "Address fields added successfully!";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>