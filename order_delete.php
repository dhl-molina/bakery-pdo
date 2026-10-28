<?php
// order_delete.php - CHILD: delete (private, POST + CSRF token only)
require 'includes/db.php';
require 'includes/auth.php';
require_login();

$cid = (int)($_GET['customer_id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM orders WHERE order_id = ?');
    $stmt->execute([$id]);
}
header("Location: orders.php?customer_id=$cid");
exit;
