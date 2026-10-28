<?php
// index.php - HOMEPAGE (private) - PARENT: list + insert customers
require 'includes/db.php';
require 'includes/auth.php';
require_login();                       // not logged in -> redirected to login.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // added_by and updated_by record WHO created the row
    $stmt = $pdo->prepare(
        'INSERT INTO customers (full_name, phone, email, added_by, updated_by) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        trim($_POST['full_name']), trim($_POST['phone']), trim($_POST['email']),
        current_user_id(), current_user_id(),
    ]);
    header('Location: index.php');
    exit;
}

// LEFT JOINs to users give us the names of the adder and the last updater
$customers = $pdo->query(
    'SELECT c.*, COUNT(o.order_id) AS total_orders,
            a.first_name AS add_first, a.last_name AS add_last,
            u.first_name AS upd_first, u.last_name AS upd_last
     FROM customers c
     LEFT JOIN orders o ON o.customer_id = c.customer_id
     LEFT JOIN users  a ON a.user_id = c.added_by
     LEFT JOIN users  u ON u.user_id = c.updated_by
     GROUP BY c.customer_id, a.user_id, u.user_id
     ORDER BY c.customer_id DESC'
)->fetchAll();

include 'includes/header.php';
?>
<h2>Customers</h2>

<form method="post" class="panel">
    <?= csrf_field() ?>
    <h3>Add a customer</h3>
    <div class="form-row">
        <label><span>Full name</span>
            <input type="text" name="full_name" required>
        </label>
        <label><span>Phone number</span>
            <input type="text" name="phone" required>
        </label>
        <label><span>Email <span class="hint">(optional)</span></span>
            <input type="email" name="email">
        </label>
        <button type="submit">Add customer</button>
    </div>
</form>

<div class="table-panel">
<?php if (!$customers): ?>
    <p class="empty">No customers yet. Add the first one above.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th class="num">Orders</th>
            <th>Added by</th><th>Last updated</th><th><span class="sr">Actions</span></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($customers as $c): ?>
        <tr>
            <td data-label="ID"><?= $c['customer_id'] ?></td>
            <td data-label="Name"><?= e($c['full_name']) ?></td>
            <td data-label="Phone"><?= e($c['phone']) ?></td>
            <td data-label="Email"><?= e($c['email']) ?: '—' ?></td>
            <td data-label="Orders" class="num"><?= $c['total_orders'] ?></td>
            <td data-label="Added by"><?= who($c['add_first'], $c['add_last']) ?></td>
            <td data-label="Last updated"><div><?= who($c['upd_first'], $c['upd_last']) ?>
                <small><?= fmt_datetime($c['last_updated']) ?></small></div></td>
            <td class="actions-cell">
                <div class="actions">
                    <a class="btn ghost" href="orders.php?customer_id=<?= $c['customer_id'] ?>">View orders</a>
                    <a class="btn ghost" href="customer_edit.php?id=<?= $c['customer_id'] ?>">Edit</a>
                    <form method="post" action="customer_delete.php?id=<?= $c['customer_id'] ?>" class="inline"
                          onsubmit="return confirm('Delete this customer and ALL their orders?')">
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
