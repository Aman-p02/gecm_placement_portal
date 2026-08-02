<?php
session_start();
require_once 'includes/db_connect.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$email = '';

if (empty($token)) {
    $error = "Invalid or missing reset token.";
} else {
    // Validate token
    $stmt = $pdo->prepare("SELECT email, expires_at FROM tbl_password_resets WHERE token = ? AND role = 'student'");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();

    if ($resetRequest) {
        if (strtotime($resetRequest['expires_at']) > time()) {
            $validToken = true;
            $email = $resetRequest['email'];
        } else {
            $error = "This password reset link has expired. Please request a new one.";
        }
    } else {
        $error = "Invalid password reset link.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $validToken) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic server-side validation
    $pwdPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/';
    
    if (empty($password) || empty($confirm_password)) {
        $error = "Both password fields are required.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (!preg_match($pwdPattern, $password)) {
        $error = "Password must have at least 8 characters, including upper, lower, number, and special character.";
    } else {
        // Hash and update password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        
        $updateStmt = $pdo->prepare("UPDATE tbl_students SET password = ? WHERE email = ?");
        if ($updateStmt->execute([$hashedPassword, $email])) {
            // Delete the token so it can't be used again
            $delStmt = $pdo->prepare("DELETE FROM tbl_password_resets WHERE token = ?");
            $delStmt->execute([$token]);
            
            $success = "Your password has been successfully reset. You can now <a href='login.php' class='alert-link'>Login</a>.";
            $validToken = false; // Hide form
        } else {
            $error = "An error occurred while updating your password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GEC Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #1B365D;
            --accent-coral: #E65A4B;
        }
        body {
            background-color: #f4f6f9;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        .auth-header {
            background: var(--primary-navy);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .btn-accent {
            background-color: var(--accent-coral);
            color: white;
            font-weight: 500;
        }
        .btn-accent:hover {
            background-color: #d94a3b;
            color: white;
        }
        .form-control { background-color: #fcfcfc; border: 1.5px solid #b0b8c1; padding: 0.6rem 1rem; border-radius: 8px; }
        .form-control:focus { border-color: var(--accent-coral); box-shadow: 0 0 0 0.25rem rgba(230, 90, 75, 0.25); background-color: #ffffff; }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <h3 class="m-0"><i class="fa-solid fa-graduation-cap me-2"></i>GEC Placement</h3>
            <p class="m-0 mt-2 text-white-50">Student Portal</p>
        </div>
        <div class="p-4">
            <h5 class="mb-4 text-center text-dark">Create New Password</h5>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success py-2 small"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($validToken): ?>
            <form action="reset_password.php?token=<?= htmlspecialchars($token) ?>" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-medium">New Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control pe-5" id="password" name="password" required>
                        <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="cursor: pointer; z-index:10;" onclick="togglePassword('password', this)"></i>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Must have 8+ chars, upper, lower, number & special char.</small>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-medium">Confirm New Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control pe-5" id="confirm_password" name="confirm_password" required>
                        <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="cursor: pointer; z-index:10;" onclick="togglePassword('confirm_password', this)"></i>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100">Reset Password</button>
            </form>
            <?php elseif (!$success): ?>
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="btn btn-outline-secondary w-100">Request New Link</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
</body>
</html>
