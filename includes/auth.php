<?php
/**
 * Auth helpers: session bootstrap, login state, current user id, require-login.
 */

require_once __DIR__ . '/functions.php';

/**
 * Is the current visitor logged in?
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Get the logged-in user's id (or null).
 */
function current_user_id() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the logged-in user's username (or null).
 */
function current_username() {
    return $_SESSION['username'] ?? null;
}

/**
 * Get the logged-in user's role (or null).
 */
function current_role() {
    return $_SESSION['role'] ?? null;
}

/**
 * Is the current user an admin?
 */
function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Force a page to require login. Redirects to login.php if not logged in.
 */
function require_login() {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in first.');
        redirect('login.php');
    }
}

/**
 * Force a page to require admin role. Redirects to index.php if not admin.
 */
function require_admin() {
    require_login();
    if (!is_admin()) {
        set_flash('error', 'Admin access required.');
        redirect('index.php');
    }
}
