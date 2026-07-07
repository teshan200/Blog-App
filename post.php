<?php
/**
 * Single blog post view (?id=123).
 * Validates the id, fetches the post, and shows edit/delete buttons
 * only if the current user is the owner.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Validate the id from the URL.
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'Invalid post id.');
    redirect('index.php');
}

// Prepared statement: bind the integer id.
$stmt = $pdo->prepare(
    'SELECT p.id, p.title, p.content, p.featured_image, p.created_at, p.updated_at, p.user_id, u.username
     FROM blogPost p
     JOIN user u ON u.id = p.user_id
     WHERE p.id = :id'
);
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    set_flash('error', 'Post not found.');
    redirect('index.php');
}

// Is the current user allowed to edit/delete this post?
$can_modify = is_logged_in() && (current_user_id() === (int)$post['user_id'] || is_admin());

$title = e($post['title']);
require __DIR__ . '/includes/header.php';
?>
<article class="card single-post">
    <?php if ($post['featured_image']): ?>
        <div class="single-featured-img">
            <img src="<?= e($post['featured_image']) ?>" alt="Featured image">
        </div>
    <?php endif; ?>

    <h1 class="post-title"><?= e($post['title']) ?></h1>
    <div class="post-meta">
        <span>by <?= e($post['username']) ?></span>
        <span>&middot;</span>
        <time>posted <?= format_date($post['created_at']) ?></time>
        <?php if ($post['updated_at'] !== $post['created_at']): ?>
            <span>&middot;</span>
            <span>updated <?= format_date($post['updated_at']) ?></span>
        <?php endif; ?>
    </div>

    <div class="post-content">
        <?= render_markdown($post['content']) ?>
    </div>

    <?php if ($can_modify): ?>
        <div class="post-actions">
            <a href="edit.php?id=<?= (int)$post['id'] ?>" class="btn">Edit</a>
            <form method="post" action="delete.php" class="inline-form"
                  onsubmit="return confirm('Delete this post? This cannot be undone.');">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int)$post['id'] ?>">
                <button type="submit" class="btn btn-danger">Delete</button>
            </form>
        </div>
    <?php endif; ?>

    <p class="muted"><a href="index.php">&larr; Back to all posts</a></p>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
