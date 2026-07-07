<?php
/**
 * Create a new blog post.
 * Only accessible by logged-in users.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login(); // guard

$errors = [];
$title_input  = '';
$content_input = '';
$featured_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title_input   = trim($_POST['title'] ?? '');
    $content_input = trim($_POST['content'] ?? '');
    $featured_path = null;

    // Validate
    if ($title_input === '' || strlen($title_input) > 150) {
        $errors[] = 'Title is required (max 150 characters).';
    }
    if ($content_input === '') {
        $errors[] = 'Content cannot be empty.';
    }

    // Handle featured image upload if a file was provided
    if (!empty($_FILES['featured_image']['name'])) {
        $result = upload_image($_FILES['featured_image']);
        if (is_string($result) && strncmp($result, 'uploads/', 8) === 0) {
            $featured_path = $result;
        } else {
            $errors[] = $result; // error message from the helper
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO blogPost (user_id, title, content, featured_image)
             VALUES (:uid, :title, :content, :featured)'
        );
        $stmt->execute([
            ':uid'      => current_user_id(),
            ':title'    => $title_input,
            ':content'  => $content_input,
            ':featured' => $featured_path,
        ]);

        $new_id = $pdo->lastInsertId();
        set_flash('success', 'Post created!');
        redirect('post.php?id=' . $new_id);
    }
}

$title = 'New Post';
require __DIR__ . '/includes/header.php';
?>
<div class="card form-card editor-wide">
    <h1>Write a new post</h1>

    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="create.php" class="form" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <label>Title
            <input type="text" name="title" value="<?= e($title_input) ?>" maxlength="150" required>
        </label>
        <label>Content (Markdown supported)
            <textarea name="content" id="editor" rows="14"><?= e($content_input) ?></textarea>
        </label>
        <label>Featured Image
            <input type="file" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp">
            <span class="form-note">Optional. Will appear on the post card and at the top of the post.</span>
        </label>
        <button type="submit" class="btn">Publish</button>
        <a href="index.php" class="btn-link">Cancel</a>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
