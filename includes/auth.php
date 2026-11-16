<?php
// includes/auth.php - session, login guard, CSRF and small helpers.
// Load it AFTER includes/db.php on every page.

// Start the session once so $_SESSION is available (it remembers who is logged in)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// true when a user has logged in
function is_logged_in(): bool
{
    return isset($_SESSION['user_id']);
}

// Put this at the top of every page that must be private.
// Not logged in? Send the visitor to login.php and stop running the page.
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

// The id of the logged-in user. It is saved in added_by / last_updated_by.
function current_user_id(): ?int
{
    return $_SESSION['user_id'] ?? null;
}

// Shortcut for htmlspecialchars (prevents HTML injection when printing values)
function e($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// "Maria Santos" or a dash when the user no longer exists
function who(?string $first, ?string $last): string
{
    return $first === null ? '—' : e(trim($first . ' ' . $last));
}

// "Nov 5, 2026" from a DATE value
function fmt_date(?string $date): string
{
    return $date ? date('M j, Y', strtotime($date)) : '—';
}

// "Oct 28, 2026, 2:03 AM" from a TIMESTAMP value
function fmt_datetime(?string $ts): string
{
    return $ts ? date('M j, Y, g:i A', strtotime($ts)) : '—';
}

// --- Responses (flash messages), as in the "returning a response" lesson ---
// Save a message in the session, then redirect. The next page shows it once and removes it.
function flash(string $text, string $type = 'success'): void
{
    $_SESSION['flash'] = ['text' => $text, 'type' => $type];
}

// Returns the saved message (and forgets it) or null. Called by includes/header.php.
function flash_get(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

// --- Searching ---
// Turns what the user typed into a LIKE pattern: "mar" -> "%mar%".
// % and _ typed by the user are escaped so they are searched literally.
function like(string $text): string
{
    return '%' . addcslashes($text, '\\%_') . '%';
}

// --- Going back to the search results after an edit or delete ---
// A list page adds "&back=<its own address>" to its Edit/Delete links.
// Only index.php / orders.php addresses are accepted, so nobody can use it to redirect elsewhere.
function back_url(string $default): string
{
    $back = $_GET['back'] ?? '';
    return preg_match('/^(index|orders)\.php(\?[A-Za-z0-9_=&%+.\-]*)?$/', $back) ? $back : $default;
}

// The address of the current list page (with its search), url-encoded for a link.
function back_param(): string
{
    $here = basename($_SERVER['PHP_SELF']);
    if (($_SERVER['QUERY_STRING'] ?? '') !== '') {
        $here .= '?' . $_SERVER['QUERY_STRING'];
    }
    return urlencode($here);
}

// --- CSRF protection: every POST form carries a secret token ---
function csrf_field(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
}

function csrf_check(): void
{
    $sent   = $_POST['csrf'] ?? '';
    $stored = $_SESSION['csrf'] ?? '';
    // an empty token must never pass (it would match an empty session value)
    if ($sent === '' || $stored === '' || !hash_equals($stored, $sent)) {
        http_response_code(400);
        die('Invalid request. Go back, refresh the page and try again.');
    }
}
