<?php

<<<<<<< HEAD
// Load PHPMailer autoloader (config phải được load trước khi gọi class này)
require_once __DIR__ . '/../vendor/autoload.php';

=======
>>>>>>> f4b95be7fe27c8af6a8f6a6cbb258ea29d4a6733
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService
{
<<<<<<< HEAD
    function send($to, $subject, $content, $fromName = 'Hải Âu English')
    {
        // Đảm bảo config đã được load
        if (!defined('SMTP_HOST')) {
            require_once __DIR__ . '/../php/config.php';
        }
        
=======
    function send($to, $subject, $content)
    {
>>>>>>> f4b95be7fe27c8af6a8f6a6cbb258ea29d4a6733
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        $mail->CharSet = "UTF-8";
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF; //Enable verbose debug output
            $mail->isSMTP(); //Send using SMTP
            $mail->Host       = SMTP_HOST; //Set the SMTP server to send through
            $mail->SMTPAuth   = true; //Enable SMTP authentication
            $mail->Username   = SMTP_USERNAME; //SMTP username
            $mail->Password   = SMTP_SECRET; //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; //Enable implicit TLS encryption
            $mail->Port       = 465; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
<<<<<<< HEAD
            $mail->setFrom(SMTP_USERNAME, $fromName);
=======
            $mail->setFrom(SMTP_USERNAME);
>>>>>>> f4b95be7fe27c8af6a8f6a6cbb258ea29d4a6733
            $mail->addAddress($to); //Add a recipient

            //Content
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $content;

            $mail->send();
<<<<<<< HEAD
            return ['success' => true, 'message' => 'Email sent successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'error' => "Mailer Error: {$mail->ErrorInfo}"];
=======
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
>>>>>>> f4b95be7fe27c8af6a8f6a6cbb258ea29d4a6733
        }
    }
}
