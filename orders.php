<?php
// orders.php - CHILD: list + insert orders for ONE customer (private)
require 'includes/db.php';
require 'includes/auth.php';
require_login();

$cid = (int)($_GET['customer_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
$stmt->execute([$cid]);
$customer = $stmt->fetch();
if (!$customer) { die('Customer not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $stmt = $pdo->prepare(
        'INSERT INTO orders (customer_id, item_name, quantity, price, pickup_date, added_by, updated_by)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $cid, trim($_POST['item_name']), (int)$_POST['quantity'], $_POST['price'], $_POST['pickup_date'],
        current_user_id(), current_user_id(),
    ]);
    header("Location: orders.php?customer_id=$cid");
    exit;
}

$stmt = $pdo->prepare(
    'SELECT o.*,
            a.first_name AS add_first, a.last_name AS add_last,
            u.first_name AS upd_first, u.last_name AS upd_last
     FROM orders o
     LEFT JOIN users a ON a.user_id = o.added_by
     LEFT JOIN users u ON u.user_id = o.updated_by
     WHERE o.customer_id = ?
     ORDER BY o.order_id DESC'
);
$stmt->execute([$cid]);
$orders = $stmt->fetchAll();

include 'includes/header.php';
?>
<a class="back" href="index.php">&larr; All customers</a>
<h2>Orders of <?= e($customer['full_name']) ?></h2>

<form method="post" class="panel">
    <?= csrf_field() ?>
    <h3>Add an order</h3>
    <div class="form-row">
        <label><span>Item</span>
            <input type="text" name="item_name" placeholder="e.g. Chocolate cake" required>
        </label>
        <label><span>Quantity</span>
            <input type="number" name="quantity" min="1" value="1" required>
        </label>
        <label><span>Price (&#8369;)</span>
            <input type="number" name="price" step="0.01" min="0" required>
        </label>
        <label><span>Pickup date</span>
            <input type="date" name="pickup_date" required>
        </label>
        <button type="submit">Add order</button>
    </div>
</form>

<div class="table-panel">
<?php if (!$orders): ?>
    <p class="empty"><?= e($customer['full_name']) ?> has no orders yet. Add one above.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Item</th><th class="num">Qty</th><th class="num">Price</th><th>Pickup</th>
            <th>Added by</th><th>Last updated</th><th><span class="sr">Actions</span></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td data-label="ID"><?= $o['order_id'] ?></td>
            <td data-label="Item"><?= e($o['item_name']) ?></td>
            <td data-label="Qty" class="num"><?= $o['quantity'] ?></td>
            <td data-label="Price" class="num">&#8369;<?= number_format($o['price'], 2) ?></td>
            <td data-label="Pickup"><?= fmt_date($o['pickup_date']) ?></td>
            <td data-label="Added by"><?= who($o['add_first'], $o['add_last']) ?></td>
            <td data-label="Last updated"><div><?= who($o['upd_first'], $o['upd_last']) ?>
                <small><?= fmt_datetime($o['last_updated']) ?></small></div></td>
            <td class="actions-cell">
                <div class="actions">
                    <a class="btn ghost" href="order_edit.php?id=<?= $o['order_id'] ?>">Edit</a>
                    <form method="post" action="order_delete.php?id=<?= $o['order_id'] ?>&customer_id=<?= $cid ?>" class="inline"
                          onsubmit="return confirm('Delete this order?')">
                        <?= csrf_field() ?>
                        <button type="submit" class="danger">Delete</button>
                    </form>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
