<?php
/**
 * Logout: clear the session completely and redirect to the home page.
 */
require_once __DIR__ . '/includes/functions.php';

// Wipe all session variables, then destroy the session.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();

// Start a fresh session so flash messages work on the redirect.
session_start();
set_flash('success', 'You have been logged out.');
redirect('index.php');
