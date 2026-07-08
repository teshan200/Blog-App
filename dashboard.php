<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

$userId = current_user_id();

$totalPosts = $pdo->prepare('SELECT COUNT(*) FROM blogPost WHERE user_id = :uid');
$totalPosts->execute([':uid' => $userId]);
$totalPosts = $totalPosts->fetchColumn();

$stmt = $pdo->prepare(
    'SELECT id, title, created_at FROM blogPost WHERE user_id = :uid ORDER BY created_at DESC'
);
$stmt->execute([':uid' => $userId]);
$myPosts = $stmt->fetchAll();

$title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>My Dashboard</h1>
    <a href="create.php" class="btn">+ New Post</a>
</div>

<div class="admin-stats">
    <div class="stat-card">
        <span class="stat-number"><?= $totalPosts ?></span>
        <span class="stat-label">My Posts</span>
    </div>
</div>

<div class="card" style="margin-top: 1.5rem;">
    <h2>My Posts</h2>
    <?php if (empty($myPosts)): ?>
        <p class="muted">You haven't written anything yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <thead>
                <tr><th>Title</th><th>Date</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($myPosts as $p): ?>
                <tr>
                    <td><a href="post.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a></td>
                    <td><?= format_date($p['created_at']) ?></td>
                    <td>
                        <a href="edit.php?id=<?= (int)$p['id'] ?>" class="btn-sm">Edit</a>
                        <form method="post" action="delete.php" class="inline-form"
                              onsubmit="return confirm('Delete this post?');">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
