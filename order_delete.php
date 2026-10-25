<?php
// order_delete.php - CHILD: delete
require 'includes/db.php';
$id  = (int)($_GET['id'] ?? 0);
$cid = (int)($_GET['customer_id'] ?? 0);
$stmt = $pdo->prepare('DELETE FROM orders WHERE order_id = ?');
$stmt->execute([$id]);
header("Location: orders.php?customer_id=$cid");
exit;
