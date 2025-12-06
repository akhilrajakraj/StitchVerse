<?php
session_start();
date_default_timezone_set('Asia/Kolkata');

// Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader or include paths manually
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- Sanitize and retrieve form data ---
    // Using filter_input for better security
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    // Basic validation
    if (empty($name) || empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($subject) || empty($message)) {
        // Handle validation errors, maybe redirect back with an error message
        $_SESSION['status_message'] = "Please fill out all fields correctly.";
        $_SESSION['status_type'] = "error"; // You can use this for styling the message
        header("Location: contact.php");
        exit();
    }

    $mail = new PHPMailer(true);

    try {
        // --- Server settings from your send_reset_link.php ---
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stitchverrse@gmail.com'; // Your Gmail address
        $mail->Password   = 'cqdaqntjinoeclpr';      // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // --- Recipients ---
        // The email is sent FROM your system email TO your support email
        $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Contact Form');
        $mail->addAddress('contact@stitchverse.com', 'StitchVerse Support'); // Where you want to receive the contact messages
        $mail->addReplyTo($email, $name); // So you can reply directly to the user

        // --- Content ---
        $mail->isHTML(true);
        $mail->Subject = 'New Contact Form Submission: ' . htmlspecialchars($subject);

        // --- Professional HTML Email Body ---
        $mail->Body    = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
                    h2 { color: #7c3aed; }
                    .field { margin-bottom: 10px; }
                    .label { font-weight: bold; color: #555; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2>New Inquiry from StitchVerse Website</h2>
                    <p>You have received a new message through the contact form.</p>
                    <hr>
                    <div class='field'>
                        <p class='label'>Name:</p>
                        <p>" . htmlspecialchars($name) . "</p>
                    </div>
                    <div class='field'>
                        <p class='label'>Email:</p>
                        <p>" . htmlspecialchars($email) . "</p>
                    </div>
                    <div class='field'>
                        <p class='label'>Subject:</p>
                        <p>" . htmlspecialchars($subject) . "</p>
                    </div>
                    <div class='field'>
                        <p class='label'>Message:</p>
                        <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    </div>
                </div>
            </body>
            </html>";
        
        $mail->AltBody = "You have a new message from: \nName: " . $name . "\nEmail: " . $email . "\nSubject: " . $subject . "\nMessage: " . $message;

        $mail->send();

        // --- Set success message and redirect ---
        $_SESSION['status_message'] = "Thank you for contacting us! We will get back to you shortly.";
        $_SESSION['status_type'] = "success";
        header("Location: contact.php");
        exit();

    } catch (Exception $e) {
        // --- Log error and set a generic error message for the user ---
        error_log("Mailer Error: {$mail->ErrorInfo}");
        $_SESSION['status_message'] = "Sorry, something went wrong and we couldn't send your message. Please try again later.";
        $_SESSION['status_type'] = "error";
        header("Location: contact.php");
        exit();
    }
} else {
    // If someone accesses the script directly without POST data, redirect them
    header("Location: contact.php");
    exit();
}
?>