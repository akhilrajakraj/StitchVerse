<?php
session_start();
require_once 'databasecon.php';

// --- PHPMailer Inclusion ---
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// --- Security Check for Admin ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    // If not an admin, redirect or show an error
    header("Location: login.php");
    exit();
}

$db = new DatabaseCon();

// --- NEW: LOGIC FOR REMOVING A TAILOR WITH REASON ---
if (isset($_POST['action']) && $_POST['action'] === 'remove_with_reason') {
    $tid = $_POST['tid'];
    $tname = $_POST['tname'];
    $temail = $_POST['temail'];
    $reason = $_POST['reason'];
    $custom_message = !empty($_POST['custom_message']) ? $_POST['custom_message'] : 'No additional details were provided.';

    // 1. Update the tailor's status in the database
    // This changes their status to 'Removed' instead of deleting, which is safer.
    $update_sql = "UPDATE treg SET status = 'Removed' WHERE tid = ?";
    $stmt = $db->executeQuery($update_sql, "i", $tid);

    // Also update their login status to prevent access
    $update_login_sql = "UPDATE login SET utype = 'removed_tailor' WHERE uid = ? AND utype = 'tailor'";
    $db->executeQuery($update_login_sql, "i", $tid);

    // 2. Send an email notification
    $mail = new PHPMailer(true);
    try {
        // Using the same SMTP settings from your send_reset_link.php
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stitchverrse@gmail.com'; 
        $mail->Password   = 'cqdaqntjinoeclpr';      
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('no-reply@stitchverse.com', 'StitchVerse Admin');
        $mail->addAddress($temail, $tname);

        $mail->isHTML(true);
        $mail->Subject = 'Important Notification Regarding Your StitchVerse Account';
        
        $mail->Body    = "
            <html><body>
                <h2>Your StitchVerse Account Status</h2>
                <p>Hi {$tname},</p>
                <p>This is an important notification regarding your tailor account on StitchVerse.</p>
                <p>After a review, your account has been removed from our active platform by an administrator. The reason provided is:</p>
                <p style='padding: 10px; background-color: #fef2f2; border-left: 4px solid #ef4444;'>
                    <strong>Reason:</strong> {$reason}<br>
                    <strong>Details:</strong> {$custom_message}
                </p>
                <p>This means your profile and designs will no longer be visible to customers, and you will not be able to log in. If you believe this is a mistake, please contact our support team.</p>
                <br>
                <p>Regards,</p>
                <p>The StitchVerse Team</p>
            </body></html>";

        $mail->send();

        $_SESSION['action_success'] = "Successfully removed the tailor and sent a notification email.";

    } catch (Exception $e) {
        // If email fails, still notify admin, but log the error
        error_log("Mailer Error while removing tailor {$tid}: {$mail->ErrorInfo}");
        $_SESSION['action_error'] = "Tailor status was updated, but the notification email could not be sent. Please check the logs.";
    }

    header("Location: approvedtailors.php");
    exit();
}

// You can add other actions here later (e.g., from your viewt.php page)
// such as approve, reject, etc.

?>