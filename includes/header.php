<?php
// includes/header.php
// The user bar (name + Logout) is only shown when someone is logged in.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bakery Shop</title>
    <!-- Fonts: if you are offline the page falls back to Georgia / system fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;600&family=Young+Serif&display=swap">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="awning" aria-hidden="true"></div>
<header class="site-header">
    <h1 class="brand">Bakery Shop</h1>
    <nav class="site-nav" aria-label="Main">
        <?php if (is_logged_in()): ?>
            <a href="index.php">Customers</a>
            <span class="who">Logged in as <strong><?= e($_SESSION['user_name']) ?></strong></span>
            <form method="post" action="logout.php" class="inline">
                <?= csrf_field() ?>
                <button type="submit">Log out</button>
            </form>
        <?php endif; ?>
    </nav>
</header>
<main>
