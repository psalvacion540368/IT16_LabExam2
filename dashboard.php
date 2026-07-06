<?php
// dashboard.php
require_once 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Access Control & Session Expiry Check (15 Mins)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 900)) {
    session_unset();
    session_destroy();
    header("Location: login.php?reason=timeout");
    exit;
}
$_SESSION['last_activity'] = time();

// Fetch normalized data using JOIN
$query = "SELECT s.id, s.student_id, s.first_name, s.last_name, c.course_name 
          FROM students s 
          LEFT JOIN courses c ON s.course_id = c.id 
          ORDER BY s.id DESC";
$students = $pdo->query($query)->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="box slide-up">
        <header class="dashboard-header">
            <h1>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </header>

        <main class="content-panel">
            <div class="action-bar">
                <h3>Student Roster</h3>
                <a href="add_student.php" class="btn btn-success">+ Add New Student</a>
            </div>

            <table class="data-table">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Course</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['student_id']) ?></td>
                                <td><?= htmlspecialchars($student['first_name']) ?></td>
                                <td><?= htmlspecialchars($student['last_name']) ?></td>
                                <td><?= htmlspecialchars($student['course_name'] ?? 'Unassigned') ?></td>
                                <td>
                                    <a href="delete_student.php?id=<?= urlencode($student['id']) ?>&csrf_token=<?= $_SESSION['csrf_token'] ?>" 
                                       class="btn-sm btn-delete" 
                                       onclick="return confirm('Are you sure?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">No student records found securely.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <?php if (isset($_SESSION['popup_msg'])): ?>
    <div id="toast-popup" class="toast-card <?= $_SESSION['popup_type'] === 'success' ? 'toast-success' : 'toast-error' ?>">
        <div class="toast-content">
            <span class="toast-icon"><?= $_SESSION['popup_type'] === 'success' ? '✅' : '❌' ?></span>
            <p class="toast-text"><?= htmlspecialchars($_SESSION['popup_msg']) ?></p>
        </div>
    </div>

    <script>
        setTimeout(() => {
            const popup = document.getElementById('toast-popup');
            if (popup) {
                popup.style.animation = 'slideOut 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards';
                setTimeout(() => popup.remove(), 400);
            }
        }, 3500); // Popup stays visible for 3.5 seconds
    </script>
<?php 
    // Securely clear flash data so it won't repeat on reload
    unset($_SESSION['popup_msg']); 
    unset($_SESSION['popup_type']); 
endif; 
?>
</body>
</html>