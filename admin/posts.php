<?php
/**
 * Admin: manage all blog posts.
 */
$root = '../';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

// Fetch all posts with author info
$stmt = $pdo->query(
    'SELECT p.id, p.title, p.created_at, p.user_id, u.username
     FROM blogPost p JOIN user u ON u.id = p.user_id
     ORDER BY p.created_at DESC'
);
$posts = $stmt->fetchAll();

$title = 'Manage Posts';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-head">
    <h1>Manage Posts</h1>
    <a href="<?= $root ?>create.php" class="btn">+ New Post</a>
</div>

<?php if (empty($posts)): ?>
    <p class="muted">No posts have been created yet.</p>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($posts as $p): ?>
                <tr>
                    <td><a href="<?= $root ?>post.php?id=<?= (int)$p['id'] ?>"><?= e($p['title']) ?></a></td>
                    <td><?= e($p['username']) ?></td>
                    <td><?= format_date($p['created_at']) ?></td>
                    <td class="action-cell">
                        <a href="<?= $root ?>edit.php?id=<?= (int)$p['id'] ?>" class="btn-sm">Edit</a>
                        <form method="post" action="<?= $root ?>delete.php" class="inline-form"
                              onsubmit="return confirm('Delete this post? This cannot be undone.');">
                            <?php csrf_field(); ?>
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn-sm btn-sm-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
