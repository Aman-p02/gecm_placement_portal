<?php
/**
 * Admin Authentication & Security Core
 */

session_start();

function require_admin_login() {
    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function require_superadmin() {
    require_admin_login();
    if ($_SESSION['admin_role'] !== 'superadmin') {
        die("Access Denied: Super Admin privileges required.");
    }
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die("CSRF Token Validation Failed.");
    }
}

function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
