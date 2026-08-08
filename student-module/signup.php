<?php
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/../includes/mailer.php';
// Redirect if already logged in
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf_token($_POST['csrf_token'] ?? '');

    $enrollmentNo = sanitize_input($_POST['enrollment_no'] ?? '');
    $fullName = sanitize_input($_POST['full_name'] ?? '');
    $branch = sanitize_input($_POST['branch'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    // Basic Server-side Validation
    if (empty($enrollmentNo) || empty($fullName) || empty($branch) || empty($email) || empty($phone) || empty($password)) {
        $error = "All fields are required.";
    } elseif (!preg_match('/^[0-9]{12}$/', $enrollmentNo)) {
        $error = "Invalid Enrollment Number. It must be exactly 12 digits.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $error = "Phone number must be exactly 10 digits.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W_]/', $password)) {
        $error = "Password must have at least 8 chars, including uppercase, lowercase, number, and special character.";
    } else {
        // Check for duplicate enrollment
        $stmt = $pdo->prepare("SELECT student_id FROM tbl_students WHERE enrollment_no = ?");
        $stmt->execute([$enrollmentNo]);
        if ($stmt->fetch()) {
            $error = "An account with this Enrollment Number already exists.";
        } else {
            // Insert new student
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $verificationToken = bin2hex(random_bytes(32));
            
            $stmt = $pdo->prepare("INSERT INTO tbl_students (enrollment_no, full_name, branch, email, phone_number, password, is_verified, verification_token) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
            if ($stmt->execute([$enrollmentNo, $fullName, $branch, $email, $phone, $hashedPassword, $verificationToken])) {
                
                // Construct verification link
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $domain = $_SERVER['HTTP_HOST'];
                $base_dir = dirname($_SERVER['PHP_SELF']);
                $verificationLink = $protocol . '://' . $domain . $base_dir . '/verify.php?token=' . $verificationToken;

                // Send verification email
                sendVerificationEmail($email, $fullName, $verificationLink);
                
                $_SESSION['signup_success'] = "Registration successful! Please check your email to activate your account.";
                header("Location: login.php");
                exit;
            } else {
                $error = "A database error occurred during registration.";
            }
        }
    }
}

// Fetch branches from database
$branchStmt = $pdo->query("SELECT branch_name AS branch FROM tbl_branches ORDER BY branch_name ASC");
$allBranches = $branchStmt->fetchAll(PDO::FETCH_COLUMN);

$csrfToken = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - GEC Modasa Placement Portal</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>

<body>
    <div class="container auth-container">
        <div class="custom-card">
            <h4 class="text-center mb-2 brand-text">GEC Modasa <span>Placement</span></h4>
            <h6 class="text-center mb-3 text-muted">Create Student Account</h6>

            <?php if ($error): ?>
                <div class="alert alert-danger py-1 mb-2 small"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form id="signupForm" action="signup.php" method="POST" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

                <div class="row g-2 mb-2">
                    <div class="col-sm-6">
                        <label for="enrollment_no" class="form-label mb-1 small">Enrollment Number</label>
                        <input type="text" class="form-control form-control-sm" id="enrollment_no" name="enrollment_no"
                            value="<?= htmlspecialchars($enrollmentNo ?? '') ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="full_name" class="form-label mb-1 small">Full Name</label>
                        <input type="text" class="form-control form-control-sm" id="full_name" name="full_name"
                            value="<?= htmlspecialchars($fullName ?? '') ?>" required>
                    </div>
                </div>

                <div class="row g-2 mb-2">
                    <div class="col-sm-6">
                        <label for="email" class="form-label mb-1 small">Email Address</label>
                        <input type="email" class="form-control form-control-sm" id="email" name="email"
                            value="<?= htmlspecialchars($email ?? '') ?>" required>
                    </div>
                    <div class="col-sm-6">
                        <label for="phone" class="form-label mb-1 small">Phone Number</label>
                        <input type="tel" class="form-control form-control-sm" id="phone" name="phone"
                            value="<?= htmlspecialchars($phone ?? '') ?>" pattern="[0-9]{10}" title="10 digit mobile number" required>
                    </div>
                </div>

                <div class="mb-2">
                    <label for="branch" class="form-label mb-1 small">Branch</label>
                    <select class="form-select form-select-sm" id="branch" name="branch" required>
                        <option value="">Select Branch</option>
                        <?php foreach ($allBranches as $bName): ?>
                            <option value="<?= htmlspecialchars($bName) ?>" <?= (isset($branch) && $branch == $bName) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($bName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-sm-6">
                        <label for="password" class="form-label mb-1 small">Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control form-control-sm pe-4" id="password" name="password" required>
                            <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-2 text-muted toggle-password" style="cursor: pointer; z-index: 10;"></i>
                        </div>
                        <div id="passwordFeedback" class="invalid-feedback">Must have 8+ chars, upper, lower, number & special char.</div>
                    </div>
                    <div class="col-sm-6">
                        <label for="confirm_password" class="form-label mb-1 small">Confirm Password</label>
                        <div class="position-relative">
                            <input type="password" class="form-control form-control-sm pe-4" id="confirm_password" name="confirm_password" required>
                            <i class="fa-solid fa-eye position-absolute top-50 end-0 translate-middle-y me-2 text-muted toggle-password" style="cursor: pointer; z-index: 10;"></i>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent btn-sm w-100">Sign Up</button>

                <div class="mt-2 text-center">
                    <span class="text-muted small">Already have an account?</span> <a href="login.php"
                        class="small fw-semibold" style="color: var(--accent-coral);">Login</a>
                </div>
            </form>
        </div>
    </div>

    <script src="js/validation.js"></script>

    
        


<?php include '../includes/footer.php'; ?>
</body>

</html>
