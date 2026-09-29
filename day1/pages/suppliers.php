<?php
// pages/suppliers.php - Vendor Directory & Quick Registration View
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$suppliers = $pdo->query("
    SELECT s.*, COUNT(p.id) AS product_count 
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier_id AND p.status = 'active'
    GROUP BY s.id
    ORDER BY s.id DESC
")->fetchAll();

$page_title = "Supplier Directory";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; gap: 30px; flex-wrap: wrap;">
    <!-- REGISTER SUPPLIER FORM -->
    <div style="flex: 1; min-width: 300px;" class="card">
        <h3 style="margin-top: 0;">+ Add Supplier Vendor</h3>
        <form action="../actions/admin.php" method="POST">
            <?php csrf_field(); ?>
            <input type="hidden" name="action" value="add_supplier">

            <div class="form-group">
                <label for="name">Vendor Company Name</label>
                <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Apex Industrial Supplies">
            </div>
            <div class="form-group">
                <label for="contact_email">Contact Email</label>
                <input type="email" id="contact_email" name="contact_email" class="form-control" required placeholder="orders@vendor.com">
            </div>
            <div class="form-group">
                <label for="phone">Support / Dispatch Phone</label>
                <input type="text" id="phone" name="phone" class="form-control" required placeholder="+1-800-555-0100">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Register Supplier</button>
        </form>
    </div>

    <!-- SUPPLIER DIRECTORY LIST -->
    <div style="flex: 2; min-width: 400px;" class="card">
        <h3 style="margin-top: 0;">Active Procurement Suppliers</h3>
        <table>
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Catalog SKUs</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers as $sup): ?>
                    <tr>
                        <td><strong style="color: #fff;"><?= htmlspecialchars($sup['name']) ?></strong></td>
                        <td><?= htmlspecialchars($sup['contact_email']) ?></td>
                        <td><?= htmlspecialchars($sup['phone']) ?></td>
                        <td><span class="badge-ok"><?= $sup['product_count'] ?> Products</span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
