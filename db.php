<?php
// db.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'student_system');

try {
    // Enable secure PDO connection with error exceptions and disabled emulated prepares
    $pdo = new PDO("mysql:host=" . DB_HOST, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // Create database and normalized tables if they do not exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $pdo->exec("USE " . DB_NAME);

    // 1. Users Table (With strong password hashing storage)
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

    // 2. Courses Table (Normalization: Removing redundant text string entries)
    $pdo->exec("CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB;");

    // 3. Students Table (Relational mapping with Foreign Keys)
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(20) NOT NULL UNIQUE,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        course_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE RESTRICT
    ) ENGINE=InnoDB;");

    // Seed default admin securely if empty (Password: admin123)
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    if ($stmt->fetchColumn() == 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
        $seed = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $seed->execute(['admin', $hash]);
        
        // Seed some sample courses
        $pdo->exec("INSERT IGNORE INTO courses (course_name) VALUES ('BS in Computer Science'), ('BS in Information Technology'), ('BS in Software Engineering')");
    }

} catch (PDOException $e) {
    // Fail silently to user to prevent Information Disclosure vulnerabilities
    error_log("Database Error: " . $e->getMessage());
    die("A system error occurred. Please try again later.");
}
?>