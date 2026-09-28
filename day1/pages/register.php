<?php
// pages/register.php - Staff Registration Form View
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$page_title = "Staff Onboarding";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 480px; margin: 40px auto;" class="card">
    <h2 style="text-align: center; margin-bottom: 20px;">Staff Onboarding</h2>
    <p style="text-align: center; margin-bottom: 25px; color: var(--text-muted);">Create a new warehouse staff access account.</p>

    <form action="../actions/auth.php" method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="register">

        <div class="form-group">
            <label for="username">Staff Username</label>
            <input type="text" id="username" name="username" class="form-control" required placeholder="e.g. john_clerk">
        </div>

        <div class="form-group">
            <label for="email">Work Email Address</label>
            <input type="email" id="email" name="email" class="form-control" required placeholder="john@warehouse.local">
        </div>

        <div class="form-group">
            <label for="password">Password (Min 8 Characters)</label>
            <input type="password" id="password" name="password" class="form-control" minlength="8" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%;">Register Staff Account</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
