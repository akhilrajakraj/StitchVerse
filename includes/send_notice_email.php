<?php
/**
 * Backend script to send important notice emails to all users.
 * Triggered by the form submission from site_settings.php.
 */
session_start();

// --- Includes ---
require_once '../includes/db.php'; // Database connection

// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load PHPMailer files - Adjust path if necessary
// Assumes 'PHPMailer' is in the same 'includes' directory
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

// --- PHPMailer Configuration (Copied from send_email_remove.php) ---
// !! IMPORTANT: Ensure these are correctly configured for your email provider !!
define('NOTICE_SMTP_HOST', 'smtp.gmail.com');
define('NOTICE_SMTP_USERNAME', 'threadhub104@gmail.com');  // Your email
define('NOTICE_SMTP_PASSWORD', 'ecplcvvdopquribv');       // Your App Password
define('NOTICE_SMTP_PORT', 587);
define('NOTICE_SMTP_SECURE', PHPMailer::ENCRYPTION_STARTTLS);
define('NOTICE_EMAIL_FROM', 'threadhub104@gmail.com'); // Sender's Email
define('NOTICE_EMAIL_FROM_NAME', 'ThreadHub Announcements'); // Sender's Name

// --- Security Check: Verify Admin Status ---
$isAdmin = false;
if (isset($_SESSION['users_id'])) {
    if (isset($conn) && !$conn->connect_error) {
        $adminUserId = $_SESSION['users_id'];
        $checkRoleStmt = $conn->prepare("SELECT role FROM users WHERE users_id = ?");
        if ($checkRoleStmt) {
            $checkRoleStmt->bind_param("i", $adminUserId);
            $checkRoleStmt->execute();
            $checkRoleStmt->bind_result($dbUserRole);
            if ($checkRoleStmt->fetch() && $dbUserRole === 'admin') {
                $isAdmin = true;
            }
            $checkRoleStmt->close();
        } else {
             error_log("Sent Notice Email: Failed to prepare role check query: " . $conn->error);
        }
    } else {
         error_log("Sent Notice Email: Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }
}

if (!$isAdmin) {
    // If not admin, redirect back with an error
    header("Location: site_settings.php?msg=" . urlencode("Unauthorized access.") . "&type=error");
    exit();
}

// --- Process Form Submission ---
$message = '';
$message_type = 'error';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_notice') {

    $subject = trim(filter_input(INPUT_POST, 'notice_subject', FILTER_SANITIZE_STRING));
    $body_html = trim($_POST['notice_message']); // Allow HTML, sanitize output later if displayed

    if (empty($subject) || empty($body_html)) {
        $message = "Subject and message body cannot be empty.";
    } else {
        // --- Fetch User Emails ---
        $user_emails = [];
        // Fetch emails of all non-admin users (customers and tailors)
        $sql_users = "SELECT email FROM users WHERE role != 'admin'";
        $result_users = $conn->query($sql_users);

        if ($result_users && $result_users->num_rows > 0) {
            while ($row = $result_users->fetch_assoc()) {
                if (!empty($row['email']) && filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
                    $user_emails[] = $row['email'];
                }
            }
            $result_users->free();
        } else {
            $message = "Could not find any users to notify.";
            error_log("Sent Notice Email: Failed to fetch user emails or no users found. DB error: " . $conn->error);
        }

        // --- Send Emails if Users Found ---
        if (!empty($user_emails)) {
            $mail = new PHPMailer(true);
            $sent_count = 0;
            $failed_count = 0;

            try {
                // Server settings
                // $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable for detailed debugging
                $mail->isSMTP();
                $mail->Host       = NOTICE_SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = NOTICE_SMTP_USERNAME;
                $mail->Password   = NOTICE_SMTP_PASSWORD;
                $mail->SMTPSecure = NOTICE_SMTP_SECURE;
                $mail->Port       = NOTICE_SMTP_PORT;

                // Sender
                $mail->setFrom(NOTICE_EMAIL_FROM, NOTICE_EMAIL_FROM_NAME);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject; // Use subject from form
                $mail->Body    = $body_html; // Use HTML body from form
                // Optional: Create a plain text version from HTML
                $mail->AltBody = strip_tags(str_replace("<br>", "\n", $body_html));

                // Add all users as BCC recipients to protect privacy
                foreach ($user_emails as $email) {
                    $mail->addBCC($email);
                }

                if ($mail->send()) {
                     $sent_count = count($user_emails); // Assume all BCCs were attempted
                     $message = "Notice successfully sent to " . $sent_count . " users.";
                     $message_type = 'success';
                } else {
                    // PHPMailer might not throw an exception for all failures, check ErrorInfo
                    $message = "Failed to send notice. Please check the logs. Mailer Error: " . $mail->ErrorInfo;
                    error_log("Sent Notice Email: Mailer Error: {$mail->ErrorInfo}");
                }

            } catch (Exception $e) {
                $message = "An error occurred while sending the notice. Please check the error logs. Mailer Error: {$mail->ErrorInfo}";
                error_log("Sent Notice Email: Exception caught: {$e->getMessage()} | Mailer Error: {$mail->ErrorInfo}");
            }
        } // End if !empty($user_emails)

    } // End if subject/body not empty

} else {
    // Redirect if accessed directly or without correct action
    $message = "Invalid request.";
}

// Close DB connection
if (isset($conn) && $conn instanceof mysqli && $conn->ping()) {
    $conn->close();
}

// Redirect back to site_settings.php with the result message
header("Location: ../admin/settings.php?msg=" . urlencode($message) . "&type=" . $message_type);
exit();
?>