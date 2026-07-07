<?php
/**
 * Home page: lists every blog post, newest first.
 * Shows author name via a JOIN and a short snippet of the content.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Fetch all posts with the author's username (one prepared query, no user input).
$sql = 'SELECT p.id, p.title, p.content, p.featured_image, p.created_at, u.username
        FROM blogPost p
        JOIN user u ON u.id = p.user_id
        ORDER BY p.created_at DESC';
$posts = $pdo->query($sql)->fetchAll();

$title = 'Home';
require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
    <h1>Recent Posts</h1>
    <?php if (is_logged_in()): ?>
        <a href="create.php" class="btn">+ New Post</a>
    <?php endif; ?>
</div>

<?php if (empty($posts)): ?>
    <p class="muted">No posts yet. Be the first to write something!</p>
<?php else: ?>
    <div class="post-grid">
        <?php foreach ($posts as $post):
            // Render markdown then strip tags to get a clean plain-text snippet.
            $snippet = mb_substr(strip_tags(render_markdown($post['content'])), 0, 180);
            if (mb_strlen(strip_tags(render_markdown($post['content']))) > 180) $snippet .= '&hellip;';
        ?>
            <article class="card post-card">
                <?php if ($post['featured_image']): ?>
                    <div class="post-card-img">
                        <a href="post.php?id=<?= (int)$post['id'] ?>">
                            <img src="<?= e($post['featured_image']) ?>" alt="">
                        </a>
                    </div>
                <?php endif; ?>
                <div class="post-card-body">
                    <h2 class="post-title"><a href="post.php?id=<?= (int)$post['id'] ?>"><?= e($post['title']) ?></a></h2>
                    <div class="post-meta">
                        <span>by <?= e($post['username']) ?></span>
                        <span>&middot;</span>
                        <time><?= format_date($post['created_at']) ?></time>
                    </div>
                    <p class="post-snippet"><?= $snippet ?></p>
                    <a href="post.php?id=<?= (int)$post['id'] ?>" class="read-more">Read more &rarr;</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
