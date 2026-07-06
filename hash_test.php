<?php
// hash_test.php
require_once 'db.php';

try {
    // Force reset the admin user to a fresh BCRYPT hash
    $hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
    
    // Clear any broken existing admin
    $pdo->exec("DELETE FROM users WHERE username = 'admin'");
    
    // Insert fresh admin
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute(['admin', $hash]);
    
    echo "Successfully repaired admin account! Try logging in now with username 'admin' and password 'admin123'.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>