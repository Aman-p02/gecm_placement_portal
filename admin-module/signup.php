<?php
/**
 * Admin Signup Page (For Sub Admins)
 */
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/admin_auth_check.php';
require_once __DIR__ . '/../includes/mailer.php';

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
                
                // Send registration email
                sendRegistrationEmail($email, $fullName, $role);
                
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
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body class="auth-page">
    <div class="container auth-container">
        <div class="custom-card auth-styled border-top border-4 border-warning">
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
                        <option value="Automobile Engineering" <?= (isset($branch) && $branch == 'Automobile Engineering') ? 'selected' : '' ?>>Automobile Engineering</option>
                        <option value="Civil Engineering" <?= (isset($branch) && $branch == 'Civil Engineering') ? 'selected' : '' ?>>Civil Engineering</option>
                        <option value="Computer Engineering" <?= (isset($branch) && $branch == 'Computer Engineering') ? 'selected' : '' ?>>Computer Engineering</option>
                        <option value="Electrical Engineering" <?= (isset($branch) && $branch == 'Electrical Engineering') ? 'selected' : '' ?>>Electrical Engineering</option>
                        <option value="Electronics & Communication" <?= (isset($branch) && $branch == 'Electronics & Communication') ? 'selected' : '' ?>>Electronics & Communication</option>
                        <option value="Information Technology" <?= (isset($branch) && $branch == 'Information Technology') ? 'selected' : '' ?>>Information Technology</option>
                        <option value="Mechanical Engineering" <?= (isset($branch) && $branch == 'Mechanical Engineering') ? 'selected' : '' ?>>Mechanical Engineering</option>
                    </select>
                </div>

                <div class="row g-2 mb-4">
                    <div class="col-sm-6">
                        <label class="form-label small">Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" name="password" id="signup_password" required>
                            <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="cursor: pointer; z-index: 10;" onclick="togglePassword('signup_password', this)"></i>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="form-label small">Confirm Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control pe-5" name="confirm_password" id="signup_confirm_password" required>
                            <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-3 text-muted" style="cursor: pointer; z-index: 10;" onclick="togglePassword('signup_confirm_password', this)"></i>
                        </div>
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


