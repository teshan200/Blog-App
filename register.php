<?php
/**
 * Registration page: validates input, checks for duplicate username/email,
 * hashes the password with password_hash(), inserts a new user, then sends a
 * verification email. The user must verify before they can log in.
 */
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/config/mail.php';

$errors = [];
$username = '';
$email   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify(); // stop CSRF

    // Grab and trim input
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // --- basic validation ---
    if ($username === '' || strlen($username) < 3 || strlen($username) > 50) {
        $errors[] = 'Username must be 3-50 characters.';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'A valid email is required.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // --- only hit the DB if basic validation passed ---
    if (empty($errors)) {
        // Check for duplicate username OR email (prepared statement).
        $stmt = $pdo->prepare('SELECT id FROM user WHERE username = :username OR email = :email LIMIT 1');
        $stmt->execute([':username' => $username, ':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already taken.';
        }
    }

    // --- all good -> insert user + send verification ---
    if (empty($errors)) {
        $hash  = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));

        $stmt = $pdo->prepare(
            'INSERT INTO user (username, email, password, verification_token) VALUES (:username, :email, :hash, :token)'
        );
        $stmt->execute([
            ':username' => $username,
            ':email'    => $email,
            ':hash'     => $hash,
            ':token'    => $token,
        ]);

        // --- Send verification email ---
        try {
            $mail = get_mailer();
            $mail->addAddress($email, $username);
            $mail->Subject = 'Verify your email – Blog App';

            $verifyUrl = 'http://' . $_SERVER['HTTP_HOST']
                       . rtrim(dirname($_SERVER['SCRIPT_NAME']), '/')
                       . '/verify.php?token=' . $token;

            $mail->Body = '<h1>Welcome, ' . htmlspecialchars($username) . '!</h1>'
                        . '<p>Please click the link below to verify your email address and activate your account:</p>'
                        . '<p><a href="' . htmlspecialchars($verifyUrl) . '">' . htmlspecialchars($verifyUrl) . '</a></p>'
                        . '<p>If you did not register, you can ignore this email.</p>';

            $mail->send();
            $mailSent = true;
        } catch (Exception $e) {
            $mailSent = false;
            // Log the error silently so registration still completes
            error_log('Mail error: ' . $e->getMessage());
        }

        if ($mailSent) {
            set_flash('success', 'Account created! Check your email to verify your account.');
        } else {
            set_flash('error', 'Account created but the verification email could not be sent. Please contact the admin.');
        }
        redirect('login.php');
    }
}

$title = 'Register';
require __DIR__ . '/includes/header.php';
?>
<div class="card form-card">
    <h1>Create an account</h1>

    <?php foreach ($errors as $err): ?>
        <div class="flash flash-error"><?= e($err) ?></div>
    <?php endforeach; ?>

    <form method="post" action="register.php" class="form">
        <?php csrf_field(); ?>
        <label>Username
            <input type="text" name="username" value="<?= e($username) ?>" maxlength="50" required>
        </label>
        <label>Email
            <input type="email" name="email" value="<?= e($email) ?>" maxlength="100" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <label>Confirm Password
            <input type="password" name="confirm" required>
        </label>
        <button type="submit" class="btn">Register</button>
    </form>
    <p class="muted">Already have an account? <a href="login.php">Login here</a>.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
