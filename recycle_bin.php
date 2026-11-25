<?php
// recycle_bin.php - CHILD: orders that were deleted, who deleted them and when (private)
require 'includes/db.php';
require 'includes/auth.php';
require_login();

// Only orders with a deleted_at date; the newest deletions first.
// Joins: the customer the order belongs to, the user who deleted it, and the user who added it.
$orders = $pdo->query(
    'SELECT o.*, c.full_name AS customer_name,
            d.first_name AS del_first, d.last_name AS del_last,
            a.first_name AS add_first, a.last_name AS add_last
     FROM orders o
     INNER JOIN customers c ON c.customer_id = o.customer_id
     LEFT JOIN users d ON d.user_id = o.deleted_by
     LEFT JOIN users a ON a.user_id = o.added_by
     WHERE o.deleted_at IS NOT NULL
     ORDER BY o.deleted_at DESC, o.order_id DESC'
)->fetchAll();

include 'includes/header.php';
?>
<h2>Recycle bin</h2>
<p class="result-note">
    Deleted orders stay here until you restore them or delete them for good.
    <?= count($orders) ?> <?= count($orders) === 1 ? 'order' : 'orders' ?> in the bin.
</p>

<div class="table-panel">
<?php if (!$orders): ?>
    <p class="empty">The recycle bin is empty. Orders you delete from a customer's orders appear here.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Customer</th><th>Image</th><th>Item</th><th class="num">Qty</th><th class="num">Price</th>
            <th>Pickup</th><th>Deleted by</th><th>Deleted on</th><th><span class="sr">Actions</span></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td data-label="ID"><?= $o['order_id'] ?></td>
            <td data-label="Customer"><?= e($o['customer_name']) ?></td>
            <td data-label="Image">
                <?php if ($o['image_path']): ?>
                    <a href="<?= e($o['image_path']) ?>" target="_blank" rel="noopener">
                        <img class="thumb" src="<?= e($o['image_path']) ?>" alt="Photo of <?= e($o['item_name']) ?>">
                    </a>
                <?php else: ?>—<?php endif; ?>
            </td>
            <td data-label="Item"><?= e($o['item_name']) ?></td>
            <td data-label="Qty" class="num"><?= $o['quantity'] ?></td>
            <td data-label="Price" class="num">&#8369;<?= number_format($o['price'], 2) ?></td>
            <td data-label="Pickup"><?= fmt_date($o['pickup_date']) ?></td>
            <td data-label="Deleted by"><?= who($o['del_first'], $o['del_last']) ?></td>
            <td data-label="Deleted on"><?= fmt_datetime($o['deleted_at']) ?></td>
            <td class="actions-cell">
                <div class="actions">
                    <form method="post" action="order_restore.php?id=<?= $o['order_id'] ?>" class="inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="ghost">Restore</button>
                    </form>
                    <form method="post" action="order_purge.php?id=<?= $o['order_id'] ?>" class="inline"
                          onsubmit="return confirm('Delete this order for good? This cannot be undone.')">
                        <?= csrf_field() ?>
                        <button type="submit" class="danger">Delete for good</button>
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
