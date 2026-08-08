<?php
require_once __DIR__ . '/includes/db_connect.php';
session_start();

$message = '';
$messageType = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = $_GET['token'];

    // Find student by token
    $stmt = $pdo->prepare("SELECT student_id, is_verified FROM tbl_students WHERE verification_token = ?");
    $stmt->execute([$token]);
    $student = $stmt->fetch();

    if ($student) {
        if ($student['is_verified'] == 1) {
            $message = "Your account is already verified. You can log in.";
            $messageType = 'info';
        } else {
            // Update the student
            $updateStmt = $pdo->prepare("UPDATE tbl_students SET is_verified = 1, verification_token = NULL WHERE student_id = ?");
            if ($updateStmt->execute([$student['student_id']])) {
                $message = "Success! Now you can login into your account.";
                $messageType = 'success';
            } else {
                $message = "An error occurred while verifying your account. Please try again later.";
                $messageType = 'danger';
            }
        }
    } else {
        $message = "Invalid or expired verification link.";
        $messageType = 'danger';
    }
} else {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - GEC Modasa Placement Portal</title>
    <link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="container auth-container mt-5">
        <div class="custom-card text-center py-5 px-4" style="max-width: 500px; margin: 0 auto;">
            <h4 class="mb-4 brand-text">GEC Modasa <span>Placement</span></h4>
            
            <?php if ($messageType === 'success'): ?>
                <i class="fa-solid fa-circle-check text-success" style="font-size: 60px; margin-bottom: 20px;"></i>
                <h3 class="mb-3">Account Verified</h3>
                <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                <a href="login.php" class="btn btn-accent px-4 py-2">Go to Login</a>
            <?php elseif ($messageType === 'info'): ?>
                <i class="fa-solid fa-circle-info text-info" style="font-size: 60px; margin-bottom: 20px;"></i>
                <h3 class="mb-3">Already Verified</h3>
                <p class="text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                <a href="login.php" class="btn btn-accent px-4 py-2">Go to Login</a>
            <?php else: ?>
                <i class="fa-solid fa-circle-xmark text-danger" style="font-size: 60px; margin-bottom: 20px;"></i>
                <h3 class="mb-3">Verification Failed</h3>
                <p class="text-danger mb-4"><?= htmlspecialchars($message) ?></p>
                <a href="signup.php" class="btn btn-outline-secondary px-4 py-2">Back to Signup</a>
            <?php endif; ?>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
</body>
</html>
