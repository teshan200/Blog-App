<?php
/**
 * Admin: delete a user.
 * POST only. Cannot delete yourself or other admins.
 * The user's posts are deleted via CASCADE.
 */
$root = '../';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_flash('error', 'Invalid request method.');
    redirect('index.php');
}

csrf_verify();

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'Invalid user id.');
    redirect('users.php');
}

// Prevent deleting yourself
if ($id === current_user_id()) {
    set_flash('error', 'You cannot delete your own account.');
    redirect('users.php');
}

// Check the target user exists and is not an admin
$stmt = $pdo->prepare('SELECT id, role FROM user WHERE id = :id');
$stmt->execute([':id' => $id]);
$target = $stmt->fetch();

if (!$target) {
    set_flash('error', 'User not found.');
    redirect('users.php');
}

if ($target['role'] === 'admin') {
    set_flash('error', 'Cannot delete another admin.');
    redirect('users.php');
}

// Delete user (FK CASCADE will remove their posts)
$stmt = $pdo->prepare('DELETE FROM user WHERE id = :id');
$stmt->execute([':id' => $id]);

set_flash('success', 'User deleted along with all their posts.');
redirect('users.php');
