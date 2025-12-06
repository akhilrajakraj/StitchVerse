<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

function sendStitchverseEmail($recipient_email, $recipient_name, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Your existing SMTP server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stitchverrse@gmail.com'; 
        $mail->Password   = 'cqdaqntjinoeclpr';      
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Team');
        $mail->addAddress($recipient_email, $recipient_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error for debugging, but don't expose it to the user
        error_log("Mailer Error for {$recipient_email}: {$mail->ErrorInfo}");
        return false;
    }
}

// --- Email Template Functions ---

function get_approval_email_template($tailor_name) {
    return "
        <html><body>
            <h2>Welcome to StitchVerse!</h2>
            <p>Hi {$tailor_name},</p>
            <p>We are thrilled to inform you that your application to become a tailor on StitchVerse has been approved! Your profile is now live and visible to customers.</p>
            <p>You can now log in to your dashboard to manage your profile, upload designs, and start receiving stitch requests.</p>
            <p>We are excited to have you as part of our community.</p>
            <br><p>Best regards,</p><p>The StitchVerse Team</p>
        </body></html>";
}

function get_rejection_email_template($tailor_name, $reason, $custom_message) {
     return "
        <html><body>
            <h2>Update on Your StitchVerse Application</h2>
            <p>Hi {$tailor_name},</p>
            <p>Thank you for your interest in joining StitchVerse. After careful review, we regret to inform you that your application could not be approved at this time.</p>
            <p style='padding: 10px; background-color: #fef2f2; border-left: 4px solid #ef4444;'>
                <strong>Reason:</strong> {$reason}<br>
                <strong>Details:</strong> {$custom_message}
            </p>
            <p>We appreciate you taking the time to apply and wish you the best.</p>
            <br><p>Regards,</p><p>The StitchVerse Team</p>
        </body></html>";
}

function get_removal_email_template($tailor_name, $reason, $custom_message) {
    return "
        <html><body>
            <h2>Important Notification Regarding Your StitchVerse Account</h2>
            <p>Hi {$tailor_name},</p>
            <p>This is an important notification regarding your tailor account on StitchVerse.</p>
            <p>After a review, your account has been removed from our active platform by an administrator. The reason provided is:</p>
            <p style='padding: 10px; background-color: #fef2f2; border-left: 4px solid #ef4444;'>
                <strong>Reason:</strong> {$reason}<br>
                <strong>Details:</strong> {$custom_message}
            </p>
            <p>This means your profile and designs will no longer be visible to customers, and you will not be able to log in. If you believe this is a mistake, please contact our support team.</p>
            <br><p>Regards,</p><p>The StitchVerse Team</p>
        </body></html>";
}
function get_customer_removal_email_template($customer_name, $reason, $custom_message) {
    return "
        <html><body>
            <h2>Important Notification Regarding Your StitchVerse Account</h2>
            <p>Hi {$customer_name},</p>
            <p>This is an important notification regarding your account on StitchVerse.</p>
            <p>After a review, your account has been removed from our active platform by an administrator. The reason provided is:</p>
            <p style='padding: 10px; background-color: #fef2f2; border-left: 4px solid #ef4444;'>
                <strong>Reason:</strong> {$reason}<br>
                <strong>Details:</strong> {$custom_message}
            </p>
            <p>This means you will no longer be able to log in or make purchases. If you believe this is a mistake or have questions about your data, please contact our support team.</p>
            <br><p>Regards,</p><p>The StitchVerse Team</p>
        </body></html>";
}