<?php
// order_purge.php - CHILD: delete an order FOR GOOD from the recycle bin (private, POST + CSRF token only)
require 'includes/db.php';
require 'includes/auth.php';
require 'includes/upload.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);

    // Only orders that are already in the recycle bin can be removed for good
    $stmt = $pdo->prepare('SELECT image_path FROM orders WHERE order_id = ? AND deleted_at IS NOT NULL');
    $stmt->execute([$id]);
    $order = $stmt->fetch();

    if ($order) {
        $pdo->prepare('DELETE FROM orders WHERE order_id = ?')->execute([$id]);
        delete_order_image($order['image_path']);      // the picture goes too
        flash('Order deleted for good.');
    } else {
        flash('That order is not in the recycle bin.', 'error');
    }
}
header('Location: recycle_bin.php');
exit;
