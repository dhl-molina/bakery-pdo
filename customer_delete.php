<?php
// customer_delete.php - PARENT: delete (private; orders are removed by ON DELETE CASCADE)
require 'includes/db.php';
require 'includes/auth.php';
require_login();

// Deleting changes data, so only POST requests with a valid CSRF token are accepted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM customers WHERE customer_id = ?');
    $stmt->execute([$id]);
}
header('Location: index.php');
exit;
