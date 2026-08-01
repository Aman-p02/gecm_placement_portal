<?php
/**
 * Admin Login Page
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
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

    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Email and Password are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM tbl_admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            // Setup Session
            $_SESSION['admin_id'] = $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['full_name'];
            $_SESSION['admin_role'] = $admin['role'];
            $_SESSION['admin_branch'] = $admin['branch']; // null for superadmin

            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid email or password.";
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
    <title>Admin Login - GEC Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #1B365D;
            --accent-coral: #E65A4B;
        }
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .auth-container { max-width: 400px; width: 100%; padding: 15px; }
        .custom-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .btn-accent { background-color: var(--accent-coral); color: white; font-weight: 500; }
        .btn-accent:hover { background-color: #d14d3f; color: white; }
        .brand-text { color: var(--primary-navy); font-weight: 700; }
        .brand-text span { color: var(--accent-coral); }
    </style>
</head>
<body>
    <div class="container auth-container">
        <div class="custom-card border-top border-4 border-warning">
            <h4 class="text-center mb-2 brand-text">GEC Modasa <span>Admin</span></h4>
            <h6 class="text-center mb-4 text-muted">Login to Admin Panel</h6>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success py-2 mb-3 small"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                
                <div class="mb-3">
                    <label class="form-label small">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-envelope text-muted"></i></span>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label small">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-lock text-muted"></i></span>
                        <input type="password" class="form-control" name="password" id="admin_password" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('admin_password', this)">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100 py-2">Login</button>
                
                <div class="mt-4 text-center">
                    <span class="text-muted small">New Sub-Admin?</span> <a href="signup.php" class="small fw-semibold" style="color: var(--accent-coral);">Create Account</a>
                </div>
            </form>
        </div>
    </div>
</body>
<script>
function togglePassword(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
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
