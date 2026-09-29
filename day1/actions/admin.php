<?php
// actions/admin.php - Handles Role Toggling and Supplier Creation (Admin Only)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin(); // Strict Admin Guard

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("SECURITY VIOLATION: Unauthorized action or CSRF verification failed.");
}

$action = $_POST['action'] ?? '';

// 1. Toggle Staff Role (Staff <-> Admin)
if ($action === 'toggle_role') {
    $target_user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $current_role   = $_POST['current_role'] ?? '';

    // Self-Demotion Defense
    if ($target_user_id == $_SESSION['user_id']) {
        header("Location: ../pages/admin_users.php?error=" . urlencode("You cannot modify your own administrative role."));
        exit;
    }

    if ($target_user_id && in_array($current_role, ['staff', 'admin'], true)) {
        $new_role = ($current_role === 'admin') ? 'staff' : 'admin';
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$new_role, $target_user_id]);
    }

    header("Location: ../pages/admin_users.php?msg=" . urlencode("Staff privilege updated successfully."));
    exit;
}

// 2. Register New Supplier
if ($action === 'add_supplier') {
    $name          = trim($_POST['name'] ?? '');
    $contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_VALIDATE_EMAIL);
    $phone         = trim($_POST['phone'] ?? '');

    if (empty($name) || !$contact_email || empty($phone)) {
        header("Location: ../pages/suppliers.php?error=" . urlencode("Please provide vendor company, valid email, and phone."));
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO suppliers (name, contact_email, phone) VALUES (?, ?, ?)");
    $stmt->execute([$name, $contact_email, $phone]);

    header("Location: ../pages/suppliers.php?msg=" . urlencode("Supplier '{$name}' registered successfully."));
    exit;
}

header("Location: ../pages/dashboard.php");
exit;
