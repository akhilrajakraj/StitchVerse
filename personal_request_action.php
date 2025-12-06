<?php
session_start();
require_once 'databasecon.php';

// PHPMailer Integration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';


// --- Security & Validation ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    http_response_code(403);
    exit("Unauthorized access.");
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['sdid']) || !is_numeric($_POST['sdid'])) {
    header("Location: personalrequest.php");
    exit();
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];
$sdid = (int)$_POST['sdid'];
$action = $_POST['action'] ?? '';

// --- Fetch necessary details for actions and emails ---
$details_sql = "SELECT sr.sdname, c.cname AS customer_name, c.email AS customer_email, t.tname AS tailor_name 
                FROM stitchreq sr 
                JOIN creg c ON sr.uid = c.cid 
                JOIN treg t ON sr.tid = t.tid 
                WHERE sr.sdid = ? AND sr.tid = ?";
$details = $db->selectData($details_sql, "ii", $sdid, $tailor_id)->fetch_assoc();

if (!$details) {
    $_SESSION['action_error'] = "Request not found or you do not have permission to modify it.";
    header("Location: personalrequest.php");
    exit();
}

// --- ACTION: ACCEPT PERSONAL REQUEST ---
if ($action === 'accept_request') {
    $sql = "UPDATE stitchreq SET sstatus = 'Accepted' WHERE sdid = ? AND tid = ?";
    $db->executeQuery($sql, "ii", $sdid, $tailor_id);

    // Send optional custom message from tailor
    $custom_subject = trim($_POST['subject'] ?? '');
    $custom_message = trim($_POST['message'] ?? '');
    if (!empty($custom_message) && !empty($custom_subject)) {
        $email_body = buildCustomMessageEmailForCustomer($details['customer_name'], $sdid, $custom_message);
        sendStitchverseEmail($details['customer_email'], $details['customer_name'], $custom_subject, $email_body);
    }
    
    // Send standard acceptance notification
    $customer_subject = "Great News! Your Stitching Request #{$sdid} has been Accepted";
    $customer_body = buildAcceptanceEmailForCustomer($details['customer_name'], $sdid, $details['tailor_name']);
    sendStitchverseEmail($details['customer_email'], $details['customer_name'], $customer_subject, $customer_body);

    $_SESSION['action_success'] = "Request #{$sdid} has been successfully accepted!";

} 
// --- ACTION: REJECT PERSONAL REQUEST ---
elseif ($action === 'reject_request') {
    $rejection_reason = trim($_POST['rejection_reason'] ?? 'No reason provided.');
    
    $sql = "UPDATE stitchreq SET sstatus = 'Rejected' WHERE sdid = ? AND tid = ?";
    $db->executeQuery($sql, "ii", $sdid, $tailor_id);

    // Send rejection email
    $subject = "Update on your Stitching Request #{$sdid}";
    $body = buildRejectionEmailForCustomer($details['customer_name'], $sdid, $details['sdname'], $rejection_reason);
    sendStitchverseEmail($details['customer_email'], $details['customer_name'], $subject, $body);

    $_SESSION['action_success'] = "Request #{$sdid} has been rejected. The customer was notified.";
}

header("Location: personalrequest.php");
exit();


// --- EMAIL HELPER FUNCTIONS ---
function sendStitchverseEmail($to_email, $to_name, $subject, $body) {
    try {
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = 'smtp.gmail.com';
        $mailer->SMTPAuth = true;
        $mailer->Username = 'stitchverrse@gmail.com';
        $mailer->Password = 'cqdaqntjinoeclpr';
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = 587;
        $mailer->setFrom('no-reply@stitchverse.com', 'StitchVerse');
        $mailer->addAddress($to_email, $to_name);
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        $mailer->send();
    } catch (Exception $e) {
        error_log("Mailer Error for {$to_email}: " . $mailer->ErrorInfo);
    }
}

function buildAcceptanceEmailForCustomer($customer_name, $sdid, $tailor_name) {
    $body = "<p>Hi " . htmlspecialchars($customer_name) . ",</p>";
    $body .= "<p>We're happy to inform you that your stitching request (#" . htmlspecialchars($sdid) . ") has been accepted by the tailor, <strong>" . htmlspecialchars($tailor_name) . "</strong>.</p>";
    $body .= "<p>They will begin working on your order and will contact you with any questions. You can track the status in your account.</p>";
    return wrapInEmailTemplate("Request Accepted!", $body);
}

function buildRejectionEmailForCustomer($customer_name, $sdid, $request_name, $reason) {
    $body = "<p>Dear " . htmlspecialchars($customer_name) . ",</p>";
    $body .= "<p>We are writing to inform you about an update on your stitching request for '<strong>" . htmlspecialchars($request_name) . "</strong>' (#" . htmlspecialchars($sdid) . ").</p>";
    $body .= "<p>Unfortunately, the tailor has had to decline the request for the following reason:</p>";
    $body .= "<blockquote style='border-left: 4px solid #ccc; padding-left: 15px; margin: 20px; font-style: italic;'><p>" . nl2br(htmlspecialchars($reason)) . "</p></blockquote>";
    $body .= "<p>We apologize for any inconvenience. Please feel free to submit another request to a different tailor.</p>";
    return wrapInEmailTemplate("Update on Your Request", $body);
}

function buildCustomMessageEmailForCustomer($customer_name, $sdid, $message) {
    $body = "<p>Hi " . htmlspecialchars($customer_name) . ",</p>";
    $body .= "<p>You have a message from your tailor regarding your request (#" . htmlspecialchars($sdid) . "):</p>";
    $body .= "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #7c3aed;'>" . nl2br(htmlspecialchars($message)) . "</div>";
    return wrapInEmailTemplate("A Message from Your Tailor", $body);
}

function wrapInEmailTemplate($title, $content) {
    $year = date("Y");
    return <<<HTML
<!DOCTYPE html><html><head><style>body{font-family:Arial,sans-serif;}</style></head><body>
<div style="max-width:600px;margin:auto;padding:20px;border:1px solid #ddd;border-radius:10px;">
    <h2 style="color:#7c3aed;text-align:center;">StitchVerse</h2>
    <h3 style="border-bottom:1px solid #eee;padding-bottom:10px;">{$title}</h3>
    {$content}
    <p>Thank you,<br>The StitchVerse Team</p>
    <div style="text-align:center;font-size:12px;color:#aaa;margin-top:20px;border-top:1px solid #eee;padding-top:10px;">
        &copy; {$year} StitchVerse. All Rights Reserved.
    </div>
</div>
</body></html>
HTML;
}
?>
