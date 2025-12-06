
<?php
session_start();
require 'databasecon.php';

// PHPMailer Integration
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'includes/PHPMailer/src/Exception.php';
require 'includes/PHPMailer/src/PHPMailer.php';
require 'includes/PHPMailer/src/SMTP.php';

// --- 1. SECURITY AND INPUT VALIDATION ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'customer') {
    header("Location: login.php");
    exit();
}
// Expecting a POST request from the new modal form
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id']) || !is_numeric($_POST['order_id'])) {
    $_SESSION['action_error'] = "Invalid cancellation request.";
    header("Location: designorders.php");
    exit();
}

$db = new DatabaseCon();
$conn = $db->getConnection();
$order_id = (int)$_POST['order_id'];
$customer_id = $_SESSION['user_id'];

// --- 2. FETCH ORDER AND VALIDATE OWNERSHIP & CANCELLATION WINDOW ---
$sql_order = "SELECT 
                    o.oid, o.ostatus, o.ordered_at,
                    u.dname,
                    t.tname, t.email AS tailor_email,
                    c.cname, c.email AS customer_email
                FROM orderdesign o
                JOIN upload u ON o.did = u.did
                JOIN treg t ON u.uid = t.tid
                JOIN creg c ON o.uid = c.cid
                WHERE o.oid = ? AND o.uid = ?";
$result_order = $db->selectData($sql_order, "ii", $order_id, $customer_id);

if ($result_order->num_rows === 0) {
    $_SESSION['action_error'] = "Order not found.";
    header("Location: designorders.php");
    exit();
}
$order = $result_order->fetch_assoc();

// --- 3. ENFORCE CANCELLATION POLICY ---
$is_cancellable = !in_array(strtolower($order['ostatus']), ['paid', 'shipped', 'cancelled']);
$submitted_time = new DateTime($order['ordered_at']);
$current_time = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
$interval = $submitted_time->diff($current_time);
$hours_passed = ($interval->days * 24) + $interval->h;

if (!$is_cancellable || $hours_passed >= 12) {
    $_SESSION['action_error'] = "This order can no longer be cancelled.";
    header("Location: designorder_details.php?id=" . $order_id);
    exit();
}

// --- 4. PROCESS CANCELLATION REASON ---
$reason_preset = $_POST['reason_preset'] ?? 'Not provided';
$reason_text = trim($_POST['reason_text'] ?? '');
$final_reason = $reason_preset;
if ($reason_preset === 'Other' && !empty($reason_text)) {
    $final_reason = $reason_text;
} elseif ($reason_preset !== 'Other' && !empty($reason_text)) {
    $final_reason .= " (Details: " . $reason_text . ")";
}

// --- 5. UPDATE DATABASE ---
try {
    $sql_update = "UPDATE orderdesign SET ostatus = 'Cancelled' WHERE oid = ?";
    $stmt = $conn->prepare($sql_update);
    if ($stmt === false) { throw new Exception("SQL Prepare Error: " . $conn->error); }
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $stmt->close();
} catch (Exception $e) {
    error_log("Order cancellation failed: " . $e->getMessage());
    $_SESSION['action_error'] = "A technical issue occurred. Could not cancel the order.";
    header("Location: designorder_details.php?id=" . $order_id);
    exit();
}

// --- 6. SEND EMAIL NOTIFICATIONS ---
$email_data = [
    'order_id' => $order['oid'],
    'design_name' => $order['dname'],
    'customer_name' => $order['cname'],
    'tailor_name' => $order['tname'],
    'reason' => $final_reason
];

// Email to Customer
sendStitchverseEmail($order['customer_email'], $order['cname'], "Your Order #{$order['oid']} has been cancelled", buildCancellationEmail($email_data, 'customer'));
// Email to Tailor
sendStitchverseEmail($order['tailor_email'], $order['tname'], "Order #{$order['oid']} has been cancelled by the customer", buildCancellationEmail($email_data, 'tailor'));

// --- 7. REDIRECT WITH SUCCESS MESSAGE ---
$_SESSION['action_success'] = "Order #{$order_id} has been successfully cancelled.";
header("Location: designorder_details.php?id=" . $order_id);
exit();


// --- EMAIL FUNCTIONS ---
function sendStitchverseEmail($to_email, $to_name, $subject, $body) {
    // This function remains the same as your previous version.
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

function buildCancellationEmail($data, $recipient_type) {
    $current_year = date("Y");
    $reason_html = "<p style='padding: 12px; background-color: #fef2f2; border-left: 4px solid #ef4444; color: #52525b; margin-top: 16px;'><strong>Reason:</strong> " . htmlspecialchars($data['reason']) . "</p>";

    if ($recipient_type === 'customer') {
        $greeting = "Hi {$data['customer_name']},";
        $message = "As requested, your order for the design '{$data['design_name']}' has been successfully cancelled. No payment will be charged.";
        $button_text = "View My Orders";
    } else { // tailor
        $greeting = "Hi {$data['tailor_name']},";
        $message = "Please be advised that the customer, {$data['customer_name']}, has cancelled their order for your design '{$data['design_name']}'. No further action is required from your side for this order.";
        $button_text = "View Dashboard";
    }

    return <<<HTML
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Order Cancellation</title><style>body{font-family:Arial,sans-serif;margin:0;padding:0;background-color:#f9fafb;}.container{max-width:600px;margin:20px auto;background-color:#ffffff;border:1px solid #e5e7eb;border-radius:8px;}.header{background:linear-gradient(to right,#f97316,#ef4444);color:white;padding:24px;text-align:center;border-radius:8px 8px 0 0;}h1,h2,p{margin:0;}.content{padding:24px;}.footer{padding:24px;text-align:center;font-size:12px;color:#6b7280;}</style></head><body><div class="container">
<div class="header"><h1>Order Cancelled</h1></div>
<div class="content">
<h2 style="font-size:20px;color:#111827;margin-bottom:8px;">{$greeting}</h2>
<p style="color:#4b5563;margin-bottom:24px;line-height:1.6;">{$message}</p>
<p style="color:#4b5563;"><strong>Order ID:</strong> #{$data['order_id']}</p>
{$reason_html}
<div style="text-align:center;margin-top:32px;"><a href='#' style="display:inline-block;padding:12px 24px;background-color:#7c3aed;color:#ffffff;text-decoration:none;border-radius:6px;font-weight:bold;">{$button_text}</a></div>
</div><div class="footer"><p>&copy; {$current_year} StitchVerse. All Rights Reserved.</p></div></div></body></html>
HTML;
}
?>
