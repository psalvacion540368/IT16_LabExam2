<?php
// index.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Route safely based on authentication state
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;