<?php
require_once __DIR__ . '/includes/db.php';
if (isset($_GET['add'])) {
    add_to_cart((int)$_GET['add'], 1);
    header('Location: cart.php');
    exit;
}
$category = $_GET['category'] ?? null;
$search = $_GET['q'] ?? null;
$products = get_products($category, $search);
$categories = get_categories();
$pageTitle = APP_NAME . ' - Shop';
include __DIR__ . '/includes/header.php';
?>
<section class="hero">
  <h1>Modern supplement store</h1>
  <p>Protein, creatine, BCAA, bars, sports drinks and more. Simple PHP version with admin, orders and inventory tracking.</p>
</section>

<form class="filters" method="get">
  <div style="flex:2;min-width:220px"><input type="text" name="q" placeholder="Search product or SKU" value="<?= htmlspecialchars($search ?? '') ?>"></div>
  <div style="flex:1;min-width:180px">
    <select name="category">
      <option value="">All categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($category === $cat['slug']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div><button class="btn" type="submit">Filter</button></div>
</form>

<div class="grid">
  <?php foreach ($products as $p): $final = product_final_price($p); ?>
    <div class="card">
      <span class="badge"><?= htmlspecialchars($p['category_name'] ?: 'Supplements') ?></span>
      <h3><?= htmlspecialchars($p['name']) ?></h3>
      <div class="muted">SKU: <?= htmlspecialchars($p['sku']) ?> • <?= htmlspecialchars($p['size']) ?></div>
      <div class="price"><?= money($final) ?>
        <?php if ($final < (float)$p['price']): ?><span class="muted" style="text-decoration:line-through;font-size:14px"><?= money((float)$p['price']) ?></span><?php endif; ?>
      </div>
      <div class="muted">In stock: <?= (int)$p['stock_qty'] ?></div>
      <p class="muted"><?= htmlspecialchars(substr($p['description'] ?: $p['name'], 0, 90)) ?></p>
      <div style="display:flex;gap:10px;flex-wrap:wrap">
        <a class="btn secondary small" href="product.php?slug=<?= urlencode($p['slug']) ?>">View</a>
        <a class="btn small" href="index.php?add=<?= (int)$p['id'] ?>">Add to cart</a>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
