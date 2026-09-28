<?php
// includes/header.php - Global Header & Responsive Navigation
require_once __DIR__ . '/auth.php';

$current_page = basename($_SERVER['PHP_SELF']);
$logged_in    = isset($_SESSION['user_id']);
$user_role    = $_SESSION['role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Warehouse Management') ?> - LogiTrack</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="brand">
        📦 <span>LogiTrack</span> Warehouse
    </a>
    <div class="nav-links">
        <?php if ($logged_in): ?>
            <a href="dashboard.php" class="<?= $current_page === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="inventory.php" class="<?= $current_page === 'inventory.php' ? 'active' : '' ?>">Inventory</a>
            <a href="stock_history.php" class="<?= $current_page === 'stock_history.php' ? 'active' : '' ?>">Audit Trail</a>
            
            <?php if ($user_role === 'admin'): ?>
                <a href="suppliers.php" class="<?= $current_page === 'suppliers.php' ? 'active' : '' ?>">Suppliers</a>
                <a href="admin_users.php" class="<?= $current_page === 'admin_users.php' ? 'active' : '' ?>">Staff Roles</a>
            <?php endif; ?>

            <span style="color: var(--text-muted); font-size: 0.85em; margin-left: 10px;">
                👤 <?= htmlspecialchars($_SESSION['username']) ?>
                <span class="role-badge role-<?= $user_role ?>"><?= strtoupper($user_role) ?></span>
            </span>
            <a href="../actions/auth.php?action=logout" style="color: #ef4444;">Logout</a>
        <?php else: ?>
            <a href="login.php" class="<?= $current_page === 'login.php' ? 'active' : '' ?>">Login</a>
            <a href="register.php" class="<?= $current_page === 'register.php' ? 'active' : '' ?>">Register Staff</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container">
    <?php if (!empty($_GET['msg'])): ?>
        <div class="msg-alert success"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>
    <?php if (!empty($_GET['error'])): ?>
        <div class="msg-alert error"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>
