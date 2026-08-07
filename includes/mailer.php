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
        $yourEmail = 'dantanivanit8@gmail.com';
        // Instructions for App Password:
        // 1. Go to Google Account Settings -> Security
        // 2. Enable 2-Step Verification
        // 3. Search for "App Passwords"
        // 4. Generate a new App Password for "Mail" and paste the 16-letter code below:
        $yourAppPassword = 'gmtl vnlp ecan hsgu';

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
        $yourEmail = 'dantanivanit8@gmail.com';
        $yourAppPassword = 'gmtl vnlp ecan hsgu';

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

function sendStatusUpdateEmail($toEmail, $userName, $companyName, $status) {
    $mail = new PHPMailer(true);
    try {
        $yourEmail = 'dantanivanit8@gmail.com';
        $yourAppPassword = 'gmtl vnlp ecan hsgu';

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

        if ($status === 'Selected') {
            $mail->Subject = 'Congratulations! You are Selected - GEC Placement Portal';
            $msgTitle = "Congratulations!";
            $msgBody = "We are thrilled to inform you that you have been <strong>Selected</strong> for placement at <strong>{$companyName}</strong>!";
            $color = "#28a745"; // Green
        } else {
            $mail->Subject = 'Application Status Update - GEC Placement Portal';
            $msgTitle = "Status Update";
            $msgBody = "Your application status for <strong>{$companyName}</strong> has been updated to: <strong>{$status}</strong>.";
            $color = "#1B365D"; // Navy
        }

        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: {$color};'>{$msgTitle}</h2>
                <p>Hello <strong>{$userName}</strong>,</p>
                <p>{$msgBody}</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='http://localhost/gec_placement_portal' style='background-color: #E65A4B; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>View Dashboard</a>
                </div>
                <p>Best regards,<br><strong>GEC Modasa Placement Cell</strong></p>
            </div>
        ";
        
        $mail->Body = $emailBody;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Status Update Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendBlockStatusEmail($toEmail, $userName, $isBlocked) {
    $mail = new PHPMailer(true);
    try {
        $yourEmail = 'dantanivanit8@gmail.com';
        $yourAppPassword = 'gmtl vnlp ecan hsgu';

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

        if ($isBlocked) {
            $mail->Subject = 'Account Suspended - GEC Placement Portal';
            $msgTitle = "Account Suspended";
            $msgBody = "Your account on the GEC Placement Portal has been <strong>suspended/blocked</strong> by the administration. You will not be able to log in or apply for placements.";
            $color = "#dc3545"; // Red
        } else {
            $mail->Subject = 'Account Reactivated - GEC Placement Portal';
            $msgTitle = "Account Reactivated";
            $msgBody = "Good news! Your account on the GEC Placement Portal has been <strong>reactivated</strong> by the administration. You can now log in and continue using the portal.";
            $color = "#28a745"; // Green
        }

        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: {$color};'>{$msgTitle}</h2>
                <p>Hello <strong>{$userName}</strong>,</p>
                <p>{$msgBody}</p>
                <p>If you have any questions, please contact the placement coordinator.</p>
                <p>Best regards,<br><strong>GEC Modasa Placement Cell</strong></p>
            </div>
        ";
        
        $mail->Body = $emailBody;
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Block Status Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

function sendVerificationEmail($toEmail, $userName, $verificationLink)
{
    $mail = new PHPMailer(true);

    try {
        $yourEmail = 'dantanivanit8@gmail.com';
        $yourAppPassword = 'gmtl vnlp ecan hsgu';

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $yourEmail;
        $mail->Password = $yourAppPassword;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom($yourEmail, 'GEC Placement Portal');
        $mail->addReplyTo($yourEmail, 'GEC Placement Portal');
        $mail->addAddress($toEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email Address - GEC Placement Portal';

        $emailBody = "
            <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #1B365D;'>Verify Your Email Address</h2>
                <p>Hello <strong>{$userName}</strong>,</p>
                <p>Thank you for registering at the GEC Placement Portal. Please click the button below to activate your account:</p>
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$verificationLink}' style='display: inline-block; background-color: #28a745; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Click here to activate your account</a>
                </div>
                <p>If you did not create this account, you can safely ignore this email.</p>
                <p>Best regards,<br><strong>GEC Modasa Placement Cell</strong></p>
            </div>
        ";

        $mail->Body = $emailBody;
        $mail->AltBody = "Hello {$userName},\n\nThank you for registering. Please copy and paste the following link into your browser to activate your account: {$verificationLink}\n\nBest regards,\nGEC Modasa Placement Cell";
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Verification Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
