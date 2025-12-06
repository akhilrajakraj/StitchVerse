<?php
/**
 * Email Sending Function using PHPMailer
 *
 * Include this file in your scripts where you need to send emails.
 * Make sure the PHPMailer library is correctly located relative to this file.
 */

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files - Adjust path if PHPMailer is installed elsewhere (e.g., via Composer)
// Assumes a 'PHPMailer' directory is in the same directory as this send_mail.php file.
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

/**
 * Sends a password reset email.
 *
 * @param string $toEmail The recipient's email address.
 * @param string $resetLink The unique password reset link.
 * @param string $username The recipient's name (optional, defaults to 'User').
 * @return bool True if the email was sent successfully, false otherwise.
 */
function sendResetEmail($toEmail, $resetLink, $username = 'User') {
    // Create a new PHPMailer instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        // --- Server settings ---
        // IMPORTANT: Configure these settings for your email provider.
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output for troubleshooting
        $mail->isSMTP();                               // Send using SMTP
        $mail->Host       = 'smtp.gmail.com';          // Set the SMTP server (e.g., smtp.gmail.com for Gmail)
        $mail->SMTPAuth   = true;                      // Enable SMTP authentication
        $mail->Username   = 'threadhub104@gmail.com';  // SMTP username (your email address) <--- CHANGE THIS
        $mail->Password   = 'ecplcvvdopquribv';       // SMTP password (use App Password for Gmail/Outlook) <--- CHANGE THIS
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                       // TCP port to connect to; use 587 for TLS, 465 for SSL

        // --- Recipients ---
        $mail->setFrom('threadhub104@gmail.com', 'ThreadHub Support'); // Sender's Email & Name <--- CHANGE THIS
        $mail->addAddress($toEmail, $username);                      // Add a recipient (Name is optional)
        // $mail->addReplyTo('info@example.com', 'Information');      // Optional: Reply-to address
        // $mail->addCC('cc@example.com');                            // Optional: CC address
        // $mail->addBCC('bcc@example.com');                          // Optional: BCC address

        // --- Content ---
        $mail->isHTML(true);                                         // Set email format to HTML
        $mail->Subject = 'Reset Your ThreadHub Password';
        // Improved HTML body for better email client compatibility
        $mail->Body    = "
        <!DOCTYPE html>
        <html lang='en'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset</title>
            <style>
                body { font-family: sans-serif; line-height: 1.6; color: #333; }
                .container { padding: 20px; max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 5px; }
                .button { display: inline-block; padding: 10px 20px; background-color: #6d28d9; color: #ffffff; text-decoration: none; border-radius: 5px; font-weight: bold; }
                .button:hover { background-color: #5b21b6; }
                p { margin-bottom: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <h3>Hello " . htmlspecialchars($username) . ",</h3>
                <p>We received a request to reset the password for your ThreadHub account associated with this email address.</p>
                <p>To reset your password, please click the button below. This link is valid for 15 minutes.</p>
                <p style='text-align: center;'>
                    <a href='" . htmlspecialchars($resetLink) . "' class='button' style='color: #ffffff;'>Reset Password</a>
                </p>
                <p>If you cannot click the button, copy and paste the following link into your browser:</p>
                <p><a href='" . htmlspecialchars($resetLink) . "' style='color: #6d28d9; word-break: break-all;'>" . htmlspecialchars($resetLink) . "</a></p>
                <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
                <p>If you did not request a password reset, please ignore this email. Your password will remain unchanged.</p>
                <p>Thank you,<br>The ThreadHub Team</p>
            </div>
        </body>
        </html>";
        // Optional: Plain text alternative for non-HTML mail clients
        $mail->AltBody = "Hello " . htmlspecialchars($username) . ",\n\nYou requested a password reset for your ThreadHub account.\n\nCopy and paste this link into your browser to reset your password (valid for 15 minutes):\n" . $resetLink . "\n\nIf you didn't request this, please ignore this email.\n\nThanks,\nThe ThreadHub Team";

        $mail->send();
        // echo 'Message has been sent'; // Uncomment for debugging
        return true;
    } catch (Exception $e) {
        // Log the error instead of echoing it directly in production
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}"; // Uncomment for debugging
        return false;
    }
}

// Example Usage (can be commented out or removed)
/*
$testEmail = 'test@example.com';
$testLink = 'http://localhost/threadhub/reset_password.php?token=test12345';
$testUsername = 'Test User';

if (sendResetEmail($testEmail, $testLink, $testUsername)) {
    echo "Test email sent successfully to $testEmail.";
} else {
    echo "Failed to send test email.";
}
*/
?>
