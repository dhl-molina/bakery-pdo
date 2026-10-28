<?php
// login.php - USER MODULE: log in with email + password
require 'includes/db.php';
require 'includes/auth.php';

// Already logged in? Go straight to the homepage.
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([trim($_POST['email'] ?? '')]);
    $user = $stmt->fetch();

    // password_verify() compares the typed password with the stored hash
    if ($user && password_verify($_POST['password'] ?? '', $user['password_hash'])) {
        session_regenerate_id(true);               // new session id after login (anti session-fixation)
        $_SESSION['user_id']   = (int)$user['user_id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        header('Location: index.php');
        exit;
    }
    // Same message for "no such email" and "wrong password" so nothing is leaked
    $error = 'Incorrect email or password.';
}

include 'includes/header.php';
?>
<div class="auth-box">
    <h2>Log in</h2>

    <?php if (isset($_GET['registered'])): ?>
        <p class="alert success">Account created! You can log in now.</p>
    <?php endif; ?>
    <?php if (isset($_GET['loggedout'])): ?>
        <p class="alert success">You have been logged out.</p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="alert error" role="alert"><?= e($error) ?></p>
    <?php endif; ?>

    <form method="post" class="panel">
        <?= csrf_field() ?>
        <div class="form-stack">
            <label><span>Email</span>
                <input type="email" name="email" required autofocus>
            </label>
            <label><span>Password</span>
                <input type="password" name="password" required>
            </label>
            <button type="submit">Log in</button>
        </div>
    </form>
    <p class="switch">No account yet? <a href="register.php">Create one</a></p>
</div>
<?php include 'includes/footer.php'; ?>
