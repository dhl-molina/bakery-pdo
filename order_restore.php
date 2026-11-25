<?php
// order_restore.php - CHILD: take an order out of the recycle bin (private, POST + CSRF token only)
require 'includes/db.php';
require 'includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);

    // Clear the delete marks. The restore counts as the latest change, so last_updated_by = this user.
    $stmt = $pdo->prepare(
        'UPDATE orders
         SET deleted_at = NULL, deleted_by = NULL, last_updated_by = ?, last_updated = CURRENT_TIMESTAMP
         WHERE order_id = ? AND deleted_at IS NOT NULL'
    );
    $stmt->execute([current_user_id(), $id]);

    if ($stmt->rowCount() > 0) {
        flash('Order restored. It is back in its customer\'s orders.');
    } else {
        flash('That order is not in the recycle bin.', 'error');
    }
}
header('Location: recycle_bin.php');
exit;
