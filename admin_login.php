<?php
require_once __DIR__ . '/includes/db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['password'] ?? '') === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php'); exit;
    }
    $error = 'Wrong password.';
}
$pageTitle = 'Admin login';
include __DIR__ . '/includes/header.php';
?>
<div class="card" style="max-width:420px;margin:40px auto">
  <h1>Admin login</h1>
  <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post">
    <div style="margin-bottom:10px"><input type="password" name="password" placeholder="Password"></div>
    <button class="btn" type="submit">Login</button>
  </form>
  <p class="muted">Default password: admin123</p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
