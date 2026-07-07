<?php
/**
 * Admin: manage users.
 * Admins can delete other users (but not other admins and not themselves).
 */
$root = '../';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

// Fetch all users
$stmt = $pdo->query(
    'SELECT id, username, email, role, created_at FROM user ORDER BY created_at DESC'
);
$users = $stmt->fetchAll();

$title = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="admin-head">
    <h1>Manage Users</h1>
</div>

<?php if (empty($users)): ?>
    <p class="muted">No users found.</p>
<?php else: ?>
    <div class="card">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= (int)$u['id'] ?></td>
                    <td><?= e($u['username']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <span class="role-badge role-<?= e($u['role']) ?>">
                            <?= e($u['role']) ?>
                        </span>
                    </td>
                    <td><?= format_date($u['created_at']) ?></td>
                    <td class="action-cell">
                        <?php if ($u['role'] !== 'admin'): ?>
                            <form method="post" action="delete-user.php" class="inline-form"
                                  onsubmit="return confirm('Delete user «<?= e($u['username']) ?>»? Their posts will also be deleted.');">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                <button type="submit" class="btn-sm btn-sm-danger">Delete</button>
                            </form>
                        <?php else: ?>
                            <span class="muted">Protected</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
