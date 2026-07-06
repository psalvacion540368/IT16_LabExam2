<?php
// add_student.php
require_once 'db.php';
if (session_status() == PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$errors = [];
$courses = $pdo->query("SELECT * FROM courses")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF validation failure.");
    }

    // Input Sanitization and Validation Pipeline
    $student_id = trim($_POST['student_id'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $course_id  = filter_var($_POST['course_id'] ?? '', FILTER_VALIDATE_INT);

    if (!preg_match('/^[a-zA-Z0-9-]+$/', $student_id)) {
        $errors[] = "Invalid Student ID alphanumeric format.";
    }
    if (empty($first_name) || empty($last_name)) {
        $errors[] = "Names cannot be empty spaces.";
    }
    if (!$course_id) {
        $errors[] = "Please select a valid course option.";
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, course_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$student_id, $first_name, $last_name, $course_id]);
            header("Location: dashboard.php");
            exit;
        } catch (PDOException $e) {
            $errors[] = "Error processing data: Duplicated Student ID entry discovered.";
        }
    }

    // Inside your add_student.php after execution success:
if (empty($errors)) {
    try {
        $stmt = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, course_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$student_id, $first_name, $last_name, $course_id]);

        // Set the success notification
        $_SESSION['popup_msg'] = "Student record added successfully!";
        $_SESSION['popup_type'] = "success";

        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['popup_msg'] = "Error: Duplicated Student ID entry discovered.";
        $_SESSION['popup_type'] = "error";
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-container box slide-up">
        <h2>Add Student Record</h2>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
        <?php endif; ?>

        <form action="add_student.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <div class="form-group">
                <label>Student ID</label>
                <input type="text" name="student_id" required>
            </div>
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" required>
            </div>
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['course_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-success">Save</button>
            <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>