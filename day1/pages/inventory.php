<?php
// pages/inventory.php - Master Warehouse Inventory Catalog View
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

// Fetch Categories for Filter Dropdown
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC")->fetchAll();

$search      = trim($_GET['search'] ?? '');
$category_id = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);
$page        = max(1, (int)($_GET['page'] ?? 1));
$per_page    = 6;
$offset      = ($page - 1) * $per_page;

$where_clauses = ["p.status = 'active'"];
$params        = [];

if (!empty($search)) {
    $where_clauses[] = "(p.name LIKE :search OR p.sku LIKE :search)";
    $params[':search'] = "%{$search}%";
}
if ($category_id) {
    $where_clauses[] = "p.category_id = :cat_id";
    $params[':cat_id'] = $category_id;
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

// Count Total Filtered Records
$count_stmt = $pdo->prepare("SELECT COUNT(*) FROM products p {$where_sql}");
$count_stmt->execute($params);
$total_records = $count_stmt->fetchColumn();
$total_pages   = ceil($total_records / $per_page);

// Fetch Filtered Product Page with Relational JOINs
$sql = "SELECT p.*, c.name AS category_name, s.name AS supplier_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN suppliers s ON p.supplier_id = s.id
        {$where_sql}
        ORDER BY p.id DESC
        LIMIT {$per_page} OFFSET {$offset}";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$page_title = "Inventory Catalog";
require_once __DIR__ . '/../includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h1 style="margin: 0;">📦 Warehouse Inventory Catalog</h1>
    <div style="display: flex; gap: 10px;">
        <a href="stock_adjust.php" class="btn btn-outline">⚡ Move Stock</a>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="product_form.php" class="btn btn-primary">+ Add Product SKU</a>
        <?php endif; ?>
    </div>
</div>

<!-- MULTI-FILTER SEARCH BAR -->
<div class="card" style="padding: 20px; margin-bottom: 25px;">
    <form method="GET" action="inventory.php" style="display: flex; gap: 15px; flex-wrap: wrap;">
        <input type="text" name="search" class="form-control" style="flex: 2; min-width: 200px;" 
               placeholder="Search by SKU or Product Name..." value="<?= htmlspecialchars($search) ?>">
        
        <select name="category_id" class="form-control" style="flex: 1; min-width: 180px;">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="inventory.php" class="btn btn-outline">Reset</a>
    </form>
</div>

<!-- INVENTORY DATA TABLE -->
<div class="card">
    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Product Name</th>
                <th>Category</th>
                <th>Supplier</th>
                <th>Unit Price</th>
                <th>Stock On Hand</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr><td colspan="8" style="text-align:center; color: var(--text-muted);">No products match your search criteria.</td></tr>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($prod['sku']) ?></code></td>
                        <td><strong style="color: #fff;"><?= htmlspecialchars($prod['name']) ?></strong></td>
                        <td><?= htmlspecialchars($prod['category_name']) ?></td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($prod['supplier_name']) ?></td>
                        <td>$<?= number_format($prod['selling_price'], 2) ?></td>
                        <td>
                            <strong style="font-size: 1.1em; color: <?= $prod['stock_quantity'] <= $prod['reorder_level'] ? '#ef4444' : '#fff' ?>;">
                                <?= $prod['stock_quantity'] ?>
                            </strong>
                        </td>
                        <td>
                            <?php if ($prod['stock_quantity'] <= $prod['reorder_level']): ?>
                                <span class="badge-low-stock">LOW STOCK (&le; <?= $prod['reorder_level'] ?>)</span>
                            <?php else: ?>
                                <span class="badge-ok">IN STOCK</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="stock_adjust.php?product_id=<?= $prod['id'] ?>" class="btn btn-outline" style="padding: 3px 8px; font-size: 0.8em;">Move</a>
                            <?php if ($_SESSION['role'] === 'admin'): ?>
                                <form action="../actions/stock.php" method="POST" style="display:inline;" onsubmit="return confirm('Archive product SKU <?= htmlspecialchars($prod['sku']) ?>?');">
                                    <?php csrf_field(); ?>
                                    <input type="hidden" name="action" value="archive_product">
                                    <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 3px 8px; font-size: 0.8em;">Archive</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- PAGINATION CONTROLS -->
    <?php if ($total_pages > 1): ?>
        <div style="display: flex; gap: 8px; justify-content: center; margin-top: 25px;">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="inventory.php?page=<?= $i ?>&search=<?= urlencode($search) ?>&category_id=<?= urlencode((string)$category_id) ?>" 
                   class="btn <?= $page == $i ? 'btn-primary' : 'btn-outline' ?>" style="padding: 5px 12px;">
                   <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
