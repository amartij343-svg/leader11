<?php
require_once __DIR__ . '/includes/db.php';
if (isset($_GET['remove'])) {
    $id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header('Location: cart.php'); exit;
}
$items = get_cart_items();
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout'])) {
    if (!$items) {
        $error = 'Cart is empty.';
    } else {
        $pdo = db();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, shipping_address, notes, total_amount) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                trim($_POST['customer_name'] ?? ''),
                trim($_POST['customer_email'] ?? ''),
                trim($_POST['customer_phone'] ?? ''),
                trim($_POST['shipping_address'] ?? ''),
                trim($_POST['notes'] ?? ''),
                cart_total()
            ]);
            $orderId = (int)$pdo->lastInsertId();
            $itemStmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, sku, quantity, price, line_total) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($items as $item) {
                $p = $item['product'];
                $itemStmt->execute([$orderId, $p['id'], $p['name'], $p['sku'], $item['qty'], $item['unit_price'], $item['line_total']]);
                add_inventory_movement((int)$p['id'], 'sale', (int)$item['qty'], trim($_POST['customer_name'] ?? ''), 'ORDER-' . $orderId, 'Website order');
            }
            $pdo->commit();
            $_SESSION['cart'] = [];
            $items = [];
            $success = 'Order created successfully. Order number: ORDER-' . $orderId;
        } catch (Throwable $e) {
            $pdo->rollBack();
            $error = 'Order failed: ' . $e->getMessage();
        }
    }
}
$pageTitle = APP_NAME . ' - Cart';
include __DIR__ . '/includes/header.php';
?>
<h1 style="margin-top:24px">Shopping cart</h1>
<?php if ($success): ?><div class="alert"><?= htmlspecialchars($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

<div class="two-col">
  <section class="card">
    <?php if (!$items): ?>
      <p class="muted">Your cart is empty.</p>
    <?php else: ?>
      <table class="table">
        <thead><tr><th>Product</th><th>Qty</th><th>Unit</th><th>Total</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($items as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['product']['name']) ?></td>
              <td><?= (int)$item['qty'] ?></td>
              <td><?= money($item['unit_price']) ?></td>
              <td><?= money($item['line_total']) ?></td>
              <td><a href="cart.php?remove=<?= (int)$item['product']['id'] ?>">Remove</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="price">Total: <?= money(cart_total()) ?></p>
    <?php endif; ?>
  </section>

  <aside class="card">
    <h3>Checkout</h3>
    <form method="post">
      <input type="hidden" name="checkout" value="1">
      <div style="margin-bottom:10px"><input name="customer_name" placeholder="Customer name" required></div>
      <div style="margin-bottom:10px"><input name="customer_email" placeholder="Email"></div>
      <div style="margin-bottom:10px"><input name="customer_phone" placeholder="Phone"></div>
      <div style="margin-bottom:10px"><textarea name="shipping_address" placeholder="Shipping address"></textarea></div>
      <div style="margin-bottom:10px"><textarea name="notes" placeholder="Order notes"></textarea></div>
      <button class="btn success" type="submit">Place order</button>
    </form>
  </aside>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
