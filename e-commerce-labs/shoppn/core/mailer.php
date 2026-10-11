<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/core.php';

/**
 * Send an email through the configured SMTP server.
 *
 * @param string $to Recipient email address.
 * @param string $subject Email subject.
 * @param string $body Email body in plain text or HTML.
 * @param bool $isHtml Whether the body contains HTML.
 * @return bool True if accepted by the mail server; false otherwise.
 */
function send_app_email(
    string $to,
    string $subject,
    string $body,
    bool $isHtml = false,
    array $attachments = []
): bool {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = app_setting('MAIL_HOST') ?? 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = app_setting('MAIL_USERNAME') ?? '';
        $mail->Password = app_setting('MAIL_PASSWORD') ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = (int) (app_setting('MAIL_PORT') ?? 587);
        $mail->CharSet = 'UTF-8';

        $fromAddress = app_setting('MAIL_FROM')
            ?? app_setting('MAIL_USERNAME')
            ?? '';

        $fromName = app_setting('MAIL_FROM_NAME') ?? 'Shoppn';

        if ($mail->Username === '' || $mail->Password === '' || $fromAddress === '') {
            error_log('Shoppn mail configuration is incomplete.');
            return false;
        }

        $mail->setFrom($fromAddress, $fromName);
        $mail->addAddress($to);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML($isHtml);

        if (!$isHtml) {
            $mail->AltBody = $body;
        }

        foreach ($attachments as $attachment) {
            if (
                !is_array($attachment)
                || !is_string($attachment['content'] ?? null)
                || !is_string($attachment['name'] ?? null)
                || !is_string($attachment['type'] ?? null)
            ) {
                error_log('Shoppn email attachment configuration is invalid.');
                return false;
            }
            $mail->addStringAttachment(
                $attachment['content'],
                $attachment['name'],
                PHPMailer::ENCODING_BASE64,
                $attachment['type']
            );
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Shoppn email sending failed: ' . $mail->ErrorInfo);
        return false;
    }
}
