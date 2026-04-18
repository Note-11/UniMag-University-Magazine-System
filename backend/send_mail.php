<?php
require_once __DIR__ . '/../vendor/autoload.php'; // Composer autoload
use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

function sendCoordinatorNotification($connection, $facultyid, $subject, $message) {
    // Prepare the query (roleid = 3 for Marketing Coordinator)
    $sql = "SELECT email 
            FROM tbluser 
            WHERE roleid = 3 AND facultyid = ? 
            LIMIT 1";

    if ($stmt = mysqli_prepare($connection, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $facultyid);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $email);

        if (mysqli_stmt_fetch($stmt)) {
            if (!empty($email)) {
                // Check MAIL_ENABLED from .env
                $mailEnabled = filter_var($_ENV['MAIL_ENABLED'] ?? false, FILTER_VALIDATE_BOOLEAN);

                if ($mailEnabled) {
                    try {
                        sendEmail($email, $subject, $message);
                    } catch (Exception $e) {
                        error_log("Mail send failed to {$email} for subject '{$subject}': " . $e->getMessage());
                    }
                } else {
                    error_log("Mail skipped (disabled) for subject '{$subject}'");
                }
            } else {
                error_log("Coordinator email empty for faculty ID: {$facultyid}, subject '{$subject}'");
            }
        } else {
            error_log("No coordinator found for faculty ID: {$facultyid}, subject '{$subject}'");
        }

        mysqli_stmt_close($stmt);
    } else {
        error_log("Failed to prepare statement: " . mysqli_error($connection));
    }
}
