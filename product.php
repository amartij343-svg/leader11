<?php
require_once __DIR__ . '/includes/db.php';
$slug = $_GET['slug'] ?? '';
$product = get_product_by_slug($slug);
if (!$product) { http_response_code(404); echo 'Product not found'; exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    add_to_cart((int)$product['id'], max(1, (int)($_POST['qty'] ?? 1)));
    header('Location: cart.php');
    exit;
}
$pageTitle = $product['name'];
include __DIR__ . '/includes/header.php';
$final = product_final_price($product);
?>
<div class="two-col" style="margin-top:24px">
  <section class="card">
    <span class="badge"><?= htmlspecialchars($product['category_name'] ?: 'Supplements') ?></span>
    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p class="muted">SKU: <?= htmlspecialchars($product['sku']) ?> • Size: <?= htmlspecialchars($product['size']) ?></p>
    <div class="price"><?= money($final) ?></div>
    <p><?= htmlspecialchars($product['description'] ?: $product['name']) ?></p>
    <p class="muted">Stock available: <?= (int)$product['stock_qty'] ?></p>
    <form method="post" class="inline">
      <div style="width:120px"><input type="number" name="qty" min="1" value="1"></div>
      <div><button class="btn" type="submit">Add to cart</button></div>
    </form>
  </section>
  <aside class="card">
    <h3>Why this store works</h3>
    <ul class="muted">
      <li>PHP + SQLite</li>
      <li>Simple admin panel</li>
      <li>Inventory movement log</li>
      <li>Order history</li>
      <li>Discounts for products and categories</li>
    </ul>
  </aside>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
