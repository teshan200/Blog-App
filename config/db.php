<?php
/**
 * Database connection using PDO.
 * Prepared statements are used everywhere; emulated prepares are turned OFF
 * so MySQL does the real parameter binding (safer against SQL injection).
 *
 * Local overrides: create config/db.local.php (gitignored) to use different
 * credentials on your machine without touching this file.
 */

// Default: XAMPP localhost
$DB_HOST = '127.0.0.1';
$DB_NAME = 'blog_app';
$DB_USER = 'root';
$DB_PASS = '';

// If a local override exists, load it (e.g. for InfinityFree credentials
// during CI/CD, or different local MySQL creds).
$localDb = __DIR__ . '/db.local.php';
if (file_exists($localDb)) {
    require $localDb;
}

// Build the Data Source Name
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";

// PDO options: throw exceptions on error, fetch assoc by default,
// turn OFF emulated prepares so we get real server-side prepared statements.
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // In a real app you'd log this; for an assignment we just die cleanly.
    die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
}
