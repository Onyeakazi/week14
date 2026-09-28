<?php
// actions/auth.php - Consolidated Auth Processor (Login, Register & Logout)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// 1. Handle Logout via GET or POST
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location: ../pages/login.php?msg=" . urlencode("You have been signed out safely."));
    exit;
}

// All mutation requests require POST and valid CSRF
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/login.php");
    exit;
}

if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("SECURITY VIOLATION: CSRF token validation failed.");
}

$action = $_POST['action'] ?? '';

// 2. Staff Registration
if ($action === 'register') {
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_DEFAULT) ?? '');
    $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (empty($username) || !$email || strlen($password) < 8) {
        header("Location: ../pages/register.php?error=" . urlencode("Validation error: Username, valid email, and 8+ char password required."));
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->execute([$username, $email]);
    if ($check->fetch()) {
        header("Location: ../pages/register.php?error=" . urlencode("Username or email address already registered."));
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, 'staff')");
    $stmt->execute([$username, $email, $hash]);

    header("Location: ../pages/login.php?msg=" . urlencode("Staff account created successfully! Please sign in."));
    exit;
}

// 3. Staff Login
if ($action === 'login') {
    $email    = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email || empty($password)) {
        header("Location: ../pages/login.php?error=" . urlencode("Please provide both email and password."));
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, username, password_hash, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];
        header("Location: ../pages/dashboard.php");
        exit;
    }

    header("Location: ../pages/login.php?error=" . urlencode("Invalid email or password credentials."));
    exit;
}

header("Location: ../pages/login.php");
exit;
