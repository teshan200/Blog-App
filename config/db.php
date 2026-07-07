<?php
/**
 * Database connection using PDO.
 * Prepared statements are used everywhere; emulated prepares are turned OFF
 * so MySQL does the real parameter binding (safer against SQL injection).
 */

// DB settings for XAMPP defaults
$DB_HOST = '127.0.0.1';
$DB_NAME = 'blog_app';
$DB_USER = 'root';
$DB_PASS = ''; // empty on default XAMPP

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
