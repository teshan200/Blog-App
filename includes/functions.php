<?php
/**
 * Helper functions used across the app.
 */

// Start a session exactly once (some pages include this multiple times).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape a string for safe output in HTML (prevents XSS).
 * Shortcut so we don't type the long form everywhere.
 */
function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect to a URL and stop the script.
 */
function redirect($path) {
    header('Location: ' . $path);
    exit;
}

/**
 * Store a one-time flash message in the session.
 * Read it on the next request with display_flash().
 */
function set_flash($type, $message) {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/**
 * Print any flash messages and clear them.
 * $type 'error' shows red, 'success' shows green.
 */
function display_flash() {
    if (empty($_SESSION['flash'])) return;
    foreach ($_SESSION['flash'] as $flash) {
        $cls = $flash['type'] === 'error' ? 'flash-error' : 'flash-success';
        echo '<div class="flash ' . $cls . '">' . e($flash['message']) . '</div>';
    }
    unset($_SESSION['flash']);
}

/**
 * Render Markdown text to safe HTML using Parsedown.
 * Parsedown escapes raw HTML by default, which prevents XSS.
 */
function render_markdown($text) {
    static $parsedown = null;
    if ($parsedown === null) {
        require_once __DIR__ . '/../lib/Parsedown.php';
        $parsedown = new Parsedown();
        $parsedown->setSafeMode(true); // strips dangerous HTML
    }
    return $parsedown->text($text ?? '');
}

/**
 * Generate (or return existing) a CSRF token stored in the session.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Print a hidden CSRF input for use inside <form>.
 */
function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

/**
 * Verify the CSRF token submitted with a POST form.
 * Kills the request if it doesn't match.
 */
function csrf_verify() {
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        die('Invalid CSRF token.');
    }
}

/**
 * Validate and save an uploaded image file to /uploads/.
 * Returns the relative path (e.g. "uploads/abc123.jpg") on success,
 * or an error string on failure. Caller checks is_string() vs str_starts_with().
 */
function upload_image($file) {
    // No file uploaded
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return 'No file uploaded.';
    }

    // Size check (5 MB max)
    if ($file['size'] > 5 * 1024 * 1024) {
        return 'Image too large (max 5 MB).';
    }

    // MIME check
    $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!in_array($mime, $allowed, true)) {
        return 'Only JPG, PNG, GIF, and WebP images are allowed.';
    }

    // Validate image integrity
    if (!getimagesize($file['tmp_name'])) {
        return 'The uploaded file is not a valid image.';
    }

    // Save with a unique name
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = bin2hex(random_bytes(12)) . '.' . $ext;
    $dest = __DIR__ . '/../uploads/' . $name;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return 'Failed to save image on the server.';
    }

    return 'uploads/' . $name;
}

/**
 * Format a MySQL timestamp into a friendlier date.
 */
function format_date($datetime) {
    $ts = strtotime($datetime);
    return date('j M Y, g:ia', $ts);
}
