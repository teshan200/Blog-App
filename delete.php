<?php
/**
 * Delete a blog post.
 * Owner-only POST handler. CSRF-protected.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', 'Invalid request method.');
    redirect('index.php');
}

csrf_verify();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'Invalid post id.');
    redirect('index.php');
}

// Fetch just the user_id for ownership check (prepared statement).
$stmt = $pdo->prepare('SELECT id, user_id FROM blogPost WHERE id = :id');
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    set_flash('error', 'Post not found.');
    redirect('index.php');
}

if ((int)$post['user_id'] !== current_user_id() && !is_admin()) {
    set_flash('error', 'You can only delete your own posts.');
    redirect('index.php');
}

// Delete (prepared statement) — admin can delete any post, owner deletes own
$sql = is_admin()
    ? 'DELETE FROM blogPost WHERE id = :id'
    : 'DELETE FROM blogPost WHERE id = :id AND user_id = :uid';
$params = [':id' => $id];
if (!is_admin()) {
    $params[':uid'] = current_user_id();
}
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

set_flash('success', 'Post deleted.');
redirect('index.php');
