<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

function sendEmail($to, $subject, $message) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'];
        $mail->Port       = $_ENV['MAIL_PORT'];

        $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;

        $mail->addReplyTo($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
        $mail->AltBody = strip_tags($message);

        // 🔎 Debug lines
        $mail->SMTPDebug = 2;              // 2 = client/server messages, 3 = full detail
        $mail->Debugoutput = 'error_log';  // send debug output to PHP error log

        if (filter_var($_ENV['MAIL_ENABLED'], FILTER_VALIDATE_BOOLEAN)) {
            $mail->send();
        } else {
            error_log("Mail skipped (disabled) for subject '{$subject}'");
        }
    } catch (Exception $e) {
        error_log("Email Error: {$mail->ErrorInfo}");
    }
}
