<?php
// delete_student.php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

// Enforce CSRF token verification on state mutations over GET parameters
$token = $_GET['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'], $token)) {
    die("Security verification code mismatch.");
}

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);

if ($id) {
    $stmt = $pdo->prepare("DELETE FROM students WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: dashboard.php");
exit;