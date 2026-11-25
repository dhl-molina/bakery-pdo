<?php
// index.php - HOMEPAGE (private) - PARENT: search + list + insert customers
require 'includes/db.php';
require 'includes/auth.php';
require_login();                       // not logged in -> redirected to login.php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // date_added fills itself (DEFAULT CURRENT_TIMESTAMP); added_by / last_updated_by record WHO did it
    $stmt = $pdo->prepare(
        'INSERT INTO customers (full_name, phone, email, added_by, last_updated_by) VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        trim($_POST['full_name']), trim($_POST['phone']), trim($_POST['email']),
        current_user_id(), current_user_id(),
    ]);
    flash('Customer added.');          // the response shown on the next page
    header('Location: index.php');
    exit;
}

// ---- SEARCH: the form sends ?search=... (GET), so the result page can be bookmarked or reloaded ----
$search = trim($_GET['search'] ?? '');

// LEFT JOINs to users give us the names of the adder and the last updater
$sql = 'SELECT c.*, COUNT(o.order_id) AS total_orders,
               a.first_name AS add_first, a.last_name AS add_last,
               u.first_name AS upd_first, u.last_name AS upd_last
        FROM customers c
        LEFT JOIN orders o ON o.customer_id = c.customer_id AND o.deleted_at IS NULL
        LEFT JOIN users  a ON a.user_id = c.added_by
        LEFT JOIN users  u ON u.user_id = c.last_updated_by';
$params = [];

if ($search !== '') {
    // Look for the text in every searchable column at once (CONCAT_WS skips empty emails)
    $sql .= " WHERE CONCAT_WS(' ', c.customer_id, c.full_name, c.phone, c.email, c.date_added) LIKE ?";
    $params[] = like($search);
}

$sql .= ' GROUP BY c.customer_id, a.user_id, u.user_id ORDER BY c.customer_id DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$back = back_param();                  // lets Edit / Delete return to these same results

include 'includes/header.php';
?>
<h2>Customers</h2>

<form method="get" action="index.php" class="panel search" role="search">
    <label class="grow"><span>Search customers</span>
        <input type="search" name="search" value="<?= e($search) ?>"
               placeholder="Name, phone, email, ID or date added (e.g. 2026-10-29)">
    </label>
    <button type="submit">Search</button>
    <?php if ($search !== ''): ?>
        <a class="btn ghost" href="index.php">Clear</a>
    <?php endif; ?>
</form>

<?php if ($search !== ''): ?>
    <p class="result-note">
        <?= count($customers) ?> <?= count($customers) === 1 ? 'customer matches' : 'customers match' ?>
        &ldquo;<?= e($search) ?>&rdquo;
    </p>
<?php endif; ?>

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
    <p class="empty">
        <?= $search !== '' ? 'No customers match your search. Try a different word or clear the search.'
                           : 'No customers yet. Add the first one above.' ?>
    </p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th class="num">Orders</th>
            <th>Added</th><th>Last updated</th><th><span class="sr">Actions</span></th>
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
            <td data-label="Added"><div><?= who($c['add_first'], $c['add_last']) ?>
                <small><?= fmt_datetime($c['date_added']) ?></small></div></td>
            <td data-label="Last updated"><div><?= who($c['upd_first'], $c['upd_last']) ?>
                <small><?= fmt_datetime($c['last_updated']) ?></small></div></td>
            <td class="actions-cell">
                <div class="actions">
                    <a class="btn ghost" href="orders.php?customer_id=<?= $c['customer_id'] ?>">View orders</a>
                    <a class="btn ghost" href="customer_edit.php?id=<?= $c['customer_id'] ?>&back=<?= $back ?>">Edit</a>
                    <form method="post" action="customer_delete.php?id=<?= $c['customer_id'] ?>&back=<?= $back ?>" class="inline"
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
