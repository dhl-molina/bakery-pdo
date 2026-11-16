<?php
// order_delete.php - CHILD: delete (private, POST + CSRF token only)
require 'includes/db.php';
require 'includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM orders WHERE order_id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        flash('Order deleted.');
    } else {
        flash('That order no longer exists.', 'error');
    }
}
header('Location: ' . back_url('orders.php'));   // back to the same search results
exit;
