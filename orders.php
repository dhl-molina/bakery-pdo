<?php
// orders.php - CHILD: search + list + insert orders (private)
//   orders.php?customer_id=3  -> the orders of ONE customer (with the "Add an order" form)
//   orders.php                -> ALL orders of every customer (search across everything)
require 'includes/db.php';
require 'includes/auth.php';
require 'includes/upload.php';
require_login();

$cid = (int)($_GET['customer_id'] ?? 0);
$customer = null;

if ($cid > 0) {
    $stmt = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
    $stmt->execute([$cid]);
    $customer = $stmt->fetch();
    if (!$customer) { die('Customer not found.'); }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (post_too_large()) {            // the image was bigger than the server allows
        flash('That image is too large. The limit is 2 MB.', 'error');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    csrf_check();
    if (!$customer) { http_response_code(400); die('Open a customer first to add an order.'); }

    // ONE image per order (optional): validate it and save the file before the INSERT
    [$imagePath, $imageError] = save_order_image($_FILES['image'] ?? null);
    if ($imageError) {
        flash($imageError, 'error');
        header("Location: orders.php?customer_id=$cid");
        exit;
    }

    // date_added fills itself; added_by / last_updated_by record WHO did it
    $stmt = $pdo->prepare(
        'INSERT INTO orders (customer_id, item_name, quantity, price, pickup_date, image_path, added_by, last_updated_by)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $cid, trim($_POST['item_name']), (int)$_POST['quantity'], $_POST['price'], $_POST['pickup_date'],
        $imagePath, current_user_id(), current_user_id(),
    ]);
    flash('Order added.');
    header("Location: orders.php?customer_id=$cid");
    exit;
}

// ---- SEARCH: ?search=... (GET) ----
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT o.*, c.full_name AS customer_name,
               a.first_name AS add_first, a.last_name AS add_last,
               u.first_name AS upd_first, u.last_name AS upd_last
        FROM orders o
        INNER JOIN customers c ON c.customer_id = o.customer_id
        LEFT JOIN users a ON a.user_id = o.added_by
        LEFT JOIN users u ON u.user_id = o.last_updated_by';
$where = ['o.deleted_at IS NULL'];   // orders in the recycle bin are not listed here
$params = [];

if ($customer) {                       // only this customer's orders
    $where[]  = 'o.customer_id = ?';
    $params[] = $cid;
}
if ($search !== '') {                  // text found in any of these columns (customer name included)
    $where[]  = "CONCAT_WS(' ', o.order_id, o.item_name, o.quantity, o.price, o.pickup_date, o.date_added, c.full_name) LIKE ?";
    $params[] = like($search);
}
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY o.order_id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

$back = back_param();                  // lets Edit / Delete return to these same results

include 'includes/header.php';
?>
<?php if ($customer): ?>
    <a class="back" href="index.php">&larr; All customers</a>
    <h2>Orders of <?= e($customer['full_name']) ?></h2>
<?php else: ?>
    <h2>Orders</h2>
<?php endif; ?>

<form method="get" action="orders.php" class="panel search" role="search">
    <?php if ($customer): ?>
        <input type="hidden" name="customer_id" value="<?= $cid ?>">
    <?php endif; ?>
    <label class="grow"><span>Search orders</span>
        <input type="search" name="search" value="<?= e($search) ?>"
               placeholder="Item, customer, price, pickup date, ID or date added">
    </label>
    <button type="submit">Search</button>
    <?php if ($search !== ''): ?>
        <a class="btn ghost" href="orders.php<?= $customer ? '?customer_id=' . $cid : '' ?>">Clear</a>
    <?php endif; ?>
</form>

<?php if ($search !== ''): ?>
    <p class="result-note">
        <?= count($orders) ?> <?= count($orders) === 1 ? 'order matches' : 'orders match' ?>
        &ldquo;<?= e($search) ?>&rdquo;
    </p>
<?php endif; ?>

<?php if ($customer): ?>
<form method="post" enctype="multipart/form-data" class="panel">
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
        <label><span>Image <span class="hint">(optional, max 2 MB)</span></span>
            <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
        </label>
        <button type="submit">Add order</button>
    </div>
</form>
<?php endif; ?>

<div class="table-panel">
<?php if (!$orders): ?>
    <p class="empty">
        <?php if ($search !== ''): ?>
            No orders match your search. Try a different word or clear the search.
        <?php elseif ($customer): ?>
            <?= e($customer['full_name']) ?> has no orders yet. Add one above.
        <?php else: ?>
            No orders yet. Open a customer and add their first order.
        <?php endif; ?>
    </p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <?php if (!$customer): ?><th>Customer</th><?php endif; ?>
            <th>Image</th><th>Item</th><th class="num">Qty</th><th class="num">Price</th><th>Pickup</th>
            <th>Added</th><th>Last updated</th><th><span class="sr">Actions</span></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $o): ?>
        <tr>
            <td data-label="ID"><?= $o['order_id'] ?></td>
            <?php if (!$customer): ?>
                <td data-label="Customer">
                    <a href="orders.php?customer_id=<?= $o['customer_id'] ?>"><?= e($o['customer_name']) ?></a>
                </td>
            <?php endif; ?>
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
            <td data-label="Added"><div><?= who($o['add_first'], $o['add_last']) ?>
                <small><?= fmt_datetime($o['date_added']) ?></small></div></td>
            <td data-label="Last updated"><div><?= who($o['upd_first'], $o['upd_last']) ?>
                <small><?= fmt_datetime($o['last_updated']) ?></small></div></td>
            <td class="actions-cell">
                <div class="actions">
                    <a class="btn ghost" href="order_edit.php?id=<?= $o['order_id'] ?>&back=<?= $back ?>">Edit</a>
                    <form method="post" action="order_delete.php?id=<?= $o['order_id'] ?>&back=<?= $back ?>" class="inline"
                          onsubmit="return confirm('Move this order to the recycle bin?')">
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
