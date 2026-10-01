<?php
// pages/stock_history.php - Immutable Warehouse Audit Trail View
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page_title = "Stock Movement Audit Trail";
require_once __DIR__ . '/../includes/header.php';

// 3-Table Relational SQL JOIN
$stmt = $pdo->query("
    SELECT sl.*, p.name AS product_name, p.sku, u.username, u.role AS user_role
    FROM stock_logs sl
    JOIN products p ON sl.product_id = p.id
    JOIN users u ON sl.user_id = u.id
    ORDER BY sl.id DESC
");
$logs = $stmt->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="margin-bottom: 5px;">📋 Warehouse Audit Trail</h1>
        <p style="color: var(--text-muted); margin: 0;">Immutable log of all incoming and outgoing warehouse stock changes.</p>
    </div>
    <div>
        <a href="stock_adjust.php" class="btn btn-primary">+ Record Movement</a>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Date & Time</th>
                <th>Product (SKU)</th>
                <th>Type</th>
                <th>Quantity</th>
                <th>Staff Member</th>
                <th>Audit Note</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No movement audit logs found.</td></tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td>#<?= $log['id'] ?></td>
                        <td><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                        <td>
                            <strong style="color:#fff;"><?= htmlspecialchars($log['product_name']) ?></strong><br>
                            <code><?= htmlspecialchars($log['sku']) ?></code>
                        </td>
                        <td>
                            <span class="badge-<?= $log['movement_type'] === 'stock_in' ? 'ok' : 'low-stock' ?>">
                                <?= $log['movement_type'] === 'stock_in' ? '▲ STOCK IN' : '▼ STOCK OUT' ?>
                            </span>
                        </td>
                        <td><strong style="font-size: 1.1em;"><?= $log['quantity'] ?></strong> units</td>
                        <td>
                            👤 <strong><?= htmlspecialchars($log['username']) ?></strong> 
                            <span class="role-badge role-<?= $log['user_role'] ?>"><?= strtoupper($log['user_role']) ?></span>
                        </td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($log['note'] ?: 'No reference note logged.') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
