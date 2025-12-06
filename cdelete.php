<?php
session_start();
require 'databasecon.php';

// --- PHPMailer Integration ---
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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Customer ID provided.");
}

$db = new DatabaseCon();
$conn = $db->getConnection();
$customer_id_to_delete = (int)$_GET['id'];

// --- Step 1: Fetch the customer's details BEFORE deleting them ---
$sql_fetch = "SELECT cname, email FROM creg WHERE cid = ?";
$result_fetch = $db->selectData($sql_fetch, "i", $customer_id_to_delete);
if ($result_fetch && $result_fetch->num_rows === 1) {
    $customer = $result_fetch->fetch_assoc();
} else {
    // If customer doesn't exist, just redirect back with an error message
    $_SESSION['action_error'] = "Customer with ID $customer_id_to_delete not found.";
    header("Location: viewc.php");
    exit();
}

// --- Step 2: Send the notification email ---
$mail = new PHPMailer(true);
try {
    // Server settings
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'stitchverrse@gmail.com'; 
    $mail->Password   = 'cqdaqntjinoeclpr';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    //Recipients
    $mail->setFrom('admin@stitchverse.com', 'StitchVerse Admin');
    $mail->addAddress($customer['email'], $customer['cname']);

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Important: Your StitchVerse Account Has Been Removed';
    $mail->Body    = "
        <html><body>
            <h2>Account Removal Notification</h2>
            <p>Hello " . htmlspecialchars($customer['cname']) . ",</p>
            <p>This email is to inform you that your customer account with StitchVerse has been removed by the site administrator.</p>
            <p>This action is final and may be due to a violation of our terms of service or inactivity. All of your associated data has been permanently deleted from our servers.</p>
            <p>If you believe this is a mistake, please contact our support team.</p>
            <p>Thank you,<br>The StitchVerse Team</p>
        </body></html>";
    
    $mail->send();
} catch (Exception $e) {
    // Log the error, but continue with the deletion process
    error_log("Mailer Error on customer deletion for ID $customer_id_to_delete: {$mail->ErrorInfo}");
}

// --- Step 3: Use a Transaction to delete from the required tables ---
$conn->begin_transaction();
try {
    // 1. Delete from `creg` table
    $stmt1 = $conn->prepare("DELETE FROM creg WHERE cid = ?");
    $stmt1->bind_param("i", $customer_id_to_delete);
    $stmt1->execute();

    // 2. Delete from `login` table
    $stmt2 = $conn->prepare("DELETE FROM login WHERE uid = ? AND utype = 'customer'");
    $stmt2->bind_param("i", $customer_id_to_delete);
    $stmt2->execute();
    
    $conn->commit();
    $_SESSION['action_success'] = "Customer (ID: $customer_id_to_delete) has been removed and a notification email was sent.";

} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    $_SESSION['action_error'] = "Error: Could not delete customer. The database operation was rolled back.";
}

header("Location: viewc.php");
exit();
?>