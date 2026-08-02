<?php
/**
 * Authentication & Security Core
 * Handles session management, CSRF tokens, and auth checks.
 */

session_start();

/**
 * Ensures the user is logged in. Redirects to login page if not.
 */
function require_login() {
    if (!isset($_SESSION['student_id'])) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Generates a CSRF token and stores it in the session if it doesn't exist.
 * Returns the token for use in forms.
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validates a submitted CSRF token against the one stored in the session.
 */
function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die("CSRF Token Validation Failed.");
    }
}

/**
 * Sanitizes input to prevent XSS.
 */
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

/**
 * Ensures the student's profile is complete.
 * Redirects to dashboard with an error if incomplete.
 */
function require_profile_completion($pdo) {
    if (!isset($_SESSION['student_id'])) {
        return; // Handled by require_login()
    }
    $studentId = $_SESSION['student_id'];
    $stmt = $pdo->prepare("SELECT district, course, sem5_cpi, sem6_cpi, first_name, surname, father_name, mother_name FROM tbl_student_profile WHERE student_id = ?");
    $stmt->execute([$studentId]);
    $profile = $stmt->fetch();
    
    if (!$profile || empty($profile['district']) || empty($profile['course']) || empty($profile['sem5_cpi']) || empty($profile['first_name']) || empty($profile['surname']) || empty($profile['father_name']) || empty($profile['mother_name'])) {
        $_SESSION['dashboard_error'] = "Please complete your profile details before accessing Placement Drives.";
        header('Location: dashboard.php');
        exit;
    }
}

