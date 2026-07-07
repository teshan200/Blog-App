<?php
/**
 * One-time migration: adds email verification columns to an existing database.
 * Run once after updating code (new installations should use database.sql).
 *
 * Usage: php migrate_verify.php
 */

$pdo = new PDO('mysql:host=127.0.0.1;dbname=blog_app;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$pdo->exec("ALTER TABLE user ADD COLUMN IF NOT EXISTS email_verified TINYINT(1) NOT NULL DEFAULT 0 AFTER role");
$pdo->exec("ALTER TABLE user ADD COLUMN IF NOT EXISTS verification_token VARCHAR(64) DEFAULT NULL AFTER email_verified");

$pdo->exec("UPDATE user SET email_verified = 1 WHERE username = 'demo'");

echo "Verification columns added. Demo user marked as verified.\n";
