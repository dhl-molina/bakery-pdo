<?php
// order_edit.php - CHILD: update (private)
require 'includes/db.php';
require 'includes/auth.php';
require 'includes/upload.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

// orders in the recycle bin cannot be edited (restore them first)
$stmt = $pdo->prepare('SELECT * FROM orders WHERE order_id = ? AND deleted_at IS NULL');
$stmt->execute([$id]);
$o = $stmt->fetch();
if (!$o) { die('Order not found.'); }

// where to return: the search results the user came from, or this customer's orders
$back = back_url('orders.php?customer_id=' . $o['customer_id']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (post_too_large()) {
        flash('That image is too large. The limit is 2 MB.', 'error');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
    csrf_check();

    // A new image replaces the old one (still only ONE image per order)
    [$newImage, $imageError] = save_order_image($_FILES['image'] ?? null);
    if ($imageError) {
        flash($imageError, 'error');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    // last_updated_by = the logged-in user, last_updated = right now
    $stmt = $pdo->prepare(
        'UPDATE orders
         SET item_name = ?, quantity = ?, price = ?, pickup_date = ?, image_path = ?,
             last_updated_by = ?, last_updated = CURRENT_TIMESTAMP
         WHERE order_id = ?'
    );
    $stmt->execute([
        trim($_POST['item_name']), (int)$_POST['quantity'], $_POST['price'], $_POST['pickup_date'],
        $newImage ?? $o['image_path'],          // keep the current image when no new one was chosen
        current_user_id(), $id,
    ]);
    if ($newImage) {
        delete_order_image($o['image_path']);   // the replaced file is no longer needed
    }
    flash('Order updated.');
    header('Location: ' . $back);
    exit;
}

include 'includes/header.php';
?>
<a class="back" href="<?= e($back) ?>">&larr; Back to orders</a>
<h2>Edit order</h2>
<form method="post" enctype="multipart/form-data" class="panel">
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
        <div class="current-image">
            <?php if ($o['image_path']): ?>
                <img class="thumb big" src="<?= e($o['image_path']) ?>" alt="Current photo of <?= e($o['item_name']) ?>">
            <?php endif; ?>
            <label class="grow"><span>
                <?= $o['image_path'] ? 'Replace image' : 'Image' ?> <span class="hint">(optional, max 2 MB)</span>
            </span>
                <input type="file" name="image" accept="image/jpeg,image/png,image/gif,image/webp">
            </label>
        </div>
        <button type="submit">Save changes</button>
    </div>
</form>
<?php include 'includes/footer.php'; ?>
