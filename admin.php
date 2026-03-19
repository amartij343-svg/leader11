<?php
require_once __DIR__ . '/includes/db.php';
require_admin();
$pdo = db();
$message = '';

if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header('Location: admin_login.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_product'])) {
        $stmt = $pdo->prepare("UPDATE products SET price = ?, discount_percent = ?, stock_qty = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([
            (float)$_POST['price'],
            (float)$_POST['discount_percent'],
            (int)$_POST['stock_qty'],
            trim($_POST['description']),
            (int)$_POST['product_id']
        ]);
        $message = 'Product updated.';
    }
    if (isset($_POST['add_movement'])) {
        add_inventory_movement((int)$_POST['product_id'], $_POST['movement_type'], (int)$_POST['quantity'], trim($_POST['recipient_name']), trim($_POST['reference_number']), trim($_POST['notes']));
        $message = 'Inventory movement saved.';
    }
    if (isset($_POST['set_category_discount'])) {
        $stmt = $pdo->prepare("UPDATE categories SET discount_percent = ? WHERE id = ?");
        $stmt->execute([(float)$_POST['category_discount'], (int)$_POST['category_id']]);
        $message = 'Category discount updated.';
    }
}

$products = get_products();
$categories = get_categories();
$movements = $pdo->query("SELECT m.*, p.name AS product_name, p.sku FROM inventory_movements m JOIN products p ON p.id = m.product_id ORDER BY m.id DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
$orderCount = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$productCount = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$stockCount = (int)$pdo->query("SELECT COALESCE(SUM(stock_qty),0) FROM products")->fetchColumn();
$pageTitle = 'Admin';
include __DIR__ . '/includes/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px">
  <h1>Admin dashboard</h1>
  <a class="btn secondary small" href="admin.php?logout=1">Logout</a>
</div>
<?php if ($message): ?><div class="alert"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="stats">
  <div class="stat"><div class="muted">Products</div><div class="price"><?= $productCount ?></div></div>
  <div class="stat"><div class="muted">Orders</div><div class="price"><?= $orderCount ?></div></div>
  <div class="stat"><div class="muted">Total stock</div><div class="price"><?= $stockCount ?></div></div>
</div>

<section class="card" style="margin-bottom:20px">
  <h2>Category discounts</h2>
  <div class="grid">
  <?php foreach ($categories as $cat): ?>
    <form method="post" class="card">
      <input type="hidden" name="set_category_discount" value="1">
      <input type="hidden" name="category_id" value="<?= (int)$cat['id'] ?>">
      <strong><?= htmlspecialchars($cat['name']) ?></strong>
      <div class="muted">Current discount: <?= (float)$cat['discount_percent'] ?>%</div>
      <div style="margin:10px 0"><input type="number" step="0.01" name="category_discount" value="<?= htmlspecialchars($cat['discount_percent']) ?>"></div>
      <button class="btn small" type="submit">Save</button>
    </form>
  <?php endforeach; ?>
  </div>
</section>

<section class="card" style="margin-bottom:20px">
  <h2>Products</h2>
  <table class="table">
    <thead><tr><th>Product</th><th>Stock</th><th>Price</th><th>Discount %</th><th>Description</th><th>Save</th></tr></thead>
    <tbody>
      <?php foreach ($products as $p): ?>
      <tr>
        <form method="post">
          <td>
            <strong><?= htmlspecialchars($p['name']) ?></strong><br>
            <span class="muted"><?= htmlspecialchars($p['sku']) ?></span>
            <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
            <input type="hidden" name="update_product" value="1">
          </td>
          <td><input type="number" name="stock_qty" value="<?= (int)$p['stock_qty'] ?>"></td>
          <td><input type="number" step="0.01" name="price" value="<?= htmlspecialchars($p['price']) ?>"></td>
          <td><input type="number" step="0.01" name="discount_percent" value="<?= htmlspecialchars($p['discount_percent']) ?>"></td>
          <td><textarea name="description"><?= htmlspecialchars($p['description']) ?></textarea></td>
          <td><button class="btn small" type="submit">Update</button></td>
        </form>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<div class="two-col">
  <section class="card">
    <h2>Add inventory movement</h2>
    <form method="post">
      <input type="hidden" name="add_movement" value="1">
      <div style="margin-bottom:10px">
        <select name="product_id" required>
          <?php foreach ($products as $p): ?><option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['sku']) ?>)</option><?php endforeach; ?>
        </select>
      </div>
      <div style="margin-bottom:10px">
        <select name="movement_type">
          <option value="in">Incoming stock</option>
          <option value="out">Outgoing stock</option>
          <option value="correction">Correction +</option>
          <option value="damaged">Damaged/lost</option>
        </select>
      </div>
      <div style="margin-bottom:10px"><input type="number" name="quantity" min="1" value="1" required></div>
      <div style="margin-bottom:10px"><input name="recipient_name" placeholder="Sold/issued to"></div>
      <div style="margin-bottom:10px"><input name="reference_number" placeholder="Reference / order number"></div>
      <div style="margin-bottom:10px"><textarea name="notes" placeholder="Notes"></textarea></div>
      <button class="btn" type="submit">Save movement</button>
    </form>
  </section>

  <section class="card">
    <h2>Latest stock movements</h2>
    <table class="table">
      <thead><tr><th>Date</th><th>Product</th><th>Type</th><th>Qty</th><th>Recipient</th></tr></thead>
      <tbody>
        <?php foreach ($movements as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m['created_at']) ?></td>
            <td><?= htmlspecialchars($m['product_name']) ?></td>
            <td><?= htmlspecialchars($m['movement_type']) ?></td>
            <td><?= (int)$m['quantity'] ?> (<?= (int)$m['stock_before'] ?>→<?= (int)$m['stock_after'] ?>)</td>
            <td><?= htmlspecialchars($m['recipient_name']) ?><br><span class="muted"><?= htmlspecialchars($m['reference_number']) ?></span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </section>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
