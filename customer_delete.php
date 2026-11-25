<?php
// customer_delete.php - PARENT: delete (private; orders are removed by ON DELETE CASCADE)
require 'includes/db.php';
require 'includes/auth.php';
require 'includes/upload.php';
require_login();

// Deleting changes data, so only POST requests with a valid CSRF token are accepted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);
    // remember the pictures of this customer's orders (the orders are removed by the cascade)
    $imgs = $pdo->prepare('SELECT image_path FROM orders WHERE customer_id = ? AND image_path IS NOT NULL');
    $imgs->execute([$id]);
    $images = $imgs->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare('DELETE FROM customers WHERE customer_id = ?');
    $stmt->execute([$id]);
    if ($stmt->rowCount() > 0) {
        foreach ($images as $path) { delete_order_image($path); }
        flash('Customer and their orders deleted.');
    } else {
        flash('That customer no longer exists.', 'error');
    }
}
header('Location: ' . back_url('index.php'));   // back to the same search results
exit;
