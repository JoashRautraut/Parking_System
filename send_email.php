<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';

function sendViolationEmail($to, $plateNumber, $remarks) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';         // Use your SMTP provider
        $mail->SMTPAuth   = true;
        $mail->Username   = '20221505@nbsc.edu.ph   ';   // Your Gmail address
        $mail->Password   = 'tlpm oday ydii akph';      // App password (not Gmail password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('your_email@gmail.com', 'Parking System');
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Parking Violation Warning';
        $mail->Body    = "
            <h3>⚠️Parking Violation Notice⚠️</h3>
            <p>Plate Number: <strong>$plateNumber</strong></p>
            <p>Remarks: $remarks</p>
            <p>This is a warning issued by the Parking Management System.</p>
        ";

        $mail->send();
        // Optionally, you can log that the email was sent
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
