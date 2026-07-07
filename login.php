<?php
/**
 * Login page: looks the user up by username, verifies the password hash with
 * password_verify(), and on success regenerates the session id + stores user info.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $errors[] = 'Please fill in both fields.';
    }

    if (empty($errors)) {
        // Look up the user by username only (prepared statement).
        $stmt = $pdo->prepare('SELECT id, username, password, role, email_verified FROM user WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Use a generic error so attackers can't enumerate usernames.
        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid username or password.';
        } elseif ((int)$user['email_verified'] !== 1) {
            $errors[] = 'Please verify your email before logging in. Check your inbox (and spam folder).';
        } else {
            // Login OK: rotate session id (prevents session fixation).
            session_regenerate_id(true);
            $_SESSION['user_id']  = (int)$user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            set_flash('success', 'Logged in. Welcome back, ' . $user['username'] . '!');
            redirect('index.php');
        }
    }
}

$title = 'Login';
require __DIR__ . '/includes/header.php';
?>
<div class="card form-card">
    <h1>Log in</h1>

    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="login.php" class="form">
        <?php csrf_field(); ?>
        <label>Username
            <input type="text" name="username" value="<?= e($username) ?>" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <button type="submit" class="btn">Login</button>
    </form>
    <p class="muted">No account yet? <a href="register.php">Register here</a>.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
