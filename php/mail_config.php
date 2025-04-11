<?php
require __DIR__ . '/../vendor/autoload.php'; // Path to PHPMailer autoload

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // MailHog SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'localhost';  // MailHog runs on your machine
        $mail->Port       = 1025;         // Default MailHog port
        $mail->SMTPAuth   = false;        // No authentication needed

        // Email headers
        $mail->setFrom('no-reply@eventhub.local', 'EventHub System');
        $mail->addAddress($to);           // Recipient

        // Content
        $mail->isHTML(false);             // Set to true if sending HTML emails
        $mail->Subject = $subject;
        $mail->Body    = $body;

        // Debugging (uncomment if emails aren't sending)
        // $mail->SMTPDebug = 2; 
        // error_log(print_r($mail, true));

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log errors to PHP error log
        error_log("MailHog Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>