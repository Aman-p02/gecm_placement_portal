<?php
/**
 * Student Login Page
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';

// Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if (isset($_SESSION['signup_success'])) {
    $success = $_SESSION['signup_success'];
    unset($_SESSION['signup_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token'] ?? '');

    $enrollmentNo = sanitize_input($_POST['enrollment_no'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($enrollmentNo) || empty($password)) {
        $error = "Please enter both Enrollment Number and Password.";
    } else {
        $stmt = $pdo->prepare("SELECT student_id, enrollment_no, password, is_blocked FROM tbl_students WHERE enrollment_no = ?");
        $stmt->execute([$enrollmentNo]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['is_blocked']) {
                $error = "Your account has been blocked by an administrator. Please contact your department.";
            } else {
                // Login success: Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['student_id'] = $user['student_id'];
                $_SESSION['enrollment_no'] = $user['enrollment_no'];

                header("Location: dashboard.php");
                exit;
            }
        } else {
            // Generic error message for security
            $error = "Invalid Enrollment Number or Password.";
        }
    }
}
$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GEC Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="container auth-container">
        <div class="custom-card">
            <h2 class="text-center mb-4 brand-text">GEC Modasa <span>Placement</span></h2>
            <h5 class="text-center mb-4 text-muted">Student Login</h5>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="mb-3">
                    <label for="enrollment_no" class="form-label">Enrollment Number</label>
                    <input type="text" class="form-control" id="enrollment_no" name="enrollment_no" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control pe-5" id="password" name="password" required>
                        <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="cursor: pointer;" onclick="togglePassword('password', this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100">Login</button>

                <div class="mt-3 text-center">
                    <span class="text-muted">Don't have an account?</span> <a href="signup.php"
                        style="color: var(--accent-coral);">Create one</a>
                </div>
            </form>
        </div>
    </div>
</body>
<script>
function togglePassword(inputId, icon) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
</html>