<?php
// pages/product_form.php - Add New Inventory SKU View (Admin Only)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_admin();

$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();
$suppliers  = $pdo->query("SELECT id, name FROM suppliers ORDER BY name ASC")->fetchAll();

$page_title = "Add Product SKU";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="max-width: 650px; margin: 20px auto;" class="card">
    <h2 style="margin-bottom: 20px;">+ Add New Inventory SKU</h2>

    <form action="../actions/stock.php" method="POST">
        <?php csrf_field(); ?>
        <input type="hidden" name="action" value="create_product">

        <div class="form-group">
            <label for="sku">Unique SKU Code</label>
            <input type="text" id="sku" name="sku" class="form-control" required placeholder="e.g. ELC-9002">
        </div>

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" class="form-control" required placeholder="e.g. Digital Caliper 150mm">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id" class="form-control" required>
                    <option value="">Select Category</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="supplier_id">Supplier Vendor</label>
                <select id="supplier_id" name="supplier_id" class="form-control" required>
                    <option value="">Select Supplier</option>
                    <?php foreach ($suppliers as $sup): ?>
                        <option value="<?= $sup['id'] ?>"><?= htmlspecialchars($sup['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label for="cost_price">Cost Price ($)</label>
                <input type="number" step="0.01" min="0" id="cost_price" name="cost_price" class="form-control" required placeholder="12.50">
            </div>
            <div class="form-group">
                <label for="selling_price">Selling Price ($)</label>
                <input type="number" step="0.01" min="0" id="selling_price" name="selling_price" class="form-control" required placeholder="24.99">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label for="stock_quantity">Initial Stock Quantity</label>
                <input type="number" min="0" id="stock_quantity" name="stock_quantity" class="form-control" required value="0">
            </div>
            <div class="form-group">
                <label for="reorder_level">Low Stock Reorder Alert Level</label>
                <input type="number" min="1" id="reorder_level" name="reorder_level" class="form-control" required value="10">
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Save Product to Catalog</button>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
