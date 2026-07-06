<?php
// login.php
require_once 'db.php';

// Secure Session Configuration
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        // Prepared Statement to defeat SQL Injection
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Safe verification timing mitigating side-channel attacks
    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {            // Prevent Session Fixation
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['last_activity'] = time();

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }

        // Inside your login.php POST handling:
if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['last_activity'] = time();

    // Set the success flash message
    $_SESSION['popup_msg'] = "Login successful! Welcome back.";
    $_SESSION['popup_type'] = "success";

    header("Location: dashboard.php");
    exit;
} else {
    // Set the error flash message
    $_SESSION['popup_msg'] = "Invalid username or password.";
    $_SESSION['popup_type'] = "error";
}
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

<style>
h2 {text-align: center;}
</style>

    <meta charset="UTF-8">
    <title>Secure Login System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="animated-bg">
    <div class="login-container box fade-in">
        <h2>System Login</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form action="login.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required autocomplete="off">
    </div>
    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <button type="submit" class="btn btn-primary pulse-hover">Login</button>
    
    <div class="form-footer" style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem;">
        <span style="color: #94a3b8;">Create a New Account?</span>
        <div style="margin-top: 0.5rem;">
            <a href="register.php" class="register-link" style="color: #10b981; text-decoration: none; font-weight: 600; transition: color 0.2s ease;">
                Register
            </a>
        </div>
    </div>
</form>
    </div>
    
</body>
</html>