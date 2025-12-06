
<?php
session_start();
require_once 'databasecon.php';

// PHPMailer Integration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';


// Security Check: Ensure a tailor is logged in.
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'tailor') {
    http_response_code(403);
    exit("Unauthorized access.");
}

$db = new DatabaseCon();
$tailor_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? null;

// --- ACTION: ACCEPT A GENERAL REQUEST ---
if ($action === 'accept_request') {
    if (!isset($_POST['sdid']) || !is_numeric($_POST['sdid'])) {
        header("Location: tailorreq.php");
        exit();
    }
    $sdid = $_POST['sdid'];

    $conn = $db->getConnection();
    $conn->begin_transaction();

    try {
        // Step 1: Lock the row and check if it's still available
        $verify_sql = "SELECT * FROM stitchreq WHERE sdid = ? AND tid = 0 AND sstatus = 'Pending' FOR UPDATE";
        $stmt = $conn->prepare($verify_sql);
        $stmt->bind_param("i", $sdid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // If available, update it with the current tailor's ID
            $update_sql = "UPDATE stitchreq SET tid = ?, sstatus = 'Accepted' WHERE sdid = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $tailor_id, $sdid);
            $update_stmt->execute();

            $conn->commit();

            // --- Fetch all details needed for emails ---
            $details_sql = "SELECT 
                                sr.sdname, 
                                c.cname AS customer_name, c.email AS customer_email, 
                                t.tname AS tailor_name, t.email AS tailor_email 
                            FROM stitchreq sr 
                            JOIN creg c ON sr.uid = c.cid 
                            JOIN treg t ON sr.tid = t.tid 
                            WHERE sr.sdid = ?";
            
            $details_stmt = $conn->prepare($details_sql);
            $details_stmt->bind_param("i", $sdid);
            $details_stmt->execute();
            $details_result = $details_stmt->get_result()->fetch_assoc();

            // --- Send Emails ---
            // Send the optional custom email from the tailor
            $custom_subject = trim($_POST['subject'] ?? '');
            $custom_message = trim($_POST['message'] ?? '');
            if (!empty($custom_message) && !empty($custom_subject)) {
                $custom_email_body = buildCustomMessageEmailForCustomer($details_result['customer_name'], $sdid, $custom_message);
                sendStitchverseEmail($details_result['customer_email'], $details_result['customer_name'], $custom_subject, $custom_email_body);
            }

            // Send standard notification to the Customer
            $customer_subject = "Great News! Your Stitching Request #{$sdid} has been Accepted";
            $customer_body = buildAcceptanceEmailForCustomer($details_result['customer_name'], $sdid, $details_result['tailor_name']);
            sendStitchverseEmail($details_result['customer_email'], $details_result['customer_name'], $customer_subject, $customer_body);

            // Send standard notification to the Tailor
            $tailor_subject = "You have accepted Stitching Request #{$sdid}";
            $tailor_body = buildAcceptanceEmailForTailor($details_result['tailor_name'], $sdid, $details_result['customer_name'], $details_result['sdname']);
            sendStitchverseEmail($details_result['tailor_email'], $details_result['tailor_name'], $tailor_subject, $tailor_body);
            
            $_SESSION['action_success'] = "Request #{$sdid} has been accepted! The customer is notified.";
            // --- MODIFIED: Changed redirect to tailorreq.php ---
            header("Location: tailorreq.php"); 
            exit();

        } else {
            $conn->rollback();
            $_SESSION['action_error'] = "This request is no longer available. It may have just been accepted.";
            header("Location: tailorreq.php");
            exit();
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error accepting request: " . $e->getMessage());
        $_SESSION['action_error'] = "A server error occurred. Please try again.";
        header("Location: tailorreq.php");
        exit();
    }
}

// Redirect if the action is not recognized
header("Location: tailorhome.php");
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
    $body .= "<p>We are happy to inform you that your stitching request (#" . htmlspecialchars($sdid) . ") has been accepted by the tailor, <strong>" . htmlspecialchars($tailor_name) . "</strong>.</p>";
    $body .= "<p>They will begin working on your custom order soon and will reach out if they have any questions. You can view the status of this request in your 'My Orders' section.</p>";
    $body .= "<p>Thank you for using StitchVerse!</p>";
    return wrapInEmailTemplate("Request Accepted!", $body);
}

function buildAcceptanceEmailForTailor($tailor_name, $sdid, $customer_name, $design_name) {
    $body = "<p>Hi " . htmlspecialchars($tailor_name) . ",</p>";
    $body .= "<p>This is a confirmation that you have successfully accepted stitching request (#" . htmlspecialchars($sdid) . ") for the item '<strong>" . htmlspecialchars($design_name) . "</strong>' from the customer, <strong>" . htmlspecialchars($customer_name) . "</strong>.</p>";
    $body .= "<p>The request has now been moved to your 'Pending Requests' list. Please keep the customer updated on your progress.</p>";
    $body .= "<p>Happy stitching!</p>";
    return wrapInEmailTemplate("Confirmation of Acceptance", $body);
}

function buildCustomMessageEmailForCustomer($customer_name, $sdid, $message) {
    $body = "<p>Hi " . htmlspecialchars($customer_name) . ",</p>";
    $body .= "<p>You have received a message from your tailor regarding your stitching request (#" . htmlspecialchars($sdid) . ").</p>";
    $body .= "<div style='background-color: #f9f9f9; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #7c3aed;'>" . nl2br(htmlspecialchars($message)) . "</div>";
    $body .= "<p>You can reply directly to the tailor if you have any questions.</p>";
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

