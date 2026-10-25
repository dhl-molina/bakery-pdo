<?php
// orders.php - CHILD: list + insert orders for ONE customer
require 'includes/db.php';
$cid = (int)($_GET['customer_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
$stmt->execute([$cid]);
$customer = $stmt->fetch();
if (!$customer) { die('Customer not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare(
        'INSERT INTO orders (customer_id, item_name, quantity, price, pickup_date) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$cid, $_POST['item_name'], $_POST['quantity'], $_POST['price'], $_POST['pickup_date']]);
    header("Location: orders.php?customer_id=$cid");
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM orders WHERE customer_id = ? ORDER BY order_id DESC');
$stmt->execute([$cid]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>
<h2>Orders of <?= htmlspecialchars($customer['full_name']) ?></h2>
<form method="post">
    <input type="text" name="item_name" placeholder="Item (e.g. Chocolate Cake)" required>
    <input type="number" name="quantity" min="1" value="1" required>
    <input type="number" name="price" step="0.01" min="0" placeholder="Price" required>
    <input type="date" name="pickup_date" required>
    <button type="submit">Add Order</button>
</form>

<table>
    <tr><th>ID</th><th>Item</th><th>Qty</th><th>Price</th><th>Pickup</th><th>Actions</th></tr>
    <?php foreach ($orders as $o): ?>
    <tr>
        <td><?= $o['order_id'] ?></td>
        <td><?= htmlspecialchars($o['item_name']) ?></td>
        <td><?= $o['quantity'] ?></td>
        <td>₱<?= number_format($o['price'], 2) ?></td>
        <td><?= $o['pickup_date'] ?></td>
        <td>
            <a class="btn" href="order_edit.php?id=<?= $o['order_id'] ?>">Edit</a>
            <a class="btn danger" href="order_delete.php?id=<?= $o['order_id'] ?>&customer_id=<?= $cid ?>"
               onclick="return confirm('Delete this order?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<p><a class="btn" href="index.php">← Back to Customers</a></p>
<?php include 'includes/footer.php'; ?>
