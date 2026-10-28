<?php
// customer_edit.php - PARENT: update (private)
require 'includes/db.php';
require 'includes/auth.php';
require_login();

$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    // updated_by = the logged-in user, last_updated = right now
    $stmt = $pdo->prepare(
        'UPDATE customers
         SET full_name = ?, phone = ?, email = ?, updated_by = ?, last_updated = CURRENT_TIMESTAMP
         WHERE customer_id = ?'
    );
    $stmt->execute([
        trim($_POST['full_name']), trim($_POST['phone']), trim($_POST['email']),
        current_user_id(), $id,
    ]);
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM customers WHERE customer_id = ?');
$stmt->execute([$id]);
$c = $stmt->fetch();
if (!$c) { die('Customer not found.'); }

include 'includes/header.php';
?>
<a class="back" href="index.php">&larr; All customers</a>
<h2>Edit customer</h2>
<form method="post" class="panel">
    <?= csrf_field() ?>
    <div class="form-stack">
        <label><span>Full name</span>
            <input type="text" name="full_name" value="<?= e($c['full_name']) ?>" required>
        </label>
        <label><span>Phone number</span>
            <input type="text" name="phone" value="<?= e($c['phone']) ?>" required>
        </label>
        <label><span>Email <span class="hint">(optional)</span></span>
            <input type="email" name="email" value="<?= e($c['email']) ?>">
        </label>
        <button type="submit">Save changes</button>
    </div>
</form>
<?php include 'includes/footer.php'; ?>
