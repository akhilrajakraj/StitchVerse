<?php
/**
 * Email Sending Function for User Removal using PHPMailer
 *
 * Include this file in scripts where you need to send account removal emails.
 * Make sure the PHPMailer library is correctly located relative to this file.
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files - Adjust path if PHPMailer is installed elsewhere (e.g., via Composer)
// Assumes a 'PHPMailer' directory is in the same directory as this send_removal_email.php file.
// If using Composer in the project root, you might use: require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// --- PHPMailer Configuration ---
// IMPORTANT: Configure these settings for your email provider.
// These are example settings based on your send_mail.php, adjust as needed.
define('SMTP_HOST', 'smtp.gmail.com');          // Set the SMTP server (e.g., smtp.gmail.com for Gmail)
define('SMTP_USERNAME', 'threadhub104@gmail.com');  // SMTP username (your email address) <--- CHANGE THIS
define('SMTP_PASSWORD', 'ecplcvvdopquribv');       // SMTP password (use App Password for Gmail/Outlook) <--- CHANGE THIS
define('SMTP_PORT', 587);                       // TCP port to connect to; use 587 for TLS, 465 for SSL
define('SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS); // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
define('EMAIL_FROM', 'threadhub104@gmail.com'); // Sender's Email <--- CHANGE THIS
define('EMAIL_FROM_NAME', 'ThreadHub Support'); // Sender's Name <--- CHANGE THIS


/**
 * Sends an account removal notification email.
 *
 * @param string $toEmail The recipient's email address.
 * @param string $userName The recipient's name.
 * @param string $reason The reason for removal provided by the admin.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendRemovalEmail($toEmail, $userName, $reason) {
    $mail = new PHPMailer(true); // Passing true enables exceptions

    try {
        // Server settings (using defined constants)
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;

        // Recipients
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($toEmail, $userName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Removal Notification from ThreadHub';

        $message_body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Account Removed</title>
            <style>
                body { font-family: sans-serif; line-height: 1.6; color: #333; }
                .container { padding: 20px; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 5px; }
                p { margin-bottom: 15px; }
                 .reason-box { background-color: #f8f8f8; padding: 15px; border-left: 4px solid #ddd; margin-top: 15px; }
                 /* Basic dark mode styles for email client support */
                 @media (prefers-color-scheme: dark) {
                     body { background-color: #1f2937; color: #f3f4f6; }
                     .container { border-color: #4b5563; background-color: #374151; }
                     .reason-box { background-color: #4b5563; border-left-color: #6b7280; color: #d1d5db;}
                 }
            </style>
        </head>
        <body>
            <div class='container'>
                <h3>Hello " . htmlspecialchars($userName) . ",</h3>
                <p>This email is to inform you that your account on ThreadHub has been permanently removed.</p>";

        if (!empty($reason)) {
            $message_body .= "<div class='reason-box'><p><strong>Reason for removal:</strong></p><p>" . nl2br(htmlspecialchars($reason)) . "</p></div>";
        }

        $message_body .= "<p>If you believe this was done in error or have any questions, please contact our support team.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p>Thank you,<br>The ThreadHub Team</p>
            </div>
        </body>
        </html>";

        $mail->Body = $message_body;

        // Plain text alternative
        $alt_message_body = "Dear " . $userName . ",\n\nYour account on ThreadHub has been permanently removed.\n\n";
        if (!empty($reason)) {
            $alt_message_body .= "Reason for removal: " . $reason . "\n\n";
        }
        $alt_message_body .= "If you believe this was done in error, please contact support.\n\nSincerely,\nThe ThreadHub Team";
        $mail->AltBody = $alt_message_body;


        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error
        error_log("Account removal email could not be sent to " . $toEmail . ". Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
