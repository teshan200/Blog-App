<?php
/**
 * Edit an existing blog post.
 * Owner-only: users can only edit their own posts.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

require_login();

// Grab the post id and make sure it belongs to the current user.
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    set_flash('error', 'Invalid post id.');
    redirect('index.php');
}

// Fetch the post (prepared statement).
$stmt = $pdo->prepare('SELECT id, user_id, title, content, featured_image FROM blogPost WHERE id = :id');
$stmt->execute([':id' => $id]);
$post = $stmt->fetch();

if (!$post) {
    set_flash('error', 'Post not found.');
    redirect('index.php');
}

// Authorization: does the post belong to the current user (or is this an admin)?
if ((int)$post['user_id'] !== current_user_id() && !is_admin()) {
    set_flash('error', 'You can only edit your own posts.');
    redirect('index.php');
}

$errors = [];
$title_input    = $post['title'];
$content_input  = $post['content'];
$featured_input = $post['featured_image']; // current value

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $title_input   = trim($_POST['title'] ?? '');
    $content_input = trim($_POST['content'] ?? '');
    $featured_path = $post['featured_image']; // keep old by default

    if ($title_input === '' || strlen($title_input) > 150) {
        $errors[] = 'Title is required (max 150 characters).';
    }
    if ($content_input === '') {
        $errors[] = 'Content cannot be empty.';
    }

    // Did the user check "Remove featured image"?
    $remove_featured = isset($_POST['remove_featured']);

    // Did the user upload a new one?
    $new_upload = !empty($_FILES['featured_image']['name']);

    if ($new_upload) {
        $result = upload_image($_FILES['featured_image']);
        if (is_string($result) && strncmp($result, 'uploads/', 8) === 0) {
            $featured_path = $result;
        } else {
            $errors[] = $result;
        }
    } elseif ($remove_featured) {
        $featured_path = null;
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'UPDATE blogPost SET title = :title, content = :content, featured_image = :featured
             WHERE id = :id AND user_id = :uid'
        );
        $stmt->execute([
            ':title'    => $title_input,
            ':content'  => $content_input,
            ':featured' => $featured_path,
            ':id'       => $id,
            ':uid'      => current_user_id(),
        ]);

        set_flash('success', 'Post updated!');
        redirect('post.php?id=' . $id);
    }
}

$title = 'Edit Post';
require __DIR__ . '/includes/header.php';
?>
<div class="card form-card editor-wide">
    <h1>Edit post</h1>

    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="edit.php?id=<?= $id ?>" class="form" enctype="multipart/form-data">
        <?php csrf_field(); ?>
        <label>Title
            <input type="text" name="title" value="<?= e($title_input) ?>" maxlength="150" required>
        </label>
        <label>Content (Markdown supported)
            <textarea name="content" id="editor" rows="14"><?= e($content_input) ?></textarea>
        </label>

        <label>Featured Image</label>
        <?php if ($post['featured_image']): ?>
            <div class="featured-preview">
                <img src="<?= e($post['featured_image']) ?>" alt="Current featured image">
                <label class="checkbox-inline">
                    <input type="checkbox" name="remove_featured" value="1">
                    Remove current featured image
                </label>
            </div>
        <?php endif; ?>
        <div class="featured-upload">
            <input type="file" name="featured_image" accept="image/jpeg,image/png,image/gif,image/webp">
            <span class="form-note">Upload a new image to replace the current one (optional).</span>
        </div>

        <button type="submit" class="btn">Update</button>
        <a href="post.php?id=<?= $id ?>" class="btn-link">Cancel</a>
    </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
