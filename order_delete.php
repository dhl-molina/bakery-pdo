<?php
// order_delete.php - CHILD: "delete" = move to the RECYCLE BIN (private, POST + CSRF token only)
// The row stays in the table. deleted_at / deleted_by record WHEN and WHO.
require 'includes/db.php';
require 'includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $id = (int)($_GET['id'] ?? 0);

    // last_updated = last_updated keeps the "last edited" time as it was (a delete is not an edit)
    $stmt = $pdo->prepare(
        'UPDATE orders
         SET deleted_at = CURRENT_TIMESTAMP, deleted_by = ?, last_updated = last_updated
         WHERE order_id = ? AND deleted_at IS NULL'
    );
    $stmt->execute([current_user_id(), $id]);

    if ($stmt->rowCount() > 0) {
        flash('Order moved to the recycle bin.');
    } else {
        flash('That order no longer exists.', 'error');
    }
}
header('Location: ' . back_url('orders.php'));   // back to the same search results
exit;
