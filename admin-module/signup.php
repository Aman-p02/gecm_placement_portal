<?php
/**
 * Admin Signup Page (For Sub Admins)
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token'] ?? '');

    $fullName = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $branch = sanitize_input($_POST['branch'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Basic Validation
    if (empty($fullName) || empty($email) || empty($phone) || empty($branch) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8) {
        $error = "Password must have at least 8 characters.";
    } else {
        // Check for duplicate email
        $stmt = $pdo->prepare("SELECT admin_id FROM tbl_admins WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "An admin account with this email already exists.";
        } else {
            // Insert new sub-admin
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $role = 'subadmin';
            
            $stmt = $pdo->prepare("INSERT INTO tbl_admins (full_name, email, phone_number, password, role, branch) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt->execute([$fullName, $email, $phone, $hashedPassword, $role, $branch])) {
                $_SESSION['signup_success'] = "Sub Admin Registration successful! You can now login.";
                header("Location: login.php");
                exit;
            } else {
                $error = "A database error occurred during registration.";
            }
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
    <title>Admin Signup - GEC Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-navy: #1B365D;
            --accent-coral: #E65A4B;
        }
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .auth-container { max-width: 500px; width: 100%; padding: 15px; }
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
            <h6 class="text-center mb-3 text-muted">Register Sub-Admin Account</h6>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 mb-3 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form action="signup.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="mb-3">
                    <label class="form-label small">Full Name</label>
                    <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($fullName ?? '') ?>" required>
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <label class="form-label small">Email Address</label>
                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small">Phone Number</label>
                        <input type="tel" class="form-control" name="phone" value="<?= htmlspecialchars($phone ?? '') ?>" pattern="[0-9]{10}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small">Department / Branch</label>
                    <select class="form-select" name="branch" required>
                        <option value="">Select Branch</option>
                        <option value="Computer Engineering" <?= (isset($branch) && $branch == 'Computer Engineering') ? 'selected' : '' ?>>Computer Engineering</option>
                        <option value="Information Technology" <?= (isset($branch) && $branch == 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                        <option value="Mechanical Engineering" <?= (isset($branch) && $branch == 'Mechanical Engineering') ? 'selected' : '' ?>>Mechanical Engineering</option>
                        <option value="Civil Engineering" <?= (isset($branch) && $branch == 'Civil Engineering') ? 'selected' : '' ?>>Civil Engineering</option>
                        <option value="Electrical Engineering" <?= (isset($branch) && $branch == 'Electrical Engineering') ? 'selected' : '' ?>>Electrical Engineering</option>
                    </select>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label small">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small">Confirm Password</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent w-100">Sign Up</button>

                <div class="mt-3 text-center">
                    <span class="text-muted small">Already have an account?</span> <a href="login.php" class="small fw-semibold" style="color: var(--accent-coral);">Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
