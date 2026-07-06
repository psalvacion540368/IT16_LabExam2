<?php
// add_user.php - Secure, Authenticated User Provisioning
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }

// Access Control: ONLY logged-in administrators can create new accounts
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF validation failure.");
    }

    $new_username = trim($_POST['new_username'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    // Strict Validation
    if (strlen($new_username) < 4 || !preg_match('/^[a-zA-Z0-9_]+$/', $new_username)) {
        $errors[] = "Username must be at least 4 alphanumeric characters.";
    }
    if (strlen($new_password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    if (empty($errors)) {
        try {
            // Secure Password Hashing
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Prepared Statement to insert the new user safely
            $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->execute([$new_username, $hashed_password]);
            
            $success = "New administrator account created successfully!";
        } catch (PDOException $e) {
            $errors[] = "Username is already taken.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Administrator</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container glass-panel slide-up">
        <h2>Provision New Admin</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success" style="background: rgba(16, 185, 129, 0.2); border: 1px solid var(--success); color: #a7f3d0;"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form action="add_user.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
                <label>New Username</label>
                <input type="text" name="new_username" required autocomplete="off">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="new_password" required>
            </div>
            <button type="submit" class="btn btn-primary pulse-hover">Create Admin</button>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </form>
    </div>
</body>
</html>