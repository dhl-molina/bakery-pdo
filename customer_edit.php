<?php
// customer_edit.php - PARENT: update
require 'includes/db.php';
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('UPDATE customers SET full_name = ?, phone = ?, email = ? WHERE customer_id = ?');
    $stmt->execute([$_POST['full_name'], $_POST['phone'], $_POST['email'], $id]);
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { die('Customer not found.'); }

include 'includes/header.php';
?>
<h2>Edit Customer</h2>
<form method="post">
    <input type="text" name="full_name" value="<?= htmlspecialchars($c['full_name']) ?>" required>
    <input type="text" name="phone" value="<?= htmlspecialchars($c['phone']) ?>" required>
    <input type="email" name="email" value="<?= htmlspecialchars($c['email']) ?>">
    <button type="submit">Save Changes</button>
</form>
<?php include 'includes/footer.php'; ?>
