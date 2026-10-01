<?php
// actions/stock.php - Handles Product Creation, Stock Adjustments & Archiving
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? '')) {
    die("SECURITY VIOLATION: Unauthorized action or CSRF verification failed.");
}

$action = $_POST['action'] ?? '';

// 1. Create New Product SKU (Admin Only)
if ($action === 'create_product') {
    require_admin();

    $sku           = strtoupper(trim($_POST['sku'] ?? ''));
    $name          = trim($_POST['name'] ?? '');
    $category_id   = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $supplier_id   = filter_input(INPUT_POST, 'supplier_id', FILTER_VALIDATE_INT);
    $cost_price    = filter_input(INPUT_POST, 'cost_price', FILTER_VALIDATE_FLOAT);
    $selling_price = filter_input(INPUT_POST, 'selling_price', FILTER_VALIDATE_FLOAT);
    $stock_qty     = filter_input(INPUT_POST, 'stock_quantity', FILTER_VALIDATE_INT);
    $reorder_lvl   = filter_input(INPUT_POST, 'reorder_level', FILTER_VALIDATE_INT);

    if (empty($sku) || empty($name) || !$category_id || !$supplier_id || $cost_price === false || $selling_price === false || $stock_qty === false || $reorder_lvl === false) {
        header("Location: ../pages/product_form.php?error=" . urlencode("Validation error: All product attributes are required."));
        exit;
    }

    $check = $pdo->prepare("SELECT id FROM products WHERE sku = ?");
    $check->execute([$sku]);
    if ($check->fetch()) {
        header("Location: ../pages/product_form.php?error=" . urlencode("A product with SKU '{$sku}' already exists."));
        exit;
    }

    $sql = "INSERT INTO products 
        (category_id, supplier_id, sku, name, cost_price, selling_price, stock_quantity, reorder_level, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
    $insert_stmt = $pdo->prepare($sql);
    $insert_stmt->execute([$category_id, $supplier_id, $sku, $name, $cost_price, $selling_price, $stock_qty, $reorder_lvl]);
    $new_product_id = $pdo->lastInsertId();

    if ($stock_qty > 0) {
        $log_stmt = $pdo->prepare("INSERT INTO stock_logs (product_id, user_id, movement_type, quantity, note) VALUES (?, ?, 'stock_in', ?, 'Initial inventory stock intake')");
        $log_stmt->execute([$new_product_id, $_SESSION['user_id'], $stock_qty]);
    }

    header("Location: ../pages/inventory.php?msg=" . urlencode("Product SKU '{$sku}' registered successfully."));
    exit;
}

// 2. Record Stock In / Stock Out (Atomic PDO Transaction)
if ($action === 'adjust_stock') {
    $product_id    = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $movement_type = $_POST['movement_type'] ?? '';
    $quantity      = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $note          = trim($_POST['note'] ?? '');

    if (!$product_id || !in_array($movement_type, ['stock_in', 'stock_out'], true) || !$quantity || $quantity < 1) {
        header("Location: ../pages/stock_adjust.php?error=" . urlencode("Invalid stock adjustment parameters."));
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, name, sku, stock_quantity FROM products WHERE id = ? AND status = 'active'");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: ../pages/stock_adjust.php?error=" . urlencode("Product not found or archived."));
        exit;
    }

    // Negative Inventory Guard
    if ($movement_type === 'stock_out' && $quantity > $product['stock_quantity']) {
        header("Location: ../pages/stock_adjust.php?error=" . urlencode("Insufficient stock! Available on hand: {$product['stock_quantity']} units."));
        exit;
    }

    $new_stock = ($movement_type === 'stock_in')
        ? ($product['stock_quantity'] + $quantity)
        : ($product['stock_quantity'] - $quantity);

    try {
        $pdo->beginTransaction();

        // 1. Update Inventory Level
        $update_stmt = $pdo->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
        $update_stmt->execute([$new_stock, $product_id]);

        // 2. Insert Immutable Audit Log
        $log_stmt = $pdo->prepare("INSERT INTO stock_logs (product_id, user_id, movement_type, quantity, note) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->execute([$product_id, $_SESSION['user_id'], $movement_type, $quantity, $note]);

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        die("TRANSACTION FAILED: " . $e->getMessage());
    }

    header("Location: ../pages/inventory.php?msg=" . urlencode("Stock committed: {$product['sku']} adjusted to {$new_stock} units."));
    exit;
}

// 3. Soft Delete Product (Admin Only)
if ($action === 'archive_product') {
    require_admin();
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    if ($product_id) {
        $stmt = $pdo->prepare("UPDATE products SET status = 'archived' WHERE id = ?");
        $stmt->execute([$product_id]);
    }
    header("Location: ../pages/inventory.php?msg=" . urlencode("Product SKU safely archived from active catalog."));
    exit;
}

header("Location: ../pages/inventory.php");
exit;
