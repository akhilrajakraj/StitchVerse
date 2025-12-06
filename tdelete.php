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
    die("Invalid Tailor ID provided.");
}

$db = new DatabaseCon();
$conn = $db->getConnection();
$tailor_id_to_delete = (int)$_GET['id'];

// --- Step 1: Fetch the tailor's details BEFORE deleting them ---
$sql_fetch = "SELECT tname, email FROM treg WHERE tid = ?";
$result_fetch = $db->selectData($sql_fetch, "i", $tailor_id_to_delete);
if ($result_fetch && $result_fetch->num_rows === 1) {
    $tailor = $result_fetch->fetch_assoc();
} else {
    // If tailor doesn't exist, just redirect back with a message
    $_SESSION['action_error'] = "Tailor with ID $tailor_id_to_delete not found.";
    header("Location: viewt.php");
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
    $mail->addAddress($tailor['email'], $tailor['tname']);

    //Content
    $mail->isHTML(true);
    $mail->Subject = 'Important: Your StitchVerse Tailor Account Has Been Removed';
    $mail->Body    = "
        <html><body>
            <h2>Account Removal Notification</h2>
            <p>Hello " . htmlspecialchars($tailor['tname']) . ",</p>
            <p>This email is to inform you that your tailor account with StitchVerse has been removed by the site administrator.</p>
            <p>This action is final and may be due to a violation of our terms of service, inactivity, or as part of a platform audit. All of your associated data, including uploaded designs and personal information, has been permanently deleted from our servers.</p>
            <p>If you believe this is a mistake, please contact our support team.</p>
            <p>Thank you,<br>The StitchVerse Team</p>
        </body></html>";
    
    $mail->send();
} catch (Exception $e) {
    // Log the error, but continue with the deletion process
    error_log("Mailer Error on tailor deletion for ID $tailor_id_to_delete: {$mail->ErrorInfo}");
}

// --- Step 3: Use a Transaction to delete from all related tables ---
$conn->begin_transaction();
try {
    // Delete from `treg` table
    $stmt1 = $conn->prepare("DELETE FROM treg WHERE tid = ?");
    $stmt1->bind_param("i", $tailor_id_to_delete);
    $stmt1->execute();

    // Delete from `login` table
    $stmt2 = $conn->prepare("DELETE FROM login WHERE uid = ? AND utype = 'tailor'");
    $stmt2->bind_param("i", $tailor_id_to_delete);
    $stmt2->execute();

    // Delete from `upload` table (tailor's designs)
    $stmt3 = $conn->prepare("DELETE FROM upload WHERE uid = ?");
    $stmt3->bind_param("i", $tailor_id_to_delete);
    $stmt3->execute();

    // Delete from `feedb` table (feedback about this tailor)
    $stmt4 = $conn->prepare("DELETE FROM feedb WHERE tid = ?");
    $stmt4->bind_param("i", $tailor_id_to_delete);
    $stmt4->execute();
    
    $conn->commit();
    $_SESSION['action_success'] = "Tailor (ID: $tailor_id_to_delete) has been removed and a notification email was sent.";

} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    $_SESSION['action_error'] = "Error: Could not delete tailor. The database operation was rolled back.";
}

header("Location: viewt.php");
exit();
?>