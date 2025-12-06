<?php
/**
 * Email Sending Function for Design Removal using PHPMailer
 *
 * Include this file in scripts where you need to send design removal emails.
 * Assumes PHPMailer library is available in a 'PHPMailer' directory within the same 'includes' folder.
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files - Adjust path if needed
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// --- PHPMailer Configuration ---
// IMPORTANT: Ensure these are correctly configured for your environment.
// Using distinct constants in case settings differ from user/tailor removal emails.
define('SMTP_DESIGN_HOST', 'smtp.gmail.com');          // SMTP server
define('SMTP_DESIGN_USERNAME', 'threadhub104@gmail.com');  // SMTP username (your email address)
define('SMTP_DESIGN_PASSWORD', 'ecplcvvdopquribv');       // SMTP password (App Password recommended)
define('SMTP_DESIGN_PORT', 587);                       // TCP port (587 for TLS)
define('SMTP_DESIGN_SECURE', PHPMailer::ENCRYPTION_STARTTLS); // Enable TLS encryption
define('EMAIL_DESIGN_FROM', 'threadhub104@gmail.com'); // Sender's Email
define('EMAIL_DESIGN_FROM_NAME', 'ThreadHub Admin'); // Sender's Name


/**
 * Sends a design removal notification email to the tailor.
 *
 * @param string $toEmail The recipient tailor's email address.
 * @param string $tailorName The recipient tailor's name.
 * @param string $designTitle The title of the removed design.
 * @param string $reason The reason for removal provided by the admin.
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendDesignRemovalEmail($toEmail, $tailorName, $designTitle, $reason) {
    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for debugging
        $mail->isSMTP();
        $mail->Host       = SMTP_DESIGN_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_DESIGN_USERNAME;
        $mail->Password   = SMTP_DESIGN_PASSWORD;
        $mail->SMTPSecure = SMTP_DESIGN_SECURE;
        $mail->Port       = SMTP_DESIGN_PORT;

        // Recipients
        $mail->setFrom(EMAIL_DESIGN_FROM, EMAIL_DESIGN_FROM_NAME);
        $mail->addAddress($toEmail, $tailorName);     // Add tailor recipient

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Notification: Your Design Has Been Removed from ThreadHub';

        // Construct HTML email body
        $message_body = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Design Removed</title>
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
                <p>This email is to inform you that your design, <strong>\"" . htmlspecialchars($designTitle) . "\"</strong>, has been removed from the ThreadHub platform by an administrator.</p>";

        if (!empty($reason)) {
            $message_body .= "<div class='reason-box'><p><strong>Reason provided for removal:</strong></p><p>" . nl2br(htmlspecialchars($reason)) . "</p></div>";
        } else {
            $message_body .= "<p>No specific reason was provided for the removal.</p>";
        }

        $message_body .= "<p>If you have any questions or believe this was done in error, please contact the ThreadHub administration or support team.</p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p>Sincerely,<br>The ThreadHub Admin Team</p>
            </div>
        </body>
        </html>";

        $mail->Body = $message_body;

        // Plain text alternative
        $alt_message_body = "Dear " . $tailorName . ",\n\n";
        $alt_message_body .= "This email is to inform you that your design, \"" . $designTitle . "\", has been removed from the ThreadHub platform by an administrator.\n\n";
        if (!empty($reason)) {
            $alt_message_body .= "Reason provided for removal:\n" . $reason . "\n\n";
        } else {
             $alt_message_body .= "No specific reason was provided for the removal.\n\n";
        }
        $alt_message_body .= "If you have any questions or believe this was done in error, please contact the ThreadHub administration or support team.\n\n";
        $alt_message_body .= "Sincerely,\nThe ThreadHub Admin Team";
        $mail->AltBody = $alt_message_body;


        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the detailed error
        error_log("Design removal email could not be sent to " . $toEmail . ". Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>