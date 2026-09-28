<?php
// pages/login.php - Staff Login Form View
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Staff Login";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 440px; margin: 40px auto;" class="card">
    <h2 style="text-align: center; margin-bottom: 20px;">Warehouse Login</h2>
    <p style="text-align: center; margin-bottom: 25px; color: var(--text-muted);">Sign in to manage stock and inventory shipments.</p>

    <form action="../actions/auth.php" method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="login">

        <div class="form-group">
            <label for="email">Staff Work Email</label>
            <input type="email" id="email" name="email" class="form-control" required placeholder="admin@warehouse.local">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Sign In to LogiTrack</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
