<?php
/**
 * Email Sending Function for Tailor Removal/Deactivation using PHPMailer
 *
 * Include this file in scripts where you need to send tailor removal/deactivation emails.
 * Make sure the PHPMailer library is correctly located relative to this file.
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files - Adjust path if PHPMailer is installed elsewhere (e.g., via Composer)
// Assumes a 'PHPMailer' directory is in the same 'includes' directory as this file.
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// --- PHPMailer Configuration ---
// IMPORTANT: Configure these settings for your email provider.
// Copied from send_email_remove.php - ensure these are correct for your environment.
define('SMTP_TAILOR_HOST', 'smtp.gmail.com');          // Set the SMTP server
define('SMTP_TAILOR_USERNAME', 'threadhub104@gmail.com');  // SMTP username (your email address)
define('SMTP_TAILOR_PASSWORD', 'ecplcvvdopquribv');       // SMTP password (use App Password for Gmail/Outlook)
define('SMTP_TAILOR_PORT', 587);                       // TCP port (587 for TLS, 465 for SSL)
define('SMTP_TAILOR_SECURE', PHPMailer::ENCRYPTION_STARTTLS); // Enable TLS encryption
define('EMAIL_TAILOR_FROM', 'threadhub104@gmail.com'); // Sender's Email
define('EMAIL_TAILOR_FROM_NAME', 'ThreadHub Admin'); // Sender's Name


/**
 * Sends a tailor account deactivation/removal notification email.
 *
 * @param string $toEmail The recipient tailor's email address.
 * @param string $tailorName The recipient tailor's name.
 * @param string $reason The reason for removal/deactivation provided by the admin.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendTailorRemovalEmail($toEmail, $tailorName, $reason) {
    $mail = new PHPMailer(true); // Passing true enables exceptions

    try {
        // Server settings (using defined constants)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for detailed debugging output
        $mail->isSMTP();
        $mail->Host       = SMTP_TAILOR_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_TAILOR_USERNAME;
        $mail->Password   = SMTP_TAILOR_PASSWORD;
        $mail->SMTPSecure = SMTP_TAILOR_SECURE;
        $mail->Port       = SMTP_TAILOR_PORT;

        // Recipients
        $mail->setFrom(EMAIL_TAILOR_FROM, EMAIL_TAILOR_FROM_NAME);
        $mail->addAddress($toEmail, $tailorName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Important Notification Regarding Your ThreadHub Tailor Account';

        // Construct email body similar to send_email_remove.php
        $message_body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Account Notification</title>
            <style>
                body { font-family: sans-serif; line-height: 1.6; color: #333; }
                .container { padding: 20px; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 5px; }
                p { margin-bottom: 15px; }
                .reason-box { background-color: #f8f8f8; padding: 15px; border-left: 4px solid #ddd; margin-top: 15px; }
                 /* Basic dark mode styles */
                 @media (prefers-color-scheme: dark) {
                     body { background-color: #1f2937; color: #f3f4f6; }
                     .container { border-color: #4b5563; background-color: #374151; }
                     .reason-box { background-color: #4b5563; border-left-color: #6b7280; color: #d1d5db;}
                 }
            </style>
        </head>
        <body>
            <div class='container'>
                <h3>Hello " . htmlspecialchars($tailorName) . ",</h3>
                <p>This email is to inform you about an important update regarding your tailor account on ThreadHub.</p>
                <p>Your account has been deactivated by the administration team.</p>";

        if (!empty($reason)) {
            $message_body .= "<div class='reason-box'><p><strong>Reason provided:</strong></p><p>" . nl2br(htmlspecialchars($reason)) . "</p></div>";
        }

        $message_body .= "<p>While your account is inactive, you will not appear in search results, and customers will not be able to send new requests to you. Existing orders may still need to be managed based on prior agreements.</p>
                <p>If you believe this was done in error or have any questions, please contact our administration or support team directly.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p>Sincerely,<br>The ThreadHub Admin Team</p>
            </div>
        </body>
        </html>";

        $mail->Body = $message_body;

        // Plain text alternative
        $alt_message_body = "Dear " . $tailorName . ",\n\nThis email is to inform you about an important update regarding your tailor account on ThreadHub.\n\nYour account has been deactivated by the administration team.\n\n";
        if (!empty($reason)) {
            $alt_message_body .= "Reason provided: " . $reason . "\n\n";
        }
        $alt_message_body .= "While your account is inactive, you will not appear in search results, and customers will not be able to send new requests to you. Existing orders may still need to be managed based on prior agreements.\n\nIf you believe this was done in error or have any questions, please contact our administration or support team directly.\n\nSincerely,\nThe ThreadHub Admin Team";
        $mail->AltBody = $alt_message_body;


        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error - Consistent with send_email_remove.php
        error_log("Tailor removal/deactivation email could not be sent to " . $toEmail . ". Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>