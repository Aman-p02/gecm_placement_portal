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
