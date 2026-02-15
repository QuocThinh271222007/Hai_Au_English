<?php

// Load PHPMailer autoloader (config phải được load trước khi gọi class này)

require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
    function send($to, $subject, $content, $fromName = 'Hải Âu English')
    {
        // đảm bảo config đã load
        if (!defined('SMTP_HOST')) {
            require_once __DIR__ . '/../php/config.php';
        }

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        $mail->CharSet = "UTF-8";

        try {
            $mail->SMTPDebug = SMTP::DEBUG_OFF;
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_SECRET;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;
            $mail->setFrom(SMTP_USERNAME, $fromName);
            $mail->addAddress($to);
            //Recipients
            $mail->setFrom(SMTP_USERNAME, $fromName);
            $mail->addAddress($to); //Add a recipient

            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $content;

            $mail->send();

            return [
                'success' => true,
                'message' => 'Email sent successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => "Mailer Error: {$mail->ErrorInfo}"
            ];

            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => "Mailer Error: {$mail->ErrorInfo}"];
        }
    }
}
