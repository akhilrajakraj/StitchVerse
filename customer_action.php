<?php
session_start();
require_once 'databasecon.php';
require_once 'mailer2.php'; // We use the central mailer

// Security Check for Admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php?error=unauthorized");
    exit();
}

$db = new DatabaseCon();
$action = $_POST['action'] ?? null;

// --- ACTION: REMOVE CUSTOMER WITH REASON ---
if ($action === 'remove_with_reason') {
    $cid = $_POST['cid'];
    $cname = $_POST['cname'];
    $cemail = $_POST['cemail'];
    $reason = $_POST['reason'];
    $custom_message = !empty($_POST['custom_message']) ? $_POST['custom_message'] : 'No additional details were provided.';

    // 1. Update the customer's login status to prevent access (non-destructive)
    $update_login_sql = "UPDATE login SET utype = 'removed_customer' WHERE uid = ? AND utype = 'customer'";
    $db->executeQuery($update_login_sql, "i", $cid);

    // 2. Send an email notification
    $subject = "Important Notification Regarding Your StitchVerse Account";
    $body = get_customer_removal_email_template($cname, $reason, $custom_message);
    $email_sent = sendStitchverseEmail($cemail, $cname, $subject, $body);

    if ($email_sent) {
        $_SESSION['action_success'] = "Successfully removed customer '{$cname}' and sent a notification email.";
    } else {
        $_SESSION['action_error'] = "Customer account for '{$cname}' was removed, but the notification email could not be sent.";
    }

    header("Location: activecustomers.php");
    exit();
}

// Redirect if the action is not recognized
header("Location: activecustomers.php");
exit();