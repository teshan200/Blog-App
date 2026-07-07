<?php
/**
 * Email verification handler.
 * The user arrives here from the link in the verification email.
 * We look up the token, mark the user as verified, and redirect to login.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$token = $_GET['token'] ?? '';

if ($token === '') {
    set_flash('error', 'No verification token provided.');
    redirect('login.php');
}

// Look up a user with this exact token
$stmt = $pdo->prepare('SELECT id, username FROM user WHERE verification_token = :token LIMIT 1');
$stmt->execute([':token' => $token]);
$user = $stmt->fetch();

if (!$user) {
    set_flash('error', 'Invalid or expired verification link.');
    redirect('login.php');
}

// Mark as verified and clear the token (one-time use)
$stmt = $pdo->prepare('UPDATE user SET email_verified = 1, verification_token = NULL WHERE id = :id');
$stmt->execute([':id' => $user['id']]);

set_flash('success', 'Email verified! You can now log in.');
redirect('login.php');
