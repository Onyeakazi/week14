<?php
// pages/admin_users.php - Staff Directory & Privilege Management View
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$users = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY id DESC")->fetchAll();

$page_title = "Staff Role Management";
require_once __DIR__ . '/../includes/header.php';
?>

<h1 style="margin-bottom: 5px;">👥 Staff Access & Role Management</h1>
<p style="color: var(--text-muted); margin-bottom: 25px;">Promote warehouse staff to administrators or adjust operational access privileges.</p>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Work Email</th>
                <th>Assigned Role</th>
                <th>Joined Date</th>
                <th>Privilege Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?= $user['id'] ?></td>
                    <td><strong style="color: #fff;"><?= htmlspecialchars($user['username']) ?></strong></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="role-badge role-<?= $user['role'] ?>"><?= strtoupper($user['role']) ?></span>
                    </td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php if ($user['id'] == $_SESSION['user_id']): ?>
                            <span style="color: var(--text-muted); font-style: italic;">(Active Session Account)</span>
                        <?php else: ?>
                            <form action="../actions/admin.php" method="POST" style="display:inline;">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="action" value="toggle_role">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="current_role" value="<?= $user['role'] ?>">
                                <button type="submit" class="btn btn-outline" style="padding: 4px 10px; font-size: 0.8em;">
                                    Switch to <?= $user['role'] === 'admin' ? 'Staff' : 'Admin' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
