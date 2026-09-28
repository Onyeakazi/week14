<?php
// includes/auth.php - Unified Security, Session Guards & CSRF Tokens
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ensures user is authenticated.
 */
function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?error=" . urlencode("Please sign in to access the warehouse system."));
        exit;
    }
}

/**
 * Ensures user has administrator privileges.
 */
function require_admin(): void {
    require_login();
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header("Location: dashboard.php?error=" . urlencode("Access Denied: Administrative privileges required."));
        exit;
    }
}

/**
 * CSRF Token Helpers
 */
function generate_csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): void {
    $token = generate_csrf_token();
    echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function verify_csrf_token(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
