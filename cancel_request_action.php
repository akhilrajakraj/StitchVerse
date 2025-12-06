
<?php
session_start();
require 'databasecon.php';

// Import PHPMailer classes, just like in your reset password script
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Require the PHPMailer source files
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// --- Step 1: Security and Input Validation ---

// Ensure an admin is logged in before proceeding
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    // Set an error message and redirect to a safe page
    $_SESSION['action_error'] = "You are not authorized to perform this action.";
    header("Location: login.php");
    exit();
}

// Check if the form was submitted correctly
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Get data from the cancellation form (from view_request_details.php)
    $sdid = $_POST['sdid'];
    $customer_email = filter_var($_POST['customer_email'], FILTER_VALIDATE_EMAIL);
    $tailor_email = filter_var($_POST['tailor_email'], FILTER_VALIDATE_EMAIL); // Can be empty
    $request_name = htmlspecialchars($_POST['request_name']);
    $reason = htmlspecialchars($_POST['reason']);
    
    // Create the redirect URL to always have a fallback location
    $redirect_url = "view_request_details.php?id=" . ($sdid ?: '');

    // Make sure we have the essential information
    if (!$sdid || !$customer_email || empty($reason)) {
        $_SESSION['action_error'] = "Missing required information to cancel the request.";
        header("Location: " . $redirect_url); // Redirect back to the details page with an error
        exit();
    }

    $db = new DatabaseCon();

    // --- Step 2: Update the Database ---

    // Prepare and execute the query to change the request status
    $update_query = "UPDATE stitchreq SET sstatus = 'Cancelled' WHERE sdid = ?";
    $db->executeQuery($update_query, "i", $sdid);

    // --- Step 3: Send Notification Emails ---

    // This function configures and sends emails using your exact settings
    function sendCancellationEmail($recipient_email, $request_id, $request_name, $reason) {
        if (empty($recipient_email)) {
            return false; // Don't try to send an email if the address is missing
        }
        
        $mail = new PHPMailer(true);

        try {
            // Server settings - COPIED DIRECTLY FROM YOUR send_reset_link.php
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'stitchverrse@gmail.com';
            $mail->Password   = 'cqdaqntjinoeclpr';       // Your Google App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Admin');
            $mail->addAddress($recipient_email);

            // Email Content
            $mail->isHTML(true);
            $mail->Subject = "StitchVerse Request Cancelled: #{$request_id}";
            
            // A clear, helpful email body for your users
            $mail->Body    = "
                <html>
                <body style='font-family: Arial, sans-serif; color: #333;'>
                    <div style='max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;'>
                        <h2 style='color: #7c3aed; text-align: center;'>StitchVerse</h2>
                        <h3>Request Update: '{$request_name}' (#{$request_id}) has been Cancelled</h3>
                        <p>Hello,</p>
                        <p>This is an automated notification to inform you that the stitch request <strong>'{$request_name}'</strong> (ID: #{$request_id}) has been cancelled by our admin team.</p>
                        <hr>
                        <h4>Reason for Cancellation:</h4>
                        <div style='padding: 12px; background-color: #fef2f2; border-left: 4px solid #ef4444; color: #52525b;'>
                            <p style='margin: 0;'><em>" . nl2br($reason) . "</em></p>
                        </div>
                        <p>We apologize for any inconvenience this may cause. If you have any questions, please contact our support.</p>
                        <br>
                        <p>Thank you,</p>
                        <p><strong>The StitchVerse Team</strong></p>
                    </div>
                </body>
                </html>";
            
            $mail->AltBody = "Your StitchVerse request #{$request_id} ('{$request_name}') has been cancelled. Reason provided: {$reason}";

            $mail->send();
            return true;
        } catch (Exception $e) {
            // Log the error for your own review, don't show it to the admin
            error_log("Cancellation Email Error for {$recipient_email}: {$mail->ErrorInfo}");
            return false;
        }
    }

    // Send the email to the customer
    sendCancellationEmail($customer_email, $sdid, $request_name, $reason);

    // IMPORTANT: Only send to the tailor if one was assigned
    if ($tailor_email) {
        sendCancellationEmail($tailor_email, $sdid, $request_name, $reason);
    }

    // --- Step 4: Redirect with a Confirmation Message ---

    // CORRECTED: Use 'action_success' to trigger the new toast notification
    $_SESSION['action_success'] = "Request #{$sdid} was successfully cancelled. Notifications sent.";
    
    // Redirect the admin back to the details page to see the status change
    header("Location: " . $redirect_url);
    exit();

} else {
    // Redirect if someone tries to access this file directly
    header("Location: index.php");
    exit();
}
?>
