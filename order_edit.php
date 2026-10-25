<?php
// order_edit.php - CHILD: update
require 'includes/db.php';
$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = ?');
$stmt->execute([$id]);
$o = $stmt->fetch();
if (!$o) { die('Order not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'UPDATE orders SET item_name = ?, quantity = ?, price = ?, pickup_date = ? WHERE order_id = ?'
    );
    $stmt->execute([$_POST['item_name'], $_POST['quantity'], $_POST['price'], $_POST['pickup_date'], $id]);
    header('Location: orders.php?customer_id=' . $o['customer_id']);
    exit;
}

include 'includes/header.php';
?>
<h2>Edit Order</h2>
<form method="post">
    <input type="text" name="item_name" value="<?= htmlspecialchars($o['item_name']) ?>" required>
    <input type="number" name="quantity" min="1" value="<?= $o['quantity'] ?>" required>
    <input type="number" name="price" step="0.01" value="<?= $o['price'] ?>" required>
    <input type="date" name="pickup_date" value="<?= $o['pickup_date'] ?>" required>
    <button type="submit">Save Changes</button>
</form>
<?php include 'includes/footer.php'; ?>
