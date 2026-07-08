<?php
/**
 * Shared page header: <head>, navbar, opens the main container.
 * Expects $title (page title) to be set before including.
 *
 * If including from a subdirectory (e.g. admin/), set $root = '../' first
 * so that asset links resolve correctly.
 */
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';
$title = $title ?? 'My Blog';
$root  = $root ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> &middot; My Blog</title>
    <link rel="stylesheet" href="<?= $root ?>assets/css/style.css">
    <!-- EasyMDE: a lightweight, vanilla-JS Markdown editor (no framework) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
</head>
<body>
<header class="navbar">
    <div class="nav-inner">
        <a href="<?= $root ?>index.php" class="brand">My Blog</a>
        <nav class="nav-links">
            <a href="<?= $root ?>index.php">Home</a>
            <?php if (is_logged_in()): ?>
                <a href="<?= $root ?>dashboard.php">Dashboard</a>
                <?php if (is_admin()): ?>
                    <a href="<?= $root ?>admin/index.php">Admin</a>
                <?php endif; ?>
                <span class="nav-user">Hi, <?= e(current_username()) ?></span>
                <a href="<?= $root ?>logout.php" class="btn-link">Logout</a>
            <?php else: ?>
                <a href="<?= $root ?>login.php">Login</a>
                <a href="<?= $root ?>register.php" class="btn-link">Register</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container">
<?php display_flash(); ?>
