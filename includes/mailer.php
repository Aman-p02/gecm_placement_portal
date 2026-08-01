<?php
/**
 * Utility script for sending emails via PHPMailer and Gmail SMTP.
 * Note: You must provide a valid Gmail address and an App Password.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Adjust path based on where this file is included from (student-module or admin-module)
$phpmailer_dir = __DIR__ . '/PHPMailer/src/';
require $phpmailer_dir . 'Exception.php';
require $phpmailer_dir . 'PHPMailer.php';
require $phpmailer_dir . 'SMTP.php';

function sendResetEmail($toEmail, $resetLink)
{
    $mail = new PHPMailer(true);

    try {
        // --- 1. ENTER YOUR GMAIL CREDENTIALS HERE ---
        $yourEmail = 'haritap40@gmail.com';
        // Instructions for App Password:
        // 1. Go to Google Account Settings -> Security
        // 2. Enable 2-Step Verification
        // 3. Search for "App Passwords"
        // 4. Generate a new App Password for "Mail" and paste the 16-letter code below:
        $yourAppPassword = 'xkeq amgh eesp xkra';

        // --- Server settings ---
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $yourEmail;
        $mail->Password = $yourAppPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // --- Recipients ---
        $mail->setFrom($yourEmail, 'GEC Placement Portal');
        $mail->addAddress($toEmail);

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - GEC Placement Portal';

        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #1B365D;'>Password Reset Request</h2>
                <p>Hello,</p>
                <p>We received a request to reset your password for the GEC Placement Portal. Click the button below to set a new password:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$resetLink}' style='background-color: #E65A4B; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password</a>
                </div>
                <p>If you did not request this, please ignore this email.</p>
                <p>Best regards,<br><strong>GEC Modasa Placement Cell</strong></p>
            </div>
        ";

        $mail->Body = $emailBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the exact error for debugging
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendRegistrationEmail($toEmail, $userName, $role)
{
    $mail = new PHPMailer(true);

    try {
        $yourEmail = 'haritap40@gmail.com';
        $yourAppPassword = 'xkeq amgh eesp xkra';

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $yourEmail;
        $mail->Password = $yourAppPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($yourEmail, 'GEC Placement Portal');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Registration Successful - GEC Placement Portal';

        $roleDisplay = ucfirst($role);

        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #1B365D;'>Welcome to GEC Placement Portal!</h2>
                <p>Hello <strong>{$userName}</strong>,</p>
                <p>Your registration as a <strong>{$roleDisplay}</strong> was successful!</p>
                <p>You can now log in to the portal and start using your account.</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/gec_placement_portal' style='background-color: #E65A4B; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Go to Portal</a>
                </div>
                <p>Best regards,<br><strong>GEC Modasa Placement Cell</strong></p>
            </div>
        ";

        $mail->Body = $emailBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Registration Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>