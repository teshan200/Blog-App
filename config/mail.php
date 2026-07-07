<?php
/**
 * Mail configuration – SMTP settings for PHPMailer.
 *
 * Credentials are loaded from config/mail.local.php (gitignored) so they
 * never end up in the repository. Copy mail.local.example.php to
 * mail.local.php and fill in your real Gmail App Password.
 *
 * Usage:
 *   $mail = get_mailer();
 *   $mail->addAddress($to);
 *   $mail->Subject = '...';
 *   $mail->Body    = '...';
 *   $mail->send();
 */

// Load PHPMailer classes (no Composer autoloader in this project).
require_once __DIR__ . '/../lib/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../lib/phpmailer/SMTP.php';
require_once __DIR__ . '/../lib/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load local (gitignored) credentials if they exist.
$MAIL_USERNAME = '';
$MAIL_PASSWORD = '';
$MAIL_FROM     = '';
$localConfig   = __DIR__ . '/mail.local.php';
if (file_exists($localConfig)) {
    require $localConfig;
}

/**
 * Create and return a pre-configured PHPMailer instance.
 * Configured for Gmail SMTP – change these if you use another provider.
 */
function get_mailer() {
    global $MAIL_USERNAME, $MAIL_PASSWORD, $MAIL_FROM;

    $mail = new PHPMailer(true); // true = enable exceptions

    // --- SMTP configuration (Gmail example) ---
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $MAIL_USERNAME;
    $mail->Password   = $MAIL_PASSWORD;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // or ENCRYPTION_STARTTLS
    $mail->Port       = 465;                         // 587 for STARTTLS

    // --- Sender info ---
    if ($MAIL_FROM !== '') {
        $mail->setFrom($MAIL_FROM, 'Blog App');
    }

    // --- Misc ---
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';

    return $mail;
}
