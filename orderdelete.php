<?php
session_start();
require 'databasecon.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $db = new DatabaseCon();
    $conn = $db->getConnection();

    // --- Collect Data from Modal Form ---
    $oid = (int)$_POST['oid'];
    $customer_email = $_POST['customer_email'];
    $tailor_email = $_POST['tailor_email'];
    $design_name = $_POST['design_name'];
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
        $mail->Subject = "Important: Your Design Order (#$oid) has been cancelled";
        $mail->Body    = "<html><body><h2>Regarding Your Order: '" . htmlspecialchars($design_name) . "'</h2><p>This email is to inform you that your order (ID: #$oid) has been cancelled by the site administrator.</p><p><b>Reason provided for cancellation:</b></p><div style='padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; border-radius: 5px;'>" . nl2br($reason) . "</div><p><b>If you have already paid for this order, your refund will be processed after a brief enquiry.</b></p><p>We apologize for any inconvenience.</p><p>Regards,<br>The StitchVerse Team</p></body></html>";
        $mail->send();

        // Email to Tailor
        if (!empty($tailor_email)) {
            $mail->clearAddresses();
            $mail->addAddress($tailor_email);
            $mail->Subject = "Notice: A Design Order (#$oid) has been cancelled";
            $mail->Body    = "<html><body><h2>Notice of Cancellation for Order #" . $oid . "</h2><p>This is to inform you that the customer order for your design '<b>" . htmlspecialchars($design_name) . "</b>' has been cancelled by a site administrator.</p><p><b>Reason provided:</b> " . nl2br($reason) . "</p><p>No further action is required from you for this order.</p><p>Regards,<br>The StitchVerse Team</p></body></html>";
            $mail->send();
        }

    } catch (Exception $e) {
        error_log("Mailer Error on design order cancellation for ID $oid: {$mail->ErrorInfo}");
    }

    // --- Delete the order and its payment record in a Transaction ---
    $conn->begin_transaction();
    try {
        $conn->prepare("DELETE FROM payment WHERE order_id = ?")->execute([$oid]);
        $conn->prepare("DELETE FROM orderdesign WHERE oid = ?")->execute([$oid]);
        $conn->commit();
        $_SESSION['action_success'] = "Order (#$oid) was cancelled and all parties were notified.";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['action_error'] = "Error: Could not delete the order.";
    }

    header("Location: manageorder.php");
    exit();
}
?>