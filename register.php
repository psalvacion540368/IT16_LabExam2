<?php
// register.php - Secure Registration Utility
require_once 'db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "All fields are required.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        try {
            // Securely hash the password using BCRYPT
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Insert into database using Prepared Statements
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$username, $hashed_password]);

            $message = "Registration successful! You can now log in.";
        } catch (PDOException $e) {
            // Check for duplicate username error
            if ($e->getCode() == 23000) {
                $error = "Username already exists.";
            } else {
                $error = "An error occurred during registration.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Registration</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="animated-bg">
    <div class="login-container box fade-in">
        <h2>Create Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($message): ?>
            <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.2); border: 1px solid var(--success); color: #a7f3d0;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        
        <form action="register.php" method="POST">
            <div class="form-group">
                <label for="username">Desired Username</label>
                <input type="text" name="username" id="username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
                    <span style="color: #94a3b8;">You have an Account?</span>

            <button type="submit" class="btn btn-success pulse-hover">Register User</button>
            
            <div class="form-footer" style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem;">
                <a href="login.php" style="color: var(--primary); text-decoration: none; font-weight: 600;">Go Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>