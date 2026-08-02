<?php
session_start();
require_once '../student-module/includes/db_connect.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';
$validToken = false;
$email = '';

if (empty($token)) {
    $error = "Invalid or missing reset token.";
} else {
    // Validate token
    $stmt = $pdo->prepare("SELECT email, expires_at FROM tbl_password_resets WHERE token = ? AND role = 'admin'");
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
        
        $updateStmt = $pdo->prepare("UPDATE tbl_admins SET password = ? WHERE email = ?");
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
    <title>Admin Reset Password - GEC Placement</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h3 class="m-0"><i class="fa-solid fa-building-columns me-2"></i>GEC Placement</h3>
            <p class="m-0 mt-2 text-white-50">Admin Portal</p>
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

                <button type="submit" class="btn btn-accent w-100 py-2">Reset Password</button>
            </form>
            <?php elseif (!$success): ?>
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="btn btn-outline-secondary w-100 py-2">Request New Link</a>
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


