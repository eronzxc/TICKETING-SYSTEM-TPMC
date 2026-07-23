<?php
require_once __DIR__ . '/mail_config.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * Sends an email via Gmail SMTP. Returns true on success, false on
 * failure (failure details are error_log'd, never shown to the user —
 * we don't want to leak SMTP internals in an API response).
 */
function sendMail(string $toEmail, string $toName, string $subject, string $bodyHtml): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = str_replace(' ', '', MAIL_PASSWORD);
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_USERNAME, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody  = strip_tags($bodyHtml);

        $mail->send();
        return true;
    } catch (PHPMailerException $e) {
        error_log('sendMail failed: ' . $mail->ErrorInfo);
        return false;
    }
}
