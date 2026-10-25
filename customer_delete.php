<?php
// customer_delete.php - PARENT: delete (orders are removed by ON DELETE CASCADE)
require 'includes/db.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('DELETE FROM customers WHERE customer_id = ?');
$stmt->execute([$id]);
header('Location: index.php');
exit;
