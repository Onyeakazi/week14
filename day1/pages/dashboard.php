<?php
// pages/dashboard.php - Executive Warehouse KPI Analytics Overview
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$page_title = "Warehouse Dashboard";
require_once __DIR__ . '/../includes/header.php';

// 1. Total Active Catalog Items
$total_products = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active'")->fetchColumn();

// 2. Low-Stock Alert Count (stock_quantity <= reorder_level)
$low_stock_count = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active' AND stock_quantity <= reorder_level")->fetchColumn();

// 3. Out of Stock Items
$out_of_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE status = 'active' AND stock_quantity = 0")->fetchColumn();

// 4. Financial Inventory Valuation (SUM of unit selling price * on-hand quantity)
$total_valuation = $pdo->query("SELECT SUM(selling_price * stock_quantity) FROM products WHERE status = 'active'")->fetchColumn() ?: 0.00;

// 5. Recent 5 Stock Movements (Joined with Products and Users)
$recent_logs = $pdo->query("
    SELECT sl.*, p.name AS product_name, p.sku, u.username 
    FROM stock_logs sl
    JOIN products p ON sl.product_id = p.id
    JOIN users u ON sl.user_id = u.id
    ORDER BY sl.id DESC LIMIT 5
")->fetchAll();
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <h1 style="font-size: 2em; margin-bottom: 5px;">Warehouse Operations Overview</h1>
        <p style="color: var(--text-muted); margin: 0;">Real-time inventory metrics, low-stock triggers, and shipment history.</p>
    </div>
    <div>
        <a href="stock_adjust.php" class="btn btn-primary">+ Record Stock Movement</a>
    </div>
</div>

<!-- KPI STAT CARDS GRID -->
<div class="grid">
    <div class="stat-card">
        <span>Active Product SKUs</span>
        <h2><?= number_format($total_products) ?></h2>
    </div>
    <div class="stat-card <?= $low_stock_count > 0 ? 'alert-card' : '' ?>">
        <span>Low Stock Alerts (&le; Threshold)</span>
        <h2 style="color: <?= $low_stock_count > 0 ? '#ef4444' : '#fff' ?>;"><?= number_format($low_stock_count) ?></h2>
    </div>
    <div class="stat-card <?= $out_of_stock > 0 ? 'alert-card' : '' ?>">
        <span>Out of Stock Units</span>
        <h2 style="color: <?= $out_of_stock > 0 ? '#ef4444' : '#fff' ?>;"><?= number_format($out_of_stock) ?></h2>
    </div>
    <div class="stat-card success-card">
        <span>Total Inventory Valuation</span>
        <h2 style="color: #10b981;">$<?= number_format($total_valuation, 2) ?></h2>
    </div>
</div>

<!-- RECENT ACTIVITY TABLE -->
<div class="card">
    <h3 style="margin-top: 0;">⚡ Recent Stock Movement Activity</h3>
    <table>
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Product (SKU)</th>
                <th>Movement</th>
                <th>Quantity</th>
                <th>Staff Member</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recent_logs)): ?>
                <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No inventory activity recorded yet.</td></tr>
            <?php else: ?>
                <?php foreach ($recent_logs as $log): ?>
                    <tr>
                        <td><?= date('M d, H:i', strtotime($log['created_at'])) ?></td>
                        <td>
                            <strong><?= htmlspecialchars($log['product_name']) ?></strong>
                            <span style="color: var(--text-muted); font-size: 0.8em;">(<?= htmlspecialchars($log['sku']) ?>)</span>
                        </td>
                        <td>
                            <span class="badge-<?= $log['movement_type'] === 'stock_in' ? 'ok' : 'low-stock' ?>">
                                <?= $log['movement_type'] === 'stock_in' ? '▲ STOCK IN' : '▼ STOCK OUT' ?>
                            </span>
                        </td>
                        <td><strong><?= $log['quantity'] ?></strong> units</td>
                        <td>👤 <?= htmlspecialchars($log['username']) ?></td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($log['note'] ?: 'N/A') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
