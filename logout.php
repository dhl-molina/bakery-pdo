<?php
// logout.php - USER MODULE: end the session
require 'includes/db.php';
require 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $_SESSION = [];                // forget the user
    session_destroy();             // remove the session on the server
    // (also remove the session cookie from the browser)
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}

header('Location: login.php?loggedout=1');
exit;
