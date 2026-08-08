<?php
session_start();
require_once '../student-module/includes/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if admin exists
        $stmt = $pdo->prepare("SELECT admin_id, full_name FROM tbl_admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin) {
            // Generate a secure reset token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store in database
            $stmt = $pdo->prepare("INSERT INTO tbl_password_resets (email, token, role, expires_at) VALUES (?, ?, 'admin', ?)");
            $stmt->execute([$email, $token, $expires_at]);

            require_once '../includes/mailer.php';
            $resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $token;

            if (sendResetEmail($email, $resetLink)) {
                $success = "A password reset link has been sent to your email. Please check your inbox (and spam folder).";
            } else {
                $error = "Failed to send the reset email. Please ensure the mailer is configured properly.";
            }
        } else {
            // We don't want to leak whether an email exists or not for security, but we will for UX here.
            $error = "No admin found with that email address.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forgot Password - GEC Modasa Placement</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>

<body class="auth-page">
    <div class="auth-card">
        <div class="auth-header">
            <h3 class="m-0"><i class="fa-solid fa-building-columns me-2"></i>GEC Placement</h3>
            <p class="m-0 mt-2 text-white-50">Admin Portal</p>
        </div>
        <div class="p-4">
            <h5 class="mb-3 text-center text-dark">Forgot Password</h5>
            <p class="text-muted text-center small mb-4">Enter your registered email address and we'll send you a link
                to reset your password.</p>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success py-2 small"><?= $success // Allowed HTML for demo link ?></div>
            <?php endif; ?>

            <form action="forgot_password.php" method="POST">
                <div class="mb-4">
                    <label class="form-label small fw-medium">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i
                                class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control border-start-0" name="email" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100 py-2 mb-3">Send Reset Link</button>

                <div class="text-center">
                    <a href="login.php" class="small text-decoration-none text-muted"><i
                            class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
                </div>
            </form>
        </div>
    </div>

<?php include 'includes/footer.php'; ?>
</body>

</html>

