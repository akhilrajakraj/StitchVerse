
<?php
session_start();
require 'databasecon.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// Security Check: Ensure an admin is logged in.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $db = new DatabaseCon();
    $conn = $db->getConnection();

    // --- Collect Data from Modal Form ---
    $sdid = (int)$_POST['sdid'];
    $customer_email = $_POST['customer_email'];
    $tailor_email = $_POST['tailor_email'];
    $request_name = $_POST['request_name'];
    $reason = htmlspecialchars($_POST['reason']);
    
    // --- Send Notification Emails ---
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'stitchverrse@gmail.com';
        $mail->Password   = 'cqdaqntjinoeclpr';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('admin@stitchverse.com', 'StitchVerse Admin');
        $mail->isHTML(true);

        // Email to Customer
        $mail->addAddress($customer_email);
        $mail->Subject = "Important: Your Stitch Request (#$sdid) has been cancelled";
        $mail->Body    = "<html><body><h2>Regarding Your Stitch Request: '" . htmlspecialchars($request_name) . "'</h2><p>This email is to inform you that your stitch request (ID: #$sdid) has been cancelled by the site administrator.</p><p><b>Reason provided for cancellation:</b></p><div style='padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; border-radius: 5px;'>" . nl2br($reason) . "</div><p><b>If you have already paid for this order, your refund will be processed after a brief enquiry.</b></p><p>We apologize for any inconvenience.</p><p>Regards,<br>The StitchVerse Team</p></body></html>";
        $mail->send();

        // Email to Tailor (if one was assigned)
        if (!empty($tailor_email)) {
            $mail->clearAddresses();
            $mail->addAddress($tailor_email);
            $mail->Subject = "Notice: A Stitch Request (#$sdid) has been cancelled";
            $mail->Body    = "<html><body><h2>Notice of Cancellation for Request #" . $sdid . "</h2><p>This is to inform you that the stitch request for the item '<b>" . htmlspecialchars($request_name) . "</b>' has been cancelled by a site administrator.</p><p><b>Reason provided:</b> " . nl2br($reason) . "</p><p>No further action is required from you for this request.</p><p>Regards,<br>The StitchVerse Team</p></body></html>";
            $mail->send();
        }

    } catch (Exception $e) {
        error_log("Mailer Error on stitch request cancellation for ID $sdid: {$mail->ErrorInfo}");
    }

    // --- CORRECTED LOGIC: Update the request's status to 'Cancelled' ---
    // This preserves the record instead of deleting it permanently.
    try {
        $stmt = $conn->prepare("UPDATE stitchreq SET sstatus = 'Cancelled' WHERE sdid = ?");
        $stmt->bind_param("i", $sdid);
        $stmt->execute();
        
        $_SESSION['action_success'] = "Request (#$sdid) was cancelled and all parties were notified.";
    } catch (mysqli_sql_exception $exception) {
        $_SESSION['action_error'] = "Error: Could not cancel the request in the database.";
    }

    // --- CORRECTED LOGIC: Redirect back to the active requests page ---
    header("Location: activerequests.php");
    exit();
}
?>