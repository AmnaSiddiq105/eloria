<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/email_config.php';


/**
 * Send HTML email using Gmail SMTP
 *
 * @param string $toEmail
 * @param string $toName
 * @param string $subject
 * @param string $htmlBody
 *
 * @return bool
 */
function sendEloriaEmail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody
): bool {

    $mail = new PHPMailer(true);

    try {

        // =================================================
        // SMTP SETTINGS
        // =================================================

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->SMTPAuth = true;

        $mail->Username = MAIL_USERNAME;

        $mail->Password = MAIL_PASSWORD;

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;


        // =================================================
        // CHARACTER SET
        // =================================================

        $mail->CharSet = 'UTF-8';


        // =================================================
        // SENDER
        // =================================================

        $mail->setFrom(
            MAIL_FROM_EMAIL,
            MAIL_FROM_NAME
        );


        // =================================================
        // RECEIVER
        // =================================================

        $mail->addAddress(
            $toEmail,
            $toName
        );


        // =================================================
        // EMAIL FORMAT
        // =================================================

        $mail->isHTML(true);

        $mail->Subject = $subject;

        $mail->Body = $htmlBody;


        // =================================================
        // SEND
        // =================================================

        return $mail->send();

    } catch (Exception $e) {

        error_log(
            'ÉLORIA Email Error: ' .
            $mail->ErrorInfo
        );

        return false;
    }
}

?>