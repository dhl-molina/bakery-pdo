<?php
// order_edit.php - CHILD: update (private)
require 'includes/db.php';
require 'includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = ?');
$stmt->execute([$id]);
$o = $stmt->fetch();
if (!$o) { die('Order not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // updated_by = the logged-in user, last_updated = right now
    $stmt = $pdo->prepare(
        'UPDATE orders
         SET item_name = ?, quantity = ?, price = ?, pickup_date = ?,
             updated_by = ?, last_updated = CURRENT_TIMESTAMP
         WHERE order_id = ?'
    );
    $stmt->execute([
        trim($_POST['item_name']), (int)$_POST['quantity'], $_POST['price'], $_POST['pickup_date'],
        current_user_id(), $id,
    ]);
    header('Location: orders.php?customer_id=' . $o['customer_id']);
    exit;
}

include 'includes/header.php';
?>
<a class="back" href="orders.php?customer_id=<?= $o['customer_id'] ?>">&larr; Back to orders</a>
<h2>Edit order</h2>
<form method="post" class="panel">
    <?= csrf_field() ?>
    <div class="form-stack">
        <label><span>Item</span>
            <input type="text" name="item_name" value="<?= e($o['item_name']) ?>" required>
        </label>
        <label><span>Quantity</span>
            <input type="number" name="quantity" min="1" value="<?= $o['quantity'] ?>" required>
        </label>
        <label><span>Price (&#8369;)</span>
            <input type="number" name="price" step="0.01" min="0" value="<?= $o['price'] ?>" required>
        </label>
        <label><span>Pickup date</span>
            <input type="date" name="pickup_date" value="<?= e($o['pickup_date']) ?>" required>
        </label>
        <button type="submit">Save changes</button>
    </div>
</form>
<?php include 'includes/footer.php'; ?>
