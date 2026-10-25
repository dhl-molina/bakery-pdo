<?php
// index.php - PARENT: list + insert customers
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare('INSERT INTO customers (full_name, phone, email) VALUES (?, ?, ?)');
    $stmt->execute([$_POST['full_name'], $_POST['phone'], $_POST['email']]);
    header('Location: index.php');
    exit;
}

$customers = $pdo->query(
    'SELECT c.*, COUNT(o.order_id) AS total_orders
     FROM customers c
     LEFT JOIN orders o ON o.customer_id = c.customer_id
     GROUP BY c.customer_id
     ORDER BY c.customer_id DESC'
)->fetchAll();

include 'includes/header.php';
?>
<h2>Register Customer</h2>
<form method="post">
    <input type="text" name="full_name" placeholder="Full name" required>
    <input type="text" name="phone" placeholder="Phone number" required>
    <input type="email" name="email" placeholder="Email (optional)">
    <button type="submit">Add Customer</button>
</form>

<h2>Customers</h2>
<table>
    <tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Orders</th><th>Actions</th></tr>
    <?php foreach ($customers as $c): ?>
    <tr>
        <td><?= $c['customer_id'] ?></td>
        <td><?= htmlspecialchars($c['full_name']) ?></td>
        <td><?= htmlspecialchars($c['phone']) ?></td>
        <td><?= htmlspecialchars($c['email']) ?></td>
        <td><?= $c['total_orders'] ?></td>
        <td>
            <a class="btn" href="orders.php?customer_id=<?= $c['customer_id'] ?>">View Orders</a>
            <a class="btn" href="customer_edit.php?id=<?= $c['customer_id'] ?>">Edit</a>
            <a class="btn danger" href="customer_delete.php?id=<?= $c['customer_id'] ?>"
               onclick="return confirm('Delete this customer and ALL their orders?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php include 'includes/footer.php'; ?>
