<?php
session_start();
require 'databasecon.php';

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader or include files manually
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// --- Security & Validation ---

// 1. Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    // Set an error message and redirect to login if not authorized
    $_SESSION['action_error'] = "You are not authorized to perform this action.";
    header("Location: login.php");
    exit();
}

// 2. Check if the form was submitted via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Redirect if accessed directly
    header("Location: adminhomepage.php");
    exit();
}

// 3. Validate and sanitize inputs
$recipient_email = filter_input(INPUT_POST, 'recipient_email', FILTER_VALIDATE_EMAIL);
$subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));
$request_id = filter_input(INPUT_POST, 'request_id', FILTER_VALIDATE_INT);

// Create the redirect URL to always have a fallback location
$redirect_url = "view_request_details.php?id=" . ($request_id ?: '');

if (!$recipient_email || empty($subject) || empty($message) || !$request_id) {
    $_SESSION['action_error'] = "Invalid data provided. Please fill out all fields.";
    header("Location: " . $redirect_url);
    exit();
}

// --- Email Sending Logic ---

$mail = new PHPMailer(true);

try {
    // --- Server settings (using the same config as your password reset) ---
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'stitchverrse@gmail.com'; // Your Gmail address
    $mail->Password   = 'cqdaqntjinoeclpr';       // Your Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // --- Recipients ---
    $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Admin');
    $mail->addAddress($recipient_email); // Add a recipient
    $mail->addReplyTo('support@stitchverse.com', 'StitchVerse Support'); // Admin's reply-to address

    // --- Content ---
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = $subject;

    // Create a professional HTML email body
    $mail->Body    = "
        <html lang='en'>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                <h2 style='color: #7c3aed; text-align: center;'>StitchVerse</h2>
                <h3 style='border-bottom: 1px solid #eee; padding-bottom: 10px;'>" . htmlspecialchars($subject) . "</h3>
                <p>Hello,</p>
                <p>This is a message from the StitchVerse administration regarding your request (ID: <strong>#" . htmlspecialchars($request_id) . "</strong>).</p>
                <div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                    " . nl2br(htmlspecialchars($message)) . "
                </div>
                <p>If you have any questions, please reply to this email.</p>
                <br>
                <p>Thank you,</p>
                <p><strong>The StitchVerse Team</strong></p>
            </div>
        </body>
        </html>";

    // Plain text version for non-HTML mail clients
    $mail->AltBody = "Hello,\n\nThis is a message from the StitchVerse administration regarding your request (ID: #" . htmlspecialchars($request_id) . "):\n\n" . htmlspecialchars($message) . "\n\nThank you,\nThe StitchVerse Team";

    $mail->send();
    $_SESSION['action_success'] = 'Email has been sent successfully!';

} catch (Exception $e) {
    // Set an error message and log the detailed error
    $_SESSION['action_error'] = "Message could not be sent. Please try again later.";
    error_log("Mailer Error from send_admin_email.php: {$mail->ErrorInfo}");
}

// Redirect back to the details page
header("Location: " . $redirect_url);
exit();
?>
