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

    $design_id = (int)$_POST['did'];
    $tailor_email = $_POST['tailor_email'];
    $design_name = $_POST['design_name']; // Get the design name from the hidden input
    $reason = htmlspecialchars($_POST['reason']);

    // --- Send Notification Email First ---
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
        $mail->addAddress($tailor_email);
        $mail->isHTML(true);
        $mail->Subject = "Regarding Your Design on StitchVerse: " . $design_name;
        $mail->Body    = "
            <html><body>
                <h2>Regarding Your Design: '" . htmlspecialchars($design_name) . "'</h2>
                <p>This email is to inform you that your design has been removed from the StitchVerse platform by an administrator.</p>
                <p><b>Reason provided for removal:</b></p>
                <div style='padding: 15px; border: 1px solid #ddd; background-color: #f9f9f9; border-radius: 5px;'>" 
                    . nl2br($reason) . 
                "</div>
                <p>If you have questions, please contact our support team.</p>
                <p>Regards,<br>The StitchVerse Team</p>
            </body></html>";
        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error on design deletion for ID $design_id: {$mail->ErrorInfo}");
    }

    // --- Delete the design and associated orders in a Transaction ---
    $conn->begin_transaction();
    try {
        $conn->prepare("DELETE FROM orderdesign WHERE did = ?")->execute([$design_id]);
        $conn->prepare("DELETE FROM upload WHERE did = ?")->execute([$design_id]);
        
        $conn->commit();
        // --- NEW, DESCRIPTIVE SUCCESS MESSAGE ---
        $_SESSION['action_success'] = "The design '".htmlspecialchars($design_name)."' was deleted and the tailor was notified.";
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $_SESSION['action_error'] = "Error: Could not delete design.";
    }

    header("Location: managedesigns.php");
    exit();
}