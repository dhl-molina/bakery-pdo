<?php
// register.php - USER MODULE: create a new account
require 'includes/db.php';
require 'includes/auth.php';

// Already logged in? No need to register again.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$errors = [];
$old = ['first_name' => '', 'last_name' => '', 'address' => '', 'age' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    // Keep what the user typed so the form can be refilled when there is an error
    foreach ($old as $field => $_) {
        $old[$field] = trim($_POST[$field] ?? '');
    }
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if ($old['first_name'] === '' || $old['last_name'] === '' || $old['address'] === '') {
        $errors[] = 'First name, last name and address are required.';
    }
    if (!ctype_digit($old['age']) || (int)$old['age'] < 1 || (int)$old['age'] > 120) {
        $errors[] = 'Age must be a number between 1 and 120.';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // Email must be unique because it is the login name
    if (!$errors) {
        $stmt = $pdo->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->execute([$old['email']]);
        if ($stmt->fetch()) {
            $errors[] = 'That email is already registered. Try logging in instead.';
        }
    }

    // --- Save the user ---
    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO users (first_name, last_name, address, age, email, password_hash)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $old['first_name'], $old['last_name'], $old['address'], (int)$old['age'], $old['email'],
            password_hash($password, PASSWORD_DEFAULT),   // store only the hash
        ]);
        header('Location: login.php?registered=1');
        exit;
    }
}

include 'includes/header.php';
?>
<div class="auth-box">
    <h2>Create an account</h2>

    <?php foreach ($errors as $err): ?>
        <p class="alert error" role="alert"><?= e($err) ?></p>
    <?php endforeach; ?>

    <form method="post" class="panel">
        <?= csrf_field() ?>
        <div class="form-stack">
            <label><span>First name</span>
                <input type="text" name="first_name" value="<?= e($old['first_name']) ?>" required>
            </label>
            <label><span>Last name</span>
                <input type="text" name="last_name" value="<?= e($old['last_name']) ?>" required>
            </label>
            <label><span>Address</span>
                <input type="text" name="address" value="<?= e($old['address']) ?>" required>
            </label>
            <label><span>Age</span>
                <input type="number" name="age" min="1" max="120" value="<?= e($old['age']) ?>" required>
            </label>
            <label><span>Email <span class="hint">(you log in with this)</span></span>
                <input type="email" name="email" value="<?= e($old['email']) ?>" required>
            </label>
            <label><span>Password <span class="hint">(at least 8 characters)</span></span>
                <input type="password" name="password" required>
            </label>
            <label><span>Confirm password</span>
                <input type="password" name="confirm_password" required>
            </label>
            <button type="submit">Create account</button>
        </div>
    </form>
    <p class="switch">Already have an account? <a href="login.php">Log in</a></p>
</div>
<?php include 'includes/footer.php'; ?>
