<?php
/**
 * Admin dashboard.
 * Shows an overview: total posts, total users, latest posts.
 */
$root = '../';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

// Count posts
$totalPosts = $pdo->query('SELECT COUNT(*) FROM blogPost')->fetchColumn();

// Count users
$totalUsers = $pdo->query('SELECT COUNT(*) FROM user')->fetchColumn();

// Latest 5 posts
$stmt = $pdo->query(
    'SELECT p.id, p.title, p.created_at, u.username
     FROM blogPost p JOIN user u ON u.id = p.user_id
     ORDER BY p.created_at DESC LIMIT 5'
);
$recentPosts = $stmt->fetchAll();

// Latest 5 registered users
$stmt = $pdo->query(
    'SELECT id, username, email, role, created_at
     FROM user ORDER BY created_at DESC LIMIT 5'
);
$recentUsers = $stmt->fetchAll();

$title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-head">
    <h1>Admin Dashboard</h1>
</div>

<div class="admin-stats">
    <div class="stat-card">
        <span class="stat-number"><?= $totalPosts ?></span>
        <span class="stat-label">Total Posts</span>
    </div>
    <div class="stat-card">
        <span class="stat-number"><?= $totalUsers ?></span>
        <span class="stat-label">Total Users</span>
    </div>
</div>

<div class="admin-grid">
    <div class="card admin-section">
        <h2>Recent Posts</h2>
        <?php if (empty($recentPosts)): ?>
            <p class="muted">No posts yet.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr><th>Title</th><th>Author</th><th>Date</th><th></th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentPosts as $p): ?>
                    <tr>
                        <td><a href="<?= $root ?>post.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a></td>
                        <td><?= e($p['username']) ?></td>
                        <td><?= format_date($p['created_at']) ?></td>
                        <td><a href="<?= $root ?>edit.php?id=<?= (int)$p['id'] ?>" class="btn-sm">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <a href="posts.php" class="btn">View all posts &rarr;</a>
        <?php endif; ?>
    </div>

    <div class="card admin-section">
        <h2>Recent Users</h2>
        <?php if (empty($recentUsers)): ?>
            <p class="muted">No users yet.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr><th>Username</th><th>Email</th><th>Role</th><th>Joined</th></tr>
                </thead>
                <tbody>
                <?php foreach ($recentUsers as $u): ?>
                    <tr>
                        <td><?= e($u['username']) ?></td>
                        <td><?= e($u['email']) ?></td>
                        <td><?= e($u['role']) ?></td>
                        <td><?= format_date($u['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <a href="users.php" class="btn">View all users &rarr;</a>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
